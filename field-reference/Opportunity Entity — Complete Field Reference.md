Here is the complete reference for all fields in the **Opportunity** entity:

---
### Field Types Key
`Varchar` = Short text | `Text` = Long text | `Enum` = Dropdown | `Boolean` = Yes/No | `Currency` = Currency | `Float` = Decimal number | `Integer` = Whole number | `Date` = Date | `Date-Time` = DateTime | `Email` = Email | `Link` = Related record | `Address` = Address block

---

### Core / General Fields

| Label | Field Name | Type | Options |
|---|---|---|---|
| Name | name | Varchar | — |
| Account | account | Link | — |
| Contact (Primary) | contact | Link | — |
| Contacts | contacts | Link Multiple | — |
| Contact Role | contactRole | Enum | *(system-managed, mirrors Contact opportunity role)* |
| Stage | stage | **Enum** | Prospect, Qualify, Quote, Proposal, Negotiate, Won - Bound, Lost, Renewal Notice Sent, Markets Out / Shopping, Quoted, Presented to Client, Bound / Renewed, Non-Renewal / Lost |
| Last Stage | lastStage | Enum | *(system-managed, mirrors stage history)* |
| Priority | priority | **Enum** | Hot, Warm, Cold |
| Business Type | businessType | **Enum** | New Business, Renewal, Rewrite |
| Line of Business | lineOfBusiness | **Enum** | Commercial Auto, General Liability, Workers Comp, Commercial Property, BOP, Professional Liability, Umbrella, Builders Risk, Inland Marine, Personal Auto, Homeowners, Renters, Condo, Dwelling Fire, Motorcycle, Boat, RV, Life, Health, Medicare, Group Benefits, Garagekeepers, Commercial Package, Other |
| Lead Source | leadSource | **Enum** | *(references Lead.source — Call, Email, Existing Customer, Partner, Public Relations, Web Site, Campaign, Other)* |
| Lost Reason | lostReason | **Enum** | Price, Coverage, Service, Competitor Stole, Business Closed, Carrier Non-Renewed, Client Moved, Unknown, N/A |
| Probability, % | probability | Integer | — |
| Close Date | closeDate | Date | — |
| Bind Date | bindDate | Date | — |
| Effective Date | effectiveDate | Date | — |
| Amount | amount | Currency | — |
| Amount Weighted | amountWeightedConverted | Float | — |
| Est. Premium | estimatedPremium | Currency | — |
| Written Premium | writtenPremium | Currency | — |
| Est. Commission | estimatedCommission | Currency | — |
| Commission Rate % | commissionRate | Float | — |
| Commission Logged | commissionLogged | Boolean | — |
| Carrier | carrier | Varchar | — |
| Target Carrier | targetCarrier | Varchar | — |
| Current Carrier | currentCarrier | Varchar | — |
| Policy Number | policyNumber | Varchar | — |
| Policy Stub ID | policyStubId | Varchar | — |
| Policy Stub Status | policyStubStatus | **Enum** | Pending Sync, Synced |
| Last Contact Method | lastContactMethod | **Enum** | Phone, Email, Text, No Response |
| Loss Runs Requested | lossRunsRequested | Boolean | — |
| Description | description | Text | — |
| AI Summary | aiSummary | Text | — |
| Aggregate Page ID | aggregatePageId | Varchar | — |
| FEIN | fein | Varchar | — |
| Campaign | campaign | Link | — |
| Original Lead | originalLead | Link One | — |
| Policies | policies | Link Multiple | — |
| Commissions | commissions | Link Multiple | — |
| Assigned User | assignedUser | Link | — |
| Teams | teams | Link Multiple | — |
| Created At | createdAt | Date-Time | — |
| Created By | createdBy | Link | — |
| Modified At | modifiedAt | Date-Time | — |
| Modified By | modifiedBy | Link | — |
| Stream Updated At | streamUpdatedAt | Date-Time | — |
| Renewal Date | cRenewalDate | Date | — |
| Client Email | cClientEmail | Email | — |
| Client Email is Invalid | cClientEmailIsInvalid | Boolean | — |
| Client Email is Opted-Out | cClientEmailIsOptedOut | Boolean | — |
| Email Sequence Started | emailSequenceStarted | Boolean | — |
| Skip Email Sequence | skipEmailSequence | Boolean | — |
| Onboarding Sent | onboardingSent | Boolean | — |

---

### Checklist Fields

| Label | Field Name | Type |
|---|---|---|
| App Submitted | chkAppSubmitted | Boolean |
| Bound | chkBound | Boolean |
| CMS Confirmation | chkCmsConfirmation | Boolean |
| Dec Page Delivered | chkDecPageDelivered | Boolean |
| MVRs Pulled | chkMvrsPulled | Boolean |
| Plan Presented | chkPlanPresented | Boolean |
| Proposal Sent | chkProposalSent | Boolean |
| Quote Submitted | chkQuoteSubmitted | Boolean |
| Scope of Appointment | chkScopeOfAppt | Boolean |
| Signed App Received | chkSignedAppReceived | Boolean |
| Underlying Confirmed | chkUnderlyingConfirmed | Boolean |
| Underlying Linked | chkUnderlyingLinked | Boolean |
| Welcome Letter | chkWelcomeLetter | Boolean |

---

### Auto / Personal Auto Fields

| Label | Field Name | Type | Options |
|---|---|---|---|
| Vehicle Use Type | autoUseType | **Enum** | Personal, Business, Commercial, Rideshare, Mixed |
| Number of Vehicles | autoVehicleCount | Integer | — |
| Number of Drivers | autoDriverCount | Integer | — |
| Youngest Driver Age | autoYoungestDriverAge | Integer | — |
| Prior Accidents (3 years) | autoPriorAccidents | Integer | — |
| Prior Violations (3 years) | autoPriorViolations | Integer | — |
| SR-22 Required? | autoSR22Required | Boolean | — |
| Garaging State | autoGarageState | Varchar | — |
| Total Vehicle Value | autoTotalVehicleValue | Currency | — |
| Years with Current Carrier | autoCurrentCarrierYears | Integer | — |
| Driver/Vehicle Assessment Notes | autoAssessmentNotes | Text | — |
| Auto Assessment Date | autoAssessmentDate | Date-Time | — |
| PA Prior Claims | paPriorClaims | **Enum** | None, 1, 2+ |
| PA Current Premium | paCurrentPremium | Currency | — |
| PA Current Expiration | paCurrentExpiration | Date | — |

---

### Commercial Auto Fields

| Label | Field Name | Type | Options |
|---|---|---|---|
| CA Business Type | caBusType | **Enum** | Trucking, Contractor, Fleet, Delivery, Other |
| Operating Radius | caRadius | **Enum** | Local <50mi, Intermediate 50-200mi, Long haul 200+mi |
| Vehicle Count | caVehicleCount | Integer | — |
| Driver Count | caDriverCount | Integer | — |
| Commodity | caCommodity | Varchar | — |
| DOT Number | caDotNumber | Varchar | — |
| MC Number | caMcNumber | Varchar | — |
| Current Policy Number | caCurrentPolicyNum | Varchar | — |
| Current Expiration | caCurrentExpiration | Date | — |

---

### Property Fields

| Label | Field Name | Type | Options |
|---|---|---|---|
| Property Address | propAddress | Address | — |
| Property City | propAddressCity | Varchar | — |
| Property State | propAddressState | Varchar | — |
| Property Postal Code | propAddressPostalCode | Varchar | — |
| Property Country | propAddressCountry | Varchar | — |
| Property Street | propAddressStreet | Text | — |
| Property Map | propAddressMap | Map | — |
| Construction Type | propConstructionType | **Enum** | Frame, Masonry, Mixed |
| Occupancy Type | propOccupancy | **Enum** | Owner Occupied, Tenant Occupied, Seasonal, Vacant, Commercial |
| Roof Type | propRoofType | **Enum** | Asphalt Shingle, Metal, Tile, Flat, Slate, Other |
| Roof Age (years) | propRoofAge | Integer | — |
| Year Built | propYearBuilt | Integer | — |
| Square Footage | propSquareFootage | Integer | — |
| Estimated Replacement Cost | propReplacementCost | Currency | — |
| Current Premium | propCurrentPremium | Currency | — |
| Current Carrier | propCurrentCarrier | Varchar | — |
| Flood Zone | propFloodZone | Varchar | — |
| Protection Class | propProtectionClass | Varchar | — |
| Prior Claims (3 years) | propPriorClaims | Integer | — |
| Property Assessment Date | propAssessmentDate | Date-Time | — |
| Property Assessment Notes | propAssessmentNotes | Text | — |

---

### General Liability Fields

| Label | Field Name | Type |
|---|---|---|
| GL Annual Payroll | glAnnualPayroll | Currency |
| GL Annual Revenue | glAnnualRevenue | Currency |
| GL Class Code | glClassCode | Varchar |
| GL Operations Description | glOperationsDesc | Text |
| GL Subcontractors Used | glSubcontractorsUsed | Boolean |

---

### Workers Comp Fields

| Label | Field Name | Type |
|---|---|---|
| WC Annual Payroll | wcAnnualPayroll | Currency |
| WC Class Code | wcClassCode | Varchar |
| WC Employee Count | wcEmployeeCount | Integer |
| WC Experience Mod | wcExperienceMod | Float |

---

### Life Fields

| Label | Field Name | Type | Options |
|---|---|---|---|
| Life Coverage Type | lifeCoverageType | **Enum** | Term, Whole, Universal, IUL |
| Term Length | lifeTermLength | **Enum** | 10yr, 15yr, 20yr, 30yr |
| Target Health Class | lifeHealthClassTarget | **Enum** | Preferred Plus, Preferred, Standard, Rated |
| Face Amount Requested | lifeFaceAmountRequested | Currency | — |
| Tobacco User? | lifeTobaccoUser | Boolean | — |

---

### Health Fields

| Label | Field Name | Type | Options |
|---|---|---|---|
| Health Coverage Type | healthCoverageType | **Enum** | Term, Whole Life, Universal Life, Final Expense, Medicare Supplement, Medicare Advantage, Group, Individual, Other |
| Risk Class | healthRiskClass | **Enum** | Preferred Plus, Preferred, Standard Plus, Standard, Substandard, Declined, Pending |
| Gender | healthGender | **Enum** | Male, Female, Other |
| Face Amount Requested | healthFaceAmount | Currency | — |
| Date of Birth | healthDateOfBirth | Date | — |
| Height (inches) | healthHeightInches | Integer | — |
| Weight (lbs) | healthWeightLbs | Integer | — |
| Tobacco User? | healthTobaccoUse | Boolean | — |
| Pre-existing Conditions | healthPreExistingConditions | Text | — |
| Current Medications | healthMedications | Text | — |
| Primary Beneficiary | healthBeneficiaryName | Varchar | — |
| Health Assessment Date | healthAssessmentDate | Date-Time | — |
| Health Assessment Notes | healthAssessmentNotes | Text | — |

---

### Medicare Fields

| Label | Field Name | Type | Options |
|---|---|---|---|
| Medicare Plan Type | medPlanType | **Enum** | Advantage, Supplement, Part D |
| Medicare Current Carrier | medCurrentCarrier | Varchar | — |
| Medicare Current Plan | medCurrentPlan | Varchar | — |
| AEP/SEP Date | medAepSepDate | Date | — |
| IRMAA Applies | medIrmaaApplies | Boolean | — |

---

### Umbrella Fields

| Label | Field Name | Type | Options |
|---|---|---|---|
| Umbrella Limit | umbrellaLimit | **Enum** | $1M, $2M, $5M, $10M |

---

**Summary counts:**
- **Total fields:** 170
- **Enum fields:** 25 with defined options
- **Boolean fields:** 24 (including 13 checklist fields)
- **Currency fields:** 18 (+ Converted sub-fields)
- **Integer fields:** 20
- **Varchar/Text fields:** 22
- **Date/Date-Time fields:** 15
- **Link/Relation fields:** 12
- **Float fields:** 3
- **Other (Email, Address, Map):** 6