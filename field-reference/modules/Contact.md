# Contact

**Entity name:** `Contact`  
**Plural label:** Contacts  
**Type:** Core entity (module: `Crm`)  
**Field count:** 54  
**Link count:** 22  

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
| `assignedUser` | assignedUser | link |  | — | — |
| `campaign` | Campaign | link |  | — | — |
| `clientType` | Client Type | enum |  | — | custom |
| `contactDriveFolderUrl` | Drive Folder URL | url |  | — | custom |
| `contactType` | Contact Type | enum |  | — | custom |
| `createdAt` | createdAt | datetime |  | — | read-only |
| `createdBy` | createdBy | link |  | — | read-only |
| `csrName` | Customer Service Representative Name | varchar |  | — | read-only, max 255, custom |
| `dateOfBirth` | Date of Birth | date |  | — | custom |
| `daysUntil65` | Days Until 65 | int |  | — | read-only, custom |
| `description` | Description | text |  | — | — |
| `doNotCall` | Do Not Call | bool |  | — | — |
| `emailAddress` | Email | email |  | — | — |
| `emailAddressIsInvalid` | emailAddressIsInvalid | bool |  | — | — |
| `emailAddressIsOptedOut` | emailAddressIsOptedOut | bool |  | — | — |
| `firstName` | firstName | varchar |  | — | max 100, pattern |
| `hasPortalUser` | Has Portal User | bool |  | — | read-only |
| `lastName` | lastName | varchar | yes | — | required, max 100, pattern |
| `middleName` | middleName | varchar |  | — | max 100, pattern |
| `modifiedAt` | modifiedAt | datetime |  | — | read-only |
| `modifiedBy` | modifiedBy | link |  | — | read-only |
| `momentumClientId` | Momentum Client ID | varchar |  | — | read-only, max 255, custom |
| `name` | Name | personName |  | — | — |
| `opportunityRole` | Opportunity Role | enum |  | — | — |
| `originalEmail` | Original Email | link |  | — | — |
| `originalLead` | Original Lead | linkOne |  | — | read-only |
| `phoneNumber` | Phone | phone |  | — | — |
| `phoneNumberIsInvalid` | phoneNumberIsInvalid | bool |  | — | — |
| `phoneNumberIsOptedOut` | phoneNumberIsOptedOut | bool |  | — | — |
| `portalUser` | Portal User | linkOne |  | — | read-only |
| `salutationName` | salutationName | enum |  | — | — |
| `streamUpdatedAt` | streamUpdatedAt | datetime |  | — | read-only |
| `targetList` | Target List | link |  | — | — |
| `targetListIsOptedOut` | targetListIsOptedOut | bool |  | — | read-only |
| `targetLists` | Target Lists | linkMultiple |  | — | — |
| `teams` | teams | linkMultiple |  | — | — |
| `title` | Contact Title | varchar |  | — | max 100 |

## Allowed values (enum / multi-enum / array / checklist)

### `clientType` — Client Type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Personal`
  - `Commercial`
  - `Employee Benefits`
  - `Supplemental`

### `contactType` — Contact Type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Client`
  - `Prospect`
  - `Spouse`
  - `Dependent`
  - `Business Owner`
  - `Underwriter`
  - `Marketing Rep`
  - `Owner`
  - `CFO`
  - `CEO`
  - `VP`
  - `Employee`

### `opportunityRole` — Opportunity Role

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Decision Maker`
  - `Evaluator`
  - `Influencer`

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
| `createdBy` | createdBy | belongsTo | `User` | `—` | — |
| `documents` | Documents | hasMany | `Document` | `contacts` | audited |
| `emails` | emails | hasChildren | `Email` | `parent` | — |
| `meetings` | meetings | hasMany | `Meeting` | `contacts` | audited |
| `modifiedBy` | modifiedBy | belongsTo | `User` | `—` | — |
| `opportunities` | Opportunities | hasMany | `Opportunity` | `contacts` | — |
| `opportunitiesPrimary` | Opportunities (Primary) | hasMany | `Opportunity` | `contact` | — |
| `originalLead` | Original Lead | hasOne | `Lead` | `createdContact` | — |
| `portalUser` | Portal User | hasOne | `User` | `contact` | — |
| `targetLists` | Target Lists | hasMany | `TargetList` | `contacts` | — |
| `tasks` | tasks | hasChildren | `Task` | `parent` | audited |
| `tasksPrimary` | Tasks (expanded) | hasMany | `Task` | `contact` | — |
| `teams` | teams | hasMany | `Team` | `—` | relation `entityTeam` |

## Unique indexes

- **createdAtId**: `createdAt`, `id`

---

_Generated 2026-06-06 from a read-only live metadata pull (`metadata.php` cache, equivalent to `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`)._
