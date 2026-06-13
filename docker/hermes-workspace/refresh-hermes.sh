#!/usr/bin/env bash
# refresh-hermes.sh — deploy the espo-mcp bridge AND make the Hermes *agent*
# actually pick up the rsg-espocrm MCP server.
#
# Why this exists (the "stuck in the container" problem):
#   * The agent entrypoint only writes config.yaml when it is MISSING
#     (`if [ ! -f "$CFG" ]`), so a redeploy never updates an existing config.
#   * The agent reads config only at startup.
#   Result: new images/config sit on the volume but the RUNNING agent never sees
#   them. This script registers the MCP server into the shared config.yaml and
#   then FORCE-RECREATES the agent so it re-reads. Idempotent; safe to re-run.
#
# Run it ON the hermes host, from this directory:
#   API_SERVER_KEY=... ./refresh-hermes.sh
set -euo pipefail
cd "$(dirname "$0")"

MCP_URL_INTERNAL="${MCP_URL:-http://espo-mcp:8080/mcp}"
AUTH="${API_SERVER_KEY:-}"

echo "==> 1/4 build + (re)start espo-mcp"
docker compose up -d --build espo-mcp

echo "==> 2/4 wait for espo-mcp /healthz"
for _ in $(seq 1 20); do
  if docker compose exec -T espo-mcp python3 -c \
      "import urllib.request;urllib.request.urlopen('http://localhost:8080/healthz',timeout=3)" 2>/dev/null; then
    echo "    espo-mcp healthy"; break
  fi
  sleep 3
done

echo "==> 3/4 register rsg-espocrm into the agent config (idempotent, preserves model + other servers)"
# mcp-bridge shares the hermes-data volume and has python3+pyyaml.
docker compose exec -T mcp-bridge python3 - "$MCP_URL_INTERNAL" "$AUTH" <<'PY'
import os, sys, yaml
url, auth = sys.argv[1], sys.argv[2]
path = os.environ.get("HERMES_CONFIG_PATH", "/opt/data/config.yaml")
cfg = yaml.safe_load(open(path).read()) if os.path.exists(path) else {}
cfg = cfg or {}
servers = cfg.setdefault("mcp_servers", {})
entry = {"transport": "streamable-http", "url": url, "enabled": True}
if auth:
    entry["headers"] = {"Authorization": f"Bearer {auth}"}
existed = "rsg-espocrm" in servers
servers["rsg-espocrm"] = entry
os.makedirs(os.path.dirname(path), exist_ok=True)
open(path, "w").write(yaml.safe_dump(cfg, default_flow_style=False, sort_keys=False))
print(f"    {'updated' if existed else 'added'} rsg-espocrm -> {url}")
print(f"    config now lists mcp_servers: {list(servers)}")
PY

echo "==> 4/4 force-recreate the agent so it RE-READS config (this is the step usually skipped)"
docker compose up -d --force-recreate hermes-agent

echo "==> waiting for hermes-agent /health"
for _ in $(seq 1 30); do
  if docker compose exec -T hermes-agent curl -fsS http://localhost:8642/health >/dev/null 2>&1; then
    echo "    hermes-agent healthy"; break
  fi
  sleep 3
done

echo "==> done. Confirm the agent loaded it:"
echo "    docker compose logs --since 2m hermes-agent | grep -i mcp"
