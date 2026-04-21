# Commission Ledger Sync Contract

This contract defines a one-way sync from EspoCRM `Commission` records to an external ledger (QuickBooks or similar) through n8n.

## Source Of Truth

- EspoCRM `Commission` stays authoritative for lifecycle and reconciliation.
- External ledger mirrors posted/estimated commission transactions for accounting.

## Idempotency Contract

- Use `ledgerKey` as the deterministic record key in Espo.
- Generate `ledgerPayloadHash` in n8n from the outbound payload body.
- Before writing to the external ledger, n8n checks whether `ledgerExternalId` exists:
  - If yes, update/patch existing external transaction.
  - If no, create the transaction and write back the new `ledgerExternalId`.
- n8n must treat `ledgerPayloadHash` as idempotency protection and skip duplicate submissions where hash has not changed.

## Required Outbound Payload

- `ledgerKey`
- `commissionType` (`New Business`, `Renewal`, `Endorsement`)
- `status`
- `reconciliationStatus`
- `policyId`, `policyName`
- `opportunityId` (if present)
- `renewalId` (if present)
- `accountId`, `accountName`
- `carrier`
- `lineOfBusiness`
- `effectiveDate`
- `expectedPaymentDate`
- `paymentReceivedDate`
- `writtenPremium`
- `commissionRate`
- `estimatedCommission`
- `postedAmount`
- `varianceAmount`
- `variancePercent`

## Writeback Fields (n8n -> Espo)

- `ledgerExternalId`: external transaction identifier.
- `ledgerSyncStatus`: `Pending`, `Synced`, or `Error`.
- `ledgerSyncedAt`: datetime of successful sync.
- `ledgerSyncError`: latest error string when sync fails.
- `ledgerPayloadHash`: hash for idempotency tracking.

## Sync Triggers

- Commission create/update where:
  - `status` changes,
  - financial fields change (`writtenPremium`, `commissionRate`, `estimatedCommission`, `postedAmount`),
  - reconciliation fields change.

## Operational Rules

- Never update reconciliation state in the external ledger only; it must be set in Espo first.
- If external sync fails, set `ledgerSyncStatus = Error` and store detail in `ledgerSyncError`.
- Retry policy should be safe due to `ledgerKey` + `ledgerPayloadHash` idempotency behavior.
