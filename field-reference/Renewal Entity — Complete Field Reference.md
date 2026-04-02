Here is the complete reference for all fields in the **Renewal** entity:

---

### Field Types Key
`Varchar` = Short text | `Text` = Long text | `Enum` = Dropdown | `Currency` = Currency | `Float` = Decimal number | `Date` = Date | `Date-Time` = DateTime | `Link` = Related record

---

| Label | Field Name | Type | Options |
|---|---|---|---|
| Account | account | Link | — |
| Assigned User | assignedUser | Link | — |
| Current Carrier | carrier | Varchar | — |
| Renewal Commission Rate | commissionRate | Float | — |
| Commissions | commissions | Link Multiple | — |
| Contact | contact | Link | — |
| Created At | createdAt | Date-Time | — |
| Created By | createdBy | Link | — |
| Current Premium | currentPremium | Currency | — |
| Expected Commission | expectedCommission | Currency | — |
| Expiration Date | expirationDate | Date | — |
| Last Contact Date | lastContactDate | Date | — |
| Last Contact Method | lastContactMethod | **Enum** | Email, Call, Text, In Person |
| Line of Business | lineOfBusiness | **Enum** | Commercial Auto, General Liability, Workers Comp, Commercial Property, BOP, Professional Liability, Umbrella, Builders Risk, Inland Marine, Personal Auto, Homeowners, Renters, Condo, Dwelling Fire, Motorcycle, Boat, RV, Life, Health, Medicare, Group Benefits, Garagekeepers, Commercial Package, Other |
| Lost Reason | lostReason | **Enum** | Price, Coverage, Unresponsive, Moved Carrier, Other |
| Modified At | modifiedAt | Date-Time | — |
| Modified By | modifiedBy | Link | — |
| Renewal Name | name | Varchar | — |
| Renewed Policy | newPolicy | Link | — |
| Expiring Policy | policy | Link | — |
| Premium Change % | premiumChange | Float | — |
| Renewal Effective Date | renewalEffectiveDate | Date | — |
| Renewal Notes | renewalNotes | Text | — |
| Renewal Premium | renewalPremium | Currency | — |
| Stage | stage | **Enum** | Identified, Outreach Sent, Quote Requested, Proposal Sent, Negotiating, Renewed - Won, Lost |
| Stream Updated At | streamUpdatedAt | Date-Time | — |
| Tasks | tasks | Link Multiple | — |
| Teams | teams | Link Multiple | — |
| Urgency | urgency | **Enum** | Critical, High, Medium, Low |

---

**Summary counts:**
- **Total fields:** 35 (excluding currency Converted/Currency sub-fields)
- **Enum fields:** 5 with defined options (+ 3 currency selector enums)
- **Currency fields:** 3 (+ Converted sub-fields)
- **Float fields:** 2
- **Varchar/Text fields:** 3
- **Date/Date-Time fields:** 7
- **Link/Relation fields:** 9