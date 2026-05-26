# Email

**Entity name:** `Email`  
**Plural label:** Emails  
**Type:** Core entity  
**Field count:** 76  
**Link count:** 21  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Email`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Email/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Email`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Email/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Email/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `account` | Account | link |  | — | — |
| `addressNameMap` | addressNameMap | jsonObject |  | — | read-only |
| `assignedUser` | assignedUser | link |  | — | — |
| `assignedUsers` | Assigned Users | linkMultiple |  | — | read-only |
| `attachments` | Attachments | attachmentMultiple |  | — | — |
| `bcc` | BCC | varchar |  | — | — |
| `bccEmailAddresses` | BCC Email Addresses | linkMultiple |  | — | read-only |
| `body` | Body | wysiwyg |  | — | — |
| `bodyPlain` | Body (Plain) | text |  | — | read-only |
| `cc` | CC | varchar |  | — | — |
| `ccEmailAddresses` | CC Email Addresses | linkMultiple |  | — | read-only |
| `createEvent` | createEvent | base |  | — | — |
| `createdAt` | createdAt | datetime |  | — | read-only |
| `createdBy` | createdBy | link |  | — | read-only |
| `createdEvent` | Created Event | linkParent |  | — | read-only |
| `dateSent` | Date Sent | datetime |  | — | — |
| `deliveryDate` | Delivery Date | datetime |  | — | read-only |
| `emailAccounts` | emailAccounts | linkMultiple |  | — | read-only |
| `emailAddress` | Email Address | varchar |  | — | — |
| `folder` | Folder | link |  | — | read-only |
| `folderId` | Folder Id | varchar |  | — | read-only |
| `folderString` | Folder | link |  | — | read-only |
| `from` | From | varchar | yes | — | required |
| `fromAddress` | From Address | varchar |  | — | read-only |
| `fromEmailAddress` | From Address (link) | link |  | — | read-only |
| `fromName` | From Name | varchar |  | — | read-only |
| `fromString` | From String | varchar |  | — | — |
| `groupFolder` | Group Folder | link |  | — | read-only |
| `groupStatusFolder` | Group Status Folder | enum |  | — | read-only, max 7 |
| `hasAttachment` | Has Attachment | bool |  | — | read-only |
| `icsContents` | ICS Contents | text |  | — | read-only |
| `icsEventData` | ICS Event Data | jsonObject |  | — | read-only |
| `icsEventDateStart` | ICS Event Date Start | datetimeOptional |  | — | read-only |
| `icsEventDateStartDate` | icsEventDateStartDate | date |  | — | read-only |
| `icsEventUid` | ICS Event UID | varchar |  | — | read-only, max 255 |
| `idHash` | idHash | jsonObject |  | — | read-only |
| `inArchive` | In Archive | bool |  | false | read-only |
| `inTrash` | In Trash | bool |  | false | read-only |
| `isAutoReply` | Is Auto-Reply | bool |  | — | read-only |
| `isBeingImported` | isBeingImported | bool |  | — | read-only |
| `isHtml` | HTML | bool |  | true | — |
| `isImportant` | Is Important | bool |  | false | read-only |
| `isJustSent` | isJustSent | bool |  | false | read-only |
| `isNotRead` | Is Not Read | bool |  | — | read-only |
| `isNotReplied` | Is Not Replied | bool |  | — | read-only |
| `isRead` | Is Read | bool |  | true | read-only |
| `isReplied` | Is Replied | bool |  | — | read-only |
| `isSystem` | Is System | bool |  | false | read-only |
| `isUsers` | Is User's | bool |  | false | read-only |
| `isUsersSent` | Is User's Sent | bool |  | — | read-only |
| `messageId` | Message Id | varchar |  | — | read-only, max 255 |
| `messageIdInternal` | Message Id (Internal) | varchar |  | — | read-only, max 300 |
| `modifiedAt` | modifiedAt | datetime |  | — | read-only |
| `modifiedBy` | modifiedBy | link |  | — | read-only |
| `name` | Name (Subject) | varchar | yes | — | required |
| `nameHash` | nameHash | jsonObject |  | — | read-only |
| `parent` | Parent | linkParent |  | — | — |
| `personStringData` | Person String Data | varchar |  | — | — |
| `replied` | Replied | link |  | — | — |
| `replies` | Replies | linkMultiple |  | — | read-only |
| `replyTo` | Reply To | varchar |  | — | — |
| `replyToAddress` | Reply-To Address | varchar |  | — | read-only |
| `replyToEmailAddresses` | Reply-To Email Addresses | linkMultiple |  | — | read-only |
| `replyToName` | Reply-To Name | varchar |  | — | read-only |
| `replyToString` | Reply To (String) | varchar |  | — | — |
| `sendAt` | Send At | datetime |  | — | — |
| `sentBy` | Sent By | link |  | — | read-only |
| `skipNotificationMap` | skipNotificationMap | jsonObject |  | — | read-only |
| `status` | Status | enum |  | `Archived` | — |
| `subject` | Subject | varchar | yes | — | required |
| `tasks` | Tasks | linkMultiple |  | — | read-only |
| `teams` | teams | linkMultiple |  | — | — |
| `to` | To | varchar | yes | — | required |
| `toEmailAddresses` | To Email Addresses | linkMultiple |  | — | read-only |
| `typeHash` | typeHash | jsonObject |  | — | read-only |
| `users` | Users | linkMultiple |  | — | read-only |

## Allowed values (enum / multi-enum / array / checklist)

### `groupStatusFolder` — Group Status Folder

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Archive`
  - `Trash`

### `status` — Status

- Type: `enum`
- Default: `Archived`
- Options:
  - `Draft`
  - `Sending`
  - `Sent`
  - `Archived`
  - `Failed`

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `account` | Account | belongsTo | `Account` | `—` | — |
| `assignedUser` | assignedUser | belongsTo | `User` | `—` | — |
| `assignedUsers` | Assigned Users | hasMany | `User` | `—` | relation `entityUser` |
| `attachments` | Attachments | hasChildren | `Attachment` | `parent` | relation `attachments` |
| `bccEmailAddresses` | BCC Email Addresses | hasMany | `EmailAddress` | `—` | relation `emailEmailAddress` |
| `ccEmailAddresses` | CC Email Addresses | hasMany | `EmailAddress` | `—` | relation `emailEmailAddress` |
| `createdBy` | createdBy | belongsTo | `User` | `—` | — |
| `createdEvent` | Created Event | belongsToParent | `—` | `—` | — |
| `emailAccounts` | emailAccounts | hasMany | `EmailAccount` | `emails` | — |
| `fromEmailAddress` | From Email Address | belongsTo | `EmailAddress` | `—` | — |
| `groupFolder` | Group Folder | belongsTo | `GroupEmailFolder` | `emails` | — |
| `modifiedBy` | modifiedBy | belongsTo | `User` | `—` | — |
| `parent` | parent | belongsToParent | `emails` | `emails` | — |
| `replied` | Replied | belongsTo | `Email` | `replies` | — |
| `replies` | Replies | hasMany | `Email` | `replied` | — |
| `replyToEmailAddresses` | Reply-To Email Addresses | hasMany | `EmailAddress` | `—` | relation `emailEmailAddress` |
| `sentBy` | Sent By | belongsTo | `User` | `—` | — |
| `tasks` | tasks | hasMany | `Task` | `email` | — |
| `teams` | teams | hasMany | `Team` | `—` | relation `entityTeam` |
| `toEmailAddresses` | To Email Addresses | hasMany | `EmailAddress` | `—` | relation `emailEmailAddress` |
| `users` | users | hasMany | `User` | `emails` | — |

---

_Generated 2026-05-26 from `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`._
