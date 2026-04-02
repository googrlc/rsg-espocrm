### Field Types Key

`int` = Integer | `enum` = Dropdown | `multiEnum` = Multi-select | `varchar` = Short text | `text` = Long text | `bool` = Boolean (Yes/No) | `currency` = Currency | `date` = Date | `datetime` = Date-Time | `phone` = Phone | `email` = Email | `url` = URL | `link` = Related record | `address` = Address block | `map` = Map

---

### All Fields (alphabetical by field name)

|Label|Field Name|Type|Options|
|---|---|---|---|
|Account Score|accountScore|Integer|—|
|Account Status|accountStatus|**Enum**|Active, Urgent, Renewing, At Risk, Inactive|
|Account type|accountType|**Enum**|Prospect, Commercial Lines, Personal Lines, Group Benefits, Medicare, Life Insurance|
|Active Policy Count|activePolicyCount|Integer|—|
|AI Assessment|aiAssessment|Text|—|
|Annual Premium|annualPremium|Currency|—|
|Annual Revenue|annualRevenue|Currency|—|
|Assessment Date|assessmentDate|Date-Time|—|
|Assigned User|assignedUser|Link|—|
|BBB Rating|bbbRating|Varchar|—|
|Best Time to Call|bestTimeToCall|Text|—|
|Billing Address|billingAddress|Address|—|
|City (Billing)|billingAddressCity|Varchar|—|
|Country (Billing)|billingAddressCountry|Varchar|—|
|Map (Billing)|billingAddressMap|Map|—|
|Postal Code (Billing)|billingAddressPostalCode|Varchar|—|
|State (Billing)|billingAddressState|Varchar|—|
|Street (Billing)|billingAddressStreet|Text|—|
|Business Entity|businessEntity|**Enum**|Sole Proprietor, LLC, Corporation, S-Corp, Partnership, Non-Profit, Other|
|Year Business Est|cYearBusinessEst|Text|—|
|Campaign|campaign|Link|—|
|Carrier|carrier|Varchar|—|
|Claims Count (3yr)|claimsCount3yr|Integer|—|
|Claims Count (Lifetime)|claimsCountLifetime|Integer|—|
|Claims Notes|claimsNotes|Text|—|
|Open Claims|claimsOpen|Integer|—|
|Client Since|clientSince|Date|—|
|Commissions|commissions|Link Multiple|—|
|Communication Notes|communicationNotes|Text|—|
|Inactive|contactIsInactive|Boolean|—|
|Title|contactRole|Varchar|—|
|Coverage Gaps|coverageGaps|Text|—|
|Created At|createdAt|Date-Time|—|
|Created By|createdBy|Link|—|
|CSR Name|csrName|Varchar|—|
|Date of Birth|dateOfBirth|Date|—|
|Days to Renewal|daysToRenewal|Integer|—|
|Dependent Ages|dependentAges|Text|—|
|Dependents Count|dependentsCount|Integer|—|
|Description|description|Text|—|
|Do Not Contact|doNotContact|Boolean|—|
|Driver 1 Accidents|driver1Accidents|Integer|—|
|Driver 1 DOB|driver1Dob|Date|—|
|Driver 1 License State|driver1LicenseState|Varchar|—|
|Driver 1 Name|driver1Name|Text|—|
|Driver 1 Violations|driver1Violations|Integer|—|
|Driver 2 Accidents|driver2Accidents|Integer|—|
|Driver 2 DOB|driver2Dob|Date|—|
|Driver 2 License State|driver2LicenseState|Varchar|—|
|Driver 2 Name|driver2Name|Text|—|
|Driver 2 Violations|driver2Violations|Integer|—|
|Driver Additional Notes|driverAdditionalNotes|Text|—|
|Driver Count|driverCount|Integer|—|
|Email|emailAddress|Email|—|
|Email Address is Invalid|emailAddressIsInvalid|Boolean|—|
|Email Address is Opted-Out|emailAddressIsOptedOut|Boolean|—|
|Employee Count|employeeCount|Integer|—|
|Est. Premium|estimatedPremium|Currency|—|
|Estimated Revenue|estimatedRevenue|Currency|—|
|FEIN|fein|Varchar|—|
|Gap: Auto UM|gapAutoUm|Boolean|—|
|Gap Count|gapCount|Integer|—|
|Gap: Final Expense|gapFinalExpense|Boolean|—|
|Gap: Landlord|gapLandlord|Boolean|—|
|Gap: Life|gapLife|Boolean|—|
|Gap Life Need Est.|gapLifeNeedEst|Currency|—|
|Gap Life Reason|gapLifeReason|Text|—|
|Gap: Medicare|gapMedicare|Boolean|—|
|Gap Medicare Eligible|gapMedicareEligible|Date|—|
|Gap: Renters|gapRenters|Boolean|—|
|Gap: Rideshare|gapRideshare|Boolean|—|
|Gap: Umbrella|gapUmbrella|Boolean|—|
|Gap Umbrella Reason|gapUmbrellaReason|Text|—|
|Gender|gender|**Enum**|Male, Female, Other, Prefer not to say|
|Google Drive Folder URL|googleDriveFolderUrl|URL|—|
|Home Alarm Monitored|homeAlarmMonitored|Boolean|—|
|Home Construction|homeConstruction|**Enum**|Frame, Masonry, Mixed, Log, Other|
|Home: Dog|homeDog|Boolean|—|
|Home Dog Breed|homeDogBreed|Text|—|
|Home Dwelling Value|homeDwellingValue|Currency|—|
|Home: Pool|homePool|Boolean|—|
|Home Purchase Year|homePurchaseYear|Integer|—|
|Home Roof Material|homeRoofMaterial|**Enum**|Asphalt Shingle, Metal, Tile, Wood Shake, Other|
|Home Roof Year|homeRoofYear|Integer|—|
|Home Security System|homeSecuritySystem|Boolean|—|
|Home Square Footage|homeSquareFootage|Integer|—|
|Home: Trampoline|homeTrampoline|Boolean|—|
|Home Year Built|homeYearBuilt|Integer|—|
|Industry|industry|**Enum**|Advertising, Aerospace, Agriculture, Apparel & Accessories, Architecture, Automotive, Banking, Biotechnology, Building Materials & Equipment, Chemical, Computer, Construction, Consulting, Creative, Culture, Defense, Education, Electric Power, Electronics, Energy, Entertainment & Leisure, Finance, Food & Beverage, Grocery, Healthcare, Hospitality, Insurance, Landscaping Services / Tree Care, Legal, Manufacturing, Marketing, Mass Media, Mining, Music, Petroleum, Publishing, Real Estate, Retail, Service, Shipping, Software, Sports, Support, Technology, Telecommunications, Television, Testing, Inspection & Certification, Transportation, Travel, Venture Capital, Water, Wholesale|
|Intel Confidence|intelConfidence|**Enum**|High, Medium, Low|
|Intel Entity Type|intelEntityType|**Enum**|LLC, Corp, Sole Prop, Partnership, Other|
|Objection|insightObjection|Text|—|
|Opener|insightOpener|Text|—|
|RSG Relationship|insightRelationship|Text|—|
|Signal|insightSignal|Text|—|
|AI Summary|intelAiSummary|Text|—|
|Est. Revenue|intelAnnualRevenueEst|Text|—|
|BBB Accredited|intelBbbAccredited|Boolean|—|
|Open Complaints|intelBbbComplaints|Integer|—|
|BBB Notes|intelBbbNotes|Text|—|
|BBB Rating (Intel)|intelBbbRating|Varchar|—|
|Cargo Type|intelCargoType|Text|—|
|Cross-Sell Opportunities|intelCrossSell|Text|—|
|DBA|intelDba|Text|—|
|DOT Incidents|intelDotIncidents|Integer|—|
|Employees (Intel)|intelEmployeeCount|Integer|—|
|Fleet Size|intelFleetSize|Integer|—|
|Growth Indicator|intelGrowthIndicator|Text|—|
|Legal Name|intelLegalName|Text|—|
|LinkedIn Notes|intelLinkedinNotes|Text|—|
|LinkedIn URL|intelLinkedinUrl|URL|—|
|NAICS|intelNaics|Text|—|
|News Notes|intelNewsNotes|Text|—|
|Operating Radius|intelOperatingRadius|Text|—|
|OSHA Violations|intelOshaViolations|Text|—|
|Owner-Operators on Payroll|intelOwnerOperators|Boolean|—|
|Intel Pack Last Run|intelPackLastRun|Date-Time|—|
|Intel Pack Run|intelPackRun|Boolean|—|
|Pain Points|intelPainPoints|Text|—|
|Intel Run|intelRun|Boolean|—|
|Run By|intelRunBy|Varchar|—|
|Last Run|intelRunDate|Date-Time|—|
|SIC|intelSic|Varchar|—|
|LinkedIn Signal|intelSignalLinkedin|Text|—|
|News Signal|intelSignalNews|Text|—|
|Intel Sources|intelSources|Text|—|
|Sources Hit|intelSourcesHit|Integer|—|
|Underwriting Flag|intelUnderwritingFlag|Text|—|
|Website (Intel)|intelWebsite|URL|—|
|Website Notes|intelWebsiteNotes|Text|—|
|Years in Business (Intel)|intelYearsInBusiness|Integer|—|
|Key Findings|keyFindings|Text|—|
|Last Claim Date|lastClaimDate|Date|—|
|Last Claim LOB|lastClaimLob|**Enum**|Auto, Home, Umbrella, Life, Medicare, Renters, Other|
|Last Claim Status|lastClaimStatus|**Enum**|Open, Closed, Subrogation|
|Last Claim Type|lastClaimType|Text|—|
|Last Contact By|lastContactBy|Link|—|
|Last Contact Date|lastContactDate|Date|—|
|Last Contact Outcome|lastContactOutcome|**Enum**|Reached, Voicemail, No Answer, Email Opened, Unresponsive|
|Last Contact Type|lastContactType|**Enum**|Call, Email, Text, In Person|
|Linked Tasks|linkedTasks|Link Multiple|—|
|LinkedIn URL|linkedinUrl|URL|—|
|LOB|lob|**Multi-Enum**|Commercial Auto, GL, Workers Comp, Cargo, Home, Auto, Life, Medicare, BOP, Umbrella, Professional Liability, Builders Risk, Transportation|
|Mailing Address|mailingAddress|Text|—|
|Mailing Address Same|mailingAddressSame|Boolean|—|
|Marital Status|maritalStatus|**Enum**|Single, Married, Divorced, Widowed, Separated|
|Modified At|modifiedAt|Date-Time|—|
|Modified By|modifiedBy|Link|—|
|Momentum Client ID|momentumClientId|Varchar|—|
|Momentum Last Synced|momentumLastSynced|Date-Time|—|
|MVR Flag|mvrFlag|Boolean|—|
|Name|name|Varchar|—|
|Next Renewal Carrier|nextRenewalCarrier|Text|—|
|Next Renewal Date|nextRenewalDate|Date|—|
|Next Renewal LOB|nextRenewalLob|Text|—|
|Next X-Date|nextXDate|Date|—|
|Next X-Date LOB|nextXDateLob|Varchar|—|
|NPS Date|npsDate|Date|—|
|NPS Score|npsScore|Integer|—|
|Number of Employees|numberOfEmployees|Integer|—|
|Original Lead|originalLead|Link One|—|
|Outreach Attempts (Current)|outreachAttemptsCurrent|Integer|—|
|Phone|phoneNumber|Phone|—|
|Phone Number is Invalid|phoneNumberIsInvalid|Boolean|—|
|Phone Number is Opted-Out|phoneNumberIsOptedOut|Boolean|—|
|Policies|policies|Link Multiple|—|
|Policy Auto Active|policyAutoActive|Boolean|—|
|Policy Auto BI Limits|policyAutoBiLimits|Text|—|
|Policy Auto Carrier|policyAutoCarrier|Text|—|
|Policy Auto Comp/Collision|policyAutoCompCollision|Boolean|—|
|Policy Auto Deductible|policyAutoDeductible|Text|—|
|Policy Auto Effective|policyAutoEffective|Date|—|
|Policy Auto Expiration|policyAutoExpiration|Date|—|
|Policy Auto Number|policyAutoNumber|Text|—|
|Policy Auto Premium|policyAutoPremium|Currency|—|
|Policy Auto UM/UIM|policyAutoUmUim|Boolean|—|
|Policy Count Active|policyCountActive|Integer|—|
|Policy Home Active|policyHomeActive|Boolean|—|
|Policy Home Carrier|policyHomeCarrier|Text|—|
|Policy Home Deductible|policyHomeDeductible|Text|—|
|Policy Home Dwelling Limit|policyHomeDwellingLimit|Currency|—|
|Policy Home Effective|policyHomeEffective|Date|—|
|Policy Home Expiration|policyHomeExpiration|Date|—|
|Policy Home Number|policyHomeNumber|Text|—|
|Policy Home Premium|policyHomePremium|Currency|—|
|Policy Home Wind/Hail Ded.|policyHomeWindHailDed|Text|—|
|Policy Life Active|policyLifeActive|Boolean|—|
|Policy Life Carrier|policyLifeCarrier|Text|—|
|Policy Life Expiration|policyLifeExpiration|Date|—|
|Policy Life Face Value|policyLifeFaceValue|Currency|—|
|Policy Life Premium|policyLifePremium|Currency|—|
|Policy Life Type|policyLifeType|**Enum**|Term, Whole, Universal, Final Expense|
|Policy Medicare Active|policyMedicareActive|Boolean|—|
|Policy Medicare AEP Flag|policyMedicareAepFlag|Boolean|—|
|Policy Medicare Carrier|policyMedicareCarrier|Text|—|
|Policy Medicare Effective|policyMedicareEffective|Date|—|
|Policy Medicare Plan Name|policyMedicarePlanName|Text|—|
|Policy Medicare Plan Type|policyMedicarePlanType|**Enum**|Medicare Advantage, Medicare Supplement, PDP|
|Policy Medicare Premium|policyMedicarePremium|Currency|—|
|Policy Renter Active|policyRenterActive|Boolean|—|
|Policy Renter Carrier|policyRenterCarrier|Text|—|
|Policy Renter Expiration|policyRenterExpiration|Date|—|
|Policy Renter Premium|policyRenterPremium|Currency|—|
|Policy Umbrella Active|policyUmbrellaActive|Boolean|—|
|Policy Umbrella Carrier|policyUmbrellaCarrier|Text|—|
|Policy Umbrella Expiration|policyUmbrellaExpiration|Date|—|
|Policy Umbrella Limit|policyUmbrellaLimit|Text|—|
|Policy Umbrella Premium|policyUmbrellaPremium|Currency|—|
|Preferred Contact|preferredContact|**Enum**|Phone, Email, Text|
|Premium Change Amount|premiumChangeAmount|Currency|—|
|Premium Change %|premiumChangePct|Float|—|
|Primary DOB|primaryDob|Date|—|
|Primary Email|primaryEmail|Email|—|
|Primary Email is Invalid|primaryEmailIsInvalid|Boolean|—|
|Primary Email is Opted-Out|primaryEmailIsOptedOut|Boolean|—|
|Primary First Name|primaryFirstName|Varchar|—|
|Primary Gender|primaryGender|**Enum**|Male, Female, Other, Prefer not to say|
|Primary Last Name|primaryLastName|Varchar|—|
|Primary Occupation|primaryOccupation|Text|—|
|Primary Phone|primaryPhone|Phone|—|
|Primary Phone is Invalid|primaryPhoneIsInvalid|Boolean|—|
|Primary Phone is Opted-Out|primaryPhoneIsOptedOut|Boolean|—|
|Property Address|propertyAddress|Text|—|
|Property City|propertyCity|Varchar|—|
|Property Count|propertyCount|Integer|—|
|Property State|propertyState|Varchar|—|
|Property Zip|propertyZip|Varchar|—|
|Rate Increase Flag|rateIncreaseFlag|Boolean|—|
|Referral Name|referralName|Text|—|
|Referral Source|referralSource|**Enum**|Referral, Google, Social Media, Cold Outreach, Walk-in, NowCerts Import, Other|
|Referrals Given|referralsGiven|Integer|—|
|Renewal Date|renewalDate|Date|—|
|Renewal Decision|renewalDecision|**Enum**|Renewing, Re-marketed, Lost — Price, Lost — Service, Lost — Carrier, Non-renewed by Carrier|
|Renewal Decision Notes|renewalDecisionNotes|Text|—|
|Renewal Outreach Stage|renewalOutreachStage|**Enum**|Not Started, Day 60 Sent, Day 30 Sent, Day 14 Sent, Confirmed, Shopped, Lost|
|Renewal Quote Amount|renewalQuoteAmount|Currency|—|
|Renewal Quote Carrier|renewalQuoteCarrier|Text|—|
|Renewal Quote Date|renewalQuoteDate|Date|—|
|Renewal Quote Received|renewalQuoteReceived|Boolean|—|
|Renewals|renewals|Link Multiple|—|
|Rental Property Count|rentalPropertyCount|Integer|—|
|Rental Property Flag|rentalPropertyFlag|Boolean|—|
|Rental Property Notes|rentalPropertyNotes|Text|—|
|Residence Type|residenceType|**Enum**|Owned, Rented, Condo, Mobile Home, Other|
|Retention Risk|retentionRisk|**Enum**|Low, Medium, High|
|Rideshare Driver Flag|rideshareDriverFlag|Boolean|—|
|Risk Score|riskScore|Integer|—|
|Score Alert Sent|scoreAlertSent|Boolean|—|
|Score Breakdown|scoreBreakdown|Text|—|
|Score Bundle Depth|scoreBundleDepth|Integer|—|
|Score Change Amount|scoreChangeAmount|Integer|—|
|Score Change Direction|scoreChangeDirection|**Enum**|Up, Down, Flat|
|Score Claims Activity|scoreClaimsActivity|Integer|—|
|Score Last Calculated|scoreLastCalculated|Date-Time|—|
|Score Last Contact|scoreLastContact|Integer|—|
|Score Payment History|scorePaymentHistory|Integer|—|
|Score Tier|scoreTier|**Enum**|Strong, Good, At Risk, Critical|
|Score Total|scoreTotal|Integer|—|
|Score Years Retained|scoreYearsRetained|Integer|—|
|Shipping Address|shippingAddress|Address|—|
|City (Shipping)|shippingAddressCity|Varchar|—|
|Country (Shipping)|shippingAddressCountry|Varchar|—|
|Map (Shipping)|shippingAddressMap|Map|—|
|Postal Code (Shipping)|shippingAddressPostalCode|Varchar|—|
|State (Shipping)|shippingAddressState|Varchar|—|
|Street (Shipping)|shippingAddressStreet|Text|—|
|SIC Code|sicCode|Varchar|—|
|Spouse DOB|spouseDob|Date|—|
|Spouse First Name|spouseFirstName|Varchar|—|
|Spouse Last Name|spouseLastName|Varchar|—|
|Spouse Occupation|spouseOccupation|Text|—|
|Stage|stage|**Enum**|New, Qualified, Proposal, Negotiation, Closed Won, Closed Lost|
|Stream Updated At|streamUpdatedAt|Date-Time|—|
|Is Opted Out (Target List)|targetListIsOptedOut|Boolean|—|
|Target List|targetList|Link|—|
|Target Lists|targetLists|Link Multiple|—|
|Teams|teams|Link Multiple|—|
|Total Active Premium|totalActivePremium|Currency|—|
|Total Annual Premium|totalAnnualPremium|Currency|—|
|Type|type|**Enum**|Commercial Lines, Personal Lines, Group Benefits, Prospect|
|Vehicle 1 Annual Miles|vehicle1AnnualMiles|Integer|—|
|Vehicle 1 Make|vehicle1Make|Varchar|—|
|Vehicle 1 Model|vehicle1Model|Varchar|—|
|Vehicle 1 Ownership|vehicle1Ownership|**Enum**|Owned, Financed, Leased|
|Vehicle 1 Use|vehicle1Use|**Enum**|Commute, Pleasure, Business, Rideshare|
|Vehicle 1 VIN|vehicle1Vin|Text|—|
|Vehicle 1 Year|vehicle1Year|Integer|—|
|Vehicle 2 Annual Miles|vehicle2AnnualMiles|Integer|—|
|Vehicle 2 Make|vehicle2Make|Varchar|—|
|Vehicle 2 Model|vehicle2Model|Varchar|—|
|Vehicle 2 Ownership|vehicle2Ownership|**Enum**|Owned, Financed, Leased|
|Vehicle 2 Use|vehicle2Use|**Enum**|Commute, Pleasure, Business, Rideshare|
|Vehicle 2 VIN|vehicle2Vin|Text|—|
|Vehicle 2 Year|vehicle2Year|Integer|—|
|Vehicle Additional Notes|vehicleAdditionalNotes|Text|—|
|Vehicle Count|vehicleCount|Integer|—|
|Website|website|URL|—|
|Website URL|websiteUrl|URL|—|
|X-Date|xDate|Date|—|
|Years at Address|yearsAtAddress|Integer|—|
|Years In Business|yearsInBusiness|Integer|—|
|Youthful Driver Flag|youthfulDriverFlag|Boolean|—|

---

**Summary counts:**

- **Total fields:** ~245 (excluding currency sub-fields like Converted/Currency selector variants)
- **Enum fields:** 35 with dropdown options
- **Multi-Enum fields:** 1 (LOB)
- **Boolean fields:** ~50
- **Integer fields:** ~40
- **Text/Varchar fields:** ~60
- **Currency fields:** ~25
- **Date/Date-Time fields:** ~30
- **Link fields:** ~20