# Renewal

**Entity name:** `Renewal`  
**Plural label:** Renewals  
**Type:** Custom entity (module: `Custom`)  
**Field count:** 35  
**Link count:** 10  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Renewal`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Renewal/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Renewal`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Renewal/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Renewal/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `account` | Account | link | yes | — | required |
| `assignedUser` | assignedUser | link |  | — | — |
| `carrier` | Carrier | varchar |  | — | max 255, custom |
| `commission_rate` | Commission Rate (Percent) | float |  | — | custom |
| `commissions` | Commissions | linkMultiple |  | — | custom |
| `contact` | Contact | link |  | — | — |
| `createdAt` | createdAt | datetime |  | — | read-only |
| `createdBy` | createdBy | link |  | — | read-only |
| `currentPremiumConverted` | currentPremiumConverted | currencyConverted |  | — | read-only |
| `currentPremiumCurrency` | currentPremiumCurrency | enum |  | — | max 3 |
| `current_premium` | Current Premium | currency |  | — | custom |
| `expectedCommissionConverted` | expectedCommissionConverted | currencyConverted |  | — | read-only |
| `expectedCommissionCurrency` | expectedCommissionCurrency | enum |  | — | max 3 |
| `expected_commission` | Expected Commission | currency |  | — | custom |
| `expiration_date` | Expiration Date | date |  | — | custom |
| `last_contact_date` | Last Contact Date | date |  | — | custom |
| `last_contact_method` | Last Contact Method | enum |  | — | custom |
| `line_of_business` | Line of Business | enum |  | — | read-only, custom |
| `lost_reason` | Lost Reason | enum |  | — | custom |
| `modifiedAt` | modifiedAt | datetime |  | — | read-only |
| `modifiedBy` | modifiedBy | link |  | — | read-only |
| `name` | Renewal Name | varchar |  | — | max 255 |
| `newPolicy` | Renewed Policy | link |  | — | — |
| `policy` | Expiring Policy | link | yes | — | required |
| `premium_change` | Premium Change (Percent) | float |  | — | read-only, custom |
| `renewalPremiumConverted` | renewalPremiumConverted | currencyConverted |  | — | read-only |
| `renewalPremiumCurrency` | renewalPremiumCurrency | enum |  | — | max 3 |
| `renewal_effective_date` | Renewal Effective Date | date |  | — | custom |
| `renewal_notes` | Renewal Notes | text |  | — | custom |
| `renewal_premium` | Renewal Premium | currency |  | — | custom |
| `stage` | Stage | enum |  | `Identified` | custom |
| `streamUpdatedAt` | streamUpdatedAt | datetime |  | — | read-only |
| `tasks` | Tasks | linkMultiple |  | — | custom |
| `teams` | teams | linkMultiple |  | — | — |
| `urgency` | Urgency | enum |  | — | custom |

## Allowed values (enum / multi-enum / array / checklist)

### `last_contact_method` — Last Contact Method

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Email`
  - `Call`
  - `Text`
  - `In person`

### `line_of_business` — Line of Business

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Commercial Auto`
  - `General Liability`
  - `Workers Comp`
  - `Commercial Property`
  - `BOP`
  - `Professional Liability`
  - `Umbrella`
  - `Builders Risk`
  - `Inland Marine`
  - `Personal Auto`
  - `Homeowners`
  - `Renters`
  - `Condo`
  - `Dwelling Fire`
  - `Motorcycle`
  - `Boat`
  - `RV`
  - `Life`
  - `Health`
  - `Medicare`
  - `Group Benefits`
  - `Garagekeepers`
  - `Commercial Package`
  - `Other`

### `lost_reason` — Lost Reason

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Price`
  - `Coverage`
  - `Unresponsive`
  - `Moved carrier`
  - `Other`

### `stage` — Stage

- Type: `enum`
- Default: `Identified`
- Options:
  - `""` _(empty)_
  - `Identified`
  - `Outreach Sent`
  - `Quote Requested`
  - `Proposal Sent`
  - `Negotiating`
  - `Renewed - Won`
  - `Lost`

### `urgency` — Urgency

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Critical`
  - `High`
  - `Medium`
  - `Low`

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `account` | Account | belongsTo | `Account` | `renewals` | custom |
| `assignedUser` | assignedUser | belongsTo | `User` | `—` | — |
| `commissions` | Commissions | hasMany | `Commission` | `renewal` | custom |
| `contact` | Contact | belongsTo | `Contact` | `renewals` | custom |
| `createdBy` | createdBy | belongsTo | `User` | `—` | — |
| `modifiedBy` | modifiedBy | belongsTo | `User` | `—` | — |
| `newPolicy` | Renewed Policy | belongsTo | `Policy` | `renewedFrom` | custom |
| `policy` | Expiring Policy | belongsTo | `Policy` | `renewals` | custom |
| `tasks` | Tasks | hasChildren | `Task` | `parent` | custom |
| `teams` | teams | hasMany | `Team` | `—` | relation `entityTeam` |

---

_Generated 2026-05-26 from `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`._
