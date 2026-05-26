# Import

**Entity name:** `Import`  
**Plural label:** Import  
**Type:** Core entity  
**Field count:** 11  
**Link count:** 2  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Import`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Import/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Import`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Import/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Import/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `attributeList` | attributeList | jsonArray |  | — | read-only |
| `createdAt` | createdAt | datetime |  | — | read-only |
| `createdBy` | createdBy | link |  | — | read-only |
| `duplicateCount` | duplicateCount | int |  | — | read-only |
| `entityType` | Entity Type | enum | yes | — | required, read-only |
| `file` | File | file | yes | — | required, read-only |
| `importedCount` | importedCount | int |  | — | read-only |
| `lastIndex` | lastIndex | int |  | — | read-only |
| `params` | params | jsonObject |  | — | read-only |
| `status` | Status | enum |  | — | read-only |
| `updatedCount` | updatedCount | int |  | — | read-only |

## Allowed values (enum / multi-enum / array / checklist)

### `status` — Status

- Type: `enum`
- Options:
  - `Standby`
  - `Pending`
  - `In Process`
  - `Complete`
  - `Failed`

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `createdBy` | createdBy | belongsTo | `User` | `—` | — |
| `errors` | Errors | hasMany | `ImportError` | `import` | read-only |

---

_Generated 2026-05-26 from `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`._
