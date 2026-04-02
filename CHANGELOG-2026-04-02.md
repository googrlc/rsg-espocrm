# EspoCRM Changelog — 2026-04-02

## 1. Layout Overflow Fixes
- Added comprehensive CSS overflow rules across all views (list, detail, kanban, modal)
- Momentum theme (`client/custom/css/momentum.css`) with full overflow containment
- Contact list column widths fixed (was 105%, now 95%)

## 2. Account Detail — 7-Tab Restructure
Replaced broken tab layout (tabs showing "2" and "4" instead of labels, bottom panels dumping everywhere) with clean 7-tab structure:

| Tab | Label | Detail Panels | Bottom Panels |
|-----|-------|--------------|---------------|
| 0 | Overview | Account Info, Key Metrics | Stream |
| 1 | Contacts | — | Contacts |
| 2 | Policies | — | Policies, Renewals |
| 3 | Activity | — | Activity Logs, Emails, Meetings, Calls, Tasks |
| 4 | Business Profile | Business Details, AI Assessment, Identity, BBB, Fleet, Risk, Gaps | Opportunities |
| 5 | Group Benefits | Census, Medical, Ancillary, Disability, Notes | — |
| 6 | Internal | AI Intel Pack, Internal IDs | Cases, Commissions |

- AI Intel Pack + Internal fields moved off Overview to Internal tab
- All `dynamicLogicVisible` conditionals preserved (Commercial vs Personal vs Group Benefits)
- Bottom panels correctly assigned to tabs (Cases/Commissions were referencing nonexistent tabs 5/6)

## 3. Lead Pipeline (Pre-Qualification Kanban)

| Kanban Column | Purpose |
|--------------|---------|
| New / Uncontacted | Just entered — alert team |
| Attempting Contact | Reached out, no response yet |
| Connected | Conversation started |
| Gathering Info | Collecting details + X-Date |
| Qualified | Ready — triggers handoff to Opportunity |
| Nurture | Not ready now, has X-Date for future |
| DNC | *(hidden from kanban)* |
| Converted | *(hidden from kanban)* |

- Added `xDate` field (date) for nurture loop automation
- New kanban layout showing name, insurance interest, X-Date, assigned user
- Preset filters: Active Leads, Converted, DNC, Nurture (X-Date)
- New PHP select filter classes: `Dnc.php`, `Nurture.php`
- Updated `Actual.php` to exclude DNC + Converted

## 4. Opportunity Pipeline (Sales Cycle Kanban)

| Kanban Column | Probability |
|--------------|------------|
| Discovery | 10% |
| Quoting | 30% |
| Proposal Presented | 60% |
| Negotiation | 80% |
| Closed Won | 100% *(hidden from kanban)* |
| Closed Lost | 0% *(hidden from kanban)* |

- Auto-probability formula updated for new stages
- `WonBoundValidation` hook now validates on "Closed Won" (requires Bind Date, Written Premium, Effective Date)
- All 5 select filter PHP classes updated (Open, Won, Lost, NewBusiness, Stalled)
- `clientDefs` dynamic logic updated (field visibility/required rules reference new stage names)
- Renewal stages preserved (Renewal Notice Sent → Bound/Renewed, Non-Renewal/Lost)
- i18n labels updated for all new stage options

## 5. n8n Workflow Definitions

### Automation A: Lead Qualification Handoff
- **Trigger**: Lead status changes to "Qualified"
- **Actions**: Create Opportunity in "Discovery" stage, set Lead to "Converted"

### Automation B: X-Date Nurture Loop
- **Trigger**: Daily at 7:00 AM ET
- **Actions**: Scan Nurture leads + Closed Lost opps for approaching X-Dates
  - Personal Lines: 30-day alert window
  - Commercial Lines: 60-day alert window
- **Output**: Creates urgent Tasks in EspoCRM assigned to account owner

## Files Changed (17 modified, 3 new)

### Modified
- `client/custom/css/momentum.css`
- `custom/Espo/Custom/Resources/layouts/Account/detail.json`
- `custom/Espo/Custom/Resources/layouts/Account/bottomPanelsDetail.json`
- `custom/Espo/Custom/Resources/layouts/Contact/list.json`
- `custom/Espo/Custom/Resources/metadata/entityDefs/Lead.json`
- `custom/Espo/Custom/Resources/metadata/entityDefs/Opportunity.json`
- `custom/Espo/Custom/Resources/metadata/clientDefs/Lead.json`
- `custom/Espo/Custom/Resources/metadata/clientDefs/Opportunity.json`
- `custom/Espo/Custom/Resources/metadata/selectDefs/Lead.json`
- `custom/Espo/Custom/Resources/i18n/en_US/Lead.json`
- `custom/Espo/Custom/Resources/i18n/en_US/Opportunity.json`
- `custom/Espo/Custom/Classes/Select/Lead/PrimaryFilters/Actual.php`
- `custom/Espo/Custom/Classes/Select/Opportunity/PrimaryFilters/Open.php`
- `custom/Espo/Custom/Classes/Select/Opportunity/PrimaryFilters/Won.php`
- `custom/Espo/Custom/Classes/Select/Opportunity/PrimaryFilters/Lost.php`
- `custom/Espo/Custom/Hooks/Opportunity/WonBoundValidation.php`

### New
- `custom/Espo/Custom/Classes/Select/Lead/PrimaryFilters/Dnc.php`
- `custom/Espo/Custom/Classes/Select/Lead/PrimaryFilters/Nurture.php`
- `custom/Espo/Custom/Resources/layouts/Lead/kanban.json`
