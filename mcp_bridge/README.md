# MCP Bridge

Standalone, **model-agnostic** MCP bridge service. Any agent (Claude, Hermes, or
other) can connect — the bridge bakes in no specific LLM.

This directory contains the bridge source (`app.py`, `Dockerfile`,
`requirements.txt`). It is deployed as the **`espo-mcp` sidecar** inside the
Docker Compose stack.

## Design: read + propose only — never a live write

The bridge exposes **read** and **propose** tools only — it has **no live-write
tool by design**. Agents propose changes to a Supabase staging table
(`crm_change_proposals`, status `pending`); a human approves; a separate
privileged worker (`../automation_workers/deterministic_committer.py`) applies
approved rows to EspoCRM with correct per-entity casing and post-write read-back.
See `../COMMISSION-WORKSPACE-DESIGN.md`.

## MCP Tools

| Tool | Kind | Backend | What it does |
|---|---|---|---|
| `ping` | health | — | bridge reachability |
| `espocrm_get_current_user` | read | EspoCRM | `/App/user` identity check |
| `search_accounts` | read | EspoCRM | Accounts by name (bracket-encoded `where`) |
| `get_account` | read | EspoCRM | one Account by id |
| `propose_crm_change` | **propose** | Supabase | insert a row into `crm_change_proposals` (status `pending`) — never touches live records. Caller sets `proposedBy` to its own identity (default `agent`). |
| `query_commission_ledger` | read | Supabase | read canonical `commission_ledger` |
| `record_commission_parity` | write | Supabase | insert `commission_parity_report` rows (the cutover gate) |

## Exposed Endpoints

- `GET /healthz` - health check
- `POST /mcp` - MCP JSON-RPC endpoint (use this as `MCP_URL`)
- `GET /mcp` - MCP discovery/probe path
- `GET /api/mcp` - dashboard-compatible MCP list
- `POST /api/mcp`, `PUT /api/mcp/{name}`, `DELETE /api/mcp/{name}`

## Required Env Vars

- `API_SERVER_KEY` - bearer token expected by the bridge (**mandatory** for any
  public deployment — if unset, the endpoint is open).
- `MCP_BRIDGE_CONFIG_PATH` - where `config.yaml` is persisted (default
  `/data/config.yaml`). Falls back to the legacy `HERMES_CONFIG_PATH` if set.
- `ESPOCRM_BASE_URL` / `ESPOCRM_API_KEY` - required for the EspoCRM read tools
  (read-scoped is enough; the bridge never writes EspoCRM).
- `SUPABASE_URL` / `SUPABASE_SERVICE_KEY` - required for `propose_crm_change`,
  `query_commission_ledger`, `record_commission_parity`. Service-role key is
  server-side only (RLS is on; service_role bypasses it).

## Persistence

`/api/mcp` writes MCP server definitions into `config.yaml`. Mount a volume at
the path set by `MCP_BRIDGE_CONFIG_PATH` so definitions survive restarts.

## Connect an agent

```
MCP_URL=https://<espo-mcp-domain>/mcp
```
