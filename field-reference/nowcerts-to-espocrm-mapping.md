# NowCerts → EspoCRM Field Mapping
## Complete Sync Reference for n8n Workflows

---

## 1. INSUREDS → Account

**NowCerts Endpoint:** `GET /api/insureds`
**EspoCRM Endpoint:** `POST /api/v1/Account` (create) or `PUT /api/v1/Account/{id}` (update)
**Dedup Key:** `momentumClientId` = NowCerts `database_id`

### Synced Fields

| # | NowCerts Field | EspoCRM Field | EspoCRM Type | Notes |
|---|---|---|---|---|
| 1 | `database_id` | `momentumClientId` | varchar | Primary dedup key |
| 2 | `commercialName` or `firstName lastName` | `name` | varchar | CL = commercialName, PL = concat |
| 3 | `firstName` | `primaryFirstName` | varchar | |
| 4 | `lastName` | `primaryLastName` | varchar | |
| 5 | `dateOfBirth` | `dateOfBirth` | date | |
| 6 | `coInsured_FirstName` | `spouseFirstName` | varchar | |
| 7 | `coInsured_LastName` | `spouseLastName` | varchar | |
| 8 | `coInsured_DateOfBirth` | `spouseDob` | date | |
| 9 | `insuredType` | `accountType` | enum | **Value map:** "Commercial" → "Commercial Lines", "Personal" → "Personal Lines" |
| 10 | `typeOfBusiness` | `businessEntity` | enum | LLC, Corp, etc. |
| 11 | `yearBusinessStarted` | `cYearBusinessEst` | text | |
| 12 | `yearsInBusiness` | `yearsInBusiness` | int | |
| 13 | `naics` | `intelNaics` | text | |
| 14 | `sicCode` | `sicCode` | varchar | |
| 15 | `fein` | `fein` | varchar | |
| 16 | `changeDate` | `momentumLastSynced` | datetime | Set on every sync |
| 17 | `createDate` | `clientSince` | date | Only on first sync |
| 18 | `referralSourceCompanyName` | `referralName` | text | |
| 19 | `leadSources[0]` | `referralSource` | enum | Map first value |
| 20 | `personNotes` | `communicationNotes` | text | Append, don't overwrite |
| 21 | `agentOfRecordDate` | `agentOfRecordDate` | date | |

### Skipped Fields (Insureds)

| NowCerts Field | Reason |
|---|---|
| `insuredSubType` | Redundant with `insuredType` |
| `prospectType` | Not useful |
| `sicDescription` | Derivable from sicCode |
| `medicareHICN` | Sensitive — goes to Opportunity only |
| `drInformation` | Goes to Opportunity only (Medicare LOB) |
| `rxInformation` | Goes to Opportunity only (Medicare LOB) |
| `partAEffectiveDate` | Goes to Opportunity only (Medicare LOB) |
| `partBEffectiveDate` | Goes to Opportunity only (Medicare LOB) |
| `lastChangeUserId` | NowCerts internal UUID |
| `lastChangeUserName` | Log to ActivityLog if needed |
| `clientType` | Not mapped |
| `origin` | NowCerts internal |
| `partnerId` | NowCerts internal |

### insuredType Value Mapping

| NowCerts Value | EspoCRM `accountType` |
|---|---|
| `Commercial` | `Commercial Lines` |
| `Personal` | `Personal Lines` |
| `Life` | `Personal Lines` |
| `Health` | `Personal Lines` |
| `Benefits` | `Group Benefits` |

---

## 2. POLICIES → Policy

**NowCerts Endpoint:** `GET /api/policies`
**EspoCRM Endpoint:** `POST /api/v1/Policy` (create) or `PUT /api/v1/Policy/{id}` (update)
**Dedup Key:** `momentumPolicyId` = NowCerts `databaseId`
**Account Link:** Match `insuredDatabaseId` to Account `momentumClientId` to get `accountId`

### Synced Fields

| # | NowCerts Field | EspoCRM Field | EspoCRM Type | Notes |
|---|---|---|---|---|
| 1 | `databaseId` | `momentumPolicyId` | varchar | Primary dedup key |
| 2 | `number` | `policyNumber` | varchar | |
| 3 | `insuredDatabaseId` | `insuredMomentumId` | varchar | Used to link `accountId` |
| 4 | `lineOfBusinessName` | `lineOfBusiness` | varchar | "Personal Auto", "Commercial Auto", etc. |
| 5 | `carrierName` | `carrier` | varchar | |
| 6 | `effectiveDate` | `effectiveDate` | date | |
| 7 | `expirationDate` | `expirationDate` | date | |
| 8 | `bindDate` | `bindDate` | date | |
| 9 | `totalPremium` | `premiumAmount` | currency | |
| 10 | `totalAgencyCommission` | `commissionAmount` | currency | |
| 11 | `status` | `status` | enum | "Active", "Cancelled", etc. |
| 12 | `businessType` | `businessType` | varchar | "Renewal", "New Business", "Rewrite" |
| 13 | `billingType` | `billingType` | enum | "Direct_Bill_100" → "Direct Bill 100" |
| 14 | `policyTerm` | `policyTerm` | int | Months (6, 12) |
| 15 | `cancellationDate` | `cancellationDate` | date | |
| 16 | `reinstatementDate` | `reinstatementDate` | date | |
| 17 | `description` | `policyNotes` | text | |
| 18 | `changeDate` | `momentumLastSynced` | datetime | Set on every sync |
| 19 | *(n8n — not on NowCerts payload)* | `syncStatus` | enum | After a **successful** Momentum→Espo PUT, set `Synced`. On API/validation failure, set `Error`. Leave unchanged when skipping a row. Do **not** clear to empty. |

### Automation-only & AMS fields (not from NowCerts GET)

These Espo fields have **no** NowCerts column. **Inbound Momentum/n8n sync must not overwrite** them on routine policy pulls (merge from existing Espo record, or omit from PUT):

| EspoCRM Field | Type | Who sets it |
|---|---|---|
| `acceptedByAmsAt` | datetime | AMS acceptance callback / integration after AMS confirms a CRM correction |
| `acceptedByAmsBy` | varchar | Same |
| `amsLockState` | enum | CRM hooks (`SendPolicyCorrectionWebhook` → `Pending AMS`); AMS integration when accepted → `Locked by AMS`; reject paths → `Rejected by AMS` |
| `amsLockReason` | text | CRM hooks + AMS integration messages |
| `syncStatus` | enum | n8n on sync outcome; CRM sets `Pending` when a correction is queued for AMS (see hooks) |

### When `amsLockState` = `Locked by AMS` (inbound sync must skip core fields)

Espo **`EnforceAmsPolicyLock`** blocks CRM saves that change these attributes while locked. **n8n** (or any job doing `PUT /api/v1/Policy/{id}`) must **merge**: keep Espo’s current values for locked attributes and only send safe updates (e.g. renewal outreach fields), **or** omit those keys from the PUT body entirely.

Locked **core** attributes (same list as server hook `CORE_FIELDS`):

`policyNumber`, `status`, `carrier`, `lineOfBusiness`, `effectiveDate`, `expirationDate`, `premiumAmount`, `businessType`, `bindDate`, `billingType`, `policyTerm`, `cancellationDate`, `reinstatementDate`, `momentumPolicyId`, `insuredMomentumId`

`policyNumber` is also rejected on change whenever `momentumPolicyId` is set, even if not fully locked — always treat as immutable from Momentum→CRM once linked.

### Skipped Fields (Policies)

| NowCerts Field | Reason |
|---|---|
| `isQuote` | **Filter in n8n** — skip quotes, only sync bound policies |
| `insuredEmail/FirstName/LastName/CommercialName` | Already on Account |
| `insuredSubType` / `insuredType` | Already on Account |
| `carrierNAIC` / `carrierFinanceSearchCoCode` | Internal carrier codes |
| `mgaName` / `mgaFinanceSearchCoCode` | Internal |
| `totalNonPremium` | Low value |
| `businessSubType` | Usually empty |
| `active` | Redundant with `status` |
| `additionalPolicyStatus` | Redundant with `status` |
| `inceptionDate` | Same as effectiveDate |
| `policyTermFormatted` | Redundant — derived from `policyTerm` |
| `binderId` | NowCerts internal |
| `percentageChange` | Log to ActivityLog for renewals |
| `lastChangeUserId/UserName` | NowCerts internal |
| `primaryOfficeDatabaseId` | NowCerts internal |
| `referralSourceDatabaseId/Name` | Already on Account |
| `origin` | NowCerts internal |
| `lineOfBusinessId` / `lineOfBusinessClassId` / `lineOfBusinessClassName` | Internal UUIDs, redundant |
| `productName` | Usually null, LOB covers it |
| `createDate` | Less useful than effectiveDate |

### billingType Value Mapping

| NowCerts Value | EspoCRM `billingType` |
|---|---|
| `Direct_Bill_100` | `Direct Bill 100` |
| `Agency_Bill_100` | `Agency Bill 100` |
| `Direct_Bill` | `Direct Bill` |
| `Agency_Bill` | `Agency Bill` |

### Policy Governance Rules (AMS as System of Record)

- AMS remains authoritative for all policy core fields.
- CRM may submit approved correction payloads, but cannot delete policies or renumber policies in AMS.
- After AMS accepts a correction, CRM sets:
  - `acceptedByAmsAt`
  - `acceptedByAmsBy`
  - `amsLockState = "Locked by AMS"`
- Once locked, core policy fields are edited in AMS (not CRM).
- Timestamp-based sync decisions are written to Activity Log/global stream (`accepted`, `locked`, `rejected`, `blocked`).

---

## 3. MEDICARE FIELDS → Opportunity

**Source:** NowCerts Insureds endpoint (not Policies)
**EspoCRM Entity:** Opportunity
**Visibility:** Only when `lineOfBusiness` = "Medicare"

| # | NowCerts Field | EspoCRM Field | EspoCRM Type |
|---|---|---|---|
| 1 | `partAEffectiveDate` | `partAEffectiveDate` | date |
| 2 | `partBEffectiveDate` | `partBEffectiveDate` | date |
| 3 | `medicareHICN` | `medicareHICN` | varchar |
| 4 | `drInformation` | `drInformation` | text |
| 5 | `rxInformation` | `rxInformation` | text |

> **Note:** `medicareHICN` is sensitive data. NowCerts is the system of record. This field will eventually be purged from EspoCRM.

---

## n8n Workflow Patterns

### Insured Sync
```
1. GET NowCerts /api/insureds (changed since last sync)
2. For each insured:
   a. Search EspoCRM: GET /api/v1/Account?where[momentumClientId]={database_id}
   b. If found → PUT /api/v1/Account/{id} with mapped fields
   c. If not found → POST /api/v1/Account with mapped fields
   d. Always set momentumLastSynced = NOW()
```

### Daily CRM -> AMS Account Enrichment (DOB/FEIN and related fields)
```
1. Trigger daily job and on CRM account updates with momentumClientId present
2. Collect approved enrichment fields (DOB, FEIN, legal name components, spouse DOB, SIC/NAICS)
3. Compare modified timestamps: only push field when CRM modifiedAt > AMS field/entity modifiedAt
4. POST account.enrichment_submitted payload to AMS integration endpoint
5. Write global stream ActivityLog decision: pushed or skipped with timestamp + changed field list
```

### Policy Sync
```
1. GET NowCerts /api/policies (changed since last sync)
2. Filter: skip where isQuote = true
3. For each policy:
   a. Look up accountId: GET /api/v1/Account?where[momentumClientId]={insuredDatabaseId}
   b. Search EspoCRM: GET /api/v1/Policy?where[momentumPolicyId]={databaseId}
   c. If found:
      - Read existing `amsLockState`, `acceptedByAmsAt`, `acceptedByAmsBy`, `amsLockReason`, `syncStatus`
      - Build PUT body from NowCerts mapped fields + accountId + momentumLastSynced (NOW)
      - If amsLockState == "Locked by AMS": strip locked core fields from PUT (see section above); keep governance fields as on server
      - On successful PUT: set syncStatus to "Synced" in payload (or follow-up PATCH)
      - On Espo 4xx/5xx: set syncStatus "Error" when you retry logging is available
   d. If not found → POST /api/v1/Policy with mapped fields + accountId + momentumLastSynced; set syncStatus "Synced" on success
   e. Never send acceptedByAmsAt / acceptedByAmsBy / amsLockState / amsLockReason from NowCerts data — preserve existing on updates
```

### Policy Change Logging (ActivityLog)
```
When a policy sync detects changes (premium, status, etc.):
POST /api/v1/ActivityLog
{
  "name": "Premium change on policy {number}",
  "activityType": "Premium Change",
  "accountId": "{accountId}",
  "policyId": "{policyId}",
  "oldPremium": {previous_totalPremium},
  "newPremium": {current_totalPremium},
  "changeEffectiveDate": "{effectiveDate}",
  "changeSummary": "Premium changed from $X to $Y on policy {number}",
  "momentumTransactionId": "{databaseId}-{changeDate}",
  "source": "NowCerts Sync",
  "dateTime": "{changeDate}"
}
```

---

## 4. TASKS -> Task (CRM to AMS Only)

**NowCerts Endpoints:**
- Pull: `GET /api/TasksList` or `POST /api/Insured/InsuredTasks`
- Push: `POST /api/Zapier/InsertTask` or `POST /api/Zapier/UpdateTask`

**EspoCRM Endpoint:** `POST /api/v1/Task` (create) or `PUT /api/v1/Task/{id}` (update)
**Dedup Key:** `momentumTaskId` = NowCerts `databaseId`
**Direction:** One-way from EspoCRM to AMS — no inbound task updates from AMS/NowCerts

### Synced Fields (EspoCRM -> AMS)

| # | NowCerts Field | EspoCRM Field | EspoCRM Type | Notes |
|---|---|---|---|---|
| 1 | `databaseId` | `momentumTaskId` | varchar | Dedup key (unique index) |
| 2 | `title` | `name` | varchar | Direct |
| 3 | `description` | `description` | text | Direct |
| 4 | `status` | `status` | enum | **Value map below** |
| 5 | `priority` | `urgency` | enum | **Value map below** |
| 6 | `dueDate` | `dateEnd` | datetime | Direct |
| 7 | `categoryName` | `taskType` | enum | **Value map below** |
| 8 | `assignedTo[0]` | `assignedUser` | link | Match by name to EspoCRM user |
| 9 | `insuredDatabaseId` | `linkedAccount` | link | Look up Account by `momentumClientId` |
| 10 | `policyNumber` | `parent` (Policy) | linkParent | Look up Policy by `policyNumber` |
| 11 | `changeDate` | `momentumLastSynced` | datetime | Set on every sync |
| 12 | — | `syncSource` | enum | Set to "Momentum" on pull, keep existing on push |

### Push: EspoCRM → NowCerts (InsertTask / UpdateTask)

| # | EspoCRM Field | NowCerts Field | Notes |
|---|---|---|---|
| 1 | `momentumTaskId` | `database_id` | Omit on create (NowCerts generates), include on update |
| 2 | `name` | `title` | Direct |
| 3 | `description` | `description` | Direct |
| 4 | `status` | `status` | Reverse value map |
| 5 | `urgency` | `priority` | Reverse value map |
| 6 | `dateEnd` | `due_date` | Direct |
| 7 | `taskType` | `category_name` | Reverse value map |
| 8 | `assignedUser.name` | `assigned_to` | `["FirstName LastName"]` |
| 9 | `linkedAccount.momentumClientId` | `insured_database_id` | Look up from linked account |

### Value Mappings

**Status (NowCerts ↔ EspoCRM):**

| NowCerts | → EspoCRM | EspoCRM | → NowCerts |
|---|---|---|---|
| `Open` | `Inbox` | `Inbox` | `Open` |
| `In Progress` | `In Progress` | `In Progress` | `In Progress` |
| `Waiting` | `Waiting on Client` | `Waiting on Client` | `Waiting` |
| — | — | `Waiting on Carrier` | `Waiting` |
| `Completed` | `Completed` | `Completed` | `Completed` |
| `Cancelled` | `Cancelled` | `Cancelled` | `Cancelled` |

**Priority (NowCerts ↔ EspoCRM):**

| NowCerts | → EspoCRM | EspoCRM | → NowCerts |
|---|---|---|---|
| `Urgent` / `Critical` | `Urgent` | `Urgent` | `Urgent` |
| `High` | `High` | `High` | `High` |
| `Normal` / `Medium` | `Normal` | `Normal` | `Normal` |
| `Low` | `Low` | `Low` | `Low` |

**Category → Task Type (NowCerts → EspoCRM):**

| NowCerts `categoryName` | EspoCRM `taskType` |
|---|---|
| Map per your NowCerts categories | Use `TaskCategoryDetailList` endpoint to get full list |

### Skipped Fields (Tasks)

| NowCerts Field | Reason |
|---|---|
| `stageName` / `perStageStatusRecord` | NowCerts workflow stages — not mapped |
| `supervisorName` | No equivalent role in RSG |
| `creatorName` | Covered by `syncSource` |
| `completion` | Derived from status (100% = Completed) |
| `insuredEmail/FirstName/LastName/CommercialName` | Already on Account |

### n8n Workflow Patterns

**Push: EspoCRM -> AMS**
```
1. Trigger: EspoCRM webhook on Task create/update (where syncSource != "Momentum")
2. If momentumTaskId is empty:
   a. POST NowCerts /api/Zapier/InsertTask with mapped fields
   b. Store returned databaseId → PUT /api/v1/Task/{id} { momentumTaskId: "{databaseId}" }
3. If momentumTaskId exists:
   a. POST NowCerts /api/Zapier/UpdateTask with database_id + mapped fields
4. Set momentumLastSynced = NOW()
5. Do not run inbound NowCerts -> EspoCRM task sync for these records
```
