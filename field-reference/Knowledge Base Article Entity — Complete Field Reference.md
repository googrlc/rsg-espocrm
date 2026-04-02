Here is the complete reference for all fields in the **Knowledge Base Article** entity:

---

### Field Types Key
`Varchar` = Short text | `Text` = Long text | `Wysiwyg` = Rich text editor | `Enum` = Dropdown | `Integer` = Whole number | `Date` = Date | `Date-Time` = DateTime | `Link` = Related record | `Attachment Multiple` = File attachments

---

| Label | Field Name | Type | Options |
|---|---|---|---|
| Name | name | Varchar | — |
| Status | status | **Enum** | Draft, In Review, Published, Archived |
| Type | type | **Enum** | Article |
| Language | language | **Enum** | *(system-managed language list)* |
| Body | body | Wysiwyg | — |
| Body Plain | bodyPlain | Text | — |
| Description | description | Text | — |
| Order | order | Integer | — |
| Publish Date | publishDate | Date | — |
| Expiration Date | expirationDate | Date | — |
| Categories | categories | Link Multiple | — |
| Portals | portals | Link Multiple | — |
| Attachments | attachments | Attachment Multiple | — |
| Assigned User | assignedUser | Link | — |
| Teams | teams | Link Multiple | — |
| Created At | createdAt | Date-Time | — |
| Created By | createdBy | Link | — |
| Modified At | modifiedAt | Date-Time | — |
| Modified By | modifiedBy | Link | — |

---

**Summary counts:**
- **Total fields:** 19
- **Enum fields:** 3 (Status, Type, Language)
- **Text/Wysiwyg fields:** 3 (Body, Body Plain, Description)
- **Varchar fields:** 1
- **Integer fields:** 1
- **Date/Date-Time fields:** 4
- **Link/Relation fields:** 6
- **Other (Attachment Multiple):** 1