# Case

**Entity name:** `Case`  
**Plural label:** Cases  
**Type:** Core entity (module: `Crm`)  
**Field count:** 21  
**Link count:** 14  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Case`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Case/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Case`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Case/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Case/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `account` | Account | link |  | — | — |
| `assignedUser` | assignedUser | link |  | — | — |
| `attachments` | Attachments | attachmentMultiple |  | — | — |
| `contact` | Contact | link |  | — | — |
| `contacts` | Contacts | linkMultiple |  | — | — |
| `createdAt` | createdAt | datetime |  | — | read-only |
| `createdBy` | createdBy | link |  | — | read-only |
| `description` | Description | text |  | — | — |
| `inboundEmail` | Group Email Account | link |  | — | read-only |
| `isInternal` | Hidden from Portal | bool |  | — | — |
| `lead` | Lead | link |  | — | — |
| `modifiedAt` | modifiedAt | datetime |  | — | read-only |
| `modifiedBy` | modifiedBy | link |  | — | read-only |
| `name` | Name | varchar | yes | — | required, pattern |
| `number` | Number | autoincrement |  | — | — |
| `originalEmail` | Original Email | link |  | — | — |
| `priority` | Priority | enum |  | `Normal` | — |
| `status` | Status | enum |  | `New` | — |
| `streamUpdatedAt` | streamUpdatedAt | datetime |  | — | read-only |
| `teams` | teams | linkMultiple |  | — | — |
| `type` | Type | enum |  | — | — |

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
- Default: `New`
- Options:
  - `New`
  - `Assigned`
  - `Pending`
  - `Day 0 Sent`
  - `Day 1 Sent`
  - `Day 7 Sent`
  - `Day 14 Sent`
  - `Day 30 Sent`
  - `Day 60 Sent`
  - `Sequence Complete`
  - `Opted Out`
  - `Closed`
  - `Rejected`
  - `Duplicate`

### `type` — Type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Question`
  - `Incident`
  - `Problem`

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `account` | Account | belongsTo | `Account` | `cases` | — |
| `articles` | Knowledge Base Articles | hasMany | `KnowledgeBaseArticle` | `cases` | audited |
| `assignedUser` | assignedUser | belongsTo | `User` | `—` | — |
| `calls` | Calls | hasChildren | `Call` | `parent` | audited |
| `contact` | Contact (Primary) | belongsTo | `Contact` | `casesPrimary` | — |
| `contacts` | contacts | hasMany | `Contact` | `cases` | — |
| `createdBy` | createdBy | belongsTo | `User` | `—` | — |
| `emails` | Emails | hasChildren | `Email` | `parent` | — |
| `inboundEmail` | Group Email Account | belongsTo | `InboundEmail` | `—` | — |
| `lead` | Lead | belongsTo | `Lead` | `cases` | — |
| `meetings` | Meetings | hasChildren | `Meeting` | `parent` | audited |
| `modifiedBy` | modifiedBy | belongsTo | `User` | `—` | — |
| `tasks` | Tasks | hasChildren | `Task` | `parent` | audited |
| `teams` | teams | hasMany | `Team` | `—` | relation `entityTeam` |

---

_Generated 2026-05-26 from `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`._
