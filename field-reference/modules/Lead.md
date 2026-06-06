# Lead

**Entity name:** `Lead`  
**Plural label:** Leads  
**Type:** Core entity (module: `Crm`)  
**Field count:** 67  
**Link count:** 17  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Lead`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Lead/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Lead`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Lead/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Lead/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `acceptanceStatus` | Acceptance Status | varchar |  | — | — |
| `acceptanceStatusCalls` | Acceptance Status (Calls) | enum |  | — | — |
| `acceptanceStatusMeetings` | Acceptance Status (Meetings) | enum |  | — | — |
| `accountName` | Account Name | varchar |  | — | pattern |
| `address` | Address | address |  | — | — |
| `addressCity` | addressCity | varchar |  | — | max 100, pattern |
| `addressCountry` | addressCountry | varchar |  | — | max 100, pattern |
| `addressMap` | addressMap | map |  | — | read-only |
| `addressPostalCode` | addressPostalCode | varchar |  | — | max 40, pattern |
| `addressState` | addressState | varchar |  | — | max 100, pattern |
| `addressStreet` | addressStreet | text |  | — | max 255 |
| `aiSummary` | AI Summary | text |  | — | custom |
| `assignedUser` | assignedUser | link |  | — | — |
| `callbackDate` | Callback Date | date |  | — | read-only, custom |
| `campaign` | Campaign | link |  | — | — |
| `convertedAt` | Converted At | datetime |  | — | read-only |
| `createdAccount` | Account | link |  | — | — |
| `createdAt` | createdAt | datetime |  | — | read-only |
| `createdBy` | createdBy | link |  | — | read-only |
| `createdContact` | Contact | link |  | — | — |
| `createdOpportunity` | Opportunity | link |  | — | — |
| `currentCarrier` | Current Carrier | varchar |  | — | max 255, custom |
| `currentMedicarePlan` | Current Medicare Plan | varchar |  | — | max 255, custom |
| `currentlyInsured` | Currently Insured? | bool |  | false | custom |
| `dateOfBirth` | Date of Birth | date |  | — | custom |
| `description` | Description | text |  | — | — |
| `doNotCall` | Do Not Call | bool |  | — | — |
| `documents` | Documents | attachmentMultiple |  | — | custom |
| `emailAddress` | Email | email |  | — | — |
| `emailAddressIsInvalid` | emailAddressIsInvalid | bool |  | — | — |
| `emailAddressIsOptedOut` | emailAddressIsOptedOut | bool |  | — | — |
| `estimatedPremium` | Estimated Premium | currency |  | — | custom |
| `estimatedPremiumConverted` | estimatedPremiumConverted | currencyConverted |  | — | read-only |
| `estimatedPremiumCurrency` | estimatedPremiumCurrency | enum |  | — | max 3 |
| `firstName` | firstName | varchar |  | — | max 100, pattern |
| `industry` | Industry | enum |  | — | — |
| `insuranceInterest` | Insurance Interest | enum |  | — | custom |
| `intelPackRun` | Intel Pack Run (AI) | bool |  | false | custom |
| `lastName` | lastName | varchar |  | — | max 100, pattern |
| `medicareEligible` | Medicare Eligible | bool |  | false | custom |
| `medicarePartADate` | Medicare Part A Date | date |  | — | custom |
| `medicarePartBDate` | Medicare Part B Date | date |  | — | custom |
| `middleName` | middleName | varchar |  | — | max 100, pattern |
| `modifiedAt` | modifiedAt | datetime |  | — | read-only |
| `modifiedBy` | modifiedBy | link |  | — | read-only |
| `name` | Name | personName |  | — | — |
| `opportunityAmount` | Opportunity Amount | currency |  | — | min 0 |
| `opportunityAmountConverted` | Opportunity Amount (converted) | currencyConverted |  | — | read-only |
| `opportunityAmountCurrency` | Opportunity Amount Currency | enum |  | — | max 3 |
| `originalEmail` | Original Email | link |  | — | — |
| `phoneNumber` | Phone | phone |  | — | — |
| `phoneNumberIsInvalid` | phoneNumberIsInvalid | bool |  | — | — |
| `phoneNumberIsOptedOut` | phoneNumberIsOptedOut | bool |  | — | — |
| `priority` | Priority | enum |  | — | custom |
| `salutationName` | salutationName | enum |  | — | — |
| `source` | Source | enum |  | — | custom |
| `sourceOpportunity` | Source Opportunity | link |  | — | read-only, custom |
| `status` | Status | enum |  | `New / Uncontacted` | — |
| `streamUpdatedAt` | streamUpdatedAt | datetime |  | — | read-only |
| `t65AlertSent` | T65 Alert Sent | bool |  | false | read-only, custom |
| `targetList` | Target List | link |  | — | — |
| `targetListIsOptedOut` | targetListIsOptedOut | bool |  | — | read-only |
| `targetLists` | Target Lists | linkMultiple |  | — | — |
| `teams` | teams | linkMultiple |  | — | — |
| `title` | Title | varchar |  | — | max 100, pattern |
| `website` | Website | url |  | — | — |
| `xDate` | Renewal X-Date | date |  | — | custom |

## Allowed values (enum / multi-enum / array / checklist)

### `insuranceInterest` — Insurance Interest

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Commercial Auto`
  - `General Liability`
  - `Workers Comp`
  - `Commercial Property`
  - `BOP`
  - `Personal Auto`
  - `Homeowners`
  - `Life`
  - `Health`
  - `Medicare`
  - `Group Benefits`
  - `Inland Marine`
  - `Multiple`
  - `Other`

### `priority` — Priority

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Hot`
  - `Warm`
  - `Cold`

### `salutationName` — salutationName

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Mr.`
  - `Ms.`
  - `Mrs.`
  - `Dr.`

### `source` — Source

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Call`
  - `Email`
  - `Existing Customer`
  - `Client Referral`
  - `Partner Referral`
  - `Public Relations`
  - `Web Site`
  - `Campaign`
  - `Other`

### `status` — Status

- Type: `enum`
- Default: `New / Uncontacted`
- Options:
  - `New / Uncontacted`
  - `Attempting Contact`
  - `Connected`
  - `Gathering Info`
  - `Qualified`
  - `Nurture`
  - `DNC`
  - `Converted`

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `assignedUser` | assignedUser | belongsTo | `User` | `—` | — |
| `calls` | calls | hasMany | `Call` | `leads` | audited |
| `campaign` | Campaign | belongsTo | `Campaign` | `leads` | — |
| `campaignLogRecords` | Campaign Log | hasChildren | `CampaignLogRecord` | `parent` | — |
| `cases` | Cases | hasMany | `Case` | `lead` | audited |
| `createdAccount` | Account | belongsTo | `Account` | `originalLead` | — |
| `createdBy` | createdBy | belongsTo | `User` | `—` | — |
| `createdContact` | Contact | belongsTo | `Contact` | `originalLead` | — |
| `createdOpportunity` | Opportunity | belongsTo | `Opportunity` | `originalLead` | — |
| `documents` | Documents | hasMany | `Document` | `leads` | audited |
| `emails` | emails | hasChildren | `Email` | `parent` | — |
| `meetings` | meetings | hasMany | `Meeting` | `leads` | audited |
| `modifiedBy` | modifiedBy | belongsTo | `User` | `—` | — |
| `sourceOpportunity` | sourceOpportunity | belongsTo | `Opportunity` | `—` | custom |
| `targetLists` | Target Lists | hasMany | `TargetList` | `leads` | — |
| `tasks` | tasks | hasChildren | `Task` | `parent` | audited |
| `teams` | teams | hasMany | `Team` | `—` | relation `entityTeam` |

## Unique indexes

- **createdAtId**: `createdAt`, `id`

---

_Generated 2026-06-06 from a read-only live metadata pull (`metadata.php` cache, equivalent to `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`)._
