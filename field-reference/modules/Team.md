# Team

**Entity name:** `Team`  
**Plural label:** Teams  
**Type:** Core entity  
**Field count:** 9  
**Link count:** 7  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Team`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Team/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Team`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Team/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Team/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `createdAt` | createdAt | datetime |  | — | read-only |
| `description` | description | text |  | — | — |
| `layoutSet` | Layout Set | link |  | — | — |
| `modifiedAt` | modifiedAt | datetime |  | — | read-only |
| `name` | Name | varchar |  | — | max 100, pattern |
| `positionList` | Position List | array |  | — | — |
| `roles` | Roles | linkMultiple |  | — | — |
| `userRole` | User Role | varchar |  | — | — |
| `workingTimeCalendar` | Working Time Calendar | link |  | — | — |

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `groupEmailFolders` | Group Email Folders | hasMany | `GroupEmailFolder` | `teams` | — |
| `inboundEmails` | Group Email Accounts | hasMany | `InboundEmail` | `teams` | — |
| `layoutSet` | Layout Set | belongsTo | `LayoutSet` | `teams` | — |
| `notes` | Notes | hasMany | `Note` | `teams` | — |
| `roles` | Roles | hasMany | `Role` | `teams` | — |
| `users` | Users | hasMany | `User` | `teams` | — |
| `workingTimeCalendar` | Working Time Calendar | belongsTo | `WorkingTimeCalendar` | `teams` | — |

---

_Generated 2026-06-06 from a read-only live metadata pull (`metadata.php` cache, equivalent to `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`)._
