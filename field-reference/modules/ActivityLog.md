# ActivityLog

**Entity name:** `ActivityLog`  
**Plural label:** ActivityLog  
**Type:** Custom entity (module: `Custom`)  
**Field count:** 33  
**Link count:** 7  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/ActivityLog`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/ActivityLog/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/ActivityLog`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/ActivityLog/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/ActivityLog/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `account` | Account | link | yes | — | required |
| `activityType` | Activity Type | enum | yes | — | required, custom |
| `assignedUser` | assignedUser | link |  | — | — |
| `changeEffectiveDate` | Effective Date | date |  | — | custom |
| `changeSummary` | Change Summary | text |  | — | custom |
| `changeType` | Change Type | enum |  | — | custom |
| `classification` | Classification | enum |  | — | custom |
| `contact` | Contact | link |  | — | — |
| `createdAt` | createdAt | datetime |  | — | read-only |
| `createdBy` | createdBy | link |  | — | read-only |
| `dateTime` | Date and Time | datetime | yes | `javascript: return this.dateTime.getNow(1);` | required, custom |
| `direction` | Direction | enum |  | — | custom |
| `duration` | Duration (Minutes) | int |  | — | custom |
| `followUpTask` | Follow-Up Task | varchar |  | — | max 255, custom |
| `loggedBy` | Logged By | varchar |  | — | max 100, custom |
| `modifiedAt` | modifiedAt | datetime |  | — | read-only |
| `modifiedBy` | modifiedBy | link |  | — | read-only |
| `momentumTransactionId` | Transaction ID | varchar |  | — | read-only, max 255, custom |
| `name` | Subject | varchar | yes | — | required, max 255 |
| `newPremium` | New Premium | currency |  | — | custom |
| `newPremiumConverted` | newPremiumConverted | currencyConverted |  | — | read-only |
| `newPremiumCurrency` | newPremiumCurrency | enum |  | — | max 3 |
| `notes` | Notes | text |  | — | custom |
| `oldPremium` | Old Premium | currency |  | — | custom |
| `oldPremiumConverted` | oldPremiumConverted | currencyConverted |  | — | read-only |
| `oldPremiumCurrency` | oldPremiumCurrency | enum |  | — | max 3 |
| `outcome` | Outcome | enum |  | — | custom |
| `policy` | Policy | link |  | — | — |
| `premiumDelta` | Premium Change | currency |  | — | read-only, custom |
| `premiumDeltaConverted` | premiumDeltaConverted | currencyConverted |  | — | read-only |
| `premiumDeltaCurrency` | premiumDeltaCurrency | enum |  | — | read-only, max 3 |
| `source` | Source | enum |  | `NowCerts Sync` | custom |
| `teams` | teams | linkMultiple |  | — | — |

## Allowed values (enum / multi-enum / array / checklist)

### `activityType` — Activity Type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Email Out`
  - `Email In`
  - `Call`
  - `n8n Automated`
  - `Manus AI`
  - `Note`
  - `Intel Run`
  - `Renewal Outreach`
  - `Endorsement`
  - `Premium Change`
  - `Coverage Add`
  - `Coverage Remove`
  - `Cancellation`

### `changeType` — Change Type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Endorsement`
  - `New Business`
  - `Cancellation`
  - `Reinstatement`
  - `Renewal`

### `classification` — Classification

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Renewal inquiry`
  - `Coverage question`
  - `Quote request`
  - `Claim related`
  - `Payment / billing`
  - `Document request`
  - `Cancellation / non-renewal notice`
  - `Carrier correspondence`
  - `Complaint`
  - `Onboarding`
  - `Marketing outreach`
  - `General correspondence`

### `direction` — Direction

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Outbound`
  - `Inbound`

### `outcome` — Outcome

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Connected`
  - `No answer voicemail`
  - `No answer no voicemail`
  - `Wrong number`

### `source` — Source

- Type: `enum`
- Default: `NowCerts Sync`
- Options:
  - `""` _(empty)_
  - `NowCerts Sync`
  - `Manual`
  - `n8n Automated`

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `account` | Account | belongsTo | `Account` | `activityLogs` | custom |
| `assignedUser` | assignedUser | belongsTo | `User` | `—` | — |
| `contact` | Contact | belongsTo | `Contact` | `activityLogs` | custom |
| `createdBy` | createdBy | belongsTo | `User` | `—` | — |
| `modifiedBy` | modifiedBy | belongsTo | `User` | `—` | — |
| `policy` | Policy | belongsTo | `Policy` | `activityLogs` | custom |
| `teams` | teams | hasMany | `Team` | `—` | relation `entityTeam` |

## Unique indexes

- **momentumTransactionId**: `momentumTransactionId`

---

_Generated 2026-06-06 from a read-only live metadata pull (`metadata.php` cache, equivalent to `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`)._
