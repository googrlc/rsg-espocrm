"""Standalone MCP bridge for Hermes, deployable to Railway.

This service keeps MCP server entries in a YAML config file and exposes:
- /mcp (alias for list/probe)
- /api/mcp (Hermes dashboard compatibility)
- /healthz (liveness)
"""

from __future__ import annotations

import logging
import os
import urllib.error
import urllib.request
from pathlib import Path
from typing import Any

import yaml
from fastapi import FastAPI, Request
from fastapi.responses import JSONResponse

log = logging.getLogger("hermes-mcp-bridge")
logging.basicConfig(level=logging.INFO, format="%(asctime)s %(name)s %(levelname)s %(message)s")

CONFIG_PATH = Path(os.environ.get("HERMES_CONFIG_PATH", "/data/config.yaml"))
AUTH_TOKEN = os.environ.get("API_SERVER_KEY", "")
KNOWN_CATEGORIES = ["All", "Connected", "Failed", "Disabled"]
MCP_PROTOCOL_VERSION = "2024-11-05"
SERVER_NAME = "rsg-espocrm-mcp-bridge"
SERVER_VERSION = "1.0.0"

app = FastAPI(title="Hermes MCP Bridge", docs_url=None, redoc_url=None)


def _check_auth(request: Request) -> bool:
    if not AUTH_TOKEN:
        return True
    auth = request.headers.get("authorization", "")
    if auth.lower().startswith("bearer "):
        return auth[7:].strip() == AUTH_TOKEN
    return False


def _read_config() -> dict[str, Any]:
    if not CONFIG_PATH.exists():
        return {}
    return yaml.safe_load(CONFIG_PATH.read_text()) or {}


def _write_config(config: dict[str, Any]) -> None:
    CONFIG_PATH.parent.mkdir(parents=True, exist_ok=True)
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


def _jsonrpc_result(request_id: Any, result: dict[str, Any]) -> JSONResponse:
    return JSONResponse({"jsonrpc": "2.0", "id": request_id, "result": result})


def _jsonrpc_error(request_id: Any, code: int, message: str) -> JSONResponse:
    return JSONResponse(
        {
            "jsonrpc": "2.0",
            "id": request_id,
            "error": {"code": code, "message": message},
        }
    )


def _mcp_tools() -> list[dict[str, Any]]:
    return [
        {
            "name": "ping",
            "description": "Health check tool that confirms bridge availability.",
            "inputSchema": {
                "type": "object",
                "properties": {
                    "message": {"type": "string", "description": "Optional ping message."}
                },
                "additionalProperties": False,
            },
        },
        {
            "name": "espocrm_get_current_user",
            "description": "Fetch current EspoCRM API user via /api/v1/App/user.",
            "inputSchema": {"type": "object", "properties": {}, "additionalProperties": False},
        },
    ]


def _run_ping(arguments: dict[str, Any]) -> str:
    message = arguments.get("message", "pong")
    return f"Bridge reachable: {message}"


def _run_espocrm_get_current_user() -> str:
    base_url = os.environ.get("ESPOCRM_BASE_URL", "").strip().rstrip("/")
    api_key = os.environ.get("ESPOCRM_API_KEY", "").strip()
    if not base_url:
        return "Missing ESPOCRM_BASE_URL."
    if not api_key:
        return "Missing ESPOCRM_API_KEY."

    req = urllib.request.Request(
        url=f"{base_url}/api/v1/App/user",
        headers={"X-Api-Key": api_key, "Accept": "application/json"},
        method="GET",
    )
    try:
        with urllib.request.urlopen(req, timeout=20) as response:
            return response.read().decode("utf-8")
    except urllib.error.HTTPError as exc:
        return f"EspoCRM request failed with HTTP {exc.code}."
    except urllib.error.URLError as exc:
        return f"EspoCRM request failed: {exc.reason}"


def _discover_payload() -> dict[str, Any]:
    tools = _mcp_tools()
    return {
        "name": SERVER_NAME,
        "description": "RSG EspoCRM MCP bridge",
        "version": SERVER_VERSION,
        "protocolVersion": MCP_PROTOCOL_VERSION,
        "capabilities": {"tools": {"listChanged": False}},
        "serverInfo": {"name": SERVER_NAME, "version": SERVER_VERSION},
        "tools": tools,
    }


@app.get("/healthz")
async def healthz() -> JSONResponse:
    return JSONResponse({"ok": True})


@app.get("/mcp")
async def list_mcp_via_short_path(request: Request):
    if not _check_auth(request):
        return JSONResponse({"ok": False, "error": "Unauthorized"}, status_code=401)
    return JSONResponse(_discover_payload())


@app.get("/mcp/discover")
async def mcp_discover_get(request: Request):
    if not _check_auth(request):
        return JSONResponse({"ok": False, "error": "Unauthorized"}, status_code=401)
    return JSONResponse(_discover_payload())


@app.post("/mcp/discover")
async def mcp_discover_post(request: Request):
    return await mcp_discover_get(request)


@app.post("/mcp")
async def mcp_jsonrpc(request: Request):
    if not _check_auth(request):
        return _jsonrpc_error(None, -32001, "Unauthorized")

    try:
        payload = await request.json()
    except Exception:
        return _jsonrpc_error(None, -32700, "Invalid JSON payload")

    request_id = payload.get("id")
    method = payload.get("method")
    params = payload.get("params") or {}

    if not method:
        return _jsonrpc_error(request_id, -32600, "Missing method")

    if method == "initialize":
        requested_version = params.get("protocolVersion", MCP_PROTOCOL_VERSION)
        return _jsonrpc_result(
            request_id,
            {
                "protocolVersion": requested_version,
                "capabilities": {"tools": {"listChanged": False}},
                "serverInfo": {"name": SERVER_NAME, "version": SERVER_VERSION},
            },
        )

    if method == "notifications/initialized":
        return JSONResponse(status_code=202, content=None)

    if method == "tools/list":
        return _jsonrpc_result(request_id, {"tools": _mcp_tools()})

    # Compatibility alias for clients that probe with a non-standard discover method.
    if method == "discover":
        return _jsonrpc_result(request_id, _discover_payload())

    if method == "tools/call":
        tool_name = params.get("name")
        arguments = params.get("arguments") or {}

        if tool_name == "ping":
            text = _run_ping(arguments)
            return _jsonrpc_result(request_id, {"content": [{"type": "text", "text": text}]})

        if tool_name == "espocrm_get_current_user":
            text = _run_espocrm_get_current_user()
            return _jsonrpc_result(request_id, {"content": [{"type": "text", "text": text}]})

        return _jsonrpc_error(request_id, -32602, f"Unknown tool: {tool_name}")

    return _jsonrpc_error(request_id, -32601, f"Method not found: {method}")


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
    if not _check_auth(request):
        return JSONResponse({"ok": False, "error": "Unauthorized"}, status_code=401)
    return JSONResponse(
        {
            "ok": False,
            "error": "Live MCP server testing requires a newer hermes-agent build.",
        },
        status_code=501,
    )


@app.api_route("/api/mcp/discover", methods=["POST"])
async def discover_mcp(request: Request):
    if not _check_auth(request):
        return JSONResponse({"ok": False, "error": "Unauthorized"}, status_code=401)
    return JSONResponse(
        {
            "ok": False,
            "error": "MCP discovery requires a newer hermes-agent build.",
        },
        status_code=501,
    )


@app.api_route("/api/mcp/presets", methods=["GET"])
async def mcp_presets(request: Request):
    if not _check_auth(request):
        return JSONResponse({"ok": False, "error": "Unauthorized"}, status_code=401)
    return JSONResponse({"presets": [], "total": 0})


@app.api_route("/api/mcp/hub-sources", methods=["GET"])
async def mcp_hub_sources(request: Request):
    if not _check_auth(request):
        return JSONResponse({"ok": False, "error": "Unauthorized"}, status_code=401)
    return JSONResponse({"sources": [], "total": 0})


@app.api_route("/api/mcp/hub-search", methods=["GET"])
async def mcp_hub_search(request: Request):
    if not _check_auth(request):
        return JSONResponse({"ok": False, "error": "Unauthorized"}, status_code=401)
    return JSONResponse({"results": [], "total": 0})


@app.api_route("/api/mcp/configure", methods=["POST"])
async def mcp_configure(request: Request):
    return await add_mcp(request)


@app.get("/api/mcp/{name}/logs")
async def mcp_logs(name: str, request: Request):
    if not _check_auth(request):
        return JSONResponse({"ok": False, "error": "Unauthorized"}, status_code=401)
    return JSONResponse({"logs": [], "name": name})
