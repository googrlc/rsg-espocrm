# Pass 1: Human-readable field labels (planned)

Cursor **Plan mode** prevented writing to `custom/Espo/Custom/Resources/i18n/en_US/*.json` (non-markdown targets). Below is the **exact deliverable** you asked for—apply these updates in Agent mode or paste manually, then omit `bin/command rebuild`.

**Shared-field canonical labels applied everywhere listed:** same string for each API name across entities.

---

## Instructions (before edit)

1. For each JSON file, under `fields`, set the label exactly as listed in the changelog.
2. New keys (not previously in `fields`) are indicated with _(add)_ — add them so UI does not rely on autofallback.
3. `options.accountType` / `options.intelEntityType` edits on Account widen abbreviations (`MGA` → "Managing General Agent"; entity types spelled out)—only if present in Account.json `options`.

---

## Summary: `Entity | Field API Name | Old Label | New Label`

Legend: blank Old means _(add)_ to `fields`. “*” suffix = preset filter keys in Policy/Renewal (not core `fields`).

### Account (`Account.json`)

| Field API Name | Old Label | New Label |
|---|---|---|
| lob | LOB | Line of Business |
| xDate | — | Renewal X-Date _(add)_ |
| estimatedPremium | Est. Premium | Estimated Premium |
| csrName | — | Customer Service Representative Name _(add)_ |
| lastContactDate | — | Last Contact Date _(add)_ |
| npsDate | — | Net Promoter Score Date _(add)_ |
| npsScore | — | Net Promoter Score _(add)_ |
| intelRun | Intel Run | Intel Run (AI) |
| intelRunDate | Last Run | Last Run (AI) |
| intelRunBy | Run By | Run By (AI) |
| intelSourcesHit | Sources Hit | Sources Hit (AI) |
| intelConfidence | Confidence | Confidence (AI) _(remove duplicate key at line ~137)_ |
| intelAiSummary | AI Summary | Assessment Summary (AI) |
| intelPainPoints | Pain Points | Pain Points (AI) |
| intelCrossSell | Cross-Sell Opportunities | Cross-Sell Opportunities (AI) |
| intelGrowthIndicator | Growth Indicator | Growth Indicator (AI) |
| intelLegalName | Legal Name | Legal Name (AI) |
| intelDba | DBA | Doing Business As (AI) |
| intelNaics | NAICS | North American Industry Classification System Code (AI) |
| intelSic | SIC | Standard Industrial Classification Code (AI) |
| intelEntityType | Entity Type | Entity Type (AI) |
| intelYearsInBusiness | Years in Business | Years in Business (AI) |
| intelEmployeeCount | Employees | Employee Count (AI) |
| intelAnnualRevenueEst | Est. Revenue | Annual Revenue Estimate (AI) |
| intelWebsite | Website | Website (AI) |
| intelLinkedinUrl | LinkedIn | LinkedIn URL (AI) |
| intelBbbRating | BBB Rating | Better Business Bureau Rating (AI) |
| intelBbbAccredited | BBB Accredited | Better Business Bureau Accredited (AI) |
| intelBbbComplaints | Open Complaints | Open Better Business Bureau Complaints (AI) |
| intelFleetSize | Fleet Size | Fleet Size (AI) |
| intelOperatingRadius | Operating Radius | Operating Radius (AI) |
| intelCargoType | Cargo Type | Cargo Type (AI) |
| intelOwnerOperators | Owner-Operators on Payroll | Owner Operators on Payroll (AI) |
| intelDotIncidents | DOT Incidents | Department of Transportation Incidents (AI) |
| intelOshaViolations | OSHA Violations | Occupational Safety and Health Administration Violations (AI) |
| intelUnderwritingFlag | Underwriting Flag | Underwriting Flag (AI) |
| intelWebsiteNotes | Website | Website Notes (AI) |
| intelNewsNotes | News | News Notes (AI) |
| intelLinkedinNotes | LinkedIn | LinkedIn Notes (AI) |
| intelBbbNotes | BBB | Better Business Bureau Notes (AI) |
| intelSignalNews | News Signal | News Signal (AI) |
| intelSignalLinkedin | LinkedIn Signal | LinkedIn Signal (AI) |
| cYearBusinessEst | Year Business Est | Calendar Year Business Established |
| annualPremiumCurrency | Annual Premium | Annual Premium Currency |
| bbbRating | BBB Rating | Better Business Bureau Rating |
| yearsInBusiness | Years In Business | Years in Business |
| employeeCount | Employee Count | Employee Count |
| numberOfEmployees | Number of Employees | Employee Count |
| claimsCount3yr | Claims (3yr) | Claims Count (Three Year) |
| dependentsCount | Dependents | Dependent Count |
| youthfulDriverFlag | Youthful Driver | Youthful Driver Flag |
| fein | FEIN | Federal Employer Identification Number |
| intelPackRun | Intel Pack Run | Intel Pack Run (AI) |
| intelPackLastRun | Intel Pack Last Run | Intel Pack Last Run (AI) |
| googleDriveFolderUrl | Google Drive Folder | Google Drive Folder Link |
| sicCode | SIC Code | Standard Industrial Classification Code |
| nextXDate | Next X-Date | Next Renewal X-Date |
| nextXDateLob | Next X-Date LOB | Next Renewal X-Date Line of Business |
| gapUmbrella | Gap: Umbrella | Coverage Gap: Umbrella |
| gapLife | Gap: Life | Coverage Gap: Life |
| gapAutoUm | Gap: Auto UM/UIM | Coverage Gap: Automobile Uninsured and Underinsured Motorist |
| gapCount | Gap Count | Coverage Gap Count |
| gapUmbrellaReason | Umbrella Gap Reason | Umbrella Coverage Gap Reason |
| gapLifeReason | Life Gap Reason | Life Coverage Gap Reason |
| gapLifeNeedEst | Life Need Estimate | Life Coverage Need Estimate |
| gapMedicare | Gap: Medicare | Coverage Gap: Medicare |
| gapRenters | Gap: Renters | Coverage Gap: Renters |
| gapRideshare | Gap: Rideshare | Coverage Gap: Rideshare |
| agentOfAgencyCode | Agent / Agency Code | Agent and Agency Code |
| gbDentalCarrier | Dental Carrier | Group Benefits Dental Carrier |
| gbDentalMonthlyPremium | Dental Monthly Premium | Group Benefits Dental Monthly Premium |
| gbVisionCarrier | Vision Carrier | Group Benefits Vision Carrier |
| gbVisionMonthlyPremium | Vision Monthly Premium | Group Benefits Vision Monthly Premium |
| gbLifeAdCarrier | Life & AD&D Carrier | Group Benefits Life and Accidental Death and Dismemberment Carrier |
| gbLifeBenefitAmount | Life Benefit Amount | Group Benefits Life Benefit Amount |
| gbStdCarrier | STD Carrier | Group Benefits Short-Term Disability Carrier |
| gbLtdCarrier | LTD Carrier | Group Benefits Long-Term Disability Carrier |
| gbEligibleEmployees | Eligible Employees | Group Benefits Eligible Employees |
| gbParticipatingEmployees | Participating Employees | Group Benefits Participating Employees |
| gbCensusReceived | Census Received | Group Benefits Census Received |
| gbCensusDate | Census Date | Group Benefits Census Date |
| gbMedicalCarrier | Medical Carrier | Group Benefits Medical Carrier |
| gbMedicalPlanType | Medical Plan Type | Group Benefits Medical Plan Type |
| gbMedicalRenewalDate | Medical Renewal Date | Group Benefits Medical Renewal Date |
| gbMedicalMonthlyPremium | Medical Monthly Premium | Group Benefits Medical Monthly Premium |
| gbEmployerContribution | Employer Contribution | Group Benefits Employer Contribution |
| gbNotes | Group Benefits Notes | Group Benefits Notes |

**`options.accountType`** (label values unchanged except last row): `"MGA": "Managing General Agent"`

**`options.intelEntityType`:** expand option labels (`LLC`, `Corp`, `Sole Prop`) to "Limited Liability Company", "Corporation", "Sole Proprietorship" (keep stored values; translate display strings only).

---

### Lead (`Lead.json`)

| Field API Name | Old Label | New Label |
|---|---|---|
| estimatedPremium | Est. Premium | Estimated Premium |
| xDate | X-Date | Renewal X-Date |
| intelPackRun | Intel Pack Run | Intel Pack Run (AI) |
| medicareEligible | — | Medicare Eligible _(add)_ |

---

### Opportunity (`Opportunity.json`)

| Field API Name | Old Label | New Label |
|---|---|---|
| estimatedPremium | Est. Premium | Estimated Premium |
| estimatedCommission | Est. Commission | Estimated Commission |
| commissionRate | Commission Rate % | Commission Rate (Percent) |
| fein | FEIN | Federal Employer Identification Number |
| gbCensusReceived | Census Received? | Group Benefits Census Received |
| gbEligibleEmployees | Eligible Employees | Group Benefits Eligible Employees |
| gbParticipatingEmployees | Participating Employees | Group Benefits Participating Employees |
| gbMedicalRenewalDate | Medical Renewal Date | Group Benefits Medical Renewal Date |
| gbMedicalPlanType | Medical Plan Type | Group Benefits Medical Plan Type |
| gbMedicalCarrier | Medical Carrier | Group Benefits Medical Carrier |
| gbEmployerContribution | Employer Contribution | Group Benefits Employer Contribution |

---

### Policy (`Policy.json`)

| Field API Name | Old Label | New Label |
|---|---|---|
| commissionRate | Commission Rate % | Commission Rate (Percent) |
| momentumLastSynced | Last Synced | Momentum Last Synced |
| premiumAmount | Premium | Premium Amount |

**`presetFilters` (abbreviation cleanup):**

| Key | Old | New |
|---|---|---|
| commAuto | Comm Auto | Commercial Auto |
| genLiability | Gen Liability | General Liability |
| lifeInsurance | Life | Life Insurance |

---

### Renewal (`Renewal.json`)

| Field API Name | Old Label | New Label |
|---|---|---|
| commissionRate | Renewal Commission Rate | Commission Rate (Percent) |
| carrier | Current Carrier | Carrier |
| premiumChange | Premium Change % | Premium Change (Percent) |

---

### Quote (`Quote.json`)

No label changes—all shared fields already matched canonical wording.

---


### Task (`Task.json`)

| Field API Name | Old Label | New Label |
|---|---|---|
| momentumLastSynced | Last Synced | Momentum Last Synced |

---

### Contact (`Contact.json`)

| Field API Name | Old Label | New Label |
|---|---|---|
| contactType | Type | Contact Type |
| csrName | — | Customer Service Representative Name _(add)_ |
| momentumClientId | — | Momentum Client ID _(add)_ |
| momentumLastSynced | — | Momentum Last Synced _(add)_ |
| medicareEligible | Medicare Eligible | Medicare Eligible |

---

### ActivityLog (`ActivityLog.json`)

| Field API Name | Old Label | New Label |
|---|---|---|
| duration | Duration (min) | Duration (Minutes) |
| dateTime | Date/Time | Date and Time |

---

### Commission (`Commission.json`)

Extend `fields` to include _(add)_ labels for clarity (only add keys that mirror entity fields):

| Field API Name | Old Label | New Label |
|---|---|---|
| lineOfBusiness | — | Line of Business _(add)_ |
| carrier | — | Carrier _(add)_ |
| commissionRate | — | Commission Rate (Percent) _(add)_ |
| estimatedCommission | — | Estimated Commission _(add)_ |
| effectiveDate | — | Effective Date _(add)_ |
| writtenPremium | — | Written Premium _(add)_ |

(Existing reconciliation keys stay; only augment missing commerce labels.)

---

### OpportunityDriver / OpportunityVehicle

No edits required (labels already match shared canonical strings).

---


### Call.json, Case.json, Meeting.json, Email.json

No `fields` block changes required beyond those files’ scope (filters only). Leave unchanged unless expanded in Pass 2.

---

## Apply

1. Switch Cursor to **Agent** mode **or** copy row-by-row from this table into each `entities` `fields` (and preset sections where noted).
2. After merge, **`bin/command rebuild` deliberately not run**—per Pass 2 note.

---

## Cross-entity parity checks (manual)

Spot-check in UI grep that these match across Account, Lead, Opportunity, Policy, Renewal, Commission, Quote, Task, Contact, OpportunityDriver/Vehicle:

`lastContactDate`, `lineOfBusiness`, `estimatedPremium`, `commissionRate`, `effectiveDate`, `expirationDate`, `dateOfBirth`, `xDate`, `bindDate`, `policyNumber`, `currentCarrier`, `lostReason`, `callbackDate`, `medicareEligible`, `momentumClientId`, `momentumLastSynced`, `gb*` group labels, `csrName`, `intelPackRun`, `businessType`.

---

*Generated for planning; Agent mode applies JSON patches.*
