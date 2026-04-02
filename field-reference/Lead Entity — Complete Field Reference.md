Here is the complete reference for all fields in the **Lead** entity:

---
### Field Types Key
`Varchar` = Short text | `Text` = Long text | `Enum` = Dropdown | `Boolean` = Yes/No | `Currency` = Currency | `Date` = Date | `Date-Time` = DateTime | `Phone` = Phone | `Email` = Email | `URL` = URL | `Link` = Related record | `Address` = Address block | `Map` = Map | `Person Name` = Name composite

---

| Label | Field Name | Type | Options |
|---|---|---|---|
| Acceptance Status | acceptanceStatus | Varchar | — |
| Acceptance Status (Calls) | acceptanceStatusCalls | Enum | *(system-managed, linked to Calls status)* |
| Acceptance Status (Meetings) | acceptanceStatusMeetings | Enum | *(system-managed, linked to Meetings status)* |
| Account Name | accountName | Varchar | — |
| Address | address | Address | — |
| City | addressCity | Varchar | — |
| Country | addressCountry | Varchar | — |
| Map | addressMap | Map | — |
| Postal Code | addressPostalCode | Varchar | — |
| State | addressState | Varchar | — |
| Street | addressStreet | Text | — |
| AI Summary | aiSummary | Text | — |
| Assigned User | assignedUser | Link | — |
| Campaign | campaign | Link | — |
| Converted At | convertedAt | Date-Time | — |
| Account (Created) | createdAccount | Link | — |
| Created At | createdAt | Date-Time | — |
| Created By | createdBy | Link | — |
| Contact (Created) | createdContact | Link | — |
| Opportunity (Created) | createdOpportunity | Link | — |
| Current Carrier | currentCarrier | Varchar | — |
| Current Medicare Plan | currentMedicarePlan | Varchar | — |
| Currently Insured? | currentlyInsured | Boolean | — |
| Date of Birth | dateOfBirth | Date | — |
| Description | description | Text | — |
| Do Not Call | doNotCall | Boolean | — |
| Email | emailAddress | Email | — |
| Email Address is Invalid | emailAddressIsInvalid | Boolean | — |
| Email Address is Opted-Out | emailAddressIsOptedOut | Boolean | — |
| Est. Premium | estimatedPremium | Currency | — |
| First Name | firstName | Varchar | — |
| Industry | industry | **Enum** | *(references Account.industry — same full list: Advertising, Aerospace, Agriculture, Apparel & Accessories, Architecture, Automotive, Banking, Biotechnology, Building Materials & Equipment, Chemical, Computer, Construction, Consulting, Creative, Culture, Defense, Education, Electric Power, Electronics, Energy, Entertainment & Leisure, Finance, Food & Beverage, Grocery, Healthcare, Hospitality, Insurance, Landscaping Services / Tree Care, Legal, Manufacturing, Marketing, Mass Media, Mining, Music, Petroleum, Publishing, Real Estate, Retail, Service, Shipping, Software, Sports, Support, Technology, Telecommunications, Television, Testing Inspection & Certification, Transportation, Travel, Venture Capital, Water, Wholesale)* |
| Insurance Interest | insuranceInterest | **Enum** | Commercial Auto, General Liability, Workers Comp, Commercial Property, BOP, Personal Auto, Homeowners, Life, Health, Medicare, Group Benefits, Inland Marine, Multiple, Other |
| Intel Pack Run | intelPackRun | Boolean | — |
| Last Name | lastName | Varchar | — |
| Medicare Eligible | medicareEligible | Boolean | — |
| Medicare Part A Date | medicarePartADate | Date | — |
| Medicare Part B Date | medicarePartBDate | Date | — |
| Middle Name | middleName | Varchar | — |
| Modified At | modifiedAt | Date-Time | — |
| Modified By | modifiedBy | Link | — |
| Name | name | Person Name | — |
| Opportunity Amount | opportunityAmount | Currency | — |
| Opportunity Amount (Converted) | opportunityAmountConverted | Currency (Converted) | — |
| Opportunity Amount Currency | opportunityAmountCurrency | Enum | *(currency selector)* |
| Original Email | originalEmail | Link | — |
| Phone | phoneNumber | Phone | — |
| Phone Number is Invalid | phoneNumberIsInvalid | Boolean | — |
| Phone Number is Opted-Out | phoneNumberIsOptedOut | Boolean | — |
| Priority | priority | **Enum** | Hot, Warm, Cold |
| Salutation | salutationName | **Enum** | Mr., Ms., Mrs., Dr. |
| Source | source | **Enum** | Call, Email, Existing Customer, Partner, Public Relations, Web Site, Campaign, Other |
| Status | status | **Enum** | New, Assigned, In Process, Converted, Recycled, Dead |
| Stream Updated At | streamUpdatedAt | Date-Time | — |
| T65 Alert Sent | t65AlertSent | Boolean | — |
| Target List | targetList | Link | — |
| Is Opted Out (Target List) | targetListIsOptedOut | Boolean | — |
| Target Lists | targetLists | Link Multiple | — |
| Teams | teams | Link Multiple | — |
| Title | title | Varchar | — |
| Website | website | URL | — |

---

**Summary counts:**
- **Total fields:** 63
- **Enum fields:** 8 with defined options (+ 3 system/currency enums)
- **Boolean fields:** 13
- **Varchar/Text fields:** 11
- **Currency fields:** 3 (+ 2 Converted sub-fields)
- **Date/Date-Time fields:** 8
- **Link/Relation fields:** 12
- **Other (Email, Phone, URL, Address, Map, Person Name):** 8