# Currency Record

**Entity name:** `CurrencyRecord`  
**Plural label:** Currencies  
**Type:** Core entity  
**Field count:** 8  
**Link count:** 1  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/CurrencyRecord`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/CurrencyRecord/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/CurrencyRecord`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/CurrencyRecord/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/CurrencyRecord/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `code` | Code | varchar | yes | — | required, read-only, max 3 |
| `deleteId` | deleteId | varchar |  | `0` | read-only, not-null, max 17 |
| `isBase` | Is Base | bool |  | — | read-only |
| `label` | Label | varchar |  | — | read-only |
| `rate` | Rate | decimal |  | — | read-only |
| `rateDate` | Rate Date | date |  | — | read-only |
| `status` | Status | enum |  | `Active` | max 8 |
| `symbol` | Symbol | varchar |  | — | read-only |

## Allowed values (enum / multi-enum / array / checklist)

### `status` — Status

- Type: `enum`
- Default: `Active`
- Options:
  - `Active`
  - `Inactive`

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `rates` | Rates | hasMany | `CurrencyRecordRate` | `record` | read-only |

---

_Generated 2026-05-26 from `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`._
