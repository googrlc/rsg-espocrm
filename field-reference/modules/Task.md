# Task

**Entity name:** `Task`  
**Plural label:** Tasks  
**Type:** Core entity (module: `Crm`)  
**Field count:** 33  
**Link count:** 8  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Task`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Task/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Task`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Task/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Task/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `account` | Account | link |  | — | read-only |
| `assignedUser` | assignedUser | link | yes | — | required |
| `attachments` | Attachments | attachmentMultiple |  | — | — |
| `automationKey` | automationKey | varchar |  | — | read-only, max 100, custom |
| `contact` | Contact | link |  | — | read-only |
| `createdAt` | createdAt | datetime |  | — | read-only |
| `createdBy` | createdBy | link |  | — | read-only |
| `dateCompleted` | Date Completed | datetime |  | — | read-only |
| `dateEnd` | Date Due | datetimeOptional |  | — | — |
| `dateEndDate` | Date End (all day) | date |  | — | — |
| `dateStart` | Date Start | datetimeOptional |  | — | — |
| `dateStartDate` | Date Start (all day) | date |  | — | — |
| `description` | Description | text |  | — | — |
| `isOverdue` | Is Overdue | bool |  | — | read-only |
| `modifiedAt` | modifiedAt | datetime |  | — | read-only |
| `modifiedBy` | modifiedBy | link |  | — | read-only |
| `momentumLastSynced` | Momentum Last Synced | datetime |  | — | custom |
| `momentumTaskId` | NowCerts Task ID | varchar |  | — | read-only, max 255, custom |
| `name` | Name | varchar | yes | — | required, pattern |
| `originalEmail` | Original Email | link |  | — | — |
| `parent` | Parent | linkParent |  | — | — |
| `priority` | Priority | enum |  | `Normal` | — |
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
  - `Gmail`
  - `Slack`
  - `Momentum`
  - `n8n`
  - `Viktor`

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
| `teams` | teams | hasMany | `Team` | `—` | relation `entityTeam` |

## Unique indexes

- **momentumTaskId**: `momentumTaskId`

---

_Generated 2026-05-26 from `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`._
