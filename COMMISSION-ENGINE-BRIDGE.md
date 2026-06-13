# Commission Engine Bridge

One-way bridge that pushes a **closed/bound Policy** from EspoCRM to the
standalone **RSG Commission Tracker** (the commissions engine) so the engine can
compute the expected commission from its own carrier rulebook.

This is separate from the [Commission Ledger Sync Contract](COMMISSION-LEDGER-SYNC-CONTRACT.md),
which syncs the internal `Commission` ledger to an external accounting ledger.
This bridge sends raw **policy inputs**; the engine does the calculation.

## Trigger

- Fires from `Policy` `AfterSave` whenever the Policy is in a configured
  "closed/won" status (default `['Active']`) **and** a watched field changed.
- Covers the **AMS → CRM → commissions** flow: the AMS is the system of record
  and lands data in stages, so the bridge pushes on the initial activation **and
  again** whenever the AMS later corrects a financial/identity field
  (`policy_number`, `carrier`, `line_of_business`, `business_type`,
  `premium_amount`, `commission_rate`, `effective_date`, `expiration_date`,
  `bind_date`).
- Unrelated saves (no watched field changed) are ignored. The engine upserts by
  `crmPolicyId`, so every re-push is idempotent — no duplicates, no double-count.
- Out of scope: when a policy leaves the closed set (e.g. `Cancelled`) the bridge
  stays silent. Chargebacks/cancellations are handled in the engine's
  reconciliation, not by this push.

Files:
- `custom/Espo/Custom/Hooks/Policy/SendPolicyClosedWebhook.php` — the trigger/gate.
- `custom/Espo/Custom/Classes/Policy/PolicyClosedWebhookDispatcher.php` — builds + sends the payload (extends `BaseWebhookDispatcher`).

## EspoCRM configuration

Add to `data/config.php` (or via a config override), then rebuild:

```php
'commissionEngineWebhookUrl' => 'https://<engine-host>/api/won-policies',
'commissionEngineWebhookSecret' => '<shared-secret>',           // optional but recommended
'commissionEngineClosedStatusList' => ['Active'],               // optional; defaults to ['Active']
```

- If `commissionEngineWebhookUrl` is empty, the bridge is **disabled** (no-op, no warnings).
- `commissionEngineWebhookSecret` is sent as the `X-Commission-Sync-Secret`
  header and must equal the engine's `COMMISSION_SYNC_SECRET`.
- The URL may point directly at the engine, or at an n8n webhook that forwards
  to the engine (use n8n if you want retries/queueing).

After changing config or deploying the PHP files: `php clear_cache.php` / Admin → Rebuild.

## Outbound payload (`POST`)

```jsonc
{
  "eventType": "policy.closed",     // "policy.cancelled" when voided === true
  "triggeredAt": "2026-06-07T12:00:00+00:00",
  "policy": {
    "crmPolicyId": "665f...",       // EspoCRM Policy id — idempotency key
    "momentumPolicyId": "NC-88123", // NowCerts/AMS Policy ID — traceability to the AMS
    "policyNumber": "WC-10231",
    "clientName": "Coastal Builders Corp",   // Account name
    "accountId": "664a...",
    "carrier": "AmTrust",
    "lineOfBusiness": "Workers Comp",
    "newRenewal": "New",            // derived from business_type (contains "renew" → Renewal)
    "premiumAmount": 12000,
    "commissionRate": 0.16,         // informational; engine uses its own rulebook
    "status": "Active",
    "voided": false,                // true for cancel statuses → engine reverses expected
    "dateWon": "2026-06-06",        // bind_date, else effective_date
    "effectiveDate": "2026-06-06",
    "expirationDate": "2027-06-06",
    "cancellationDate": null,       // set when voided
    "cancellationReason": "",       // set when voided
    "modifiedAt": "2026-06-07 12:00:00"
  },
  "changes": { "status": { "old": "Up for Renewal", "new": "Active" } }
}
```

## Field syntax: AMS → CRM → Supabase → engine

The same policy flows through four layers. Column names are the snake_case mirror
of the engine's `WonPolicy` (only renaming differs — values stay identical).
CRM Policy fields are from
[`exports/crm_fields_account_contact_policy.csv`](exports/crm_fields_account_contact_policy.csv)
(that file is the **field inventory**, one row per field — not a record export).

| AMS (NowCerts)        | CRM Policy field        | Webhook `policy.*`   | Supabase `commission_won_policies` | Engine `WonPolicy`   |
|-----------------------|-------------------------|----------------------|------------------------------------|----------------------|
| Policy ID             | `momentumPolicyId`      | `momentumPolicyId`   | `nowcerts_policy_id`               | `momentumPolicyId`   |
| (CRM internal id)     | `id`                    | `crmPolicyId`        | `crm_policy_id` (unique)           | `crmPolicyId`        |
| Policy Number         | `policy_number`         | `policyNumber`       | `policy_number`                    | `policyNumber`       |
| Insured / Account     | `account`→`accountName` | `clientName`         | `client_name`                      | `clientName`         |
| Carrier               | `carrier`               | `carrier`            | `carrier`                          | `carrier`            |
| Line of Business      | `line_of_business`*     | `lineOfBusiness`     | `line_of_business`                 | `lineOfBusiness`     |
| Business Type         | `business_type`         | `newRenewal`†        | `new_renewal`                      | `newRenewal`         |
| Premium               | `premium_amount`        | `premiumAmount`      | `premium_amount`                   | `premiumAmount`      |
| Commission Rate %     | `commission_rate`       | `commissionRate`     | `commission_rate`                  | (engine recalculates)|
| Status (int)          | `status` (enum text)    | `status` / `voided`  | `status` / `voided`                | `voided`             |
| Bind / Effective      | `bind_date`/`effective_date` | `dateWon`       | `date_won`                         | `dateWon`            |
| Effective / Expiration| `effective_date`/`expiration_date` | `effectiveDate`/`expirationDate` | `effective_date`/`expiration_date` | — |
| Cancellation Date     | `cancellation_date`     | `cancellationDate`   | `cancellation_date`                | `cancellationDate`   |
| Cancellation Reason   | `cancellation_reason`   | `cancellationReason` | `cancellation_reason`              | `cancellationReason` |

\* CRM sends the **normalized** `line_of_business` (not `line_of_business_raw`).
† `newRenewal` = `business_type` contains "renew" → `Renewal`, else `New`.

Cross-system identity also lives in Supabase `sync_mappings`
(`object_type='policy'`, `espocrm_id`=`crm_policy_id`, `nowcerts_id`=`nowcerts_policy_id`).

**AMS status (the original "status = 1").** NowCerts `PolicyStatus` is an integer
enum; the NowCerts→CRM sync lands it as the CRM `status` text enum
(`Active`, `Cancelled`, `Flat Cancel`, ...). The bridge keys off the CRM text
status, so it is independent of the AMS integer code.

> **The one place syntax can silently drift — LOB & carrier vocabulary.** The
> engine matches a rule by `carrier` + `lineOfBusiness` + `newRenewal`. LOB is now
> compared through a synonym map (`LOB_ALIASES` in `src/utils.ts`) so CRM/AMS names
> line up with rulebook names — e.g. `General Liability`→`Commercial GL`,
> `Umbrella`→`Excess Liability`, `WC`→`Workers Comp`. **Carrier names are not
> aliased** (too data-dependent): if the AMS carrier string (e.g. `AmTrust
> Financial`) differs from the rulebook (`AmTrust`), expected falls to 0 until the
> names are reconciled. Keep carrier naming consistent across all four layers.

## Engine endpoint

- `POST /api/won-policies` — secret-protected ingest (idempotent upsert by `crmPolicyId`).
- `GET  /api/won-policies` — the React app reads this on load and on "Sync from CRM".
- See `server/index.js` in the commissions engine repo for the contract.

## Test

```bash
curl -X POST "$ENGINE_URL/api/won-policies" \
  -H 'Content-Type: application/json' \
  -H "X-Commission-Sync-Secret: $SECRET" \
  -d '{"eventType":"policy.closed","policy":{"crmPolicyId":"test-1","policyNumber":"WC-1","clientName":"Acme","carrier":"AmTrust","lineOfBusiness":"Workers Comp","newRenewal":"New","premiumAmount":12000,"status":"Active","dateWon":"2026-06-06"}}'
```

Then open the engine → Won Policies tab → "Sync from CRM" → the row appears with
expected commission computed from the AmTrust / Workers Comp / New rule.
