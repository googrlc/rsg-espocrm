# Task Notify ↔ Slack Acknowledge — live wiring

Two workflows on the live n8n instance (`n8n-9uiaa-u69864.vm.elestio.app`):

| File | n8n ID | Webhook path | Purpose |
|---|---|---|---|
| `espo-task-created-slack-notify.json` | `Qh2hQOdrxouquuJ0` | `/webhook/espo-task-created` | Service task status → Slack notification |
| `slack-acknowledge-espocrm-writeback.json` | `eB8zvXuOOssZrOXk` | `/webhook/slack-task-ack` | Slack "Acknowledge" button → CRM status write-back |

## Trigger (important)

The notify workflow is **not** fed by a native EspoCRM "Task.create" webhook. It is fed by the
custom `ServiceWebhookDispatcher` (`custom/Espo/Custom/Classes/Task/ServiceWebhookDispatcher.php`),
fired by the `SendServiceWebhook` afterSave hook. It fires on **status transitions**, not on create:

| Status change | eventType | EspoCRM config key → URL |
|---|---|---|
| → In Progress | `service.task_started` | `serviceStartedWebhookUrl` |
| → Waiting on Client | `service.request_to_client` | `serviceRequestWebhookUrl` |
| → Completed | `service.task_completed` | `serviceCompletionWebhookUrl` (→ Hermes, not this workflow) |

`serviceStartedWebhookUrl` and `serviceRequestWebhookUrl` are pointed at
`https://n8n-9uiaa-u69864.vm.elestio.app/webhook/espo-task-created`.

Payload is a **flat** object: `body.task.{id,name,status,assignedUserId,...}` + `body.eventType`,
with header `X-Service-Webhook-Secret` (config key `serviceWebhookSecret`).

## Credentials (n8n)

- `Espocrm_api (X-Api-Key write)` — httpHeaderAuth, header `X-Api-Key`. Must be the **write-capable**
  EspoCRM key (`op://rsg_infrastructure/ESPOCRM_API_KEY/text`). The old read-only cred silently failed PUTs.
- `Slack_bot_token (agency_assistant)` — httpHeaderAuth, header `Authorization` = `Bearer xoxb-…`.
  The previous Slack cred had an **empty header name** → "header name must be a non-empty string".
- `Supabase Postgres` — Postgres connection for `slack_user_map` + `task_notify_audit`.

## Required n8n environment variables

Set these in the n8n Elestio environment (they are read via `$env` in the Code nodes; secrets are kept
out of this repo on purpose):

| Var | Value | Effect if unset |
|---|---|---|
| `SERVICE_WEBHOOK_SECRET` | EspoCRM `serviceWebhookSecret` | inbound secret check on notify is **skipped** (workflow still runs) |
| `SLACK_SIGNING_SECRET` | signing secret of the Slack app whose Interactivity URL points at `/webhook/slack-task-ack` (agency_assistant app) | Slack signature verification on ack is **skipped** |
| `TASK_NOTIFY_FALLBACK_CHANNEL` | Slack channel ID for unmapped/unassigned tasks (default `C0AUP125PRU`) | falls back to the hardcoded default |

## Supabase tables

`public.slack_user_map` (espo_user_id ↔ slack_user_id) and `public.task_notify_audit`
(append-only `notified`/`acknowledged` log). Migration in `rsg-hermes`.
Seed `slack_user_map` with each EspoCRM user ↔ Slack user ID before notifications can DM owners.

## Remaining manual step

Point the Slack app's **Interactivity Request URL** at
`https://n8n-9uiaa-u69864.vm.elestio.app/webhook/slack-task-ack` so the Acknowledge button reaches the
ack workflow, then set `SLACK_SIGNING_SECRET` to that app's signing secret.
