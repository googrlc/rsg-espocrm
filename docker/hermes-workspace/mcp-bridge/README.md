# MCP Bridge

Lightweight reverse proxy that sits between Hermes Workspace and the Hermes Agent
dashboard, adding `/api/mcp` CRUD endpoints backed by `config.yaml`.

## Why

The current `nousresearch/hermes-agent:latest` (v0.13.0) dashboard does not expose
`/api/mcp`. The Workspace UI's `probeMcp()` call therefore returns `false` and the
MCP Servers page shows "Not available on this backend."

This bridge intercepts `/api/mcp*` requests and serves them from the shared
`config.yaml` on the `hermes-data` volume. All other dashboard traffic is proxied
unchanged to the real dashboard at `hermes-agent:9119`.

## Architecture

```
Workspace  →  mcp-bridge:9119  →  hermes-agent:9119
                ↓ (intercepts /api/mcp)
              config.yaml (hermes-data volume)
```

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `UPSTREAM_DASHBOARD_URL` | `http://hermes-agent:9119` | Real dashboard URL |
| `HERMES_CONFIG_PATH` | `/opt/data/config.yaml` | Path to shared config |
| `API_SERVER_KEY` | (empty) | Bearer token for auth |

## Removal

Once a future `hermes-agent` release ships native `/api/mcp`, remove the
`mcp-bridge` service from `docker-compose.yml` and point `HERMES_DASHBOARD_URL`
back to `http://hermes-agent:9119`.
