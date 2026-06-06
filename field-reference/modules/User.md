# User

**Entity name:** `User`  
**Plural label:** Users  
**Type:** Core entity  
**Field count:** 57  
**Link count:** 19  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/User`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/User/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/User`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/User/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/User/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `acceptanceStatus` | Acceptance Status | varchar |  | — | — |
| `acceptanceStatusCalls` | Acceptance Status (Calls) | enum |  | — | — |
| `acceptanceStatusMeetings` | Acceptance Status (Meetings) | enum |  | — | — |
| `account` | Account (Primary) | link |  | — | read-only |
| `accounts` | Accounts | linkMultiple |  | — | — |
| `apiKey` | API Key | varchar |  | — | read-only, max 100 |
| `auth2FA` | 2FA | foreign |  | — | read-only |
| `authLogRecordId` | authLogRecordId | varchar |  | — | — |
| `authMethod` | Authentication Method | enum |  | — | max 24 |
| `authTokenId` | authTokenId | varchar |  | — | — |
| `avatar` | Avatar | image |  | — | — |
| `avatarColor` | Avatar Color | colorpicker |  | — | — |
| `contact` | Contact | link |  | — | — |
| `createdAt` | createdAt | datetime |  | — | read-only |
| `createdBy` | createdBy | link |  | — | read-only |
| `dashboardTemplate` | Dashboard Template | link |  | — | — |
| `defaultTeam` | Default Team | link |  | — | — |
| `deleteId` | deleteId | varchar |  | `0` | read-only, not-null, max 17 |
| `emailAddress` | Email | email |  | — | — |
| `emailAddressIsInvalid` | emailAddressIsInvalid | bool |  | — | — |
| `emailAddressIsOptedOut` | emailAddressIsOptedOut | bool |  | — | — |
| `emailAddressList` | emailAddressList | array |  | — | read-only |
| `excludeFromReplyEmailAddressList` | excludeFromReplyEmailAddressList | array |  | — | read-only |
| `firstName` | firstName | varchar |  | — | max 100, pattern |
| `gender` | Gender | enum |  | — | — |
| `ipAddress` | IP Address | varchar |  | — | — |
| `isActive` | Is Active | bool |  | true | — |
| `lastAccess` | Last Access | datetime |  | — | read-only |
| `lastName` | lastName | varchar | yes | — | required, max 100, pattern |
| `layoutSet` | Layout Set | link |  | — | — |
| `middleName` | middleName | varchar |  | — | max 100, pattern |
| `modifiedAt` | modifiedAt | datetime |  | — | read-only |
| `name` | Name | personName |  | — | — |
| `password` | Password | password |  | — | max 150 |
| `passwordConfirm` | Confirm Password | password |  | — | max 150 |
| `phoneNumber` | Phone | phone |  | — | — |
| `phoneNumberIsInvalid` | phoneNumberIsInvalid | bool |  | — | — |
| `phoneNumberIsOptedOut` | phoneNumberIsOptedOut | bool |  | — | — |
| `portal` | Portal | link |  | — | read-only |
| `portalRoles` | Portal Roles | linkMultiple |  | — | — |
| `portals` | Portals | linkMultiple |  | — | — |
| `position` | Position in Team | varchar |  | — | read-only, max 100 |
| `recordAccessLevels` | recordAccessLevels | jsonObject |  | — | read-only |
| `roles` | Roles | linkMultiple |  | — | — |
| `salutationName` | salutationName | enum |  | — | — |
| `secretKey` | Secret Key | varchar |  | — | read-only, max 100 |
| `sendAccessInfo` | Send Email with Access Info to User | bool |  | — | — |
| `targetListIsOptedOut` | targetListIsOptedOut | bool |  | — | read-only |
| `teamRole` | Position | varchar |  | — | — |
| `teams` | Teams | linkMultiple |  | — | — |
| `title` | Title | varchar |  | — | max 100, pattern |
| `token` | token | varchar |  | — | — |
| `type` | Type | enum |  | `regular` | max 24 |
| `userData` | User Data | linkOne |  | — | — |
| `userEmailAddressList` | userEmailAddressList | array |  | — | read-only |
| `userName` | User Name | varchar | yes | — | required, max 50 |
| `workingTimeCalendar` | Working Time Calendar | link |  | — | — |

## Allowed values (enum / multi-enum / array / checklist)

### `authMethod` — Authentication Method

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `ApiKey`
  - `Hmac`

### `gender` — Gender

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Male`
  - `Female`
  - `Neutral`

### `salutationName` — salutationName

- Type: `enum`
- Options:
  - `""` _(empty)_
  - `Mr.`
  - `Ms.`
  - `Mrs.`
  - `Dr.`

### `type` — Type

- Type: `enum`
- Default: `regular`
- Options:
  - `regular`
  - `admin`
  - `portal`
  - `system`
  - `super-admin`
  - `api`

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `accounts` | Accounts | hasMany | `Account` | `portalUsers` | relation `AccountPortalUser` |
| `calls` | calls | hasMany | `Call` | `users` | — |
| `contact` | Contact | belongsTo | `Contact` | `portalUser` | — |
| `createdBy` | createdBy | belongsTo | `User` | `—` | — |
| `dashboardTemplate` | Dashboard Template | belongsTo | `DashboardTemplate` | `—` | — |
| `defaultTeam` | Default Team | belongsTo | `Team` | `—` | — |
| `emails` | emails | hasMany | `Email` | `users` | — |
| `layoutSet` | Layout Set | belongsTo | `LayoutSet` | `—` | no-join |
| `meetings` | meetings | hasMany | `Meeting` | `users` | — |
| `notes` | Notes | hasMany | `Note` | `users` | — |
| `portalRoles` | Portal Roles | hasMany | `PortalRole` | `users` | — |
| `portals` | Portals | hasMany | `Portal` | `users` | — |
| `roles` | Roles | hasMany | `Role` | `users` | — |
| `targetLists` | Target Lists | hasMany | `TargetList` | `users` | — |
| `tasks` | Tasks | hasMany | `Task` | `assignedUser` | — |
| `teams` | Teams | hasMany | `Team` | `users` | — |
| `userData` | User Data | hasOne | `UserData` | `user` | — |
| `workingTimeCalendar` | Working Time Calendar | belongsTo | `WorkingTimeCalendar` | `—` | no-join |
| `workingTimeRanges` | Working Time Exceptions | hasMany | `WorkingTimeRange` | `users` | — |

---

_Generated 2026-06-06 from a read-only live metadata pull (`metadata.php` cache, equivalent to `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`)._
