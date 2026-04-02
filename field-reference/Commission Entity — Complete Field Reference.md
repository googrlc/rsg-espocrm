Here is the complete reference for all fields in the **Commission** entity:

---

### Field Types Key
`Varchar` = Short text | `Text` = Long text | `Enum` = Dropdown | `Boolean` = Yes/No | `Currency` = Currency | `Float` = Decimal number | `Date` = Date | `Date-Time` = DateTime | `Link` = Related record

---

| Label | Field Name | Type | Options |
|---|---|---|---|
| Account | account | Link | — |
| Assigned User | assignedUser | Link | — |
| Carrier | carrier | Varchar | — |
| Commission Notes | commissionNotes | Text | — |
| Commission Rate | commissionRate | Float | — |
| Commission Type | commissionType | **Enum** | New Business, Renewal |
| Contact | contact | Link | — |
| Created At | createdAt | Date-Time | — |
| Created By | createdBy | Link | — |
| Effective Date | effectiveDate | Date | — |
| Estimated Commission | estimatedCommission | Currency | — |
| Expected Payment Date | expectedPaymentDate | Date | — |
| Line of Business | lineOfBusiness | **Enum** | Commercial Auto, General Liability, Workers Comp, Commercial Property, BOP, Professional Liability, Umbrella, Personal Auto, Homeowners, Renters, Condo, Life, Health, Medicare, Group Benefits, Other |
| Modified At | modifiedAt | Date-Time | — |
| Modified By | modifiedBy | Link | — |
| Name | name | Varchar | — |
| Opportunity | opportunity | Link | — |
| Overdue Flag | overdueFlag | Boolean | — |
| Payment Received Date | paymentReceivedDate | Date | — |
| Policy | policy | Link | — |
| Posted Amount | postedAmount | Currency | — |
| Producer | producer | Varchar | — |
| Renewal | renewal | Link | — |
| Status | status | **Enum** | Estimated, Posted, Overdue |
| Stream Updated At | streamUpdatedAt | Date-Time | — |
| Teams | teams | Link Multiple | — |
| Variance Amount | varianceAmount | Currency | — |
| Variance Percent | variancePercent | Float | — |
| Written Premium | writtenPremium | Currency | — |

---

**Summary counts:**
- **Total fields:** 37 (excluding currency Converted/Currency sub-fields)
- **Enum fields:** 3 with defined options (+ 4 currency selector enums)
- **Currency fields:** 4 (+ Converted sub-fields)
- **Float fields:** 2
- **Boolean fields:** 1
- **Varchar/Text fields:** 4
- **Date/Date-Time fields:** 7
- **Link/Relation fields:** 8