---
name: espocrm-field-dictionary
scope: Load when you need specific field names, types, or labels for a CRM entity
priority: medium
token_cost: ~1200
---

# RSG EspoCRM — Field Dictionary

Comprehensive field tables per entity. Tables start with the most frequently queried fields, followed by specialized or computed groups.

For enum values and stage pipelines see `schema.md`. For field type definitions see the **Field Type Reference** section at the bottom.

---

## Account

### Core identity & contact (query these most)

| Field name | Label | Notes |
|---|---|---|
| `name` | Name | Business or household name |
| `accountType` | Account Type | See `schema.md` for enum values |
| `accountStatus` | Account Status | `Active`, `Urgent`, `Renewing`, `At Risk`, `Inactive` |
| `type` | Type | Pipeline category mirror: `Commercial Lines`, `Personal Lines`, `Group Benefits`, `Prospect` |
| `primaryFirstName` / `primaryLastName` | Primary Name | Named insured on household |
| `primaryEmail` | Primary Email | Check `primaryEmailIsOptedOut` before sending |
| `primaryPhone` | Primary Phone | Check `primaryPhoneIsOptedOut` before calling |
| `emailAddress` | Email (Account-level) | Business email |
| `phoneNumber` | Phone | Business phone |
| `doNotContact` | Do Not Contact | **Boolean — if true, do NOT initiate any outreach** |
| `preferredContact` | Preferred Contact | `Phone`, `Email`, `Text` |
| `googleDriveFolderUrl` | Google Drive Folder URL | Link to client Drive folder |

### Renewal tracking

| Field name | Label | Notes |
|---|---|---|
| `renewalOutreachStage` | Renewal Outreach Stage | See `schema.md` |
| `renewalDecision` | Renewal Decision | See `schema.md` |
| `daysToRenewal` | Days to Renewal | Computed; do not overwrite |
| `nextRenewalDate` | Next Renewal Date | Computed; do not overwrite |
| `nextXDate` | Next X-Date | Computed |
| `nextXDateLob` | Next X-Date LOB | Computed |

### Scoring & health

| Field name | Label | Notes |
|---|---|---|
| `accountScore` | Account Score | Computed integer; do not manually edit |
| `scoreTier` | Score Tier | `Strong`, `Good`, `At Risk`, `Critical` |
| `retentionRisk` | Retention Risk | `Low`, `Medium`, `High` |
| `riskScore` | Risk Score | Underwriting risk integer |

### Policy summary rollup fields (all read-only, computed from linked Policies)

`activePolicyCount`, `totalActivePremium`, `totalAnnualPremium`, `policyCountActive`, `annualPremium`

Per-LOB shortcut fields: `policyAutoActive`, `policyAutoCarrier`, `policyAutoPremium`, `policyHomeActive`, `policyHomeCarrier`, `policyHomePremium`, `policyLifeActive`, `policyMedicareActive`

### LOB multi-select

`lob` (Multi-Enum) — see `schema.md` for full value list

### Coverage gap flags (Boolean)

`gapAutoUm`, `gapFinalExpense`, `gapLandlord`, `gapLife`, `gapMedicare`, `gapRenters`, `gapRideshare`, `gapUmbrella`

`gapCount` — computed total of `true` gap flags

### Intel Pack fields (AI-enriched — read-only unless you are the Intel Pack agent)

`intelAiSummary`, `intelLegalName`, `intelDba`, `intelNaics`, `intelSic`, `intelFleetSize`, `intelEmployeeCount`, `intelAnnualRevenueEst`, `intelBbbRating`, `intelBbbAccredited`, `intelOshaViolations`, `intelDotIncidents`, `intelCrossSell`, `intelPainPoints`, `insightObjection`, `insightOpener`, `insightSignal`, `insightRelationship`

`intelPackRun` / `intelPackLastRun` — set `intelPackRun = true` to trigger the next run; never forge `intelPackLastRun`

### External IDs (immutable)

`momentumClientId`, `momentumLastSynced`

---

## Contact

### Core fields (query these most)

| Field name | Label | Notes |
|---|---|---|
| `firstName` / `lastName` | Name | Person name |
| `householdRole` | Household Role | `Primary`, `Spouse`, `Dependent`, `Co-insured` |
| `clientType` | Client Type | `Personal`, `Commercial` |
| `doNotCall` | Do Not Call | **Boolean — if true, do NOT call** |
| `emailAddressIsOptedOut` | Email Opted-Out | If true, do not send email |
| `opportunityRole` | Opportunity Role | `Decision Maker`, `Evaluator`, `Influencer` |

### Life & Medicare fields

| Field name | Label | Notes |
|---|---|---|
| `dateOfBirth` | Date of Birth | Sensitive PII — do not log or expose |
| `medicareEligible` | Medicare Eligible | Boolean |
| `medicarePlanType` | Medicare Plan Type | `Supplement`, `Advantage`, `Part D` |
| `lifeHealthClass` | Health Class (Life) | `Preferred Plus`, `Preferred`, `Standard Plus`, `Standard`, `Substandard` |

### Links & external IDs

| Field name | Label | Notes |
|---|---|---|
| `momentumClientId` | Momentum Client ID | External AMS identifier; do not overwrite |
| `originalLead` | Original Lead | Link to the Lead record this Contact was converted from |

---

## Lead

### Core fields (query these most)

| Field name | Label | Notes |
|---|---|---|
| `status` | Status | `New`, `Assigned`, `In Process`, `Converted`, `Recycled`, `Dead` |
| `priority` | Priority | `Hot`, `Warm`, `Cold` |
| `source` | Source | `Call`, `Email`, `Existing Customer`, `Partner`, `Public Relations`, `Web Site`, `Campaign`, `Other` |
| `doNotCall` | Do Not Call | **Boolean — if true, do NOT call** |
| `insuranceInterest` | Insurance Interest | Multi-select of LOBs the lead expressed interest in |

### Qualification & coverage fields

| Field name | Label | Notes |
|---|---|---|
| `estimatedPremium` | Est. Premium | Currency; early-stage estimate |
| `currentlyInsured` | Currently Insured? | Boolean |
| `currentCarrier` | Current Carrier | Text |
| `medicareEligible` | Medicare Eligible | Boolean |
| `t65AlertSent` | T65 Alert Sent | Boolean; set by automation when near Medicare age |

### Conversion fields (all read-only after conversion)

| Field name | Label | Notes |
|---|---|---|
| `convertedAt` | Converted At | Timestamp set on conversion |
| `createdAccount` | Created Account | Link set on conversion |
| `createdContact` | Created Contact | Link set on conversion |
| `createdOpportunity` | Created Opportunity | Link set on conversion |

---

## Opportunity

### Core fields (query these most)

| Field name | Label | Notes |
|---|---|---|
| `stage` | Stage | See `schema.md` for pipeline order |
| `businessType` | Business Type | `New Business`, `Renewal`, `Rewrite` |
| `lineOfBusiness` | Line of Business | Full LOB enum — see `schema.md` |
| `priority` | Priority | `Hot`, `Warm`, `Cold` |
| `closeDate` | Close Date | Projected close/bind date |
| `amount` | Amount | Bound/written premium (Currency) |
| `carrier` | Carrier | Bound carrier |
| `lostReason` | Lost Reason | See `schema.md` |

### Premium & commission fields

| Field name | Label | Notes |
|---|---|---|
| `estimatedPremium` | Est. Premium | Pre-bind estimate |
| `writtenPremium` | Written Premium | Confirmed written premium |
| `commissionRate` | Commission Rate % | Float |
| `estimatedCommission` | Est. Commission | Currency |
| `targetCarrier` | Target Carrier | Carrier being quoted |
| `currentCarrier` | Current Carrier | Client's incumbent carrier |

### Bind & policy linkage

| Field name | Label | Notes |
|---|---|---|
| `bindDate` | Bind Date | Actual bind date; set when `Won - Bound` |
| `effectiveDate` | Effective Date | Policy effective date |
| `policyStubId` | Policy Stub ID | Populated when stub Policy record is created on bind |
| `policyStubStatus` | Policy Stub Status | `Pending Sync`, `Synced` |

### Checklist fields (Boolean — tick as steps are completed)

`chkAppSubmitted`, `chkBound`, `chkCmsConfirmation`, `chkDecPageDelivered`, `chkMvrsPulled`, `chkPlanPresented`, `chkProposalSent`, `chkQuoteSubmitted`, `chkScopeOfAppt`, `chkSignedAppReceived`, `chkUnderlyingConfirmed`, `chkUnderlyingLinked`, `chkWelcomeLetter`

### Email automation flags (set by automation — do not manually toggle)

`emailSequenceStarted`, `skipEmailSequence`, `onboardingSent`

---

## Policy

### Core fields (query these most)

| Field name | Label | Notes |
|---|---|---|
| `name` | Name | Policy name / number |
| `status` | Status | See `schema.md` for full enum |
| `carrier` | Carrier | Insurer name |
| `lineOfBusiness` | Line of Business | Text (free-form from AMS) |
| `premiumAmount` | Premium Amount | Currency |
| `effectiveDate` | Effective Date | Date |
| `expirationDate` | Expiration Date | Date |
| `businessType` | Business Type | `New Business`, `Renewal`, `Rewrite` |
| `urgency` | Urgency | `Low`, `Medium`, `High`, `Critical` |

### Commission fields

| Field name | Label | Notes |
|---|---|---|
| `commissionRate` | Commission Rate | Float |
| `commissionAmount` | Commission Amount | Currency |

### Sync & AMS lock fields

| Field name | Label | Notes |
|---|---|---|
| `syncStatus` | Sync Status | `Synced`, `Pending`, `Error`, `Skipped` — managed by sync engine |
| `amsLockState` | AMS Lock State | `Unlocked`, `Pending AMS`, `Locked by AMS`, `Rejected by AMS` |
| `amsLockReason` | AMS Lock Reason | Text set when locked/rejected |
| `acceptedByAmsAt` | AMS Accepted At | Read-only; set by AMS acceptance |
| `acceptedByAmsBy` | AMS Accepted By | Read-only; set by AMS acceptance |

### Immutable external IDs

`momentumPolicyId`, `insuredMomentumId`, `momentumLastSynced` — **never overwrite**

---

## Renewal

| Field name | Label | Notes |
|---|---|---|
| `name` | Renewal Name | Auto-generated |
| `stage` | Stage | See `schema.md` |
| `urgency` | Urgency | `Critical`, `High`, `Medium`, `Low` |
| `lineOfBusiness` | Line of Business | Full LOB enum |
| `carrier` | Current Carrier | Incumbent carrier |
| `currentPremium` | Current Premium | Currency |
| `renewalPremium` | Renewal Premium | Currency |
| `premiumChange` | Premium Change % | Float |
| `expirationDate` | Expiration Date | Policy expiration |
| `renewalEffectiveDate` | Renewal Effective Date | New policy effective date |
| `lastContactDate` | Last Contact Date | Date |
| `lastContactMethod` | Last Contact Method | `Email`, `Call`, `Text`, `In Person` |
| `lostReason` | Lost Reason | `Price`, `Coverage`, `Unresponsive`, `Moved Carrier`, `Other` |
| `expectedCommission` | Expected Commission | Currency |
| `policy` | Expiring Policy | Link to expiring Policy |
| `newPolicy` | Renewed Policy | Link to new/renewed Policy |

---

## Commission

### Core fields (query these most)

| Field name | Label | Notes |
|---|---|---|
| `name` | Name | Auto-generated key |
| `commissionType` | Commission Type | `New Business`, `Renewal` |
| `status` | Status | `Estimated`, `Posted`, `Overdue` |
| `carrier` | Carrier | Insurer |
| `lineOfBusiness` | Line of Business | LOB enum |
| `effectiveDate` | Effective Date | Date |
| `writtenPremium` | Written Premium | Currency |
| `commissionRate` | Commission Rate | Float |
| `estimatedCommission` | Estimated Commission | Currency |
| `postedAmount` | Posted Amount | Currency |
| `varianceAmount` | Variance Amount | Currency (posted − estimated) |
| `variancePercent` | Variance Percent | Float |
| `expectedPaymentDate` | Expected Payment Date | Date |
| `paymentReceivedDate` | Payment Received Date | Date |
| `overdueFlag` | Overdue Flag | Boolean; computed by scheduled job |

### Ledger sync fields (owned by n8n — do not manually edit)

`ledgerKey`, `ledgerExternalId`, `ledgerSyncStatus` (`Pending`/`Synced`/`Error`), `ledgerSyncedAt`, `ledgerSyncError`, `ledgerPayloadHash`, `reconciliationStatus`

---

## Task

### Core fields (query these most)

| Field name | Label | Notes |
|---|---|---|
| `name` | Name | Short task title |
| `status` | Status | `Inbox`, `In Progress`, `Waiting on Client`, `Waiting on Carrier`, `Completed`, `Cancelled` |
| `priority` | Priority | `Low`, `Normal`, `High`, `Urgent` |
| `taskType` | Task Type | `Client Service`, `Policy Change`, `Renewal`, `New Business`, `Follow Up`, `Onboarding`, `Claims`, `Commission`, `Admin`, `Other` |
| `dateEnd` | Date Due | Due date/time (SLA reference; aliased as `slaDueDate` in webhook payloads) |
| `isOverdue` | Is Overdue | Boolean; computed |
| `parent` | Parent | Polymorphic link: Account, Contact, Lead, Opportunity, Case, Policy, or Renewal |

### Source & triage fields

| Field name | Label | Notes |
|---|---|---|
| `taskSource` | Source | `Account`, `Contact`, `Policy` |
| `syncSource` | Sync Source | `Manual`, `Gmail`, `Slack`, `Momentum`, `n8n`, `Viktor` |
| `triageReason` | Triage Reason | Text explaining why task was escalated |
| `triageSummary` | Triage Summary | AI-generated summary of triage |
| `dateCompleted` | Date Completed | Set automatically when status → `Completed` |

---

## Field Type Reference

| Type label | Meaning |
|---|---|
| `Varchar` | Short text (single line) |
| `Text` | Long text (multi-line) |
| `Enum` | Dropdown — only listed values are valid |
| `Multi-Enum` | Multi-select dropdown — accepts a JSON array of option strings |
| `Boolean` | `true` / `false` (Yes/No) |
| `Currency` | Monetary amount (USD) |
| `Float` | Decimal number |
| `Integer` | Whole number |
| `Date` | `YYYY-MM-DD` |
| `Date-Time` | `YYYY-MM-DDTHH:MM:SSZ` (UTC) |
| `Link` | Foreign key to a single related record |
| `Link Multiple` | Many-to-many relation |
| `Link One` | One-to-one foreign key |
| `Link Parent` | Polymorphic relation (entity type + ID) |

When writing via API: Enum fields must receive exactly one listed option string. Multi-Enum fields accept a JSON array of option strings.
