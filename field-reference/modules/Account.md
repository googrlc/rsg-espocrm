# Account

**Entity name:** `Account`  
**Plural label:** Accounts  
**Type:** Core entity (module: `Crm`)  
**Field count:** 279  
**Link count:** 29  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Account`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Account/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Account`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Account/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Account/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `account_score` | Account Score | int |  | 0 | read-only, min 0, max 100, custom |
| `account_status` | Account Health | enum |  | `Active` | custom |
| `account_type` | Account Type | enum |  | — | custom |
| `activePolicyCount` | [OLD] Active Policy Count | int |  | — | min 0, custom |
| `agent_of_agency_code` | Agent and Agency Code | text |  | — | custom |
| `agent_of_record_date` | Agent of Record Date | date |  | — | read-only, custom |
| `ai_assessment` | AI Assessment | text |  | — | custom |
| `annualPremiumConverted` | Annual Premium Converted | currencyConverted |  | — | read-only |
| `annualPremiumCurrency` | Annual Premium Currency | enum |  | — | max 3 |
| `annualRevenueConverted` | Annual Revenue Converted | currencyConverted |  | — | read-only |
| `annualRevenueCurrency` | Annual Revenue Currency | enum |  | — | max 3 |
| `annual_premium` | Annual Premium | currency |  | — | custom |
| `annual_revenue` | Annual Revenue | currency |  | — | custom |
| `assessment_date` | Assessment Date | datetime |  | — | custom |
| `assignedUser` | assignedUser | link |  | — | — |
| `bbb_rating` | Better Business Bureau Rating | text |  | — | custom |
| `best_time_to_call` | Best Time to Call | text |  | — | custom |
| `billingAddress` | Billing Address | address |  | — | — |
| `billingAddressCity` | billingAddressCity | varchar |  | — | max 100, pattern |
| `billingAddressCountry` | billingAddressCountry | varchar |  | — | max 100, pattern |
| `billingAddressMap` | billingAddressMap | map |  | — | read-only |
| `billingAddressPostalCode` | billingAddressPostalCode | varchar |  | — | max 40, pattern |
| `billingAddressState` | billingAddressState | varchar |  | — | max 100, pattern |
| `billingAddressStreet` | billingAddressStreet | text |  | — | max 255 |
| `businessEntity` | Business Entity | enum |  | — | custom |
| `cConstructionSpecialty` | Construction Specialty | enum |  | `General Contractor` | max 100, custom |
| `cYearBusinessEst` | Calendar Year Business Established | text |  | — | custom |
| `campaign` | Campaign | link |  | — | — |
| `carrier` | Carrier | text |  | — | custom |
| `carrierPolicies` | Carrier Policies | linkMultiple |  | — | custom |
| `carrier_code` | Carrier Code | text |  | — | custom |
| `claims_count_3yr` | Claims Count (Three Year) | int |  | — | custom |
| `claims_count_lifetime` | claims_count_lifetime | int |  | — | custom |
| `claims_notes` | Claims Notes | text |  | — | custom |
| `claims_open` | Open Claims | int |  | — | custom |
| `clientNotes` | Client Notes | linkMultiple |  | — | custom |
| `client_since` | Client Since | date |  | — | custom |
| `clvAnnualCommissionConverted` | clvAnnualCommissionConverted | currencyConverted |  | — | read-only |
| `clvAnnualCommissionCurrency` | clvAnnualCommissionCurrency | enum |  | — | read-only, max 3 |
| `clvCurrentConverted` | clvCurrentConverted | currencyConverted |  | — | read-only |
| `clvCurrentCurrency` | clvCurrentCurrency | enum |  | — | read-only, max 3 |
| `clvProjectedConverted` | clvProjectedConverted | currencyConverted |  | — | read-only |
| `clvProjectedCurrency` | clvProjectedCurrency | enum |  | — | read-only, max 3 |
| `clvWithCrossSellConverted` | clvWithCrossSellConverted | currencyConverted |  | — | read-only |
| `clvWithCrossSellCurrency` | clvWithCrossSellCurrency | enum |  | — | read-only, max 3 |
| `clv_annual_commission` | Annual Commission | currency |  | — | read-only, custom |
| `clv_current` | CLV (To Date) | currency |  | — | read-only, custom |
| `clv_last_calculated` | CLV Last Calculated | datetime |  | — | read-only, custom |
| `clv_projected` | CLV (Projected) | currency |  | — | read-only, custom |
| `clv_retention_rate_applied` | Retention Rate Used | float |  | — | read-only, custom |
| `clv_tenure_years` | Tenure (Years) | float |  | — | read-only, custom |
| `clv_with_cross_sell` | CLV (with Cross-Sell) | currency |  | — | read-only, custom |
| `commissions` | Commissions | linkMultiple |  | — | custom |
| `communication_notes` | Communication Notes | text |  | — | custom |
| `contactIsInactive` | Inactive | bool |  | false | — |
| `contactRole` | Title | varchar |  | — | max 100, pattern |
| `coverage_gaps` | Coverage Gaps | text |  | — | custom |
| `createdAt` | createdAt | datetime |  | — | read-only |
| `createdBy` | createdBy | link |  | — | read-only |
| `csrName` | Customer Service Representative Name | text |  | — | custom |
| `dateOfBirth` | dateOfBirth | date |  | — | custom |
| `days_to_renewal` | Days to Next Renewal | int |  | — | read-only, custom |
| `description` | Description | text |  | — | — |
| `do_not_contact` | do_not_contact | bool |  | false | custom |
| `documentLinks` | documentLinks | text |  | — | custom |
| `documents` | Documents | attachmentMultiple |  | — | custom |
| `downloadsStatements` | downloadsStatements | bool |  | false | custom |
| `emailAddress` | Email | email |  | — | — |
| `emailAddressIsInvalid` | emailAddressIsInvalid | bool |  | — | — |
| `emailAddressIsOptedOut` | emailAddressIsOptedOut | bool |  | — | — |
| `employee_count` | Employee Count | int |  | — | custom |
| `estimatedPremiumConverted` | Estimated Premium Converted | currencyConverted |  | — | read-only |
| `estimatedPremiumCurrency` | estimatedPremiumCurrency | enum |  | — | max 3 |
| `estimatedRevenueConverted` | Estimated Revenue Converted | currencyConverted |  | — | read-only |
| `estimatedRevenueCurrency` | estimatedRevenueCurrency | enum |  | — | max 3 |
| `estimated_premium` | Estimated Premium | currency |  | — | custom |
| `estimated_revenue` | Estimated Revenue | currency |  | — | custom |
| `fein` | Federal Employer Identification Number | varchar |  | — | max 20, custom |
| `gapAutoUm` | Coverage Gap: Automobile Uninsured and Underinsured Motorist | bool |  | — | custom |
| `gapCount` | Coverage Gap Count | int |  | — | read-only, custom |
| `gapFinalExpense` | gapFinalExpense | bool |  | — | custom |
| `gapLandlord` | gapLandlord | bool |  | — | custom |
| `gapLife` | Coverage Gap: Life | bool |  | — | custom |
| `gapLifeNeedEst` | Life Coverage Need Estimate | currency |  | — | custom |
| `gapLifeNeedEstConverted` | gapLifeNeedEstConverted | currencyConverted |  | — | read-only |
| `gapLifeNeedEstCurrency` | gapLifeNeedEstCurrency | enum |  | — | max 3 |
| `gapLifeReason` | Life Coverage Gap Reason | text |  | — | custom |
| `gapMedicare` | Coverage Gap: Medicare | bool |  | — | custom |
| `gapMedicareEligible` | Medicare Eligible | date |  | — | custom |
| `gapRenters` | Coverage Gap: Renters | bool |  | — | custom |
| `gapRideshare` | Coverage Gap: Rideshare | bool |  | — | custom |
| `gapUmbrella` | Coverage Gap: Umbrella | bool |  | — | custom |
| `gapUmbrellaReason` | Umbrella Coverage Gap Reason | text |  | — | custom |
| `gbCensusDate` | Group Benefits Census Date | date |  | — | custom |
| `gbCensusReceived` | Group Benefits Census Received | bool |  | false | custom |
| `gbDentalCarrier` | Group Benefits Dental Carrier | text |  | — | custom |
| `gbDentalMonthlyPremium` | Group Benefits Dental Monthly Premium | currency |  | — | custom |
| `gbDentalMonthlyPremiumConverted` | gbDentalMonthlyPremiumConverted | currencyConverted |  | — | read-only |
| `gbDentalMonthlyPremiumCurrency` | gbDentalMonthlyPremiumCurrency | enum |  | — | max 3 |
| `gbEligibleEmployees` | Group Benefits Eligible Employees | int |  | — | custom |
| `gbEmployerContribution` | Group Benefits Employer Contribution | text |  | — | custom |
| `gbLifeAdCarrier` | Group Benefits Life and Accidental Death and Dismemberment Carrier | text |  | — | custom |
| `gbLifeBenefitAmount` | Group Benefits Life Benefit Amount | currency |  | — | custom |
| `gbLifeBenefitAmountConverted` | gbLifeBenefitAmountConverted | currencyConverted |  | — | read-only |
| `gbLifeBenefitAmountCurrency` | gbLifeBenefitAmountCurrency | enum |  | — | max 3 |
| `gbLtdCarrier` | Group Benefits Long-Term Disability Carrier | text |  | — | custom |
| `gbMedicalCarrier` | Group Benefits Medical Carrier | text |  | — | custom |
| `gbMedicalMonthlyPremium` | Group Benefits Medical Monthly Premium | currency |  | — | custom |
| `gbMedicalMonthlyPremiumConverted` | gbMedicalMonthlyPremiumConverted | currencyConverted |  | — | read-only |
| `gbMedicalMonthlyPremiumCurrency` | gbMedicalMonthlyPremiumCurrency | enum |  | — | max 3 |
| `gbMedicalPlanType` | Group Benefits Medical Plan Type | enum |  | — | custom |
| `gbMedicalRenewalDate` | Group Benefits Medical Renewal Date | date |  | — | custom |
| `gbNotes` | Group Benefits Notes | text |  | — | custom |
| `gbParticipatingEmployees` | Group Benefits Participating Employees | int |  | — | custom |
| `gbStdCarrier` | Group Benefits Short-Term Disability Carrier | text |  | — | custom |
| `gbVisionCarrier` | Group Benefits Vision Carrier | text |  | — | custom |
| `gbVisionMonthlyPremium` | Group Benefits Vision Monthly Premium | currency |  | — | custom |
| `gbVisionMonthlyPremiumConverted` | gbVisionMonthlyPremiumConverted | currencyConverted |  | — | read-only |
| `gbVisionMonthlyPremiumCurrency` | gbVisionMonthlyPremiumCurrency | enum |  | — | max 3 |
| `gbVoluntaryBenefits` | Voluntary Benefits | text |  | — | custom |
| `gender` | gender | enum |  | — | custom |
| `general_notes` | General Notes & Requests | text |  | — | custom |
| `google_drive_folder_url` | Google Drive Folder Link | url |  | — | custom |
| `industry` | Industry | enum |  | — | — |
| `insight_objection` | Objection | text |  | — | custom |
| `insight_opener` | Opener | text |  | — | custom |
| `insight_relationship` | RSG Relationship | text |  | — | custom |
| `insight_signal` | Signal | text |  | — | custom |
| `intel_ai_summary` | Assessment Summary (AI) | text |  | — | custom |
| `intel_annual_revenue_est` | Annual Revenue Estimate (AI) | text |  | — | custom |
| `intel_bbb_accredited` | Better Business Bureau Accredited (AI) | bool |  | — | custom |
| `intel_bbb_complaints` | Open Better Business Bureau Complaints (AI) | int |  | — | custom |
| `intel_bbb_notes` | Better Business Bureau Notes (AI) | text |  | — | custom |
| `intel_bbb_rating` | Better Business Bureau Rating (AI) | varchar |  | — | max 10, custom |
| `intel_cargo_type` | Cargo Type (AI) | text |  | — | custom |
| `intel_confidence` | Confidence (AI) | enum |  | — | custom |
| `intel_cross_sell` | Cross-Sell Opportunities (AI) | text |  | — | custom |
| `intel_dba` | Doing Business As (AI) | text |  | — | custom |
| `intel_dot_incidents` | Department of Transportation Incidents (AI) | int |  | — | custom |
| `intel_employee_count` | Employee Count (AI) | int |  | — | custom |
| `intel_entity_type` | Entity Type (AI) | enum |  | — | custom |
| `intel_fleet_size` | Fleet Size (AI) | int |  | — | custom |
| `intel_growth_indicator` | Growth Indicator (AI) | text |  | — | custom |
| `intel_legal_name` | Legal Name (AI) | text |  | — | custom |
| `intel_linkedin_notes` | LinkedIn Notes (AI) | text |  | — | custom |
| `intel_linkedin_url` | LinkedIn URL (AI) | url |  | — | custom |
| `intel_naics` | North American Industry Classification System Code (AI) | text |  | — | custom |
| `intel_news_notes` | News Notes (AI) | text |  | — | custom |
| `intel_operating_radius` | Operating Radius (AI) | text |  | — | custom |
| `intel_osha_violations` | Occupational Safety and Health Administration Violations (AI) | text |  | — | custom |
| `intel_owner_operators` | Owner Operators on Payroll (AI) | bool |  | — | custom |
| `intel_pack_last_run` | Intel Pack Last Run (AI) | datetime |  | — | read-only, custom |
| `intel_pack_run` | Intel Pack Run (AI) | bool |  | false | custom |
| `intel_pain_points` | Pain Points (AI) | text |  | — | custom |
| `intel_run` | Intel Run (AI) | bool |  | false | custom |
| `intel_run_by` | Run By (AI) | text |  | — | custom |
| `intel_run_date` | Last Run (AI) | datetime |  | — | custom |
| `intel_sic` | Standard Industrial Classification Code (AI) | text |  | — | custom |
| `intel_signal_linkedin` | LinkedIn Signal (AI) | text |  | — | custom |
| `intel_signal_news` | News Signal (AI) | text |  | — | custom |
| `intel_sources` | intel_sources | text |  | — | custom |
| `intel_sources_hit` | Sources Hit (AI) | int |  | — | custom |
| `intel_underwriting_flag` | Underwriting Flag (AI) | text |  | — | custom |
| `intel_website` | Website (AI) | url |  | — | custom |
| `intel_website_notes` | Website Notes (AI) | text |  | — | custom |
| `intel_years_in_business` | Years in Business (AI) | int |  | — | custom |
| `key_findings` | Key Findings | text |  | — | custom |
| `lastContactBy` | lastContactBy | link |  | — | custom |
| `last_claim_date` | Last Claim Date | date |  | — | custom |
| `last_claim_lob` | last_claim_lob | enum |  | — | custom |
| `last_claim_status` | last_claim_status | enum |  | — | custom |
| `last_claim_type` | Last Claim Type | text |  | — | custom |
| `last_contact_date` | Last Contact Date | date |  | — | custom |
| `last_contact_outcome` | last_contact_outcome | enum |  | — | custom |
| `last_contact_type` | last_contact_type | enum |  | — | custom |
| `linkedin_url` | linkedin_url | url |  | — | custom |
| `lob` | Line of Business | multiEnum |  | — | custom |
| `mailingAddress` | mailingAddress | text |  | — | custom |
| `mailingAddressSame` | Mailing Address Same | bool |  | true | custom |
| `maritalStatus` | maritalStatus | enum |  | — | custom |
| `modifiedAt` | modifiedAt | datetime |  | — | read-only |
| `modifiedBy` | modifiedBy | link |  | — | read-only |
| `momentum_client_id` | Momentum Client ID | text |  | — | read-only, custom |
| `momentum_last_synced` | Momentum Last Synced | datetime |  | — | custom |
| `name` | Name | varchar | yes | — | required, max 249, pattern |
| `next_renewal_carrier` | next_renewal_carrier | text |  | — | custom |
| `next_renewal_date` | next_renewal_date | date |  | — | custom |
| `next_renewal_lob` | next_renewal_lob | text |  | — | custom |
| `next_x_date` | Next Renewal X-Date | date |  | — | custom |
| `next_x_date_lob` | Next Renewal X-Date Line of Business | text |  | — | custom |
| `nps_date` | Net Promoter Score Date | date |  | — | custom |
| `nps_score` | Net Promoter Score | int |  | — | custom |
| `numberOfEmployees` | [OLD] Number of Employees | int |  | — | custom |
| `originalLead` | Original Lead | linkOne |  | — | read-only |
| `outreach_attempts_current` | outreach_attempts_current | int |  | — | custom |
| `phoneNumber` | Phone | phone |  | — | — |
| `phoneNumberIsInvalid` | phoneNumberIsInvalid | bool |  | — | — |
| `phoneNumberIsOptedOut` | phoneNumberIsOptedOut | bool |  | — | — |
| `policies` | policies | linkMultiple |  | — | custom |
| `policyCountActive` | Active Policies | int |  | — | read-only, custom |
| `preferred_contact` | preferred_contact | enum |  | — | custom |
| `premiumChangeAmountConverted` | premiumChangeAmountConverted | currencyConverted |  | — | read-only |
| `premiumChangeAmountCurrency` | premiumChangeAmountCurrency | enum |  | — | max 3 |
| `premium_change_amount` | premium_change_amount | currency |  | — | custom |
| `premium_change_pct` | premium_change_pct | float |  | — | custom |
| `primaryDob` | primaryDob | date |  | — | custom |
| `primaryEmail` | primaryEmail | email |  | — | custom |
| `primaryEmailIsInvalid` | primaryEmailIsInvalid | bool |  | — | — |
| `primaryEmailIsOptedOut` | primaryEmailIsOptedOut | bool |  | — | — |
| `primaryFirstName` | primaryFirstName | varchar |  | — | max 100, custom |
| `primaryGender` | primaryGender | enum |  | — | custom |
| `primaryLastName` | primaryLastName | varchar |  | — | max 100, custom |
| `primaryOccupation` | primaryOccupation | text |  | — | custom |
| `primaryPhone` | primaryPhone | phone |  | — | custom |
| `primaryPhoneIsInvalid` | primaryPhoneIsInvalid | bool |  | — | — |
| `primaryPhoneIsOptedOut` | primaryPhoneIsOptedOut | bool |  | — | — |
| `property_address` | property_address | text |  | — | custom |
| `property_city` | property_city | varchar |  | — | max 50, custom |
| `property_state` | property_state | varchar |  | `GA` | max 5, custom |
| `property_zip` | property_zip | varchar |  | — | max 10, custom |
| `rate_increase_flag` | rate_increase_flag | bool |  | false | custom |
| `referral_name` | referral_name | text |  | — | custom |
| `referral_source` | referral_source | enum |  | — | custom |
| `referralsGiven` | referralsGiven | int |  | 0 | custom |
| `renewalQuoteAmountConverted` | renewalQuoteAmountConverted | currencyConverted |  | — | read-only |
| `renewalQuoteAmountCurrency` | renewalQuoteAmountCurrency | enum |  | — | max 3 |
| `renewal_date` | Renewal Date | date |  | — | custom |
| `renewal_decision` | renewal_decision | enum |  | — | custom |
| `renewal_decision_notes` | renewal_decision_notes | text |  | — | custom |
| `renewal_outreach_stage` | renewal_outreach_stage | enum |  | — | custom |
| `renewal_quote_amount` | renewal_quote_amount | currency |  | — | custom |
| `renewal_quote_carrier` | renewal_quote_carrier | text |  | — | custom |
| `renewal_quote_date` | renewal_quote_date | date |  | — | custom |
| `renewal_quote_received` | renewal_quote_received | bool |  | false | custom |
| `renewals` | renewals | linkMultiple |  | — | custom |
| `retention_risk` | Retention Risk | enum |  | — | custom |
| `risk_score` | Risk Score | int |  | — | custom |
| `score_alert_sent` | score_alert_sent | bool |  | false | custom |
| `score_breakdown` | score_breakdown | text |  | — | read-only, custom |
| `score_bundle_depth` | score_bundle_depth | int |  | — | min 0, max 20, custom |
| `score_change_amount` | score_change_amount | int |  | — | custom |
| `score_change_direction` | score_change_direction | enum |  | — | custom |
| `score_claims_activity` | score_claims_activity | int |  | — | min 0, max 20, custom |
| `score_last_calculated` | score_last_calculated | datetime |  | — | custom |
| `score_last_contact` | score_last_contact | int |  | — | min 0, max 20, custom |
| `score_payment_history` | score_payment_history | int |  | — | min 0, max 20, custom |
| `score_tier` | Client Tier | enum |  | — | read-only, custom |
| `score_total` | score_total | int |  | — | read-only, min 0, max 100, custom |
| `score_years_retained` | score_years_retained | int |  | — | min 0, max 20, custom |
| `shippingAddress` | Shipping Address | address |  | — | — |
| `shippingAddressCity` | shippingAddressCity | varchar |  | — | max 100, pattern |
| `shippingAddressCountry` | shippingAddressCountry | varchar |  | — | max 100, pattern |
| `shippingAddressMap` | shippingAddressMap | map |  | — | read-only |
| `shippingAddressPostalCode` | shippingAddressPostalCode | varchar |  | — | max 40, pattern |
| `shippingAddressState` | shippingAddressState | varchar |  | — | max 100, pattern |
| `shippingAddressStreet` | shippingAddressStreet | text |  | — | max 255 |
| `sicCode` | Sic Code | varchar |  | — | max 40, pattern |
| `sic_code` | Standard Industrial Classification Code | text |  | — | custom |
| `stage` | Stage | enum |  | — | custom |
| `streamUpdatedAt` | streamUpdatedAt | datetime |  | — | read-only |
| `targetList` | Target List | link |  | — | — |
| `targetListIsOptedOut` | targetListIsOptedOut | bool |  | — | read-only |
| `targetLists` | Target Lists | linkMultiple |  | — | — |
| `teams` | teams | linkMultiple |  | — | — |
| `totalActivePremiumConverted` | totalActivePremiumConverted | currencyConverted |  | — | read-only |
| `totalActivePremiumCurrency` | totalActivePremiumCurrency | enum |  | — | max 3 |
| `totalAnnualPremium` | [OLD] Total Annual Premium | currency |  | — | read-only, custom |
| `totalAnnualPremiumConverted` | totalAnnualPremiumConverted | currencyConverted |  | — | read-only |
| `totalAnnualPremiumCurrency` | totalAnnualPremiumCurrency | enum |  | — | read-only, max 3 |
| `totalCarrierPremiumConverted` | totalCarrierPremiumConverted | currencyConverted |  | — | read-only |
| `totalCarrierPremiumCurrency` | totalCarrierPremiumCurrency | enum |  | — | read-only, max 3 |
| `total_active_premium` | Total Portfolio Premium | currency |  | — | custom |
| `total_carrier_premium` | Total Carrier Premium | currency |  | — | read-only, custom |
| `type` | Type | enum |  | — | read-only |
| `website` | Website | url |  | — | — |
| `websiteUrl` | [OLD] Website URL | url |  | — | custom |
| `what_you_have_today` | What You Have Today | text |  | — | custom |
| `x_date` | Renewal X-Date | date |  | — | custom |
| `years_in_business` | Years in Business | int |  | — | custom |

## Allowed values (enum / multi-enum / array / checklist)

### `account_status` — Account Health

- Type: `enum`
- Default: `Active`
- Options:
  - `""` _(empty)_
  - `Active`
  - `Urgent`
  - `Renewing`
  - `At Risk`
  - `Inactive`

### `account_type` — Account Type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Prospect`
  - `Commercial Lines`
  - `Personal Lines`
  - `Group Benefits`
  - `Medicare`
  - `Life Insurance`
  - `Carrier`
  - `MGA`

### `businessEntity` — Business Entity

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Sole Proprietor`
  - `LLC`
  - `Corporation`
  - `S-Corp`
  - `Partnership`
  - `Non-Profit`
  - `Other`

### `cConstructionSpecialty` — Construction Specialty

- Type: `enum`
- Default: `General Contractor`
- Options:
  - `General Contractor`
  - `Site Preparation / Demolition`
  - `Excavation & Grading`
  - `Earthwork & Soil Stabilization`
  - `Concrete`
  - `Masonry`
  - `Structural Steel / Iron Work`
  - `Carpentry / Framing`
  - `Welding & Fabrication`
  - `Roofing`
  - `Waterproofing & Dampproofing`
  - `Siding & Cladding`
  - `Windows & Glazing / Curtain Wall`
  - `EIFS / Stucco`
  - `Drywall / Framing`
  - `Insulation`
  - `Flooring`
  - `Painting & Coatings`
  - `Ceilings`
  - `Millwork & Casework / Cabinetry`
  - `Doors, Frames & Hardware`
  - `Plumbing`
  - `HVAC`
  - `Electrical`
  - `Fire Protection / Sprinkler Systems`
  - `Controls & Building Automation`
  - `Landscaping / Hardscaping`
  - `Asphalt Paving`
  - `Concrete Paving`
  - `Fencing`
  - `Signage`
  - `Security Systems`
  - `Audio/Visual & Low Voltage`
  - `Elevators & Lifts`
  - `Swimming Pools & Fountains`
  - `Solar / Renewable Energy`
  - `Environmental Remediation`

### `gbMedicalPlanType` — Group Benefits Medical Plan Type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `HMO`
  - `PPO`
  - `EPO`
  - `HDHP`
  - `POS`
  - `HRA`
  - `Indemnity`

### `gender` — gender

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Male`
  - `Female`
  - `Other`
  - `Prefer not to say`

### `industry` — Industry

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Advertising`
  - `Aerospace`
  - `Agriculture`
  - `Apparel & Accessories`
  - `Architecture`
  - `Automotive`
  - `Banking`
  - `Biotechnology`
  - `Building Materials & Equipment`
  - `Chemical`
  - `Computer`
  - `Construction`
  - `Consulting`
  - `Creative`
  - `Culture`
  - `Defense`
  - `Education`
  - `Electric Power`
  - `Electronics`
  - `Energy`
  - `Entertainment & Leisure`
  - `Finance`
  - `Food & Beverage`
  - `Grocery`
  - `Healthcare`
  - `Hospitality`
  - `Insurance`
  - `Landscaping Services / Tree Care`
  - `Legal`
  - `Manufacturing`
  - `Marketing`
  - `Mass Media`
  - `Mining`
  - `Music`
  - `Petroleum`
  - `Publishing`
  - `Real Estate`
  - `Retail`
  - `Service`
  - `Shipping`
  - `Software`
  - `Sports`
  - `Support`
  - `Technology`
  - `Telecommunications`
  - `Television`
  - `Testing, Inspection & Certification`
  - `Transportation`
  - `Travel`
  - `Venture Capital`
  - `Water`
  - `Wholesale`

### `intel_confidence` — Confidence (AI)

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `High`
  - `Medium`
  - `Low`

### `intel_entity_type` — Entity Type (AI)

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `LLC`
  - `Corp`
  - `Sole Prop`
  - `Partnership`
  - `Other`

### `last_claim_lob` — last_claim_lob

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Auto`
  - `Home`
  - `Umbrella`
  - `Life`
  - `Medicare`
  - `Renters`
  - `Other`

### `last_claim_status` — last_claim_status

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Open`
  - `Closed`
  - `Subrogation`

### `last_contact_outcome` — last_contact_outcome

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Reached`
  - `Voicemail`
  - `No Answer`
  - `Email Opened`
  - `Unresponsive`

### `last_contact_type` — last_contact_type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Call`
  - `Email`
  - `Text`
  - `In Person`

### `lob` — Line of Business

- Type: `multiEnum`
- Options:
  - `Commercial Auto`
  - `GL`
  - `Workers Comp`
  - `Cargo`
  - `Home`
  - `Auto`
  - `Life`
  - `Medicare`
  - `BOP`
  - `Umbrella`
  - `Professional Liability`
  - `Builders Risk`
  - `Transportation`

### `maritalStatus` — maritalStatus

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Single`
  - `Married`
  - `Divorced`
  - `Widowed`
  - `Separated`

### `preferred_contact` — preferred_contact

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Phone`
  - `Email`
  - `Text`

### `primaryGender` — primaryGender

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Male`
  - `Female`
  - `Other`
  - `Prefer not to say`

### `referral_source` — referral_source

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Referral`
  - `Google`
  - `Social Media`
  - `Cold Outreach`
  - `Walk-in`
  - `NowCerts Import`
  - `Other`

### `renewal_decision` — renewal_decision

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Renewing`
  - `Re-marketed`
  - `Lost — Price`
  - `Lost — Service`
  - `Lost — Carrier`
  - `Non-renewed by Carrier`

### `renewal_outreach_stage` — renewal_outreach_stage

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Not Started`
  - `Day 60 Sent`
  - `Day 30 Sent`
  - `Day 14 Sent`
  - `Confirmed`
  - `Shopped`
  - `Lost`

### `retention_risk` — Retention Risk

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Low`
  - `Medium`
  - `High`

### `score_change_direction` — score_change_direction

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Up`
  - `Down`
  - `Flat`

### `score_tier` — Client Tier

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Strong`
  - `Good`
  - `At Risk`
  - `Critical`

### `stage` — Stage

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `New`
  - `Qualified`
  - `Proposal`
  - `Negotiation`
  - `Closed Won`
  - `Closed Lost`

### `type` — Type

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Commercial Lines`
  - `Personal Lines`
  - `Group Benefits`
  - `Prospect`

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `activityLogs` | activityLogs | hasMany | `ActivityLog` | `account` | custom |
| `assignedUser` | assignedUser | belongsTo | `User` | `—` | — |
| `calls` | calls | hasChildren | `Call` | `parent` | audited |
| `callsPrimary` | Calls (expanded) | hasMany | `Call` | `account` | — |
| `campaign` | Campaign | belongsTo | `Campaign` | `accounts` | — |
| `campaignLogRecords` | Campaign Log | hasChildren | `CampaignLogRecord` | `parent` | — |
| `carrierPolicies` | Carrier Policies | hasMany | `Policy` | `carrierAccount` | custom |
| `cases` | Cases | hasMany | `Case` | `account` | — |
| `clientNotes` | Client Notes | hasMany | `ClientNote` | `account` | custom |
| `commissions` | commissions | hasMany | `Commission` | `account` | custom |
| `contacts` | Contacts | hasMany | `Contact` | `accounts` | — |
| `contactsPrimary` | Contacts (primary) | hasMany | `Contact` | `account` | — |
| `createdBy` | createdBy | belongsTo | `User` | `—` | — |
| `documents` | Documents | hasMany | `Document` | `accounts` | audited |
| `emails` | emails | hasChildren | `Email` | `parent` | — |
| `emailsPrimary` | Emails (expanded) | hasMany | `Email` | `account` | — |
| `lastContactBy` | lastContactBy | belongsTo | `User` | `—` | custom |
| `meetings` | meetings | hasChildren | `Meeting` | `parent` | audited |
| `meetingsPrimary` | Meetings (expanded) | hasMany | `Meeting` | `account` | — |
| `modifiedBy` | modifiedBy | belongsTo | `User` | `—` | — |
| `opportunities` | Opportunities | hasMany | `Opportunity` | `account` | — |
| `originalLead` | Original Lead | hasOne | `Lead` | `createdAccount` | — |
| `policies` | policies | hasMany | `Policy` | `account` | custom |
| `portalUsers` | Portal Users | hasMany | `User` | `accounts` | — |
| `renewals` | renewals | hasMany | `Renewal` | `account` | custom |
| `targetLists` | Target Lists | hasMany | `TargetList` | `accounts` | — |
| `tasks` | tasks | hasChildren | `Task` | `parent` | — |
| `tasksPrimary` | Tasks (expanded) | hasMany | `Task` | `account` | — |
| `teams` | teams | hasMany | `Team` | `—` | relation `entityTeam` |

## Unique indexes

- **createdAtId**: `createdAt`, `id`

---

_Generated 2026-06-06 from a read-only live metadata pull (`metadata.php` cache, equivalent to `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`)._
