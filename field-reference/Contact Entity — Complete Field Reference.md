Here is the complete reference for all fields in the **Contact** entity:

---

## Contact Entity — Complete Field Reference

### Field Types Key
`Varchar` = Short text | `Text` = Long text | `Enum` = Dropdown | `Multi-Enum` = Multi-select | `Boolean` = Yes/No | `Currency` = Currency | `Date` = Date | `Date-Time` = DateTime | `Phone` = Phone | `Email` = Email | `URL` = URL | `Link` = Related record | `Address` = Address block | `Foreign` = Foreign key value | `Person Name` = Name composite

---

| Label | Field Name | Type | Options |
|---|---|---|---|
| Acceptance Status | acceptanceStatus | Varchar | — |
| Acceptance Status (Calls) | acceptanceStatusCalls | Enum | *(system-managed, linked to Calls status)* |
| Acceptance Status (Meetings) | acceptanceStatusMeetings | Enum | *(system-managed, linked to Meetings status)* |
| Account | account | Link | — |
| Account Inactive | accountIsInactive | Boolean | — |
| Title (Account) | accountRole | Varchar | — |
| Account Type | accountType | Foreign | — |
| Accounts | accounts | Link Multiple | — |
| Activity Logs | activityLogs | Link Multiple | — |
| Address | address | Address | — |
| City | addressCity | Varchar | — |
| Country | addressCountry | Varchar | — |
| Map | addressMap | Map | — |
| Postal Code | addressPostalCode | Varchar | — |
| State | addressState | Varchar | — |
| Street | addressStreet | Text | — |
| AEP/SEP Date | aepSepDate | Date | — |
| Assigned User | assignedUser | Link | — |
| Campaign | campaign | Link | — |
| Client Type | clientType | **Enum** | Personal, Commercial |
| Commissions | commissions | Link Multiple | — |
| Drive Folder URL | contactDriveFolderUrl | URL | — |
| Created At | createdAt | Date-Time | — |
| Created By | createdBy | Link | — |
| CSR Name | csrName | Varchar | — |
| Date of Birth | dateOfBirth | Date | — |
| Description | description | Text | — |
| Do Not Call | doNotCall | Boolean | — |
| Email | emailAddress | Email | — |
| Email Address is Invalid | emailAddressIsInvalid | Boolean | — |
| Email Address is Opted-Out | emailAddressIsOptedOut | Boolean | — |
| First Name | firstName | Varchar | — |
| Has Portal User | hasPortalUser | Boolean | — |
| Household Role | householdRole | **Enum** | Primary, Spouse, Dependent, Co-insured |
| IRMAA Applies | irmaApplies | Boolean | — |
| Last Name | lastName | Varchar | — |
| Annual Premium (Life) | lifeAnnualPremium | Currency | — |
| Beneficiary On File | lifeBeneficiaryOnFile | Boolean | — |
| Coverage In Force | lifeCoverageInForce | Boolean | — |
| Face Amount | lifeFaceAmount | Currency | — |
| Health Class | lifeHealthClass | **Enum** | Preferred Plus, Preferred, Standard Plus, Standard, Substandard |
| Policy Type (Life) | lifePolicyType | **Enum** | Term, Whole, Universal |
| Life Review Date | lifeReviewDate | Date | — |
| Medicare Carrier | medicareCarrier | Varchar | — |
| Medicare Eligible | medicareEligible | Boolean | — |
| Medicare Part B | medicarePartB | Boolean | — |
| Plan Type (Medicare) | medicarePlanType | **Enum** | Supplement, Advantage, Part D |
| Middle Name | middleName | Varchar | — |
| Modified At | modifiedAt | Date-Time | — |
| Modified By | modifiedBy | Link | — |
| Momentum Client ID | momentumClientId | Varchar | — |
| Name | name | Person Name | — |
| Opportunity Role | opportunityRole | **Enum** | Decision Maker, Evaluator, Influencer |
| Original Email | originalEmail | Link | — |
| Original Lead | originalLead | Link One | — |
| Phone | phoneNumber | Phone | — |
| Phone Number is Invalid | phoneNumberIsInvalid | Boolean | — |
| Phone Number is Opted-Out | phoneNumberIsOptedOut | Boolean | — |
| Policies | policies | Link Multiple | — |
| Portal User | portalUser | Link One | — |
| Renewals | renewals | Link Multiple | — |
| Salutation | salutationName | **Enum** | Mr., Ms., Mrs., Dr. |
| Stream Updated At | streamUpdatedAt | Date-Time | — |
| Target List | targetList | Link | — |
| Is Opted Out (Target List) | targetListIsOptedOut | Boolean | — |
| Target Lists | targetLists | Link Multiple | — |
| Teams | teams | Link Multiple | — |
| Account Title | title | Varchar | — |
| Account Any ID | accountAnyId | Varchar | — |

---

**Summary counts:**
- **Total fields:** 73
- **Enum fields:** 8 (with dropdown options)
- **Boolean fields:** 16
- **Varchar/Text fields:** 12
- **Currency fields:** 4 (plus Converted sub-fields)
- **Date/Date-Time fields:** 7
- **Link/Relation fields:** 18
- **Other (Email, Phone, URL, Address, Map):** 8