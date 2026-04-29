Here is the complete reference for all fields in the **Policy** entity:

---

### Field Types Key
`Varchar` = Short text | `Text` = Long text | `Enum` = Dropdown | `Boolean` = Yes/No | `Currency` = Currency | `Float` = Decimal number | `Date` = Date | `Date-Time` = DateTime | `Link` = Related record

---

| Label | Field Name | Type | Options |
|---|---|---|---|
| Account | account | Link | — |
| Activity Logs | activityLogs | Link Multiple | — |
| Agency Fee | agencyFee | Currency | — |
| AMS Accepted At | acceptedByAmsAt | Date-Time | *(read-only in CRM; AMS acceptance)* |
| AMS Accepted By | acceptedByAmsBy | Varchar | *(read-only in CRM)* |
| AMS Lock Reason | amsLockReason | Text | — |
| AMS Lock State | amsLockState | **Enum** | Unlocked, Pending AMS, Locked by AMS, Rejected by AMS |
| Assigned User | assignedUser | Link | — |
| Business Type | businessType | **Enum** | New Business, Renewal, Rewrite |
| Carrier | carrier | Varchar | — |
| Commission Amount | commissionAmount | Currency | — |
| Commission Rate | commissionRate | Float | — |
| Commissions | commissions | Link Multiple | — |
| Contact | contact | Link | — |
| Coverage Amount | coverageAmount | Currency | — |
| Created At | createdAt | Date-Time | — |
| Created By | createdBy | Link | — |
| Deductible | deductible | Currency | — |
| Effective Date | effectiveDate | Date | — |
| Email Sequence Started | emailSequenceStarted | Boolean | — |
| Expiration Date | expirationDate | Date | — |
| Insured Momentum ID | insuredMomentumId | Varchar | — |
| Last Contact Date | lastContactDate | Date | — |
| Last Contact Method | lastContactMethod | **Enum** | Phone, Email, Text, No Response |
| Line of Business | lineOfBusiness | Varchar | — |
| Modified At | modifiedAt | Date-Time | — |
| Modified By | modifiedBy | Link | — |
| Momentum Last Synced | momentumLastSynced | Date-Time | — |
| Momentum Policy ID | momentumPolicyId | Varchar | — |
| Name | name | Varchar | — |
| Opportunities | opportunities | Link Multiple | — |
| Policy Notes | policyNotes | Text | — |
| Premium Amount | premiumAmount | Currency | — |
| Premium At Risk | premiumAtRisk | Currency | — |
| Renewals | renewals | Link Multiple | — |
| Renewed From | renewedFrom | Link Multiple | — |
| Status | status | **Enum** | Active, Up for Renewal, Renewing, Renewed, Expired, Cancelled, Flat Cancel, Pending Cancel, Non-Renewed, Lapsed |
| Stream Updated At | streamUpdatedAt | Date-Time | — |
| Sync Status | syncStatus | **Enum** | Synced, Pending, Error, Skipped |
| Teams | teams | Link Multiple | — |
| Urgency | urgency | **Enum** | Low, Medium, High, Critical |

---

**Summary counts:**
- **Total fields:** 53 (excluding currency Converted/Currency sub-fields; includes AMS governance fields above)
- **Enum fields:** 5 with defined options (+ 5 currency selector enums)
- **Currency fields:** 5 (+ Converted sub-fields)
- **Boolean fields:** 1
- **Varchar/Text fields:** 7
- **Date/Date-Time fields:** 8
- **Float fields:** 1
- **Link/Relation fields:** 10