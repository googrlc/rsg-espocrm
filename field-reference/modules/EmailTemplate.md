# Email Template

**Entity name:** `EmailTemplate`  
**Plural label:** Email Templates  
**Type:** Core entity  
**Field count:** 15  
**Link count:** 6  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/EmailTemplate`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/EmailTemplate/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/EmailTemplate`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/EmailTemplate/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/EmailTemplate/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `assignedUser` | assignedUser | link |  | — | — |
| `attachments` | Attachments | attachmentMultiple |  | — | — |
| `body` | Body | wysiwyg |  | — | — |
| `category` | Category | link |  | — | — |
| `createdAt` | createdAt | datetime |  | — | read-only |
| `createdBy` | createdBy | link |  | — | read-only |
| `description` | description | text |  | — | — |
| `isHtml` | HTML | bool |  | true | — |
| `modifiedAt` | modifiedAt | datetime |  | — | read-only |
| `modifiedBy` | modifiedBy | link |  | — | read-only |
| `name` | Name | varchar | yes | — | required |
| `oneOff` | One-off | bool |  | false | — |
| `status` | Status | enum |  | `Active` | max 8 |
| `subject` | Subject | varchar |  | — | — |
| `teams` | teams | linkMultiple |  | — | — |

## Allowed values (enum / multi-enum / array / checklist)

### `status` — Status

- Type: `enum`
- Default: `Active`
- Options:
  - `Active`
  - `Inactive`

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `assignedUser` | assignedUser | belongsTo | `User` | `—` | — |
| `attachments` | attachments | hasChildren | `Attachment` | `parent` | — |
| `category` | category | belongsTo | `EmailTemplateCategory` | `emailTemplates` | — |
| `createdBy` | createdBy | belongsTo | `User` | `—` | — |
| `modifiedBy` | modifiedBy | belongsTo | `User` | `—` | — |
| `teams` | teams | hasMany | `Team` | `—` | relation `entityTeam` |

---

_Generated 2026-05-26 from `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`._
