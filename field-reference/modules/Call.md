# Call

**Entity name:** `Call`  
**Plural label:** Calls  
**Type:** Core entity (module: `Crm`)  
**Field count:** 22  
**Link count:** 9  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Call`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Call/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Call`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Call/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Call/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `acceptanceStatus` | Acceptance Status | enum |  | — | — |
| `account` | Account | link |  | — | read-only |
| `assignedUser` | assignedUser | link | yes | — | required |
| `contacts` | Contacts | linkMultiple |  | — | — |
| `createdAt` | createdAt | datetime |  | — | read-only |
| `createdBy` | createdBy | link |  | — | read-only |
| `dateEnd` | Date End | datetime | yes | — | required |
| `dateStart` | Date Start | datetime | yes | `javascript: return this.dateTime.getNow(15);` | required |
| `description` | Description | text |  | — | — |
| `direction` | Direction | enum |  | `Outbound` | — |
| `duration` | Duration | duration |  | 300 | — |
| `leads` | Leads | linkMultiple |  | — | — |
| `modifiedAt` | modifiedAt | datetime |  | — | read-only |
| `modifiedBy` | modifiedBy | link |  | — | read-only |
| `name` | Name | varchar | yes | — | required, pattern |
| `parent` | Parent | linkParent |  | — | — |
| `phoneNumbersMap` | phoneNumbersMap | jsonObject |  | — | — |
| `reminders` | Reminders | jsonArray |  | — | — |
| `status` | Status | enum |  | `Planned` | — |
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

### `direction` — Direction

- Type: `enum`
- Default: `Outbound`
- Options:
  - `Outbound`
  - `Inbound`

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
| `contacts` | contacts | hasMany | `Contact` | `calls` | — |
| `createdBy` | createdBy | belongsTo | `User` | `—` | — |
| `leads` | leads | hasMany | `Lead` | `calls` | — |
| `modifiedBy` | modifiedBy | belongsTo | `User` | `—` | — |
| `parent` | parent | belongsToParent | `calls` | `calls` | — |
| `teams` | teams | hasMany | `Team` | `—` | relation `entityTeam` |
| `users` | users | hasMany | `User` | `calls` | — |

---

_Generated 2026-05-26 from `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`._
