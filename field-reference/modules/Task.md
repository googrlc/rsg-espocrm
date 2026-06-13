# Task

**Entity name:** `Task`  
**Plural label:** Tasks  
**Type:** Core entity (module: `Crm`)  
**Field count:** 61  
**Link count:** 10  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Task`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Task/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Task`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Task/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Task/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `account` | Account | link |  | — | — |
| `assignedUser` | assignedUser | link | yes | — | required |
| `attachments` | Attachments | attachmentMultiple |  | — | — |
| `automationKey` | automationKey | varchar |  | — | read-only, max 100, custom |
| `carrier` | carrier | varchar |  | — | max 150, custom |
| `confirm1Label` | confirm1Label | varchar |  | — | max 200, custom |
| `confirm1Note` | confirm1Note | text |  | — | custom |
| `confirm1Status` | confirm1Status | enum |  | `Pending` | custom |
| `confirm2Label` | confirm2Label | varchar |  | — | max 200, custom |
| `confirm2Note` | confirm2Note | text |  | — | custom |
| `confirm2Status` | confirm2Status | enum |  | `Pending` | custom |
| `confirm3Label` | confirm3Label | varchar |  | — | max 200, custom |
| `confirm3Note` | confirm3Note | text |  | — | custom |
| `confirm3Status` | confirm3Status | enum |  | `Pending` | custom |
| `confirm4Label` | confirm4Label | varchar |  | — | max 200, custom |
| `confirm4Note` | confirm4Note | text |  | — | custom |
| `confirm4Status` | confirm4Status | enum |  | `Pending` | custom |
| `confirmationProgress` | confirmationProgress | varchar |  | — | read-only, max 16, custom |
| `contact` | Contact | link |  | — | — |
| `createdAt` | createdAt | datetime |  | — | read-only |
| `createdBy` | createdBy | link |  | — | read-only |
| `dateCompleted` | Date Completed | datetime |  | — | read-only |
| `dateEnd` | Date Due | datetimeOptional |  | — | — |
| `dateEndDate` | Date End (all day) | date |  | — | — |
| `dateStart` | Start Date | datetimeOptional |  | — | read-only |
| `dateStartDate` | Date Start (all day) | date |  | — | read-only |
| `description` | Description | text |  | — | — |
| `isOverdue` | Is Overdue | bool |  | — | read-only |
| `managerNotes` | managerNotes | wysiwyg |  | — | custom |
| `modifiedAt` | modifiedAt | datetime |  | — | read-only |
| `modifiedBy` | modifiedBy | link |  | — | read-only |
| `momentumLastSynced` | Momentum Last Synced | datetime |  | — | custom |
| `momentumTaskId` | NowCerts Task ID | varchar |  | — | read-only, max 255, custom |
| `name` | Name | varchar | yes | — | required, pattern |
| `originalEmail` | Original Email | link |  | — | — |
| `parent` | Parent | linkParent |  | — | — |
| `policies` | Policies | linkMultiple |  | — | custom |
| `policy` | policy | link |  | — | — |
| `policyEffectiveDate` | policyEffectiveDate | date |  | — | custom |
| `policyExpirationDate` | policyExpirationDate | date |  | — | custom |
| `policyNumber` | policyNumber | varchar |  | — | max 100, custom |
| `policyType` | policyType | varchar |  | — | max 100, custom |
| `priority` | Priority | enum |  | `Normal` | — |
| `refAccessCode` | refAccessCode | varchar |  | — | max 100, custom |
| `refLink` | refLink | url |  | — | custom |
| `refLink2` | refLink2 | url |  | — | custom |
| `refNotes` | refNotes | text |  | — | custom |
| `refNumber` | refNumber | varchar |  | — | max 100, custom |
| `refNumber2` | refNumber2 | varchar |  | — | max 100, custom |
| `refSupportContact` | refSupportContact | varchar |  | — | max 150, custom |
| `reminders` | Reminders | jsonArray |  | — | — |
| `sourceActivityLogId` | sourceActivityLogId | varchar |  | — | read-only, max 24, custom |
| `status` | Status | enum |  | `Inbox` | — |
| `streamUpdatedAt` | streamUpdatedAt | datetime |  | — | read-only |
| `syncSource` | Sync Source | enum |  | — | — |
| `taskSource` | Source | enum |  | — | custom |
| `taskType` | Task Type | enum |  | — | — |
| `teams` | teams | linkMultiple |  | — | — |
| `triageReason` | Triage Reason | text |  | — | — |
| `triageSummary` | Triage Summary | text |  | — | — |
| `urgency` | Urgency | enum |  | `Normal` | — |

## Allowed values (enum / multi-enum / array / checklist)

### `confirm1Status` — confirm1Status

- Type: `enum`
- Default: `Pending`
- Options:
  - `Pending`
  - `Confirmed`
  - `Needs Follow-up`

### `confirm2Status` — confirm2Status

- Type: `enum`
- Default: `Pending`
- Options:
  - `Pending`
  - `Confirmed`
  - `Needs Follow-up`

### `confirm3Status` — confirm3Status

- Type: `enum`
- Default: `Pending`
- Options:
  - `Pending`
  - `Confirmed`
  - `Needs Follow-up`

### `confirm4Status` — confirm4Status

- Type: `enum`
- Default: `Pending`
- Options:
  - `Pending`
  - `Confirmed`
  - `Needs Follow-up`

### `priority` — Priority

- Type: `enum`
- Default: `Normal`
- Options:
  - `Low`
  - `Normal`
  - `High`
  - `Urgent`

### `status` — Status

- Type: `enum`
- Default: `Inbox`
- Options:
  - `Inbox`
  - `In Progress`
  - `Waiting on Client`
  - `Waiting on Carrier`
  - `Completed`
  - `Cancelled`

### `syncSource` — Sync Source

- Type: `enum`
- Options:
  - `Manual`
  - `Email`
  - `API`
  - `Momentum`
  - `n8n`
  - `Hermes`

### `taskSource` — Source

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Account`
  - `Contact`
  - `Opportunity`
  - `Policy`

### `taskType` — Task Type

- Type: `enum`
- Options:
  - `Client Service`
  - `Policy Change`
  - `Renewal`
  - `New Business`
  - `Follow Up`
  - `Onboarding`
  - `Claims`
  - `Commission`
  - `Underwriting Request`
  - `Underwriter Follow-up`
  - `Agency Admin`
  - `Admin`
  - `Other`

### `urgency` — Urgency

- Type: `enum`
- Default: `Normal`
- Options:
  - `Urgent`
  - `High`
  - `Normal`
  - `Low`

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `account` | Account | belongsTo | `Account` | `—` | — |
| `assignedUser` | assignedUser | belongsTo | `User` | `tasks` | — |
| `contact` | Contact | belongsTo | `Contact` | `—` | — |
| `createdBy` | createdBy | belongsTo | `User` | `—` | — |
| `email` | Email | belongsTo | `Email` | `tasks` | — |
| `modifiedBy` | modifiedBy | belongsTo | `User` | `—` | — |
| `parent` | parent | belongsToParent | `tasks` | `tasks` | — |
| `policies` | Policies | hasMany | `Policy` | `coiTasks` | custom, relation `taskPolicy` |
| `policy` | policy | belongsTo | `Policy` | `tasks` | custom |
| `teams` | teams | hasMany | `Team` | `—` | relation `entityTeam` |

## Unique indexes

- **momentumTaskId**: `momentumTaskId`

---

_Generated 2026-06-06 from a read-only live metadata pull (`metadata.php` cache, equivalent to `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`)._
