# Opportunity

**Entity name:** `Opportunity`  
**Plural label:** Opportunities  
**Type:** Core entity (module: `Crm`)  
**Field count:** 197  
**Link count:** 20  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Opportunity`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Opportunity/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Opportunity`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Opportunity/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Opportunity/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `account` | Account | link |  | — | — |
| `aggregatePageId` | Aggregate Page ID | varchar |  | — | max 255, custom |
| `aiSummary` | AI Summary | text |  | — | custom |
| `amount` | Amount | currency |  | — | read-only, min 0 |
| `amountConverted` | Amount (converted) | currencyConverted |  | — | read-only |
| `amountCurrency` | Amount Currency | enum |  | — | read-only, max 3 |
| `amountWeightedConverted` | Amount Weighted | float |  | — | read-only |
| `assignedUser` | assignedUser | link |  | — | — |
| `autoAssessmentDate` | Auto Assessment Date | datetime |  | — | custom |
| `autoAssessmentNotes` | Driver/Vehicle Assessment Notes | text |  | — | custom |
| `autoCurrentCarrierYears` | Years with Current Carrier | int |  | — | custom |
| `autoDriverCount` | Number of Drivers | int |  | — | custom |
| `autoGarageState` | Garaging State | varchar |  | — | max 50, custom |
| `autoPriorAccidents` | Prior Accidents (3 years) | int |  | — | custom |
| `autoPriorViolations` | Prior Violations (3 years) | int |  | — | custom |
| `autoSR22Required` | SR-22 Required? | bool |  | false | custom |
| `autoTotalVehicleValue` | Total Vehicle Value | currency |  | — | custom |
| `autoTotalVehicleValueConverted` | autoTotalVehicleValueConverted | currencyConverted |  | — | read-only |
| `autoTotalVehicleValueCurrency` | autoTotalVehicleValueCurrency | enum |  | — | max 3 |
| `autoUseType` | Vehicle Use Type | enum |  | — | custom |
| `autoVehicleCount` | Number of Vehicles | int |  | — | custom |
| `autoYoungestDriverAge` | Youngest Driver Age | int |  | — | custom |
| `bindDate` | Bind Date | date |  | — | custom |
| `businessType` | Business Type | enum |  | — | custom |
| `cClientEmail` | cClientEmail | email |  | — | custom |
| `cClientEmailIsInvalid` | cClientEmailIsInvalid | bool |  | — | — |
| `cClientEmailIsOptedOut` | cClientEmailIsOptedOut | bool |  | — | — |
| `cRenewalDate` | Renewal Date | date |  | — | custom |
| `caBusType` | Business Type | enum |  | — | custom |
| `caCommodity` | Commodity | varchar |  | — | max 255, custom |
| `caCurrentExpiration` | Current Policy Expiration | date |  | — | custom |
| `caCurrentPolicyNum` | Current Policy Number | varchar |  | — | max 100, custom |
| `caCurrentVehicleModifications` | Current Vehicle Modifications | text |  | — | custom |
| `caDotNumber` | DOT Number | varchar |  | — | max 20, custom |
| `caDriverCount` | Driver Count | int |  | — | custom |
| `caEquipmentType` | Equipment Type | enum |  | — | custom |
| `caEquipmentValue` | Equipment Value | currency |  | — | custom |
| `caEquipmentValueConverted` | caEquipmentValueConverted | currencyConverted |  | — | read-only |
| `caEquipmentValueCurrency` | caEquipmentValueCurrency | enum |  | — | max 3 |
| `caMcNumber` | MC Number | varchar |  | — | max 20, custom |
| `caRadius` | Operating Radius | enum |  | — | custom |
| `caVehicleCount` | Vehicle Count | int |  | — | custom |
| `callbackDate` | Callback Date | date |  | — | read-only, custom |
| `campaign` | Campaign | link |  | — | — |
| `carrier` | Carrier | varchar |  | — | max 255, custom |
| `chkAppSubmitted` | chkAppSubmitted | bool |  | false | custom |
| `chkBound` | chkBound | bool |  | false | custom |
| `chkCmsConfirmation` | chkCmsConfirmation | bool |  | false | custom |
| `chkDecPageDelivered` | chkDecPageDelivered | bool |  | false | custom |
| `chkMvrsPulled` | chkMvrsPulled | bool |  | false | custom |
| `chkPlanPresented` | chkPlanPresented | bool |  | false | custom |
| `chkProposalSent` | chkProposalSent | bool |  | false | custom |
| `chkQuoteSubmitted` | chkQuoteSubmitted | bool |  | false | custom |
| `chkScopeOfAppt` | chkScopeOfAppt | bool |  | false | custom |
| `chkSignedAppReceived` | chkSignedAppReceived | bool |  | false | custom |
| `chkUnderlyingConfirmed` | chkUnderlyingConfirmed | bool |  | false | custom |
| `chkUnderlyingLinked` | chkUnderlyingLinked | bool |  | false | custom |
| `chkWelcomeLetter` | chkWelcomeLetter | bool |  | false | custom |
| `closeDate` | Close Date | date | yes | — | required |
| `commissionLogged` | Commission Logged | bool |  | false | custom |
| `commissionRate` | Commission Rate (Percent) | float |  | — | custom |
| `commissions` | commissions | linkMultiple |  | — | custom |
| `contact` | Contact (Primary) | link |  | — | — |
| `contactRole` | Contact Role | enum |  | — | — |
| `contacts` | Contacts | linkMultiple |  | — | — |
| `createdAt` | createdAt | datetime |  | — | read-only |
| `createdBy` | createdBy | link |  | — | read-only |
| `currentCarrier` | Current Carrier | varchar |  | — | max 255, custom |
| `description` | Description | text |  | — | — |
| `drInformation` | Doctor Information | text |  | — | custom |
| `driverDetails` | Driver Details | text |  | — | custom |
| `effectiveDate` | Effective Date | date |  | — | custom |
| `emailSequenceStarted` | Email Sequence Started | bool |  | false | custom |
| `estimatedCommission` | Estimated Commission | currency |  | — | custom |
| `estimatedCommissionConverted` | estimatedCommissionConverted | currencyConverted |  | — | read-only |
| `estimatedCommissionCurrency` | estimatedCommissionCurrency | enum |  | — | max 3 |
| `estimatedPremium` | Estimated Premium | currency |  | — | custom |
| `estimatedPremiumConverted` | estimatedPremiumConverted | currencyConverted |  | — | read-only |
| `estimatedPremiumCurrency` | estimatedPremiumCurrency | enum |  | — | max 3 |
| `fein` | Federal Employer Identification Number | varchar |  | — | max 20, custom |
| `gbCensusReceived` | Group Benefits Census Received | bool |  | false | custom |
| `gbEligibleEmployees` | Group Benefits Eligible Employees | int |  | — | custom |
| `gbEmployerContribution` | Group Benefits Employer Contribution | varchar |  | — | max 100, custom |
| `gbMedicalCarrier` | Group Benefits Medical Carrier | varchar |  | — | max 255, custom |
| `gbMedicalPlanType` | Group Benefits Medical Plan Type | enum |  | — | custom |
| `gbMedicalRenewalDate` | Group Benefits Medical Renewal Date | date |  | — | custom |
| `gbParticipatingEmployees` | Group Benefits Participating Employees | int |  | — | custom |
| `glAnnualPayroll` | glAnnualPayroll | currency |  | — | custom |
| `glAnnualPayrollConverted` | glAnnualPayrollConverted | currencyConverted |  | — | read-only |
| `glAnnualPayrollCurrency` | glAnnualPayrollCurrency | enum |  | — | max 3 |
| `glAnnualRevenue` | glAnnualRevenue | currency |  | — | custom |
| `glAnnualRevenueConverted` | glAnnualRevenueConverted | currencyConverted |  | — | read-only |
| `glAnnualRevenueCurrency` | glAnnualRevenueCurrency | enum |  | — | max 3 |
| `glClassCode` | glClassCode | varchar |  | — | max 20, custom |
| `glOperationsDesc` | glOperationsDesc | text |  | — | custom |
| `glSubcontractorsUsed` | glSubcontractorsUsed | bool |  | false | custom |
| `healthAssessmentDate` | Health Assessment Date | datetime |  | — | custom |
| `healthAssessmentNotes` | Health Risk Assessment Notes | text |  | — | custom |
| `healthBeneficiaryName` | Primary Beneficiary | varchar |  | — | max 255, custom |
| `healthCoverageType` | Coverage Type | enum |  | — | custom |
| `healthDateOfBirth` | Date of Birth | date |  | — | custom |
| `healthFaceAmount` | Face Amount Requested | currency |  | — | custom |
| `healthFaceAmountConverted` | healthFaceAmountConverted | currencyConverted |  | — | read-only |
| `healthFaceAmountCurrency` | healthFaceAmountCurrency | enum |  | — | max 3 |
| `healthGender` | Gender | enum |  | — | custom |
| `healthHeightInches` | Height (inches) | int |  | — | custom |
| `healthMedications` | Current Medications | text |  | — | custom |
| `healthPreExistingConditions` | Pre-existing Conditions | text |  | — | custom |
| `healthRiskClass` | Risk Class | enum |  | — | custom |
| `healthTobaccoUse` | Tobacco User? | bool |  | false | custom |
| `healthWeightLbs` | Weight (lbs) | int |  | — | custom |
| `lastContactMethod` | Last Contact Method | enum |  | — | custom |
| `lastStage` | Last Stage | enum |  | — | — |
| `leadSource` | Lead Source | enum |  | — | custom |
| `lifeCoverageType` | Life Coverage Type | enum |  | — | custom |
| `lifeFaceAmountRequested` | lifeFaceAmountRequested | currency |  | — | custom |
| `lifeFaceAmountRequestedConverted` | lifeFaceAmountRequestedConverted | currencyConverted |  | — | read-only |
| `lifeFaceAmountRequestedCurrency` | lifeFaceAmountRequestedCurrency | enum |  | — | max 3 |
| `lifeHealthClassTarget` | lifeHealthClassTarget | enum |  | — | custom |
| `lifeTermLength` | lifeTermLength | enum |  | — | custom |
| `lifeTobaccoUser` | lifeTobaccoUser | bool |  | false | custom |
| `lineOfBusiness` | Line of Business | enum | yes | — | required, custom |
| `lossRunsRequested` | Loss Runs Requested | bool |  | false | custom |
| `lostReason` | Lost Reason | enum |  | — | custom |
| `medAepSepDate` | AEP/SEP Date | date |  | — | custom |
| `medCurrentCarrier` | Current Medicare Carrier | varchar |  | — | max 255, custom |
| `medCurrentPlan` | Current Plan | varchar |  | — | max 255, custom |
| `medIrmaaApplies` | IRMAA Applies | bool |  | false | custom |
| `medPlanType` | Medicare Plan Type | enum |  | — | custom |
| `medicareHICN` | Medicare HICN | varchar |  | — | max 50, custom |
| `modifiedAt` | modifiedAt | datetime |  | — | read-only |
| `modifiedBy` | modifiedBy | link |  | — | read-only |
| `name` | Name | varchar | yes | — | required, pattern |
| `onboardingSent` | Onboarding Sent | bool |  | false | custom |
| `opportunityDrivers` | Driver Details | linkMultiple |  | — | custom |
| `opportunityVehicles` | Vehicle Details | linkMultiple |  | — | custom |
| `originalLead` | Original Lead | linkOne |  | — | read-only |
| `paCurrentExpiration` | paCurrentExpiration | date |  | — | custom |
| `paCurrentPremium` | paCurrentPremium | currency |  | — | custom |
| `paCurrentPremiumConverted` | paCurrentPremiumConverted | currencyConverted |  | — | read-only |
| `paCurrentPremiumCurrency` | paCurrentPremiumCurrency | enum |  | — | max 3 |
| `paPriorClaims` | paPriorClaims | enum |  | — | custom |
| `partAEffectiveDate` | Part A Effective Date | date |  | — | custom |
| `partBEffectiveDate` | Part B Effective Date | date |  | — | custom |
| `policies` | policies | linkMultiple |  | — | custom |
| `policyNumber` | Policy Number | varchar |  | — | max 100, custom |
| `policyStubId` | Policy Stub ID | varchar |  | — | read-only, max 50, custom |
| `policyStubStatus` | Policy Stub Status | enum |  | — | read-only, custom |
| `priority` | Priority | enum |  | — | custom |
| `probability` | Probability, % | int |  | — | min 0, max 100 |
| `propAddress` | propAddress | address |  | — | custom |
| `propAddressCity` | propAddressCity | varchar |  | — | max 100, pattern |
| `propAddressCountry` | propAddressCountry | varchar |  | — | max 100, pattern |
| `propAddressMap` | propAddressMap | map |  | — | read-only |
| `propAddressPostalCode` | propAddressPostalCode | varchar |  | — | max 40, pattern |
| `propAddressState` | propAddressState | varchar |  | — | max 100, pattern |
| `propAddressStreet` | propAddressStreet | text |  | — | max 255 |
| `propAssessmentDate` | Property Assessment Date | datetime |  | — | custom |
| `propAssessmentNotes` | Property Assessment Notes | text |  | — | custom |
| `propConstructionType` | Construction Type | enum |  | — | custom |
| `propCurrentCarrier` | propCurrentCarrier | varchar |  | — | max 255, custom |
| `propCurrentPremium` | propCurrentPremium | currency |  | — | custom |
| `propCurrentPremiumConverted` | propCurrentPremiumConverted | currencyConverted |  | — | read-only |
| `propCurrentPremiumCurrency` | propCurrentPremiumCurrency | enum |  | — | max 3 |
| `propFloodZone` | Flood Zone | varchar |  | — | max 20, custom |
| `propOccupancy` | Occupancy Type | enum |  | — | custom |
| `propPriorClaims` | Prior Claims (3 years) | int |  | — | custom |
| `propProtectionClass` | Protection Class | varchar |  | — | max 10, custom |
| `propReplacementCost` | Estimated Replacement Cost | currency |  | — | custom |
| `propReplacementCostConverted` | propReplacementCostConverted | currencyConverted |  | — | read-only |
| `propReplacementCostCurrency` | propReplacementCostCurrency | enum |  | — | max 3 |
| `propRoofAge` | Roof Age (years) | int |  | — | custom |
| `propRoofType` | Roof Type | enum |  | — | custom |
| `propSquareFootage` | Square Footage | int |  | — | custom |
| `propYearBuilt` | Year Built | int |  | — | custom |
| `quoteNumber` | Quote Number | varchar |  | — | max 100, custom |
| `quotes` | quotes | linkMultiple |  | — | custom |
| `recycleToLead` | Recycle To Lead | bool |  | false | custom |
| `recycledLead` | Recycled Lead | link |  | — | read-only, custom |
| `rxInformation` | Rx Information | text |  | — | custom |
| `skipEmailSequence` | Skip Email Sequence | bool |  | false | custom |
| `stage` | Stage | enum |  | `Discovery` | — |
| `streamUpdatedAt` | streamUpdatedAt | datetime |  | — | read-only |
| `targetCarrier` | Target Carrier | varchar |  | — | max 255, custom |
| `teams` | teams | linkMultiple |  | — | — |
| `umbrellaLimit` | umbrellaLimit | enum |  | — | custom |
| `vehicleDetails` | Vehicle Details | text |  | — | custom |
| `wcAnnualPayroll` | wcAnnualPayroll | currency |  | — | custom |
| `wcAnnualPayrollConverted` | wcAnnualPayrollConverted | currencyConverted |  | — | read-only |
| `wcAnnualPayrollCurrency` | wcAnnualPayrollCurrency | enum |  | — | max 3 |
| `wcClassCode` | wcClassCode | varchar |  | — | max 20, custom |
| `wcEmployeeCount` | wcEmployeeCount | int |  | — | custom |
| `wcExperienceMod` | wcExperienceMod | float |  | — | custom |
| `writtenPremium` | Written Premium | currency |  | — | custom |
| `writtenPremiumConverted` | writtenPremiumConverted | currencyConverted |  | — | read-only |
| `writtenPremiumCurrency` | writtenPremiumCurrency | enum |  | — | max 3 |
| `xDate` | Renewal X-Date | date |  | — | custom |

## Allowed values (enum / multi-enum / array / checklist)

### `autoUseType` — Vehicle Use Type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Personal`
  - `Business`
  - `Commercial`
  - `Rideshare`
  - `Mixed`

### `businessType` — Business Type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `New Business`
  - `Renewal`
  - `Rewrite`

### `caBusType` — Business Type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Trucking`
  - `Contractor`
  - `Fleet`
  - `Delivery`
  - `Other`

### `caEquipmentType` — Equipment Type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Owned`
  - `Leased`
  - `Rented`
  - `Mixed`
  - `Other`

### `caRadius` — Operating Radius

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Local <50mi`
  - `Intermediate 50-200mi`
  - `Long haul 200+mi`

### `gbMedicalPlanType` — Group Benefits Medical Plan Type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `PPO`
  - `HMO`
  - `HDHP`
  - `EPO`
  - `POS`
  - `Other`

### `healthCoverageType` — Coverage Type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Term`
  - `Whole Life`
  - `Universal Life`
  - `Final Expense`
  - `Medicare Supplement`
  - `Medicare Advantage`
  - `Group`
  - `Individual`
  - `Other`

### `healthGender` — Gender

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Male`
  - `Female`
  - `Other`

### `healthRiskClass` — Risk Class

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Preferred Plus`
  - `Preferred`
  - `Standard Plus`
  - `Standard`
  - `Substandard`
  - `Declined`
  - `Pending`

### `lastContactMethod` — Last Contact Method

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Phone`
  - `Email`
  - `Text`
  - `No Response`

### `leadSource` — Lead Source

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

### `lifeCoverageType` — Life Coverage Type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Term`
  - `Whole`
  - `Universal`
  - `IUL`

### `lifeHealthClassTarget` — lifeHealthClassTarget

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Preferred Plus`
  - `Preferred`
  - `Standard`
  - `Rated`

### `lifeTermLength` — lifeTermLength

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `10yr`
  - `15yr`
  - `20yr`
  - `30yr`

### `lineOfBusiness` — Line of Business

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Commercial Auto`
  - `Transportation / Trucking`
  - `General Liability`
  - `Workers Comp`
  - `Commercial Property`
  - `BOP`
  - `Professional Liability`
  - `Umbrella`
  - `Builders Risk`
  - `Inland Marine`
  - `Personal Auto`
  - `Homeowners`
  - `Renters`
  - `Condo`
  - `Dwelling Fire`
  - `Motorcycle`
  - `Boat`
  - `RV`
  - `Life`
  - `Health`
  - `Medicare`
  - `Group Benefits`
  - `Garagekeepers`
  - `Commercial Package`
  - `Other`

### `lostReason` — Lost Reason

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Price`
  - `Coverage`
  - `Service`
  - `Competitor Stole`
  - `Business Closed`
  - `Carrier Non-Renewed`
  - `Client Moved`
  - `Unknown`
  - `N/A`

### `medPlanType` — Medicare Plan Type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Advantage`
  - `Supplement`
  - `Part D`

### `paPriorClaims` — paPriorClaims

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `None`
  - `1`
  - `2+`

### `policyStubStatus` — Policy Stub Status

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Pending Sync`
  - `Synced`

### `priority` — Priority

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Hot`
  - `Warm`
  - `Cold`

### `propConstructionType` — Construction Type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Frame`
  - `Masonry`
  - `Mixed`

### `propOccupancy` — Occupancy Type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Owner Occupied`
  - `Tenant Occupied`
  - `Seasonal`
  - `Vacant`
  - `Commercial`

### `propRoofType` — Roof Type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Asphalt Shingle`
  - `Metal`
  - `Tile`
  - `Flat`
  - `Slate`
  - `Other`

### `stage` — Stage

- Type: `enum`
- Default: `Discovery`
- Options:
  - `""` _(empty)_
  - `Discovery`
  - `Quoting`
  - `Markets Out / Shopping`
  - `Proposal Presented`
  - `Negotiation`
  - `Closed Won`
  - `Closed Lost`

### `umbrellaLimit` — umbrellaLimit

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `$1M`
  - `$2M`
  - `$5M`
  - `$10M`

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `account` | account | belongsTo | `Account` | `opportunities` | — |
| `assignedUser` | assignedUser | belongsTo | `User` | `—` | — |
| `calls` | calls | hasChildren | `Call` | `parent` | audited |
| `campaign` | Campaign | belongsTo | `Campaign` | `opportunities` | — |
| `commissions` | commissions | hasMany | `Commission` | `opportunity` | custom |
| `contact` | Contact (Primary) | belongsTo | `Contact` | `opportunitiesPrimary` | — |
| `contacts` | Contacts | hasMany | `Contact` | `opportunities` | — |
| `createdBy` | createdBy | belongsTo | `User` | `—` | — |
| `documents` | Documents | hasMany | `Document` | `opportunities` | audited |
| `emails` | emails | hasChildren | `Email` | `parent` | — |
| `meetings` | meetings | hasChildren | `Meeting` | `parent` | audited |
| `modifiedBy` | modifiedBy | belongsTo | `User` | `—` | — |
| `opportunityDrivers` | opportunityDrivers | hasMany | `OpportunityDriver` | `opportunity` | custom |
| `opportunityVehicles` | opportunityVehicles | hasMany | `OpportunityVehicle` | `opportunity` | custom |
| `originalLead` | Original Lead | hasOne | `Lead` | `createdOpportunity` | — |
| `policies` | policies | hasMany | `Policy` | `opportunities` | custom, relation `policyOpportunity` |
| `quotes` | quotes | hasMany | `Quote` | `opportunity` | custom |
| `recycledLead` | recycledLead | belongsTo | `Lead` | `—` | custom |
| `tasks` | tasks | hasChildren | `Task` | `parent` | audited |
| `teams` | teams | hasMany | `Team` | `—` | relation `entityTeam` |

## Unique indexes

- **createdAtId**: `createdAt`, `id`

---

_Generated 2026-05-26 from `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`._
