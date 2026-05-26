# Document

**Entity name:** `Document`  
**Plural label:** Documents  
**Type:** Core entity (module: `Crm`)  
**Field count:** 15  
**Link count:** 9  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Document`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Document/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Document`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Document/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Document/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `accounts` | Accounts | linkMultiple |  | — | — |
| `assignedUser` | assignedUser | link |  | — | — |
| `createdAt` | createdAt | datetime |  | — | read-only |
| `createdBy` | createdBy | link |  | — | read-only |
| `description` | Description | text |  | — | — |
| `expirationDate` | Expiration Date | date |  | — | — |
| `file` | File | file | yes | — | required |
| `folder` | Folder | link |  | — | — |
| `modifiedAt` | modifiedAt | datetime |  | — | read-only |
| `modifiedBy` | modifiedBy | link |  | — | read-only |
| `name` | Name | varchar | yes | — | required, pattern |
| `publishDate` | Publish Date | date | yes | `javascript: return this.dateTime.getToday();` | required |
| `status` | Status | enum |  | `Active` | — |
| `teams` | teams | linkMultiple |  | — | — |
| `type` | Type | enum |  | — | — |

## Allowed values (enum / multi-enum / array / checklist)

### `status` — Status

- Type: `enum`
- Default: `Active`
- Options:
  - `Draft`
  - `Active`
  - `Canceled`
  - `Expired`

### `type` — Type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Contract`
  - `NDA`
  - `EULA`
  - `License Agreement`

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `accounts` | Accounts | hasMany | `Account` | `documents` | — |
| `assignedUser` | assignedUser | belongsTo | `User` | `—` | — |
| `contacts` | Contacts | hasMany | `Contact` | `documents` | — |
| `createdBy` | createdBy | belongsTo | `User` | `—` | — |
| `folder` | Folder | belongsTo | `DocumentFolder` | `documents` | — |
| `leads` | Leads | hasMany | `Lead` | `documents` | — |
| `modifiedBy` | modifiedBy | belongsTo | `User` | `—` | — |
| `opportunities` | Opportunities | hasMany | `Opportunity` | `documents` | — |
| `teams` | teams | hasMany | `Team` | `—` | relation `entityTeam` |

---

_Generated 2026-05-26 from `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`._
