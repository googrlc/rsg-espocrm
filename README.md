# RSG EspoCRM Configs

Backup of RSG EspoCRM customizations — field reference, entity definitions, layouts.

## Contents
- `field-reference/` — Complete field definitions for all entities
- `custom/` — Server-side custom entity defs (requires SSH to refresh)

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
