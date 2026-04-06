# NowCerts → EspoCRM Account Sync — Field Mapping

## Shared Fields (All Account Types)

These fields sync regardless of whether the account is Commercial, Personal, or Medicare.

| NowCerts Field | EspoCRM Field | Type | Transform | Notes |
|---|---|---|---|---|
| `database_id` | `momentumClientId` | varchar(255) | Direct | Primary dedup key |
| `customerId` | `nowCertsCustomerId` | varchar(50) | Direct | Human-readable NowCerts customer ID |
| `leadSources` | `leadSource` | varchar(255) | Join array → string | First value from array, or comma-joined |
| `insuredType` | `accountType` | enum | See mapping below | Maps NowCerts type to EspoCRM type |
| `prospectType` | `prospectType` | enum | Map values (see below) | Prospect classification |
| `preferredLanguage` | `preferredLanguage` | enum | Map to English/Spanish/Other | Person-level preference |
| `referralSourceCompanyName` | `referralName` | text | Direct | Referral source company |
| `createDate` | `clientSince` | date | Extract date from datetime | Only set if `clientSince` is empty |
| `changeDate` | `momentumLastSynced` | datetime | Direct | Track last sync timestamp |
| `origin` | `referralSource` | enum | `"Data Import"` → `"NowCerts Import"` | Lead source attribution |

## Commercial Lines Fields (Account)

Only sync when `insuredType` = `"Commercial"`.

| NowCerts Field | EspoCRM Field | Type | Transform | Notes |
|---|---|---|---|---|
| `dba` | `dba` | varchar(255) | Direct | Doing Business As name |
| `yearBusinessStarted` | `yearsInBusiness` | int | `currentYear - yearBusinessStarted` | Calculate; fallback to `yearsInBusiness` |
| `yearsInBusiness` | `yearsInBusiness` | int | Direct | Only if `yearBusinessStarted` is null |
| `naics` | `naicsCode` | varchar(10) | Direct | NAICS industry classification |
| `sicCode` | `sicCode` | varchar(20) | Direct | SIC industry code |
| `sicDescription` | `sicDescription` | varchar(255) | Direct | Human-readable SIC description |
| `typeOfBusiness` | `businessEntity` | enum | Map values (see below) | Entity type for underwriting |

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

Use `momentumClientId` (NowCerts `database_id`) as the unique key to match/update existing EspoCRM accounts. Never create duplicates — always upsert by this key.

## Sync Direction

**NowCerts → EspoCRM only** (one-way). NowCerts is the system of record for these fields.

## n8n Workflow Notes

- **Workflow ID:** `RUOvjckWMUyrzJeJ` (Commercial Lines — already deployed)
- Trigger: Scheduled every 15 min or webhook from NowCerts
- Upsert logic: Match on `momentumClientId`, update mapped fields, skip nulls
- Set `momentumLastSynced` to current datetime on each successful sync
- For personal lines: extend workflow to handle `insuredType` routing (Commercial vs Personal vs Medicare)
- Medicare policy fields sync during policy sync, not account sync
