# Knowledge Base Article

**Entity name:** `KnowledgeBaseArticle`  
**Plural label:** Knowledge Base  
**Type:** Core entity (module: `Crm`)  
**Field count:** 19  
**Link count:** 7  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/KnowledgeBaseArticle`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/KnowledgeBaseArticle/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/KnowledgeBaseArticle`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/KnowledgeBaseArticle/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/KnowledgeBaseArticle/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `assignedUser` | assignedUser | link |  | — | — |
| `attachments` | Attachments | attachmentMultiple |  | — | — |
| `body` | Body | wysiwyg |  | — | — |
| `bodyPlain` | Body Plain | text |  | — | read-only |
| `categories` | Categories | linkMultiple |  | — | — |
| `createdAt` | createdAt | datetime |  | — | read-only |
| `createdBy` | createdBy | link |  | — | read-only |
| `description` | Description | text |  | — | — |
| `expirationDate` | Expiration Date | date |  | — | — |
| `language` | Language | enum |  | — | — |
| `modifiedAt` | modifiedAt | datetime |  | — | read-only |
| `modifiedBy` | modifiedBy | link |  | — | read-only |
| `name` | Name | varchar | yes | — | required, pattern |
| `order` | order | int |  | — | — |
| `portals` | Portals | linkMultiple |  | — | — |
| `publishDate` | Publish Date | date |  | — | — |
| `status` | Status | enum |  | `Draft` | — |
| `teams` | teams | linkMultiple |  | — | — |
| `type` | Type | enum |  | `Article` | — |

## Allowed values (enum / multi-enum / array / checklist)

### `status` — Status

- Type: `enum`
- Default: `Draft`
- Options:
  - `Draft`
  - `In Review`
  - `Published`
  - `Archived`

### `type` — Type

- Type: `enum`
- Default: `Article`
- Options:
  - `Article`

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `assignedUser` | assignedUser | belongsTo | `User` | `—` | — |
| `cases` | Cases | hasMany | `Case` | `articles` | — |
| `categories` | Categories | hasMany | `KnowledgeBaseCategory` | `articles` | — |
| `createdBy` | createdBy | belongsTo | `User` | `—` | — |
| `modifiedBy` | modifiedBy | belongsTo | `User` | `—` | — |
| `portals` | Portals | hasMany | `Portal` | `articles` | — |
| `teams` | teams | hasMany | `Team` | `—` | relation `entityTeam` |

---

_Generated 2026-05-26 from `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`._
