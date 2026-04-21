# RSG EspoCRM Configs

Backup of RSG EspoCRM customizations — field reference, entity definitions, layouts.

## Contents
- `field-reference/` — Complete field definitions for all entities
- `custom/` — Server-side custom entity defs (requires SSH to refresh)
- `COMMISSION-LEDGER-SYNC-CONTRACT.md` — one-way Espo-to-ledger sync contract and idempotency rules

## SSH Access
Server: rrespocrm-rsg-u69864.vm.elestio.app
Key: RSG Elestio EspoCRM (1Password)
Note: Port 22 is IP-restricted — whitelist your IP in Elestio dashboard first

## Service Webhooks
Set these in EspoCRM config to enable outbound service-task webhooks:

- `serviceWebhookUrl` — fallback URL for all service task events
- `serviceStartedWebhookUrl` — optional override for tasks moved to `In Progress`
- `serviceRequestWebhookUrl` — optional override for tasks moved to `Waiting on Client`
- `serviceCompletionWebhookUrl` — optional override for tasks moved to `Completed`
- `serviceWebhookSecret` — optional shared secret sent in `X-Service-Webhook-Secret`

The webhook fires for service-oriented tasks when status changes to `In Progress`, `Waiting on Client`, or `Completed`.
Those same status changes also create an `ActivityLog` record on the client in EspoCRM, so the account timeline has an internal audit trail even if n8n is only handling outbound email.

Webhook payload highlights for n8n:
- `eventType` values are `service.task_started`, `service.request_to_client`, and `service.task_completed`
- `task.status` and alias `task.queueStatus`
- `task.dateDue` and alias `task.slaDueDate`
- `task.assignedUserId` and alias `task.ownerId`
- `task.clientEmail` and `task.clientName`
- `task.sourceActivityLogId`

## Intel Pack webhook (Account)

Set these in EspoCRM `data/config.php` for the **Run Intel Pack** action on Account records:

- `intelPackWebhookUrl` — n8n (or other) HTTP endpoint URL
- `intelPackWebhookSecret` — **required** when the URL is set; used for HMAC verification (do not commit real values to this repo)

EspoCRM sends a `POST` with `Content-Type: application/json` and signs the **raw JSON body** with HMAC-SHA256:

- Header: `X-Intel-Pack-Signature: sha256=<64-char lowercase hex>`
- The `<hex>` value is `HMAC_SHA256(body, intelPackWebhookSecret)` in hex (same as PHP `hash_hmac('sha256', $body, $secret)`).

On n8n, reject requests where the signature does not match (use a constant-time compare on the hex strings). Payload fields include `entityType`, `entityId`, `assignedUserName`, and `momentumId`.

## Policy Sync Governance (AMS-first)

Policy records are AMS-first with controlled CRM correction workflow:

- `acceptedByAmsAt` / `acceptedByAmsBy` capture AMS acceptance of CRM corrections.
- `amsLockState` controls CRM edit lock lifecycle: `Unlocked` -> `Pending AMS` -> `Locked by AMS` (or `Rejected by AMS`).
- When `amsLockState` is `Locked by AMS`, core policy fields are blocked in CRM and must be changed in AMS.
- Sync decisions (`accepted`, `locked`, `rejected`, `blocked`) are logged to `ActivityLog` for global-stream timestamp visibility.

Set these config keys to enable outbound correction payloads from CRM:

- `policyCorrectionWebhookUrl` — endpoint that receives `policy.correction_submitted`
- `policyCorrectionWebhookSecret` — optional shared secret sent as `X-Policy-Sync-Secret`

Set these config keys to enable outbound account enrichment payloads from CRM:

- `accountEnrichmentWebhookUrl` — endpoint that receives `account.enrichment_submitted`
- `accountEnrichmentWebhookSecret` — optional shared secret sent as `X-Account-Sync-Secret`
