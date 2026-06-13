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

## MCP Tools

The bridge exposes **read** and **propose** tools only — it has **no live-write
tool by design**. Agents propose changes to a Supabase staging table; a separate
privileged committer applies approved rows. See `../COMMISSION-WORKSPACE-DESIGN.md`.

| Tool | Kind | Backend | What it does |
|---|---|---|---|
| `ping` | health | — | bridge reachability |
| `espocrm_get_current_user` | read | EspoCRM | `/App/user` identity check |
| `search_accounts` | read | EspoCRM | Accounts by name (bracket-encoded `where`) |
| `get_account` | read | EspoCRM | one Account by id |
| `propose_crm_change` | **propose** | Supabase | insert a row into `crm_change_proposals` (status `pending`) — never touches live records |
| `query_commission_ledger` | read | Supabase | read canonical `commission_ledger` |
| `record_commission_parity` | write | Supabase | insert `commission_parity_report` rows (the cutover gate) |

## Required Env Vars

- `API_SERVER_KEY` - bearer token expected by the bridge (**mandatory** for any
  public deployment — if unset, the endpoint is open).
- `HERMES_CONFIG_PATH` - where `config.yaml` is persisted (default `/data/config.yaml`)
- `ESPOCRM_BASE_URL` - EspoCRM base URL (e.g. `https://crm.example.com`); required
  for `espocrm_get_current_user`, `search_accounts`, `get_account`.
- `ESPOCRM_API_KEY` - EspoCRM API-user key (read-scoped is enough; the bridge never
  writes EspoCRM).
- `SUPABASE_URL` - Supabase project URL (e.g. `https://wibscqhkvpijzqbhjphg.supabase.co`);
  required for `propose_crm_change`, `query_commission_ledger`, `record_commission_parity`.
- `SUPABASE_SERVICE_KEY` - Supabase **service-role** key (server-side only; RLS is
  on, service_role bypasses it). The browser/agent never sees this.

## Verify locally

```bash
pip install -r requirements.txt
export API_SERVER_KEY=dev-secret SUPABASE_URL=... SUPABASE_SERVICE_KEY=... \
       ESPOCRM_BASE_URL=... ESPOCRM_API_KEY=...
uvicorn app:app --port 8080 &

# list tools
curl -s -H "Authorization: Bearer dev-secret" -X POST localhost:8080/mcp \
  -d '{"jsonrpc":"2.0","id":1,"method":"tools/list"}' | python3 -m json.tool

# propose a CRM enrichment (lands in crm_change_proposals, status=pending)
curl -s -H "Authorization: Bearer dev-secret" -X POST localhost:8080/mcp \
  -d '{"jsonrpc":"2.0","id":2,"method":"tools/call","params":{"name":"propose_crm_change",
       "arguments":{"entity":"Account","op":"enrich","matchKey":"mc-123",
       "after":{"website":"https://acme.com"},"rationale":"found site","confidence":0.9,
       "source":"manual"}}}'
```

## Persistence

`/api/mcp` writes MCP server definitions into `config.yaml`. Mount a
volume at the path set by `HERMES_CONFIG_PATH` so definitions survive
container restarts.

## Hermes Value

Set in Hermes:

```
MCP_URL=https://<espo-mcp-domain>/mcp
```
