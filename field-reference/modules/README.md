# EspoCRM Field Guide

Per-module field reference for the RSG EspoCRM instance, generated from the live `/Metadata` API.

## Instance

- API base: `https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1`
- Authentication:
  - GET requests: header `X-Api-Key: {API_KEY}` (do NOT include `Content-Type`)
  - POST/PATCH/DELETE: header `Authorization: Basic <base64(API_KEY:)>` with `Content-Type: application/json`

## Modules

| Module | Label | Fields | Links | Type |
|---|---|---|---|---|
| [Account](Account.md) | Account | 262 | 30 | Core |
| [ActivityLog](ActivityLog.md) | ActivityLog | 33 | 7 | Custom |
| [Call](Call.md) | Call | 22 | 9 | Core |
| [Campaign](Campaign.md) | Campaign | 38 | 17 | Core |
| [Case](Case.md) | Case | 21 | 14 | Core |
| [ClientNote](ClientNote.md) | Client Note | 9 | 4 | Custom (no nav tab) |
| [Commission](Commission.md) | Commission | 45 | 9 | Custom |
| [Contact](Contact.md) | Contact | 80 | 26 | Core |
| [CurrencyRecord](CurrencyRecord.md) | Currency Record | 8 | 1 | Core |
| [Document](Document.md) | Document | 15 | 9 | Core |
| [Email](Email.md) | Email | 76 | 21 | Core |
| [EmailTemplate](EmailTemplate.md) | Email Template | 15 | 6 | Core |
| [Import](Import.md) | Import | 11 | 2 | Core |
| [KnowledgeBaseArticle](KnowledgeBaseArticle.md) | Knowledge Base Article | 19 | 7 | Core |
| [Lead](Lead.md) | Lead | 66 | 17 | Core |
| [Meeting](Meeting.md) | Meeting | 26 | 9 | Core |
| [Opportunity](Opportunity.md) | Opportunity | 197 | 20 | Core |
| [Policy](Policy.md) | Policy | 69 | 13 | Custom |
| [Quote](Quote.md) | Quote | 14 | 2 | Custom (no nav tab) |
| [Renewal](Renewal.md) | Renewal | 35 | 10 | Custom |
| [TargetList](TargetList.md) | Target List | 16 | 13 | Core |
| [Task](Task.md) | Task | 33 | 8 | Core |
| [Team](Team.md) | Team | 9 | 6 | Core |
| [Template](Template.md) | PDF Template | 29 | 3 | Core |
| [User](User.md) | User | 54 | 16 | Core |
| [WorkingTimeCalendar](WorkingTimeCalendar.md) | Working Time Calendar | 23 | 4 | Core |

## How this was generated

Source: live `GET /api/v1/Metadata` (keys: `entityDefs`, `scopes`, `clientDefs`) plus `GET /api/v1/I18n?locale=en_US` for labels.

_Last generated: 2026-05-26_
