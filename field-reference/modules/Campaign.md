# Campaign

**Entity name:** `Campaign`  
**Plural label:** Campaigns  
**Type:** Core entity (module: `Crm`)  
**Field count:** 38  
**Link count:** 17  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Campaign`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Campaign/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Campaign`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Campaign/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Campaign/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `accountsTemplate` | Accounts Template | link |  | — | — |
| `assignedUser` | assignedUser | link |  | — | — |
| `bouncedCount` | Bounced | int |  | — | read-only |
| `bouncedPercentage` | bouncedPercentage | int |  | — | read-only |
| `budget` | Budget | currency |  | — | — |
| `budgetConverted` | Budget (converted) | currencyConverted |  | — | read-only |
| `budgetCurrency` | Budget Currency | enum |  | — | max 3 |
| `clickedCount` | Clicked | int |  | — | read-only |
| `clickedPercentage` | clickedPercentage | int |  | — | read-only |
| `contactsTemplate` | Contacts Template | link |  | — | — |
| `createdAt` | createdAt | datetime |  | — | read-only |
| `createdBy` | createdBy | link |  | — | read-only |
| `description` | Description | text |  | — | — |
| `endDate` | End Date | date |  | — | — |
| `excludingTargetLists` | Excluding Target Lists | linkMultiple |  | — | — |
| `hardBouncedCount` | Hard Bounced | int |  | — | read-only |
| `leadCreatedCount` | Leads Created | int |  | — | read-only |
| `leadsTemplate` | Leads Template | link |  | — | — |
| `mailMergeOnlyWithAddress` | Skip records w/o filled address | bool |  | true | — |
| `modifiedAt` | modifiedAt | datetime |  | — | read-only |
| `modifiedBy` | modifiedBy | link |  | — | read-only |
| `name` | Name | varchar | yes | — | required, pattern |
| `openedCount` | Opened | int |  | — | read-only |
| `openedPercentage` | openedPercentage | int |  | — | read-only |
| `optedInCount` | Opted In | int |  | — | read-only |
| `optedOutCount` | Opted Out | int |  | — | read-only |
| `optedOutPercentage` | optedOutPercentage | int |  | — | read-only |
| `revenue` | Revenue | currency |  | — | read-only |
| `revenueConverted` | Revenue (converted) | currencyConverted |  | — | read-only |
| `revenueCurrency` | revenueCurrency | enum |  | — | read-only, max 3 |
| `sentCount` | Sent | int |  | — | read-only |
| `softBouncedCount` | Soft Bounced | int |  | — | read-only |
| `startDate` | Start Date | date |  | — | — |
| `status` | Status | enum |  | `Planning` | — |
| `targetLists` | Target Lists | linkMultiple |  | — | — |
| `teams` | teams | linkMultiple |  | — | — |
| `type` | Type | enum |  | `Email` | max 64 |
| `usersTemplate` | Users Template | link |  | — | — |

## Allowed values (enum / multi-enum / array / checklist)

### `status` — Status

- Type: `enum`
- Default: `Planning`
- Options:
  - `Planning`
  - `Active`
  - `Inactive`
  - `Complete`

### `type` — Type

- Type: `enum`
- Default: `Email`
- Options:
  - `Email`
  - `Newsletter`
  - `Informational Email`
  - `Web`
  - `Television`
  - `Radio`
  - `Mail`

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `accounts` | Accounts | hasMany | `Account` | `campaign` | — |
| `accountsTemplate` | Accounts Template | belongsTo | `Template` | `—` | no-join |
| `assignedUser` | assignedUser | belongsTo | `User` | `—` | — |
| `campaignLogRecords` | Log | hasMany | `CampaignLogRecord` | `campaign` | — |
| `contacts` | Contacts | hasMany | `Contact` | `campaign` | — |
| `contactsTemplate` | Contacts Template | belongsTo | `Template` | `—` | no-join |
| `createdBy` | createdBy | belongsTo | `User` | `—` | — |
| `excludingTargetLists` | Excluding Target Lists | hasMany | `TargetList` | `campaignsExcluding` | relation `campaignTargetListExcluding` |
| `leads` | Leads | hasMany | `Lead` | `campaign` | — |
| `leadsTemplate` | Leads Template | belongsTo | `Template` | `—` | no-join |
| `massEmails` | Mass Emails | hasMany | `MassEmail` | `campaign` | — |
| `modifiedBy` | modifiedBy | belongsTo | `User` | `—` | — |
| `opportunities` | Opportunities | hasMany | `Opportunity` | `campaign` | — |
| `targetLists` | Target Lists | hasMany | `TargetList` | `campaigns` | — |
| `teams` | teams | hasMany | `Team` | `—` | relation `entityTeam` |
| `trackingUrls` | Tracking URLs | hasMany | `CampaignTrackingUrl` | `campaign` | — |
| `usersTemplate` | Users Template | belongsTo | `Template` | `—` | no-join |

---

_Generated 2026-06-06 from a read-only live metadata pull (`metadata.php` cache, equivalent to `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`)._
