# ClientNote

**Entity name:** `ClientNote`  
**Plural label:** ClientNote  
**Type:** Custom entity (module: `Custom`)  
**Field count:** 8  
**Link count:** 3  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/ClientNote`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/ClientNote/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/ClientNote`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/ClientNote/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/ClientNote/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `account` | Account | link | yes | — | required |
| `category` | Category | enum | yes | `General Note` | required, custom |
| `content` | Note | text | yes | — | required, custom |
| `createdAt` | Date | datetime |  | — | read-only |
| `createdBy` | Added By | link |  | — | read-only |
| `modifiedAt` | Modified | datetime |  | — | read-only |
| `modifiedBy` | Modified By | link |  | — | read-only |
| `name` | name | varchar |  | — | max 255 |

## Allowed values (enum / multi-enum / array / checklist)

### `category` — Category

- Type: `enum`
- Default: `General Note`
- Options:
  - `""` _(empty)_
  - `General Note`
  - `Request`

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `account` | Account | belongsTo | `Account` | `clientNotes` | custom |
| `createdBy` | createdBy | belongsTo | `User` | `—` | — |
| `modifiedBy` | modifiedBy | belongsTo | `User` | `—` | — |

---

_Generated 2026-06-06 from a read-only live metadata pull (`metadata.php` cache, equivalent to `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`)._
