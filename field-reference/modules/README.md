# EspoCRM Field Guide

Per-module field reference for the RSG EspoCRM instance, generated from live `/Metadata`.

## Instance

- API base: `https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1`
- Authentication:
  - GET requests: header `X-Api-Key: {API_KEY}` (do NOT include `Content-Type`)
  - POST/PATCH/DELETE: header `Authorization: Basic <base64(API_KEY:)>` with `Content-Type: application/json`

## Modules

| Module | Label | Fields | Links | Type |
|---|---|---|---|---|
| [Account](Account.md) | Account | 279 | 29 | Core |
| [ActivityLog](ActivityLog.md) | ActivityLog | 33 | 7 | Custom |
| [Call](Call.md) | Call | 22 | 9 | Core |
| [Campaign](Campaign.md) | Campaign | 38 | 17 | Core |
| [Case](Case.md) | Case | 21 | 14 | Core |
| [ClientNote](ClientNote.md) | ClientNote | 8 | 3 | Custom (no nav tab) |
| [Commission](Commission.md) | Commission | 45 | 9 | Custom |
| [Contact](Contact.md) | Contact | 54 | 22 | Core |
| [CurrencyRecord](CurrencyRecord.md) | Currency Record | 8 | 1 | Core |
| [Document](Document.md) | Document | 15 | 9 | Core |
| [Email](Email.md) | Email | 77 | 22 | Core |
| [EmailTemplate](EmailTemplate.md) | Email Template | 15 | 6 | Core |
| [Import](Import.md) | Import | 11 | 2 | Core |
| [KnowledgeBaseArticle](KnowledgeBaseArticle.md) | Knowledge Base Article | 19 | 7 | Core |
| [Lead](Lead.md) | Lead | 67 | 17 | Core |
| [Meeting](Meeting.md) | Meeting | 26 | 9 | Core |
| [Opportunity](Opportunity.md) | Opportunity | 214 | 20 | Core |
| [Policy](Policy.md) | Policy | 70 | 15 | Custom |
| [Quote](Quote.md) | Quote | 16 | 3 | Custom |
| [Renewal](Renewal.md) | Renewal | 39 | 10 | Custom |
| [TargetList](TargetList.md) | Target List | 16 | 13 | Core |
| [Task](Task.md) | Task | 61 | 10 | Core |
| [Team](Team.md) | Team | 9 | 7 | Core |
| [Template](Template.md) | PDF Template | 29 | 3 | Core |
| [User](User.md) | User | 57 | 19 | Core |
| [WorkingTimeCalendar](WorkingTimeCalendar.md) | Working Time Calendar | 23 | 4 | Core |

## How this was generated

Source: a read-only live metadata pull (`metadata.php` cache, equivalent to `GET /api/v1/Metadata` keys `entityDefs`/`scopes`) plus the merged `en_US` i18n for labels. Regenerate with `tools/build-module-docs.py`.

_Last generated: 2026-06-06_
