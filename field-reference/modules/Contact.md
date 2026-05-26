# Contact

**Entity name:** `Contact`  
**Plural label:** Contacts  
**Type:** Core entity (module: `Crm`)  
**Field count:** 80  
**Link count:** 26  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Contact`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Contact/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Contact`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Contact/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Contact/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `acceptanceStatus` | Acceptance Status | varchar |  | — | — |
| `acceptanceStatusCalls` | Acceptance Status (Calls) | enum |  | — | — |
| `acceptanceStatusMeetings` | Acceptance Status (Meetings) | enum |  | — | — |
| `account` | Account | link |  | — | — |
| `accountAnyId` | accountAnyId | varchar |  | — | — |
| `accountIsInactive` | Account Inactive | bool |  | — | — |
| `accountRole` | Title | varchar |  | — | — |
| `accountType` | Account Type | foreign |  | — | read-only |
| `accounts` | Accounts | linkMultiple |  | — | custom |
| `activityLogs` | activityLogs | linkMultiple |  | — | custom |
| `address` | Address | address |  | — | — |
| `addressCity` | addressCity | varchar |  | — | max 100, pattern |
| `addressCountry` | addressCountry | varchar |  | — | max 100, pattern |
| `addressMap` | addressMap | map |  | — | read-only |
| `addressPostalCode` | addressPostalCode | varchar |  | — | max 40, pattern |
| `addressState` | addressState | varchar |  | — | max 100, pattern |
| `addressStreet` | addressStreet | text |  | — | max 255 |
| `aepSepDate` | AEP/SEP Date | date |  | — | custom |
| `assignedUser` | assignedUser | link |  | — | — |
| `campaign` | Campaign | link |  | — | — |
| `clientType` | clientType | enum |  | — | custom |
| `commissions` | commissions | linkMultiple |  | — | custom |
| `contactCategory` | contactCategory | enum |  | — | custom |
| `contactDriveFolderUrl` | Drive Folder URL | url |  | — | custom |
| `contactRole` | Contact Role | enum |  | — | custom |
| `contactType` | Contact Type | enum |  | — | custom |
| `createdAt` | createdAt | datetime |  | — | read-only |
| `createdBy` | createdBy | link |  | — | read-only |
| `csrName` | Customer Service Representative Name | varchar |  | — | read-only, max 255, custom |
| `dateOfBirth` | dateOfBirth | date |  | — | custom |
| `daysUntil65` | Days Until 65 | int |  | — | read-only, custom |
| `description` | Description | text |  | — | — |
| `doNotCall` | Do Not Call | bool |  | — | — |
| `emailAddress` | Email | email |  | — | — |
| `emailAddressIsInvalid` | emailAddressIsInvalid | bool |  | — | — |
| `emailAddressIsOptedOut` | emailAddressIsOptedOut | bool |  | — | — |
| `firstName` | firstName | varchar |  | — | max 100, pattern |
| `hasPortalUser` | Has Portal User | bool |  | — | read-only |
| `householdRole` | householdRole | enum |  | — | custom |
| `irmaApplies` | IRMAA Applies | bool |  | false | custom |
| `lastName` | lastName | varchar | yes | — | required, max 100, pattern |
| `lifeAnnualPremium` | Annual Premium | currency |  | — | custom |
| `lifeAnnualPremiumConverted` | lifeAnnualPremiumConverted | currencyConverted |  | — | read-only |
| `lifeAnnualPremiumCurrency` | lifeAnnualPremiumCurrency | enum |  | — | max 3 |
| `lifeBeneficiaryOnFile` | Beneficiary On File | bool |  | false | custom |
| `lifeCoverageInForce` | Coverage In Force | bool |  | false | custom |
| `lifeFaceAmount` | Face Amount | currency |  | — | custom |
| `lifeFaceAmountConverted` | lifeFaceAmountConverted | currencyConverted |  | — | read-only |
| `lifeFaceAmountCurrency` | lifeFaceAmountCurrency | enum |  | — | max 3 |
| `lifeHealthClass` | Health Class | enum |  | — | custom |
| `lifePolicyType` | Policy Type | enum |  | — | custom |
| `lifeReviewDate` | Life Review Date | date |  | — | custom |
| `medicareCarrier` | Medicare Carrier | varchar |  | — | max 255, custom |
| `medicareEligible` | Medicare Eligible | bool |  | false | custom |
| `medicarePartB` | Medicare Part B | bool |  | false | custom |
| `medicarePlanType` | Plan Type | enum |  | — | custom |
| `middleName` | middleName | varchar |  | — | max 100, pattern |
| `modifiedAt` | modifiedAt | datetime |  | — | read-only |
| `modifiedBy` | modifiedBy | link |  | — | read-only |
| `momentumClientId` | Momentum Client ID | varchar |  | — | read-only, max 255, custom |
| `name` | Name | personName |  | — | — |
| `opportunityRole` | Opportunity Role | enum |  | — | — |
| `originalEmail` | Original Email | link |  | — | — |
| `originalLead` | Original Lead | linkOne |  | — | read-only |
| `originalLeadSource` | Original Lead Source | varchar |  | — | read-only, max 255, custom |
| `phoneNumber` | Phone | phone |  | — | — |
| `phoneNumberIsInvalid` | phoneNumberIsInvalid | bool |  | — | — |
| `phoneNumberIsOptedOut` | phoneNumberIsOptedOut | bool |  | — | — |
| `policies` | policies | linkMultiple |  | — | custom |
| `portalUser` | Portal User | linkOne |  | — | read-only |
| `relationshipToAccount` | Relationship to Account | enum |  | — | custom |
| `renewals` | renewals | linkMultiple |  | — | custom |
| `salutationName` | salutationName | enum |  | — | — |
| `streamUpdatedAt` | streamUpdatedAt | datetime |  | — | read-only |
| `targetList` | Target List | link |  | — | — |
| `targetListIsOptedOut` | targetListIsOptedOut | bool |  | — | read-only |
| `targetLists` | Target Lists | linkMultiple |  | — | — |
| `teams` | teams | linkMultiple |  | — | — |
| `title` | Account Title | varchar |  | — | max 100 |
| `underwrittenPolicies` | Underwritten Policies | linkMultiple |  | — | custom |

## Allowed values (enum / multi-enum / array / checklist)

### `clientType` — clientType

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Personal`
  - `Commercial`

### `contactCategory` — contactCategory

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Client`
  - `Prospect`
  - `Carrier`
  - `Other`

### `contactRole` — Contact Role

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Underwriter`

### `contactType` — Contact Type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Client`
  - `Prospect`
  - `Spouse`
  - `Dependent`
  - `Business Owner`

### `householdRole` — householdRole

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Primary`
  - `Spouse`
  - `Dependent`
  - `Co-insured`

### `lifeHealthClass` — Health Class

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Preferred Plus`
  - `Preferred`
  - `Standard Plus`
  - `Standard`
  - `Substandard`

### `lifePolicyType` — Policy Type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Term`
  - `Whole`
  - `Universal`

### `medicarePlanType` — Plan Type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Supplement`
  - `Advantage`
  - `Part D`

### `opportunityRole` — Opportunity Role

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Decision Maker`
  - `Evaluator`
  - `Influencer`

### `relationshipToAccount` — Relationship to Account

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Head of Household`
  - `Secondary`
  - `Employee`

### `salutationName` — salutationName

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Mr.`
  - `Ms.`
  - `Mrs.`
  - `Dr.`

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `account` | Account (Primary) | belongsTo | `Account` | `—` | — |
| `accounts` | Accounts | hasMany | `Account` | `contacts` | custom, relation `accountContact` |
| `activityLogs` | activityLogs | hasMany | `ActivityLog` | `contact` | custom |
| `assignedUser` | assignedUser | belongsTo | `User` | `—` | — |
| `calls` | calls | hasMany | `Call` | `contacts` | audited |
| `campaign` | Campaign | belongsTo | `Campaign` | `contacts` | — |
| `campaignLogRecords` | Campaign Log | hasChildren | `CampaignLogRecord` | `parent` | — |
| `cases` | Cases | hasMany | `Case` | `contacts` | — |
| `casesPrimary` | Cases (Primary) | hasMany | `Case` | `contact` | — |
| `commissions` | commissions | hasMany | `Commission` | `contact` | custom |
| `createdBy` | createdBy | belongsTo | `User` | `—` | — |
| `documents` | Documents | hasMany | `Document` | `contacts` | audited |
| `emails` | emails | hasChildren | `Email` | `parent` | — |
| `meetings` | meetings | hasMany | `Meeting` | `contacts` | audited |
| `modifiedBy` | modifiedBy | belongsTo | `User` | `—` | — |
| `opportunities` | Opportunities | hasMany | `Opportunity` | `contacts` | — |
| `opportunitiesPrimary` | Opportunities (Primary) | hasMany | `Opportunity` | `contact` | — |
| `originalLead` | Original Lead | hasOne | `Lead` | `createdContact` | — |
| `policies` | policies | hasMany | `Policy` | `contact` | custom |
| `portalUser` | Portal User | hasOne | `User` | `contact` | — |
| `renewals` | renewals | hasMany | `Renewal` | `contact` | custom |
| `targetLists` | Target Lists | hasMany | `TargetList` | `contacts` | — |
| `tasks` | tasks | hasChildren | `Task` | `parent` | audited |
| `tasksPrimary` | Tasks (expanded) | hasMany | `Task` | `contact` | — |
| `teams` | teams | hasMany | `Team` | `—` | relation `entityTeam` |
| `underwrittenPolicies` | underwrittenPolicies | hasMany | `Policy` | `underwriter` | custom |

## Unique indexes

- **createdAtId**: `createdAt`, `id`

---

_Generated 2026-05-26 from `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`._
