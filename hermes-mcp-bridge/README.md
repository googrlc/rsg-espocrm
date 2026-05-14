# Hermes MCP Bridge (Railway)

Standalone MCP bridge service for Hermes configuration flows.

## Exposed Endpoints

- `GET /healthz` - health check
- `POST /mcp` - MCP JSON-RPC endpoint (use this in Hermes)
- `GET /mcp` - legacy probe/list path
- `GET /api/mcp` - Hermes dashboard-compatible MCP list
- `POST /api/mcp`, `PUT /api/mcp/{name}`, `DELETE /api/mcp/{name}`

## Required Env Vars

- `API_SERVER_KEY` - bearer token expected by the bridge (optional but recommended)
- `HERMES_CONFIG_PATH` - where `config.yaml` is persisted (default `/data/config.yaml`)
- `ESPOCRM_BASE_URL` - required for `espocrm_get_current_user` tool
- `ESPOCRM_API_KEY` - required for `espocrm_get_current_user` tool

## Persistence (Important)

`/api/mcp` writes MCP server definitions into `config.yaml`.
Without a persistent disk, this file is lost on rebuild/redeploy.

For Railway, mount a volume to `/data` and keep:

- `HERMES_CONFIG_PATH=/data/config.yaml`

## Railway Deploy

1. Create a new Railway service from this folder (`hermes-mcp-bridge`).
2. Add a Railway Volume and mount it at `/data`.
3. Ensure the service exposes a public domain.
4. Set env vars:
   - `API_SERVER_KEY=<strong-random-token>`
   - `HERMES_CONFIG_PATH=/data/config.yaml`
5. Redeploy.
6. Validate:
   - `GET https://<railway-domain>/healthz`
   - `GET https://<railway-domain>/mcp`

## Hermes Value

Use this in Hermes:

`MCP_URL=https://<railway-domain>/mcp`
