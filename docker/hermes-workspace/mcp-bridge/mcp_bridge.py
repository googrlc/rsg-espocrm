"""MCP API bridge for Hermes Agent dashboard.

Sits between Hermes Workspace and the Hermes Agent dashboard, adding
/api/mcp CRUD endpoints backed by config.yaml on a shared volume.
All other requests are proxied to the real dashboard unchanged.
"""

from __future__ import annotations

import logging
import os
from pathlib import Path
from typing import Any

import httpx
import yaml
from fastapi import FastAPI, Request, Response
from fastapi.responses import JSONResponse

log = logging.getLogger("mcp-bridge")
logging.basicConfig(level=logging.INFO, format="%(asctime)s %(name)s %(levelname)s %(message)s")

DASHBOARD_URL = os.environ.get("UPSTREAM_DASHBOARD_URL", "http://hermes-agent:9119")
CONFIG_PATH = Path(os.environ.get("HERMES_CONFIG_PATH", "/opt/data/config.yaml"))
AUTH_TOKEN = os.environ.get("API_SERVER_KEY", "")
KNOWN_CATEGORIES = ["All", "Connected", "Failed", "Disabled"]

app = FastAPI(title="MCP Bridge", docs_url=None, redoc_url=None)


# ---------------------------------------------------------------------------
# Auth
# ---------------------------------------------------------------------------

def _check_auth(request: Request) -> bool:
    if not AUTH_TOKEN:
        return True
    auth = request.headers.get("authorization", "")
    if auth.lower().startswith("bearer "):
        return auth[7:].strip() == AUTH_TOKEN
    return False


# ---------------------------------------------------------------------------
# Config helpers
# ---------------------------------------------------------------------------

def _read_config() -> dict[str, Any]:
    if not CONFIG_PATH.exists():
        return {}
    return yaml.safe_load(CONFIG_PATH.read_text()) or {}


def _write_config(config: dict[str, Any]) -> None:
    CONFIG_PATH.write_text(yaml.safe_dump(config, default_flow_style=False, sort_keys=False))


def _get_servers(config: dict[str, Any]) -> dict[str, Any]:
    raw = config.get("mcp_servers", {})
    return dict(raw) if isinstance(raw, dict) else {}


def _normalize(name: str, entry: Any) -> dict[str, Any]:
    if not isinstance(entry, dict):
        entry = {"command": str(entry)} if entry else {}
    enabled = entry.get("enabled", True) if "enabled" in entry else not entry.get("disabled", False)
    transport = entry.get("transport", "stdio" if entry.get("command") else "sse")
    return {
        "id": name,
        "name": name,
        "enabled": enabled,
        "command": entry.get("command"),
        "args": entry.get("args", []),
        "env": entry.get("env", {}),
        "url": entry.get("url"),
        "headers": entry.get("headers", {}),
        "transportType": transport,
        "status": "unknown",
    }


def _to_entry(body: dict[str, Any]) -> dict[str, Any]:
    out: dict[str, Any] = {}
    if body.get("transportType"):
        out["transport"] = body["transportType"]
    for key in ("command", "args", "env", "url", "headers"):
        if body.get(key):
            out[key] = body[key]
    if "enabled" in body:
        out["enabled"] = body["enabled"]
    if body.get("authType") and body["authType"] != "none":
        auth: dict[str, Any] = {"type": body["authType"]}
        if body.get("bearerToken"):
            auth["token"] = body["bearerToken"]
        if body.get("oauth"):
            auth["oauth"] = body["oauth"]
        out["auth"] = auth
    if body.get("toolMode") and body["toolMode"] != "all":
        out["tool_mode"] = body["toolMode"]
    if body.get("includeTools"):
        out["include_tools"] = body["includeTools"]
    if body.get("excludeTools"):
        out["exclude_tools"] = body["excludeTools"]
    return out


# ---------------------------------------------------------------------------
# /api/mcp CRUD
# ---------------------------------------------------------------------------

@app.get("/api/mcp")
async def list_mcp(request: Request):
    if not _check_auth(request):
        return JSONResponse({"ok": False, "error": "Unauthorized"}, status_code=401)
    config = _read_config()
    servers = _get_servers(config)
    items = [_normalize(n, e) for n, e in servers.items() if e is not None]
    return JSONResponse({"servers": items, "total": len(items), "categories": KNOWN_CATEGORIES})


@app.post("/api/mcp")
async def add_mcp(request: Request):
    if not _check_auth(request):
        return JSONResponse({"ok": False, "error": "Unauthorized"}, status_code=401)
    body = await request.json()
    name = (body.get("name") or "").strip()
    if not name:
        return JSONResponse({"ok": False, "error": "Missing server name"}, status_code=400)
    config = _read_config()
    if "mcp_servers" not in config:
        config["mcp_servers"] = {}
    entry = _to_entry(body)
    config["mcp_servers"][name] = entry
    _write_config(config)
    log.info("Added MCP server: %s", name)
    return JSONResponse({"ok": True, "server": _normalize(name, entry)})


@app.put("/api/mcp/{name}")
async def update_mcp(name: str, request: Request):
    if not _check_auth(request):
        return JSONResponse({"ok": False, "error": "Unauthorized"}, status_code=401)
    body = await request.json()
    config = _read_config()
    servers = _get_servers(config)
    if name not in servers:
        return JSONResponse({"ok": False, "error": f"MCP server not found: {name}"}, status_code=404)
    existing = servers[name] if isinstance(servers[name], dict) else {}
    for key in ("command", "args", "env", "url", "headers", "enabled"):
        if key in body:
            existing[key] = body[key]
    if "transportType" in body:
        existing["transport"] = body["transportType"]
    config["mcp_servers"][name] = existing
    _write_config(config)
    log.info("Updated MCP server: %s", name)
    return JSONResponse({"ok": True, "server": _normalize(name, existing)})


@app.delete("/api/mcp/{name}")
async def delete_mcp(name: str, request: Request):
    if not _check_auth(request):
        return JSONResponse({"ok": False, "error": "Unauthorized"}, status_code=401)
    config = _read_config()
    servers = _get_servers(config)
    if name not in servers:
        return JSONResponse({"ok": False, "error": f"MCP server not found: {name}"}, status_code=404)
    del config["mcp_servers"][name]
    _write_config(config)
    log.info("Deleted MCP server: %s", name)
    return JSONResponse({"ok": True})


@app.api_route("/api/mcp/test", methods=["POST"])
async def test_mcp(request: Request):
    return JSONResponse({
        "ok": False,
        "error": "Live MCP server testing requires a newer hermes-agent build. "
                 "Servers can still be configured and will be available at next agent restart.",
    }, status_code=501)


@app.api_route("/api/mcp/discover", methods=["POST"])
async def discover_mcp(request: Request):
    return JSONResponse({
        "ok": False,
        "error": "MCP discovery requires a newer hermes-agent build.",
    }, status_code=501)


@app.api_route("/api/mcp/presets", methods=["GET"])
async def mcp_presets(request: Request):
    return JSONResponse({"presets": [], "total": 0})


@app.api_route("/api/mcp/hub-sources", methods=["GET"])
async def mcp_hub_sources(request: Request):
    return JSONResponse({"sources": [], "total": 0})


@app.api_route("/api/mcp/hub-search", methods=["GET"])
async def mcp_hub_search(request: Request):
    return JSONResponse({"results": [], "total": 0})


@app.api_route("/api/mcp/configure", methods=["POST"])
async def mcp_configure(request: Request):
    return await add_mcp(request)


@app.get("/api/mcp/{name}/logs")
async def mcp_logs(name: str, request: Request):
    return JSONResponse({"logs": [], "name": name})


# ---------------------------------------------------------------------------
# Reverse proxy – everything else goes to the real dashboard
# ---------------------------------------------------------------------------

_client = httpx.AsyncClient(base_url=DASHBOARD_URL, timeout=30.0, follow_redirects=True)


@app.api_route("/{path:path}", methods=["GET", "POST", "PUT", "DELETE", "PATCH", "OPTIONS", "HEAD"])
async def proxy(path: str, request: Request):
    url = f"/{path}"
    if request.url.query:
        url += f"?{request.url.query}"
    headers = {k: v for k, v in request.headers.items() if k.lower() != "host"}
    try:
        resp = await _client.request(
            method=request.method,
            url=url,
            headers=headers,
            content=await request.body(),
        )
        skip = {"transfer-encoding", "content-encoding", "content-length"}
        resp_headers = {k: v for k, v in resp.headers.items() if k.lower() not in skip}
        return Response(content=resp.content, status_code=resp.status_code, headers=resp_headers)
    except httpx.ConnectError:
        return JSONResponse({"error": "Dashboard unreachable"}, status_code=502)


@app.get("/")
async def root(request: Request):
    return await proxy("", request)
