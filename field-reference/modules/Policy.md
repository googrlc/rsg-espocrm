# Policy

**Entity name:** `Policy`  
**Plural label:** Policies  
**Type:** Custom entity (module: `Custom`)  
**Field count:** 69  
**Link count:** 13  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Policy`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Policy/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Policy`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Policy/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Policy/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `acceptedByAmsAt` | AMS Accepted At | datetime |  | — | read-only, custom |
| `acceptedByAmsBy` | AMS Accepted By | varchar |  | — | read-only, max 100, custom |
| `account` | account | link |  | — | — |
| `activityLogs` | activityLogs | linkMultiple |  | — | custom |
| `agencyFeeConverted` | agencyFeeConverted | currencyConverted |  | — | read-only |
| `agencyFeeCurrency` | agencyFeeCurrency | enum |  | — | max 3 |
| `agency_fee` | Agency Fee | currency |  | — | custom |
| `amsLockReason` | AMS Lock Reason | text |  | — | read-only, custom |
| `amsLockState` | AMS Lock State | enum |  | `Unlocked` | read-only, custom |
| `assignedUser` | assignedUser | link |  | — | — |
| `billing_type` | Billing Type | enum |  | — | custom |
| `bind_date` | Bind Date | date |  | — | custom |
| `business_type` | Business Type | varchar |  | — | max 100, custom |
| `cancellation_date` | Cancellation Date | date |  | — | custom |
| `cancellation_reason` | Cancellation Reason | text |  | — | custom |
| `carrier` | Carrier | varchar |  | — | max 255, custom |
| `carrierAccount` | Carrier Account | link |  | — | — |
| `carrierPortalUrl` | Carrier Portal | url |  | — | read-only, custom |
| `commissionAmount` | Commission Amount | currency |  | — | read-only, custom |
| `commissionAmountConverted` | commissionAmountConverted | currencyConverted |  | — | read-only |
| `commissionAmountCurrency` | commissionAmountCurrency | enum |  | — | read-only, max 3 |
| `commission_rate` | Commission Rate (Percent) | float |  | — | custom |
| `commissions` | commissions | linkMultiple |  | — | custom |
| `contact` | contact | link |  | — | — |
| `coverageAmountConverted` | coverageAmountConverted | currencyConverted |  | — | read-only |
| `coverageAmountCurrency` | coverageAmountCurrency | enum |  | — | max 3 |
| `coverage_amount` | Coverage Amount | currency |  | — | custom |
| `createdAt` | createdAt | datetime |  | — | read-only |
| `createdBy` | createdBy | link |  | — | read-only |
| `daysRemaining` | Days Remaining | int |  | — | read-only, custom |
| `deductible` | Deductible | currency |  | — | custom |
| `deductibleConverted` | deductibleConverted | currencyConverted |  | — | read-only |
| `deductibleCurrency` | deductibleCurrency | enum |  | — | max 3 |
| `effective_date` | Effective Date | date |  | — | custom |
| `email_sequence_started` | Email Sequence Started | bool |  | false | custom |
| `expiration_date` | Expiration Date | date |  | — | custom |
| `insuredMomentumId` | NowCerts Insured ID | varchar |  | — | read-only, max 255, custom |
| `last_contact_date` | Last Contact Date | date |  | — | custom |
| `last_contact_method` | Last Contact Method | enum |  | — | custom |
| `line_of_business` | Line of Business | varchar |  | — | max 500, custom |
| `line_of_business_raw` | Line of Business (original) | varchar |  | — | read-only, max 500, custom |
| `modifiedAt` | modifiedAt | datetime |  | — | read-only |
| `modifiedBy` | modifiedBy | link |  | — | read-only |
| `momentumPolicyId` | NowCerts Policy ID | varchar |  | — | read-only, max 255, custom |
| `momentum_last_synced` | Momentum Last Synced | datetime |  | — | read-only, custom |
| `name` | name | varchar |  | — | max 100 |
| `opportunities` | opportunities | linkMultiple |  | — | — |
| `policy_notes` | Policy Notes | text |  | — | custom |
| `policy_number` | Policy Number | varchar |  | — | max 100, custom |
| `policy_term` | Policy Term (months) | int |  | — | custom |
| `premiumAmountConverted` | premiumAmountConverted | currencyConverted |  | — | read-only |
| `premiumAmountCurrency` | premiumAmountCurrency | enum |  | — | max 3 |
| `premiumAtRisk` | Premium at Risk | currency |  | — | custom |
| `premiumAtRiskConverted` | premiumAtRiskConverted | currencyConverted |  | — | read-only |
| `premiumAtRiskCurrency` | premiumAtRiskCurrency | enum |  | — | max 3 |
| `premium_amount` | Premium Amount | currency |  | — | custom |
| `propertyAddress` | Property Address | text |  | — | custom |
| `reinstatement_date` | Reinstatement Date | date |  | — | custom |
| `renewals` | renewals | linkMultiple |  | — | custom |
| `renewedFrom` | renewedFrom | linkMultiple |  | — | custom |
| `status` | Status | enum |  | `Active` | custom |
| `statusLabel` | Status Badge | enum |  | — | read-only, custom |
| `streamUpdatedAt` | streamUpdatedAt | datetime |  | — | read-only |
| `sync_status` | Sync Status | enum |  | `Pending` | read-only, custom |
| `teams` | teams | linkMultiple |  | — | — |
| `underwriter` | Underwriter | link |  | — | custom |
| `urgency` | Urgency | enum |  | — | custom |
| `urgencyIcon` | Urgency | varchar |  | — | read-only, max 8, custom |
| `vin` | VIN | varchar |  | — | max 100, custom |

## Allowed values (enum / multi-enum / array / checklist)

### `amsLockState` — AMS Lock State

- Type: `enum`
- Default: `Unlocked`
- Options:
  - `""` _(empty)_
  - `Unlocked`
  - `Pending AMS`
  - `Locked by AMS`
  - `Rejected by AMS`

### `billing_type` — Billing Type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Direct Bill`
  - `Agency Bill`
  - `Direct Bill 100`
  - `Agency Bill 100`

### `last_contact_method` — Last Contact Method

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Phone`
  - `Email`
  - `Text`
  - `No Response`

### `status` — Status

- Type: `enum`
- Default: `Active`
- Options:
  - `""` _(empty)_
  - `Active`
  - `Up for Renewal`
  - `Renewing`
  - `Renewed`
  - `Expired`
  - `Cancelled`
  - `Flat Cancel`
  - `Pending Cancel`
  - `Non-Renewed`
  - `Lapsed`

### `statusLabel` — Status Badge

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `ACTIVE`
  - `UNKNOWN`
  - `RENEWAL WINDOW`
  - `CRITICAL RENEWAL`
  - `EXPIRED`

### `sync_status` — Sync Status

- Type: `enum`
- Default: `Pending`
- Options:
  - `""` _(empty)_
  - `Synced`
  - `Pending`
  - `Error`
  - `Skipped`

### `urgency` — Urgency

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Low`
  - `Medium`
  - `High`
  - `Critical`

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `account` | account | belongsTo | `Account` | `policies` | custom |
| `activityLogs` | activityLogs | hasMany | `ActivityLog` | `policy` | custom |
| `assignedUser` | assignedUser | belongsTo | `User` | `—` | — |
| `carrierAccount` | carrierAccount | belongsTo | `Account` | `carrierPolicies` | custom |
| `commissions` | commissions | hasMany | `Commission` | `policy` | custom |
| `contact` | contact | belongsTo | `Contact` | `policies` | custom |
| `createdBy` | createdBy | belongsTo | `User` | `—` | — |
| `modifiedBy` | modifiedBy | belongsTo | `User` | `—` | — |
| `opportunities` | opportunities | hasMany | `Opportunity` | `policies` | custom, relation `policyOpportunity` |
| `renewals` | renewals | hasMany | `Renewal` | `policy` | custom |
| `renewedFrom` | renewedFrom | hasMany | `Renewal` | `newPolicy` | custom |
| `teams` | teams | hasMany | `Team` | `—` | relation `entityTeam` |
| `underwriter` | underwriter | belongsTo | `Contact` | `underwrittenPolicies` | custom |

---

_Generated 2026-05-26 from `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`._
