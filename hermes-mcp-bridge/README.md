# Hermes MCP Bridge

Standalone MCP bridge service for Hermes configuration flows.

This directory contains the bridge source (`app.py`, `Dockerfile`,
`requirements.txt`). The bridge is deployed as the **`espo-mcp` sidecar**
inside the Hermes Docker Compose stack — not on Railway.

## Exposed Endpoints

- `GET /healthz` - health check
- `POST /mcp` - MCP JSON-RPC endpoint (use this as `MCP_URL` in Hermes)
- `GET /mcp` - MCP discovery/probe path
- `GET /api/mcp` - Hermes dashboard-compatible MCP list
- `POST /api/mcp`, `PUT /api/mcp/{name}`, `DELETE /api/mcp/{name}`

## Required Env Vars

- `API_SERVER_KEY` - bearer token expected by the bridge (optional but recommended)
- `HERMES_CONFIG_PATH` - where `config.yaml` is persisted (default `/data/config.yaml`)
- `ESPOCRM_BASE_URL` - required for `espocrm_get_current_user` tool
- `ESPOCRM_API_KEY` - required for `espocrm_api_key` tool

## Persistence

`/api/mcp` writes MCP server definitions into `config.yaml`. Mount a
volume at the path set by `HERMES_CONFIG_PATH` so definitions survive
container restarts.

## Hermes Value

Set in Hermes:

```
MCP_URL=https://<espo-mcp-domain>/mcp
```
