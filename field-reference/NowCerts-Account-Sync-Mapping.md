# NowCerts → EspoCRM Account Sync — Commercial Lines Field Mapping

## Sync Fields (Commercial Lines)

| NowCerts Field | EspoCRM Field | Type | Transform | Notes |
|---|---|---|---|---|
| `dba` | `dba` | varchar(255) | Direct | Doing Business As name — new field added for sync |
| `insuredType` | `accountType` | enum | `"Commercial"` → `"Commercial Lines"` | Maps NowCerts insured type to EspoCRM account type |
| `insuredSubType` | — | — | Use to confirm `accountType` | Backup validation for type mapping |
| `yearBusinessStarted` | `yearsInBusiness` | int | `currentYear - yearBusinessStarted` | Calculate years; fallback to `yearsInBusiness` if `yearBusinessStarted` is null |
| `yearsInBusiness` | `yearsInBusiness` | int | Direct | Only use if `yearBusinessStarted` is null |
| `naics` | `naicsCode` | varchar(10) | Direct | NAICS industry classification — new field added for sync |
| `sicCode` | `sicCode` | varchar(20) | Direct | SIC industry code |
| `sicDescription` | `sicDescription` | varchar(255) | Direct | Human-readable SIC description — new field added for sync |
| `typeOfBusiness` | `businessEntity` | enum | Map values (see below) | Entity type for underwriting |
| `createDate` | `clientSince` | date | Extract date from datetime | Only set if `clientSince` is empty (don't overwrite) |
| `changeDate` | `momentumLastSynced` | datetime | Direct | Track last NowCerts sync timestamp |
| `origin` | `referralSource` | enum | `"Data Import"` → `"NowCerts Import"` | Lead source attribution |

## typeOfBusiness → businessEntity Value Mapping

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

## Skipped Fields (Not Relevant to Commercial Lines)

| NowCerts Field | Reason |
|---|---|
| `coInsured_FirstName/MiddleName/LastName/DateOfBirth` | Personal lines co-insured — always null for commercial |
| `partAEffectiveDate`, `partBEffectiveDate` | Medicare Part A/B — not commercial |
| `medicareHICN` | Medicare beneficiary ID |
| `drInformation`, `rxInformation` | Health/Medicare data |
| `prospectType` | Redundant with `insuredType` |
| `personNotes` | Usually empty; use EspoCRM stream instead |
| `lastChangeUserId`, `lastChangeUserName` | NowCerts internal audit trail |
| `partnerId` | NowCerts internal reference |
| `leadSources` | Usually empty array; use EspoCRM `referralSource` |
| `clientType` | Usually empty string |
| `agentOfRecordDate` | Rarely populated |
| `acquisitionDate` | Redundant with `createDate` |

## Dedup Key

Use `momentumClientId` (NowCerts `database_id`) as the unique key to match/update existing EspoCRM accounts. Never create duplicates — always upsert by this key.

## Sync Direction

**NowCerts → EspoCRM only** (one-way). NowCerts is the system of record for these fields.

## n8n Workflow Notes

- Trigger: Scheduled (e.g., every 15 min) or webhook from NowCerts
- Filter: Only process records where `insuredType` = `"Commercial"`
- Upsert logic: Match on `momentumClientId`, update mapped fields, skip nulls for optional fields
- Set `momentumLastSynced` to current datetime on each successful sync
