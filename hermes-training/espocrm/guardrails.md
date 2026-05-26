---
name: espocrm-guardrails
scope: Load before writing or updating any CRM record
priority: high
token_cost: ~500
---

# RSG EspoCRM — Safe-Use Guardrails

These rules are non-negotiable. Violating them can corrupt data or break sync contracts with external systems.

---

## Policy records — AMS-first lock

| Rule | Detail |
|---|---|
| **Never edit core policy fields when `amsLockState = Locked by AMS`** | Locked fields: carrier, premium, effective/expiration dates, LOB, commission fields, AMS IDs |
| **`momentumPolicyId` and `insuredMomentumId` are immutable** | Overwriting them breaks the AMS sync join |
| **`syncStatus` is computed** | Managed by the sync engine; do not set manually |
| **Corrections flow through the correction webhook** | Submit a correction request — do not directly overwrite while locked |
| **Lock lifecycle** | `Unlocked` → `Pending AMS` (correction submitted) → `Locked by AMS` or `Rejected by AMS` |
| **`acceptedByAmsAt` / `acceptedByAmsBy` are read-only** | Set by AMS; never forge them |

---

## Commission records — ledger idempotency

| Rule | Detail |
|---|---|
| **Do not manually edit ledger sync fields** | `ledgerExternalId`, `ledgerSyncStatus`, `ledgerSyncedAt`, `ledgerSyncError`, `ledgerPayloadHash`, `ledgerKey` are owned by n8n |
| **Reconciliation state must be set in Espo first** | Never update reconciliation state in the external ledger only |
| **`overdueFlag` is computed** | Set by a scheduled job; do not manually override |

---

## Account rollup & score fields — all computed

Do not overwrite any of these:

**Score fields:** `accountScore`, `scoreTotal`, `scoreTier`, `scoreBundleDepth`, `scoreClaimsActivity`, `scoreLastContact`, `scorePaymentHistory`, `scoreYearsRetained`, `scoreChangeAmount`, `scoreChangeDirection`, `scoreLastCalculated`

**Policy rollup fields:** `activePolicyCount`, `totalActivePremium`, `totalAnnualPremium`, `policyCountActive`, `daysToRenewal`, and all per-LOB `policy*` shortcut fields

---

## Intel Pack fields — enrichment outputs

| Rule | Detail |
|---|---|
| **`intel*` and `insight*` fields are read-only** | Populated by the Intel Pack agent; do not overwrite with manual guesses |
| **To trigger a new Intel run** | Set `intelPackRun = true` on the Account; the hook handles execution and resets the flag |

---

## Opt-out / Do Not Contact

| Rule | Detail |
|---|---|
| **Always check before outreach** | Verify `doNotContact` (Account), `doNotCall` (Contact/Lead), `emailAddressIsOptedOut`, `phoneNumberIsOptedOut` before composing any outreach |
| **Do not clear opt-out flags** | Never set any `*IsOptedOut` or `doNot*` field to `false` without explicit user instruction |

---

## Lead conversion

| Rule | Detail |
|---|---|
| **Do not edit converted Leads** | Once `status = Converted`, edit the resulting Account, Contact, and Opportunity instead |
| **Conversion links are read-only** | `createdAccount`, `createdContact`, `createdOpportunity`, `convertedAt` are set by the conversion process |

---

## Task webhooks — most task types

| Rule | Detail |
|---|---|
| **Status changes fire real client notifications** | Moving a task to `In Progress`, `Waiting on Client`, or `Completed` triggers n8n webhooks that send emails/notifications to clients — for task types: `Client Service`, `Policy Change`, `Claims`, `Follow Up`, `Onboarding`, `Admin`, `Other` (and blank type) |
| **`New Business`, `Renewal`, `Commission` do NOT fire webhooks** | Only those task types are safe to bulk-update without client impact |
| **Do not bulk-update webhook-triggering task statuses** | Each status change has real client-facing consequences; update one at a time with user confirmation |

---

## External IDs — never forge

The following fields are set by external systems and must never be manually created or overwritten:

`momentumClientId` (Account, Contact) · `momentumPolicyId` (Policy) · `insuredMomentumId` (Policy) · `momentumLastSynced` (Account, Policy)

---

## Attachment → Google Drive sync

When an Attachment is created on a record whose Account has a `googleDriveFolderUrl`, the attachment is automatically synced to Drive. Do not delete or re-upload attachments to work around sync issues without diagnosing the root cause first.

---

## General write discipline

- **Reads are always safe.** Use GET/search operations freely.
- **Confirm before bulk updates.** Ask for explicit confirmation before updating more than one record in a single action.
- **Never delete records.** Use status changes (`Cancelled`, `Inactive`, `Dead`) instead of delete operations unless the user explicitly requests and confirms a hard delete.
- **Log your reasoning.** When updating sensitive fields (premium, carrier, lock state), briefly note why in `description` or `commissionNotes`, or ask the user to confirm with a note.
