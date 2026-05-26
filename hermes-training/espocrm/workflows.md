---
name: espocrm-workflows
scope: Load when using MCP tools or handling webhooks
priority: medium
token_cost: ~400
---

# RSG EspoCRM — MCP Workflows & Webhook Contracts

---

## MCP Tool Usage

The MCP bridge (`espo-mcp` Docker sidecar in the Hermes Compose stack) exposes EspoCRM via MCP tools. Connect Hermes by setting `MCP_URL=https://<espo-mcp-domain>/mcp`.

### Current tools

| Tool | Purpose |
|---|---|
| `ping` | Health check — confirm the bridge is reachable |
| `espocrm_get_current_user` | Fetch the API user the bridge is authenticated as |

Additional EspoCRM CRUD tools are added as the bridge grows.

### Tool interaction patterns

- **Reads are always safe.** Use GET/search operations freely.
- **Writes require caution.** Apply the governance rules in `guardrails.md` before any create or update.
- **Confirm before bulk updates.** Ask for explicit confirmation before updating more than one record in a single action.
- **Log your reasoning.** When updating sensitive fields (premium, carrier, lock state), briefly note why in the `description` or `commissionNotes` field, or ask the user to confirm the change with a note.
- **Never delete records.** Use status changes (`Cancelled`, `Inactive`, `Dead`) instead of delete operations unless the user explicitly requests and confirms a hard delete.

---

## Webhook & Integration Contracts

| Integration | Trigger | Secret header |
|---|---|---|
| Service task notifications | Task status → `In Progress` / `Waiting on Client` / `Completed` | `X-Service-Webhook-Secret` |
| Intel Pack | Account `intelPackRun = true` | `X-Intel-Pack-Signature` (HMAC-SHA256) |
| Policy correction | CRM correction submitted | `X-Policy-Sync-Secret` |
| Account enrichment | Account enrichment submitted | `X-Account-Sync-Secret` |
| Attachment → Drive | Attachment created | `X-Attachment-Sync-Secret` |
| Commission ledger | Commission create/update (financial/status change) | *(n8n internal)* |

All webhooks must be verified with their respective secret before acting on the payload. Use constant-time comparison for HMAC signatures.

### Service task webhook events

When a `Client Service` task changes status:
- → `In Progress` fires `service.task_started`
- → `Waiting on Client` fires `service.request_to_client`
- → `Completed` fires `service.task_completed`

Each status change also writes an `ActivityLog` record to the account timeline.
