# Momentum Sync Setup (Standardized)

This is the canonical naming for sync secrets and API config across EspoCRM + n8n.

## 1) n8n Environment Variables

Use:

- `MOMENTUM_BASE_URL`
- `MOMENTUM_API_KEY`
- `ESPO_BASE_URL`
- `ESPO_API_KEY`
- `POLICY_SYNC_SHARED_SECRET`
- `ACCOUNT_SYNC_SHARED_SECRET`

Reference template: `n8n/momentum-sync.env.example`

## 2) EspoCRM Config Mapping

Set in EspoCRM config:

- `policyCorrectionWebhookSecret` = same value as `POLICY_SYNC_SHARED_SECRET`
- `accountEnrichmentWebhookSecret` = same value as `ACCOUNT_SYNC_SHARED_SECRET`
- `policyCorrectionWebhookUrl` = n8n webhook endpoint for policy correction intake
- `accountEnrichmentWebhookUrl` = n8n webhook endpoint for account enrichment intake

## 3) Momentum Auth Flow

Momentum OpenAPI docs define API key exchange endpoint:

- `POST /api/token/exchange-api-key`

Use `MOMENTUM_API_KEY` to obtain a bearer token, then call Momentum API endpoints with:

- `Authorization: Bearer <token>`

## 4) Security Rule

Every inbound webhook from EspoCRM to n8n must verify the matching shared secret header:

- Policy sync: `X-Policy-Sync-Secret` == `POLICY_SYNC_SHARED_SECRET`
- Account sync: `X-Account-Sync-Secret` == `ACCOUNT_SYNC_SHARED_SECRET`
