# Target List

**Entity name:** `TargetList`  
**Plural label:** Target Lists  
**Type:** Core entity (module: `Crm`)  
**Field count:** 16  
**Link count:** 13  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/TargetList`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/TargetList/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/TargetList`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/TargetList/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/TargetList/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `assignedUser` | assignedUser | link |  | — | — |
| `category` | Category | link |  | — | — |
| `createdAt` | createdAt | datetime |  | — | read-only |
| `createdBy` | createdBy | link |  | — | read-only |
| `description` | Description | text |  | — | — |
| `entryCount` | Entry Count | int |  | — | read-only |
| `excludingActionList` | Excluding | multiEnum |  | — | — |
| `includingActionList` | Including | multiEnum |  | — | — |
| `isOptedOut` | Is Opted Out | bool |  | — | read-only |
| `modifiedAt` | modifiedAt | datetime |  | — | read-only |
| `modifiedBy` | modifiedBy | link |  | — | read-only |
| `name` | Name | varchar | yes | — | required, pattern |
| `optedOutCount` | Opted Out Count | int |  | — | read-only |
| `sourceCampaign` | Source Campaign | link |  | — | — |
| `targetStatus` | Target Status | enum |  | — | read-only |
| `teams` | teams | linkMultiple |  | — | — |

## Allowed values (enum / multi-enum / array / checklist)

### `targetStatus` — Target Status

- Type: `enum`
- Options:
  - `Listed`
  - `Opted Out`

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `accounts` | Accounts | hasMany | `Account` | `targetLists` | — |
| `assignedUser` | assignedUser | belongsTo | `User` | `—` | — |
| `campaigns` | Campaigns | hasMany | `Campaign` | `targetLists` | — |
| `campaignsExcluding` | campaignsExcluding | hasMany | `Campaign` | `excludingTargetLists` | — |
| `category` | Category | belongsTo | `TargetListCategory` | `category` | — |
| `contacts` | Contacts | hasMany | `Contact` | `targetLists` | — |
| `createdBy` | createdBy | belongsTo | `User` | `—` | — |
| `leads` | Leads | hasMany | `Lead` | `targetLists` | — |
| `massEmails` | Mass Emails | hasMany | `MassEmail` | `targetLists` | — |
| `massEmailsExcluding` | massEmailsExcluding | hasMany | `MassEmail` | `excludingTargetLists` | — |
| `modifiedBy` | modifiedBy | belongsTo | `User` | `—` | — |
| `teams` | teams | hasMany | `Team` | `—` | relation `entityTeam` |
| `users` | users | hasMany | `User` | `targetLists` | — |

---

_Generated 2026-06-06 from a read-only live metadata pull (`metadata.php` cache, equivalent to `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`)._
