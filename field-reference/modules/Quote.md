# Quote

**Entity name:** `Quote`  
**Plural label:** Quote  
**Type:** Custom entity (module: `Custom`)  
**Field count:** 14  
**Link count:** 2  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Quote`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Quote/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Quote`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Quote/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Quote/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `account` | Account | link |  | — | — |
| `carrier` | Carrier | varchar | yes | — | required, max 255, custom |
| `effectiveDate` | Effective Date | date |  | — | custom |
| `expirationDate` | Expiration Date | date |  | — | custom |
| `lineOfBusiness` | Line of Business | enum |  | — | custom |
| `name` | name | varchar |  | — | max 255 |
| `notes` | Notes | text |  | — | custom |
| `opportunity` | Opportunity | link | yes | — | required |
| `premium` | Premium Amount | currency |  | — | custom |
| `premiumConverted` | premiumConverted | currencyConverted |  | — | read-only |
| `premiumCurrency` | premiumCurrency | enum |  | — | max 3 |
| `quoteNumber` | Quote Number | varchar | yes | — | required, max 100, custom |
| `status` | Status | enum | yes | `Draft` | required, custom |
| `streamUpdatedAt` | streamUpdatedAt | datetime |  | — | read-only |

## Allowed values (enum / multi-enum / array / checklist)

### `lineOfBusiness` — Line of Business

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Commercial Auto`
  - `Transportation / Trucking`
  - `General Liability`
  - `Workers Comp`
  - `Commercial Property`
  - `BOP`
  - `Professional Liability`
  - `Umbrella`
  - `Other`

### `status` — Status

- Type: `enum`
- Default: `Draft`
- Options:
  - `""` _(empty)_
  - `Draft`
  - `Received`
  - `Presented`
  - `Bound`
  - `Declined`

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `account` | account | belongsTo | `Account` | `quotes` | custom |
| `opportunity` | opportunity | belongsTo | `Opportunity` | `quotes` | custom |

---

_Generated 2026-05-26 from `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`._
