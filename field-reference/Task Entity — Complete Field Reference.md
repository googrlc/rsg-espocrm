Here is the complete reference for all fields in the **Task** entity:

---
### Field Types Key
`Varchar` = Short text | `Text` = Long text | `Enum` = Dropdown | `Boolean` = Yes/No | `Date` = Date | `Date-Time` = DateTime | `Date/Date-Time` = Optional date or datetime | `Link` = Related record | `Link Parent` = Polymorphic related record | `Attachment Multiple` = File attachments | `Json Array` = JSON array (reminders)

---

| Label | Field Name | Type | Options |
|---|---|---|---|
| Name | name | Varchar | — |
| Status | status | **Enum** | Inbox, In Progress, Waiting on Client, Waiting on Carrier, Completed, Cancelled |
| Priority | priority | **Enum** | Low, Normal, High, Urgent |
| Urgency | urgency | **Enum** | Urgent, High, Normal, Low |
| Task Type | taskType | **Enum** | Client Service, Policy Change, Renewal, New Business, Follow Up, Onboarding, Claims, Commission, Admin, Other |
| Source | taskSource | **Enum** | Account, Contact, Policy |
| Sync Source | syncSource | **Enum** | Manual, Gmail, Slack, Momentum, n8n, Viktor |
| Parent | parent | Link Parent | *(links to: Account, Contact, Lead, Opportunity, Case, Policy, Renewal)* |
| Account | account | Link | — |
| Linked Account | linkedAccount | Link | — |
| Contact | contact | Link | — |
| Original Email | originalEmail | Link | — |
| Assigned User | assignedUser | Link | — |
| Teams | teams | Link Multiple | — |
| Date Start | dateStart | Date/Date-Time | — |
| Date Due | dateEnd | Date/Date-Time | — |
| Date Start (all day) | dateStartDate | Date | — |
| Date End (all day) | dateEndDate | Date | — |
| Date Completed | dateCompleted | Date-Time | — |
| Is Overdue | isOverdue | Boolean | — |
| Description | description | Text | — |
| Triage Reason | triageReason | Text | — |
| Triage Summary | triageSummary | Text | — |
| Attachments | attachments | Attachment Multiple | — |
| Reminders | reminders | Json Array | — |
| Created At | createdAt | Date-Time | — |
| Created By | createdBy | Link | — |
| Modified At | modifiedAt | Date-Time | — |
| Modified By | modifiedBy | Link | — |
| Stream Updated At | streamUpdatedAt | Date-Time | — |

---

**Summary counts:**
- **Total fields:** 30
- **Enum fields:** 6 with defined options
- **Boolean fields:** 1
- **Varchar/Text fields:** 4
- **Date/Date-Time fields:** 7
- **Link/Relation fields:** 9
- **Other (Attachment Multiple, Json Array, Link Parent):** 3