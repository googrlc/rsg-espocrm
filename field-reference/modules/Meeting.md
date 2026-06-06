# Meeting

**Entity name:** `Meeting`  
**Plural label:** Meetings  
**Type:** Core entity (module: `Crm`)  
**Field count:** 26  
**Link count:** 9  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Meeting`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Meeting/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Meeting`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Meeting/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Meeting/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `acceptanceStatus` | Acceptance Status | enum |  | — | — |
| `account` | Account | link |  | — | read-only |
| `assignedUser` | assignedUser | link | yes | — | required |
| `contacts` | Contacts | linkMultiple |  | — | — |
| `createdAt` | createdAt | datetime |  | — | read-only |
| `createdBy` | createdBy | link |  | — | read-only |
| `dateEnd` | Date End | datetimeOptional | yes | — | required |
| `dateEndDate` | Date End (all day) | date |  | — | — |
| `dateStart` | Date Start | datetimeOptional | yes | `javascript: return this.dateTime.getNow(15);` | required |
| `dateStartDate` | Date Start (all day) | date |  | — | — |
| `description` | Description | text |  | — | — |
| `duration` | Duration | duration |  | 3600 | — |
| `isAllDay` | Is All-Day | bool |  | — | — |
| `joinUrl` | Join URL | url |  | — | read-only, max 320 |
| `leads` | Leads | linkMultiple |  | — | — |
| `modifiedAt` | modifiedAt | datetime |  | — | read-only |
| `modifiedBy` | modifiedBy | link |  | — | read-only |
| `name` | Name | varchar | yes | — | required, pattern |
| `parent` | Parent | linkParent |  | — | — |
| `reminders` | Reminders | jsonArray |  | — | — |
| `sourceEmail` | Source Email | link |  | — | — |
| `status` | Status | enum |  | `Planned` | — |
| `streamUpdatedAt` | streamUpdatedAt | datetime |  | — | read-only |
| `teams` | teams | linkMultiple |  | — | — |
| `uid` | UID | varchar |  | — | read-only, max 255 |
| `users` | Users | linkMultiple |  | — | — |

## Allowed values (enum / multi-enum / array / checklist)

### `acceptanceStatus` — Acceptance Status

- Type: `enum`
- Options:
  - `None`
  - `Accepted`
  - `Tentative`
  - `Declined`

### `status` — Status

- Type: `enum`
- Default: `Planned`
- Options:
  - `Planned`
  - `Held`
  - `Not Held`

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `account` | account | belongsTo | `Account` | `—` | — |
| `assignedUser` | assignedUser | belongsTo | `User` | `—` | — |
| `contacts` | contacts | hasMany | `Contact` | `meetings` | — |
| `createdBy` | createdBy | belongsTo | `User` | `—` | — |
| `leads` | leads | hasMany | `Lead` | `meetings` | — |
| `modifiedBy` | modifiedBy | belongsTo | `User` | `—` | — |
| `parent` | parent | belongsToParent | `meetings` | `meetings` | — |
| `teams` | teams | hasMany | `Team` | `—` | relation `entityTeam` |
| `users` | users | hasMany | `User` | `meetings` | — |

---

_Generated 2026-06-06 from a read-only live metadata pull (`metadata.php` cache, equivalent to `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`)._
