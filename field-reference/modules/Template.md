# PDF Template

**Entity name:** `Template`  
**Plural label:** PDF Templates  
**Type:** Core entity  
**Field count:** 29  
**Link count:** 3  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Template`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Template/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Template`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Template/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Template/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `body` | Body | wysiwyg |  | — | — |
| `bottomMargin` | Bottom Margin | float |  | 20 | — |
| `createdAt` | createdAt | datetime |  | — | read-only |
| `createdBy` | createdBy | link |  | — | read-only |
| `description` | description | text |  | — | — |
| `entityType` | Entity Type | enum | yes | — | required |
| `filename` | Filename | varchar |  | — | max 150 |
| `fontFace` | Font | enum |  | — | — |
| `footer` | Footer | wysiwyg |  | — | — |
| `footerPosition` | Footer Position | float |  | 10 | — |
| `header` | Header | wysiwyg |  | — | — |
| `headerPosition` | Header Position | float |  | 0 | — |
| `leftMargin` | Left Margin | float |  | 10 | — |
| `modifiedAt` | modifiedAt | datetime |  | — | read-only |
| `modifiedBy` | modifiedBy | link |  | — | read-only |
| `name` | Name | varchar | yes | — | required, pattern |
| `pageFormat` | Paper Format | enum |  | `A4` | — |
| `pageHeight` | Page Height (mm) | float |  | — | min 1 |
| `pageOrientation` | Page Orientation | enum |  | `Portrait` | — |
| `pageWidth` | Page Width (mm) | float |  | — | min 1 |
| `printFooter` | Print Footer | bool |  | — | — |
| `printHeader` | Print Header | bool |  | — | — |
| `rightMargin` | Right Margin | float |  | 10 | — |
| `status` | Status | enum |  | `Active` | max 8 |
| `style` | Style | text |  | — | — |
| `teams` | teams | linkMultiple |  | — | — |
| `title` | Title | varchar |  | — | — |
| `topMargin` | Top Margin | float |  | 10 | — |
| `variables` | Available Placeholders | base |  | — | — |

## Allowed values (enum / multi-enum / array / checklist)

### `pageFormat` — Paper Format

- Type: `enum`
- Default: `A4`
- Options:
  - `A3`
  - `A4`
  - `A5`
  - `A6`
  - `A7`
  - `Custom`

### `pageOrientation` — Page Orientation

- Type: `enum`
- Default: `Portrait`
- Options:
  - `Portrait`
  - `Landscape`

### `status` — Status

- Type: `enum`
- Default: `Active`
- Options:
  - `Active`
  - `Inactive`

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `createdBy` | createdBy | belongsTo | `User` | `—` | — |
| `modifiedBy` | modifiedBy | belongsTo | `User` | `—` | — |
| `teams` | teams | hasMany | `Team` | `—` | relation `entityTeam` |

---

_Generated 2026-06-06 from a read-only live metadata pull (`metadata.php` cache, equivalent to `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`)._
