# NowCerts → EspoCRM Sync — Complete Field Mapping

## API Endpoints

| Entity | NowCerts Endpoint | Method | Format |
|---|---|---|---|
| Accounts | `/api/InsuredDetailList` | GET | OData (`$filter`, `$count`, `$orderby`, `$skip`, `$top`) |
| Policies | `/api/PolicyDetailList` | GET | OData |
| Response format | `{ "@odata.count": N, "value": [...] }` | | |

## Account Sync — InsuredDetailList Fields

### Shared Fields (All Account Types)

| NowCerts Field | EspoCRM Field | Type | Transform | Notes |
|---|---|---|---|---|
| `id` | `momentumClientId` | varchar(255) | Direct | **Primary dedup key** (GUID) |
| `customerId` | `nowCertsCustomerId` | varchar(50) | Direct | Internal billing/MC number |
| `insuredId` | `nowCertsCustomerId` | varchar(50) | Fallback if customerId null | Internal ID number |
| `insuredType` | `accountType` | enum | See mapping below | Commercial/Personal/etc |
| `type` | — | — | "Insured" vs "Prospect" → accountStatus | Determines active vs prospect |
| `prospectType` | `prospectType` | enum | Map values (see below) | Hot/Cold/Warm prospect |
| `referralSourceCompanyName` | `referralName` | text | Direct | Referral source company |
| `createDate` | `clientSince` | date | Extract date | Only on CREATE |
| `changeDate` | `momentumLastSynced` | datetime | Direct | Used for incremental filter |
| `addressLine1` + `addressLine2` | `billingAddressStreet` | address | Concat with newline | Street address |
| `city` | `billingAddressCity` | varchar | Direct | City |
| `state` | `billingAddressState` | varchar | Direct | State |
| `zipCode` | `billingAddressPostalCode` | varchar | Direct | Zip |
| `eMail` | `emailAddressData` | email | `[{emailAddress, primary: true}]` | **CREATE only** — EspoCRM format |
| `eMail2`, `eMail3` | — | — | Not synced | Secondary/tertiary emails |
| `phone` / `cellPhone` | `phoneNumberData` | phone | `[{phoneNumber, primary: true}]` | **CREATE only** — EspoCRM format |
| `fax` | — | — | Not synced | Fax number |
| `smsPhone` | — | — | Not synced | SMS number |
| `description` | `description` | text | CREATE only | Account description |
| `active` | — | — | Used as API filter `active eq true` | Only sync active records |

## Commercial Lines Fields (Account)

Only sync when `insuredType` = `"Commercial"`.

| NowCerts Field | EspoCRM Field | Type | Transform | Notes |
|---|---|---|---|---|
| `dba` | `dba` | varchar(255) | Direct | Doing Business As name |
| `yearBusinessStarted` | `yearBusinessStarted` | int | Direct | Raw year — new field added |
| `yearBusinessStarted` | `yearsInBusiness` | int | `currentYear - yearBusinessStarted` | Also calculate years in business |
| `yearsInBusiness` | `yearsInBusiness` | int | Direct | Fallback if `yearBusinessStarted` is null |
| `naics` | `naicsCode` | varchar(10) | Direct | NAICS industry classification |
| `sicCode` | `sicCode` | varchar(20) | Direct | SIC industry code |
| `sicDescription` | `sicDescription` | varchar(255) | Direct | Human-readable SIC description |
| `typeOfBusiness` | `businessEntity` | enum | Map values (see below) | Entity type for underwriting |
| `fein` | `fein` | varchar(20) | Direct | Federal Employer ID Number |
| `website` | `websiteUrl` | url | Direct | Business website |

## Personal Lines Fields (Account)

Only sync when `insuredType` = `"Personal"`.

| NowCerts Field | EspoCRM Field | Type | Transform | Notes |
|---|---|---|---|---|
| `coInsured_FirstName` | `spouseFirstName` | varchar(100) | Direct | Co-insured / spouse first name |
| `coInsured_LastName` | `spouseLastName` | varchar(100) | Direct | Co-insured / spouse last name |
| `coInsured_DateOfBirth` | `spouseDob` | date | Extract date | Co-insured date of birth |
| `medicareHICN` | `medicareId` | varchar(20) | Direct | Medicare Beneficiary ID — person-level, stays on Account |

## Medicare Policy Fields (Policy Entity)

These fields go on the **Policy** record, NOT the Account. They are coverage-level data that can differ per Medicare policy.

| NowCerts Field | EspoCRM Policy Field | Type | Transform | Notes |
|---|---|---|---|---|
| `partAEffectiveDate` | `partAEffectiveDate` | date | Extract date | Medicare Part A enrollment date |
| `partBEffectiveDate` | `partBEffectiveDate` | date | Extract date | Medicare Part B enrollment date |
| `drInformation` | `drInformation` | text | Direct | Doctor/provider info for plan network |
| `rxInformation` | `rxInformation` | text | Direct | Prescription info for Part D selection |

## insuredType → accountType Mapping

| NowCerts `insuredType` | EspoCRM `accountType` |
|---|---|
| `"Commercial"` | `"Commercial Lines"` |
| `"Individual"` / `"Personal"` | `"Personal Lines"` |
| `"Medicare"` | `"Medicare"` |
| `"Life"` | `"Life Insurance"` |
| `"Group"` | `"Group Benefits"` |

## prospectType → prospectType Mapping

| NowCerts `prospectType` | EspoCRM `prospectType` |
|---|---|
| `"Cold_Prospect"` | `"Cold Prospect"` |
| `"Warm_Prospect"` | `"Warm Prospect"` |
| `"Hot_Prospect"` | `"Hot Prospect"` |
| `"Referral"` | `"Referral"` |
| `"Cross_Sell"` | `"Cross-Sell"` |
| `null` / empty | `""` |

## typeOfBusiness → businessEntity Mapping

| NowCerts `typeOfBusiness` | EspoCRM `businessEntity` |
|---|---|
| `""` (empty) | `""` (empty) |
| `"Sole Proprietorship"` | `"Sole Proprietor"` |
| `"LLC"` | `"LLC"` |
| `"Corporation"` | `"Corporation"` |
| `"S-Corporation"` | `"S-Corp"` |
| `"Partnership"` | `"Partnership"` |
| `"Non-Profit"` | `"Non-Profit"` |
| *(anything else)* | `"Other"` |

## Skipped Fields

| NowCerts Field | Reason |
|---|---|
| `coInsured_MiddleName` | No matching EspoCRM field, rarely populated |
| `personNotes` | Usually empty; use EspoCRM stream instead |
| `lastChangeUserId`, `lastChangeUserName` | NowCerts internal audit trail |
| `partnerId` | NowCerts internal reference |
| `clientType` | Usually empty string |
| `agentOfRecordDate` | Rarely populated |
| `acquisitionDate` | Redundant with `createDate` |
| `isSuperVisior` | NowCerts internal flag |
| `userDisplayName` | NowCerts internal display name |

## Dedup Key

Use `momentumClientId` (NowCerts `id` field — a GUID) as the unique key. Never create duplicates — always upsert by this key.

## Sync Direction

**Bidirectional.** NowCerts is system of record; EspoCRM enrichments push back.

## Incremental Sync

API query: `$filter=changeDate ge {2hrs ago} and active eq true`
This means only recently modified active records are pulled each hour.

---

# Policy Sync — PolicyDetailList Field Mapping

## NowCerts → EspoCRM Policy Fields

| NowCerts Field | EspoCRM Field | Type | Notes |
|---|---|---|---|
| `id` | `momentumPolicyId` | varchar(255) | **Primary dedup key** (GUID) |
| `insuredDatabaseId` | `insuredMomentumId` | varchar(255) | Resolves Account link |
| `number` | `policyNumber` | varchar(100) | Policy number |
| `carrierName` | `carrier` | varchar(255) | Insurance carrier |
| `carrierNAIC` | — | — | Not synced (available if needed) |
| `mgaName` | — | — | Not synced (available if needed) |
| `isQuote` | — | — | **Skipped if true** — only sync real policies |
| `status` | `status` | enum | Mapped to EspoCRM enum values |
| `effectiveDate` | `effectiveDate` | date | Policy start date |
| `expirationDate` | `expirationDate` | date | Renewal trigger date |
| `totalPremium` | `premiumAmount` | currency | Total endorsements premium |
| `totalAgencyCommission` | `commissionAmount` | currency | Total agency commission |
| `totalNonPremium` | `agencyFee` | currency | Non-premium amount (fees) |
| `businessType` | `businessType` | enum | New Business / Renewal / Rewrite |
| `businessSubType` | — | — | Not synced (available if needed) |
| `description` | `policyNotes` | text | Policy description |
| `billingType` | — | — | Not synced (Direct_Bill_100 etc) |
| `bindDate` | — | — | Not synced (available if needed) |
| `cancellationDate` | — | — | Not synced (status covers this) |
| `reinstatementDate` | — | — | Not synced |
| `inceptionDate` | — | — | Not synced (effectiveDate covers this) |
| `policyTerm` | — | — | Not synced (12 months etc) |
| `percentageChange` | — | — | Not synced (renewal % change) |
| `insuredType` | — | — | Not synced (already on Account) |
| `insuredEmail/FirstName/LastName/CommercialName` | — | — | Not synced (already on Account) |
| `partAEffectiveDate` | `partAEffectiveDate` | date | Medicare Part A (policy-level) |
| `partBEffectiveDate` | `partBEffectiveDate` | date | Medicare Part B (policy-level) |
| `drInformation` | `drInformation` | text | Doctor info (Medicare) |
| `rxInformation` | `rxInformation` | text | Rx info (Medicare) |

## Policy Dedup Key

`momentumPolicyId` (NowCerts `id` field — GUID). Account link resolved via `insuredMomentumId` → Account.`momentumClientId`.

## Policy Incremental Sync

API query: `$filter=changeDate ge {2hrs ago} and active eq true`
Quotes filtered out: `isQuote === true` skipped in transform.

---

# Contact Sync — Field Mapping

## NowCerts → EspoCRM Contact Fields

| NowCerts Field | EspoCRM Field | Type | Notes |
|---|---|---|---|
| `database_id` | `momentumClientId` | varchar(255) | Primary dedup key |
| `insuredDatabaseId` | — | — | Resolves Account link via `accountsIds` |
| `firstName` | `firstName` | varchar | First name |
| `lastName` | `lastName` | varchar | Last name |
| `middleName` | `middleName` | varchar | Middle name |
| `email` | `emailAddress` | email | Contact email |
| `phone` | `phoneNumber` | phone | Contact phone |
| `dateOfBirth` | `dateOfBirth` | date | DOB |
| `contactType` | `householdRole` | enum | Primary / Spouse / Dependent / Co-insured |
| `insuredType` | `clientType` | enum | Commercial / Personal |
| `csrName` | `csrName` | varchar(255) | Assigned CSR |
| `addressLine1/2` | `addressStreet` | address | Concat with newline |
| `city` | `addressCity` | varchar | City |
| `state` | `addressState` | varchar | State |
| `zipCode` | `addressPostalCode` | varchar | Zip |

## Contact Dedup Key

`momentumClientId` (NowCerts `database_id`). Account link resolved via `insuredDatabaseId` → Account.`momentumClientId` → `accountsIds`.

---

# n8n Workflow Registry

| # | Workflow | ID | Direction | Schedule | Entity |
|---|---|---|---|---|---|
| 1 | NowCerts → EspoCRM Account Sync | `RUOvjckWMUyrzJeJ` | Pull | Every hour | Account (all types) |
| 2 | EspoCRM → NowCerts Account Reverse Sync | `wu4tql14ZPQSEimI` | Push | Webhook + 2x daily | Account (all types) |
| 3 | NowCerts → EspoCRM Policy Sync | `h3z5WpwFAwQG0FOW` | Pull | Every hour | Policy |
| 4 | EspoCRM → NowCerts Policy Reverse Sync | `jEhpIHl6FRucScf0` | Push | Webhook + 2x daily | Policy |
| 5 | NowCerts → EspoCRM Contact Sync | `1Q2jyfy1rdqXGKlg` | Pull | Every hour | Contact |
| 6 | EspoCRM → NowCerts Contact Reverse Sync | `pgM2Odt43m9sjH8p` | Push | Webhook + 2x daily | Contact |

## Setup Checklist

1. Create **"NowCerts API Key"** credential in n8n (Header Auth: `Authorization: Bearer YOUR_TOKEN`)
2. Create **"EspoCRM API Key"** credential in n8n (Header Auth: `X-Api-Key: YOUR_KEY`)
3. Update all placeholder URLs in the 6 workflows
4. In EspoCRM Admin → Webhooks, create 3 webhooks:
   - Account afterSave → webhook URL from workflow #2
   - Policy afterSave → webhook URL from workflow #4
   - Contact afterSave → webhook URL from workflow #6
5. Activate all 6 workflows in n8n
