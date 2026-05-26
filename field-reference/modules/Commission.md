# Commission

**Entity name:** `Commission`  
**Plural label:** Commissions  
**Type:** Custom entity (module: `Custom`)  
**Field count:** 45  
**Link count:** 9  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Commission`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Commission/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Commission`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Commission/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Commission/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `account` | account | link | yes | — | required |
| `assignedUser` | assignedUser | link |  | — | — |
| `carrier` | Carrier | varchar |  | — | max 255, custom |
| `commissionNotes` | commissionNotes | text |  | — | custom |
| `commissionRate` | Commission Rate (Percent) | float |  | — | custom |
| `commissionType` | commissionType | enum | yes | — | required, custom |
| `contact` | contact | link |  | — | — |
| `createdAt` | createdAt | datetime |  | — | read-only |
| `createdBy` | createdBy | link |  | — | read-only |
| `effectiveDate` | Effective Date | date |  | — | custom |
| `estimatedCommission` | Estimated Commission | currency |  | — | read-only, custom |
| `estimatedCommissionConverted` | estimatedCommissionConverted | currencyConverted |  | — | read-only |
| `estimatedCommissionCurrency` | estimatedCommissionCurrency | enum |  | — | read-only, max 3 |
| `expectedPaymentDate` | expectedPaymentDate | date |  | — | custom |
| `ledgerExternalId` | Ledger External ID | varchar |  | — | read-only, max 255, custom |
| `ledgerKey` | Ledger Key | varchar |  | — | read-only, max 255, custom |
| `ledgerPayloadHash` | Ledger Payload Hash | varchar |  | — | read-only, max 128, custom |
| `ledgerSyncError` | Ledger Sync Error | text |  | — | read-only, custom |
| `ledgerSyncStatus` | Ledger Sync Status | enum |  | `Pending` | read-only, custom |
| `ledgerSyncedAt` | Ledger Synced At | datetime |  | — | read-only, custom |
| `lineOfBusiness` | Line of Business | enum |  | — | custom |
| `modifiedAt` | modifiedAt | datetime |  | — | read-only |
| `modifiedBy` | modifiedBy | link |  | — | read-only |
| `name` | name | varchar | yes | — | required, max 255 |
| `opportunity` | opportunity | link |  | — | — |
| `overdueFlag` | overdueFlag | bool |  | false | read-only, custom |
| `paymentReceivedDate` | paymentReceivedDate | date |  | — | custom |
| `policy` | policy | link | yes | — | required |
| `postedAmount` | postedAmount | currency |  | — | custom |
| `postedAmountConverted` | postedAmountConverted | currencyConverted |  | — | read-only |
| `postedAmountCurrency` | postedAmountCurrency | enum |  | — | max 3 |
| `postingSource` | postingSource | enum |  | `Manual` | read-only, custom |
| `producer` | producer | varchar |  | — | max 255, custom |
| `reconciliationStatus` | Reconciliation Status | enum |  | `Unreconciled` | custom |
| `renewal` | renewal | link |  | — | — |
| `status` | status | enum | yes | `Estimated` | required, custom |
| `streamUpdatedAt` | streamUpdatedAt | datetime |  | — | read-only |
| `teams` | teams | linkMultiple |  | — | — |
| `varianceAmount` | varianceAmount | currency |  | — | read-only, custom |
| `varianceAmountConverted` | varianceAmountConverted | currencyConverted |  | — | read-only |
| `varianceAmountCurrency` | varianceAmountCurrency | enum |  | — | read-only, max 3 |
| `variancePercent` | variancePercent | float |  | — | read-only, custom |
| `writtenPremium` | Written Premium | currency |  | — | custom |
| `writtenPremiumConverted` | writtenPremiumConverted | currencyConverted |  | — | read-only |
| `writtenPremiumCurrency` | writtenPremiumCurrency | enum |  | — | max 3 |

## Allowed values (enum / multi-enum / array / checklist)

### `commissionType` — commissionType

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `New Business`
  - `Renewal`
  - `Endorsement`

### `ledgerSyncStatus` — Ledger Sync Status

- Type: `enum`
- Default: `Pending`
- Options:
  - `""` _(empty)_
  - `Pending`
  - `Synced`
  - `Error`

### `lineOfBusiness` — Line of Business

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Commercial Auto`
  - `General Liability`
  - `Workers Comp`
  - `Commercial Property`
  - `BOP`
  - `Professional Liability`
  - `Umbrella`
  - `Personal Auto`
  - `Homeowners`
  - `Renters`
  - `Condo`
  - `Life`
  - `Health`
  - `Medicare`
  - `Group Benefits`
  - `Other`

### `postingSource` — postingSource

- Type: `enum`
- Default: `Manual`
- Options:
  - `""` _(empty)_
  - `Manual`
  - `AMS Download`

### `reconciliationStatus` — Reconciliation Status

- Type: `enum`
- Default: `Unreconciled`
- Options:
  - `""` _(empty)_
  - `Unreconciled`
  - `Reconciled`
  - `Disputed`

### `status` — status

- Type: `enum`
- Default: `Estimated`
- Options:
  - `""` _(empty)_
  - `Estimated`
  - `Posted`
  - `Overdue`

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `account` | account | belongsTo | `Account` | `commissions` | custom |
| `assignedUser` | assignedUser | belongsTo | `User` | `—` | — |
| `contact` | contact | belongsTo | `Contact` | `commissions` | custom |
| `createdBy` | createdBy | belongsTo | `User` | `—` | — |
| `modifiedBy` | modifiedBy | belongsTo | `User` | `—` | — |
| `opportunity` | opportunity | belongsTo | `Opportunity` | `commissions` | custom |
| `policy` | policy | belongsTo | `Policy` | `commissions` | custom |
| `renewal` | renewal | belongsTo | `Renewal` | `commissions` | custom |
| `teams` | teams | hasMany | `Team` | `—` | relation `entityTeam` |

---

_Generated 2026-05-26 from `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`._
