# Working Time Calendar

**Entity name:** `WorkingTimeCalendar`  
**Plural label:** Working Time Calendars  
**Type:** Core entity  
**Field count:** 23  
**Link count:** 4  

**API endpoints**

- List: `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/WorkingTimeCalendar`
- Get:  `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/WorkingTimeCalendar/{id}`
- Create: `POST https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/WorkingTimeCalendar`
- Update: `PATCH https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/WorkingTimeCalendar/{id}`
- Delete: `DELETE https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/WorkingTimeCalendar/{id}`

## Fields

| API name | Label | Type | Required | Default | Constraints |
|---|---|---|---|---|---|
| `createdAt` | createdAt | datetime |  | — | read-only |
| `createdBy` | createdBy | link |  | — | read-only |
| `description` | description | text |  | — | — |
| `modifiedAt` | modifiedAt | datetime |  | — | read-only |
| `modifiedBy` | modifiedBy | link |  | — | read-only |
| `name` | name | varchar | yes | — | required |
| `teams` | teams | linkMultiple |  | — | read-only |
| `timeRanges` | Workday Schedule | jsonArray | yes | `[['9:00', '17:00']]` | required |
| `timeZone` | Time Zone | enum |  | — | — |
| `weekday0` | Sun | bool |  | false | — |
| `weekday0TimeRanges` | Sun Schedule | jsonArray |  | — | — |
| `weekday1` | Mon | bool |  | true | — |
| `weekday1TimeRanges` | Mon Schedule | jsonArray |  | — | — |
| `weekday2` | Tue | bool |  | true | — |
| `weekday2TimeRanges` | Tue Schedule | jsonArray |  | — | — |
| `weekday3` | Wed | bool |  | true | — |
| `weekday3TimeRanges` | Wed Schedule | jsonArray |  | — | — |
| `weekday4` | Thu | bool |  | true | — |
| `weekday4TimeRanges` | Thu Schedule | jsonArray |  | — | — |
| `weekday5` | Fri | bool |  | true | — |
| `weekday5TimeRanges` | Fri Schedule | jsonArray |  | — | — |
| `weekday6` | Sat | bool |  | false | — |
| `weekday6TimeRanges` | Sat Schedule | jsonArray |  | — | — |

## Relationships (links)

| API name | Label | Type | Target entity | Foreign link | Notes |
|---|---|---|---|---|---|
| `createdBy` | createdBy | belongsTo | `User` | `—` | — |
| `modifiedBy` | modifiedBy | belongsTo | `User` | `—` | — |
| `ranges` | Exceptions | hasMany | `WorkingTimeRange` | `calendars` | — |
| `teams` | teams | hasMany | `Team` | `workingTimeCalendar` | read-only |

---

_Generated 2026-06-06 from a read-only live metadata pull (`metadata.php` cache, equivalent to `GET https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1/Metadata`)._
