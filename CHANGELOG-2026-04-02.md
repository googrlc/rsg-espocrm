# RSG EspoCRM — Changelog April 2, 2026

## 1. Layout Overflow Fix (System-Wide)
**File:** `client/custom/css/momentum.css` (new)

- Added global `overflow-x: hidden` on body, `#content`, all containers
- List/table views get `overflow-x: auto` + `table-layout: fixed` — tables scroll inside their panel instead of breaking the page
- Table cells truncate long values with ellipsis; name/account/contact columns wrap
- All field values get `word-break: break-word` — fixes long URLs, emails, addresses
- Text fields (AI Assessment, Notes, Coverage Gaps, etc.) wrap properly
- Side panels, modals, kanban boards, header breadcrumbs — all overflow-constrained
- Responsive breakpoints: 768px (smaller fonts, stacked columns) and 480px (hides low-priority columns on mobile)
- Includes all existing Momentum theme custom styles (LOB tags, status badges, stage bar, etc.)

## 2. Contact List Column Width Fix
**File:** `custom/.../layouts/Contact/list.json`

- Column widths reduced from 105% to 95% total (was guaranteed horizontal overflow)

## 3. Selectize Search Focus Fix
**File:** `client/custom/src/selectize-fix.js` (new)

- Script was referenced in `client.json` but missing from repo — caused 404 and broken search focus
- New script prevents blur when clicking dropdowns, restores focus after DOM re-renders, fixes global search bar losing cursor

## 4. Account Detail View — Tabbed Layout Restructure
**Files:** `custom/.../layouts/Account/detail.json`, `bottomPanelsDetail.json`

**Before:** 5 tabs with two showing as "2" and "4" (null labels), all bottom panels dumped on page

**After:** 7 clean tabs:

| Tab | Label | Content |
|-----|-------|---------|
| 0 | Overview | Account Info, Key Metrics, Stream |
| 1 | Contacts | Contacts list |
| 2 | Policies | Policies + Renewals lists |
| 3 | Activity | Activity Logs, Emails, Meetings, Calls, Tasks |
| 4 | Business Profile | Business Details, AI Assessment, Identity, BBB, Fleet, Risk, Coverage Gaps + Opportunities |
| 5 | Group Benefits | Census, Medical, Dental/Vision/Life, Disability, Notes (GB accounts only) |
| 6 | Internal | AI Intel Pack, Momentum IDs, Google Drive + Cases, Commissions |

- Fixed Cases (tab 5→6) and Commissions (tab 6→6) referencing non-existent tabs
- Moved AI Intel Pack and Internal IDs out of Overview into Internal tab
- All `dynamicLogicVisible` conditions preserved for PL/CL/GB account types

## 5. Kanban Views Enabled
**Files:** `custom/.../clientDefs/Lead.json`, `custom/.../layouts/Lead/kanban.json`

All 4 main entities now have list + kanban toggle (2 views each):

| Entity | Kanban By | Columns |
|--------|-----------|---------|
| Policy | status | Active, Renewing, Expired, etc. |
| Task | status | Not Started, Started, Completed |
| Lead | status | New/Uncontacted → Nurture (DNC/Converted hidden) |
| Opportunity | stage | Discovery → Negotiation (Closed Won/Lost hidden) |

## 6. Universal Lead Pipeline
**Files:** `entityDefs/Lead.json`, `clientDefs/Lead.json`, `i18n/en_US/Lead.json`, Lead select filters

**Old stages:** New → Assigned → In Process → Converted | Dead

**New stages:**
1. **New / Uncontacted** — just entered, alert team
2. **Attempting Contact** — reached out, no response
3. **Connected** — conversation started
4. **Gathering Info** — collecting details + X-Date
5. **Qualified** — triggers handoff to Opportunity (Automation A)
6. **Nurture** — not ready now, X-Date set for future re-engagement
7. **DNC** — do not contact (hidden from kanban)
8. **Converted** — pushed to Opportunity (hidden from kanban)

- Added **xDate** field (date) for X-Date nurture loop automation
- Updated select filters: Actual excludes Converted/DNC/Nurture; Dead filter → DNC

## 7. Universal Opportunity Pipeline
**Files:** `entityDefs/Opportunity.json`, `clientDefs/Opportunity.json`, `i18n/en_US/Opportunity.json`, Opportunity select filters, `WonBoundValidation.php`

**Old stages:** Prospect → Qualify → Quote → Proposal → Negotiate → Won-Bound | Lost (+ 6 renewal stages)

**New stages:**
1. **Discovery** (10%) — deep dive into needs
2. **Quoting** (30%) — building proposal
3. **Proposal Presented** (60%) — quote in client's hands
4. **Negotiation** (80%) — working through objections
5. **Closed Won** (100%) — deal signed
6. **Closed Lost** (0%) — deal fell through

- Removed 6 separate renewal stages (now one universal pipeline for new biz + renewals)
- Updated formula (auto-probability mapping)
- Updated `WonBoundValidation` hook → validates on "Closed Won"
- Updated Won/Lost/Open PHP select filters
- Updated all clientDefs dynamic logic references

## 8. n8n Workflow Definitions (New)
**Files:** `n8n-workflows/lead-qualification-handoff.json`, `n8n-workflows/xdate-nurture-loop.json`

### Automation A: Lead Qualification Handoff
- **Trigger:** Webhook from EspoCRM when Lead → "Qualified"
- **Flow:** Extract lead data → Create Opportunity (stage: Discovery) → Set Lead to "Converted"
- **Requires:** `ESPO_URL` env var + `EspoCRM API Key` credential (X-Api-Key header)

### Automation B: X-Date Nurture Loop
- **Trigger:** Daily at 7:00 AM
- **Flow:** Fetch Nurture leads + Closed Lost opps → Calculate alert windows → Create urgent Tasks
- **Logic:** Personal lines = 30-day window before X-Date, Commercial = 60-day window
- **Requires:** `ESPO_URL` env var + `EspoCRM API Key` credential

---

## Setup Required After Deploy
1. Copy all files to EspoCRM server
2. Clear cache (Administration → Clear Cache)
3. **Data migration:** Existing records with old stage values (Won-Bound, Lost, Dead, etc.) need to be updated via SQL or EspoCRM mass update to match new stage names
4. **n8n:** Import the two workflow JSON files, configure `ESPO_URL` environment variable and `EspoCRM API Key` credential
5. **EspoCRM webhook:** Create a webhook in EspoCRM (Administration → Webhooks) that fires on Lead update when status = "Qualified", pointing to `{n8n_url}/webhook/espo-lead-qualified`
