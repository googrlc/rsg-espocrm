# Kanban Card Implementation Guide

## Overview

This guide documents the enhanced Kanban card implementation for your EspoCRM system. The cards now display comprehensive information at-a-glance while maintaining visual clarity.

---

## 1. Front-of-Card: "At-a-Glance" Essentials

These fields are now visible on the Kanban card **without clicking**:

### Lead Card Layout
| Field | Purpose | Visual Treatment |
|-------|---------|------------------|
| **Lead Name** | Primary identifier | Bold header with priority badge |
| **Account Name** | Associated company/contact | Sub-header with 👤 icon |
| **Email/Phone** | Contact methods | Shown if available |
| **Estimated Premium** | Deal value ($) | Large green currency display |
| **Priority** | Hot/Warm/Cold | Color-coded badge (Red/Amber/Blue) |
| **Source** | Lead origin (Inbound, Referral, etc.) | Purple tag |
| **Insurance Interest** | Product category | Blue tag |
| **Status** | Current pipeline stage | Drives column placement |
| **X-Date** | Expiration/next action date | Color-coded footer (🔴 Overdue, 🟡 Today, 🟢 Upcoming) |
| **Assigned User** | Owner | Avatar with initials |

### Opportunity Card Layout
| Field | Purpose | Visual Treatment |
|-------|---------|------------------|
| **Opportunity Name** | Deal identifier | Bold header with priority badge |
| **Account** | Associated company | Sub-header |
| **Estimated Premium** | Deal value ($) | Large green currency display |
| **Priority** | Hot/Warm/Cold | Color-coded badge |
| **Line of Business** | Product category | Blue tag |
| **Business Type** | New/Renewal/Rewrite | Purple tag |
| **Stage** | Pipeline position | Drives column placement |
| **Close Date** | Expected close date | Color-coded footer |
| **Assigned User** | Owner | Avatar with initials |

---

## 2. Strategic Metadata (Visual Tags)

### Color-Coded Tags
Tags appear in the middle section of the card:

| Tag Type | Color | Example |
|----------|-------|---------|
| **Lead Source** | Purple (`#f3e8ff`) | `Inbound`, `Cold Call`, `Referral` |
| **Product Category** | Blue (`#e0f2fe`) | `SaaS`, `Commercial Auto`, `Medicare` |
| **Priority Level** | Badge (Red/Amber/Blue) | `[HOT]`, `[WARM]`, `[COLD]` |

### Priority Styling
- **HOT**: Red badge (`#fee2e2` background, `#dc2626` text)
- **WARM**: Amber badge (`#fef3c7` background, `#d97706` text)
- **COLD**: Blue badge (`#dbeafe` background, `#2563eb` text)

---

## 3. Progress & Health Indicators

### Stagnation Timer
Shows how many days the card has been in the current column:
- **0-7 days**: Normal display
- **8-14 days**: Amber warning (`⏱️ 10d in stage`)
- **15+ days**: Red critical (card background turns amber/red)

### Activity Counter
Displays recent activity (emails, calls, notes):
- Shows "📧 Activity logged" when communication exists
- Future enhancement: Show count like "💬 4 Notes"

### Checklist Progress
For entities with checklist fields (Medicare, Umbrella):
- Visible as a mini progress bar at the bottom
- Green fill proportional to completed items
- Example: Medicare checklists (Scope, Plan, App, CMS, Welcome)

---

## 4. Conditional Formatting (Card Weight Management)

### High-Value Cards
- **Threshold**: $5,000+ estimated premium
- **Treatment**: Green left border, larger value display
- **Purpose**: Instantly prioritize high-impact deals

### Stagnant Cards
- **Warning (8-14 days)**: Amber background (`#fef3c7`)
- **Critical (15+ days)**: Red background (`#fee2e2`)
- **Purpose**: Prevent deals from falling through cracks

### Due Date Color Coding
- **Overdue**: Red text with "Overdue (Xd)" format
- **Today**: Amber text with "Today" label
- **Within 7 days**: Green text with "Xd" countdown
- **Beyond 7 days**: Standard date format (e.g., "Oct 12")

---

## 5. Inside the Card (Deep Dive)

When you click to open the card, the detail view should include:

### Recommended Detail View Sections

#### Communication History
- **Location**: Activities panel (bottom of detail view)
- **Content**: Synced email feed, call logs, meeting notes
- **EspoCRM Entity**: `Email`, `Call`, `Meeting` linked to Lead/Opportunity

#### Pain Points
- **Field**: `aiSummary` (Lead/Opportunity)
- **Purpose**: 2-3 sentence brief from Manus intel — pre-call context
- **Tooltip**: "Pre-call intel from Manus prospect intelligence pack"

#### Decision Criteria
- **Custom Field Recommendation**: Add `decisionCriteria` (text field)
- **Examples**: "Budget approval pending", "Security audit required", "Legal review"
- **Location**: Common panel in detail layout

#### Internal Notes
- **Field**: Use `aiAssessment` or create custom `internalNotes` (text field)
- **Purpose**: Specific nuances like "Prefers Zoom over Teams" or "Out of office until Tuesday"
- **Visibility**: Restrict to internal users (not portal-accessible)

### Adding New Fields to Detail Views

1. **Define the field** in `metadata/entityDefs/{Entity}.json`:
```json
"decisionCriteria": {
    "type": "text",
    "isCustom": true,
    "tooltipText": "What the client needs to say Yes (budget, audit, etc.)"
}
```

2. **Add i18n label** in `i18n/en_US/{Entity}.json`:
```json
"fields": {
    "decisionCriteria": "Decision Criteria"
}
```

3. **Add to layout** in `layouts/{Entity}/detail.json`:
```json
{
    "name": "decisionCriteria",
    "customLabel": "Decision Criteria"
}
```

4. **Rebuild** EspoCRM cache after metadata changes

---

## 6. File Structure

### Modified Files
```
custom/Espo/Custom/Resources/layouts/
├── Lead/kanban.json                    ✅ Updated (11 fields)
└── Opportunity/kanban.json             ✅ Updated (9 fields)

custom/Espo/Custom/Resources/metadata/clientDefs/
├── Lead.json                           ✅ Added kanbanCardView
└── Opportunity.json                    ✅ Added kanbanCardView

client/custom/css/
└── momentum.css                        ✅ Added ~250 lines of kanban card styling
```

### New Files
```
client/custom/src/views/
├── lead/kanban-card.js                 ✅ Custom view logic
└── opportunity/kanban-card.js          ✅ Custom view logic

client/custom/res/templates/record/
├── kanban-card.tpl                     ✅ Lead card template
└── kanban-card-opportunity.tpl         ✅ Opportunity card template
```

---

## 7. Implementation Steps

### Step 1: Clear Cache & Rebuild
After deploying these changes:
```bash
# In EspoCRM admin panel:
Admin > Clear Cache
Admin > Rebuild
```

### Step 2: Verify Kanban View
1. Navigate to **Leads** or **Opportunities**
2. Switch to **Kanban** view (grid icon in top-right)
3. Verify cards display:
   - ✅ Priority badge (if set)
   - ✅ Deal/Lead name
   - ✅ Account/Contact name
   - ✅ Deal value (formatted as currency)
   - ✅ Tags (source, product category)
   - ✅ Due date with color coding
   - ✅ Owner avatar (initials)
   - ✅ Stagnation timer

### Step 3: Test Conditional Formatting
- Create a lead with `$6,000` estimated premium → Verify green left border
- Modify a lead's `modifiedAt` to 10 days ago → Verify amber background
- Set `xDate` to yesterday → Verify red "Overdue" label

### Step 4: Customize Further (Optional)
- Adjust premium threshold in `kanban-card.js` (line ~100):
  ```javascript
  if (estimatedPremium && estimatedPremium >= 5000) { // Change 5000 to your threshold
  ```
- Adjust stagnation thresholds in `momentum.css` and `.js` files
- Add more tags by editing the `card-tags` section in templates

---

## 8. Example Card Renderings

### Lead Card Example
```
┌─────────────────────────────────────┐
│ [HOT] Acme Corp – Cloud Migration   │  ← Priority badge + Name
├─────────────────────────────────────┤
│ 👤 Acme Corporation                 │  ← Account name
│ $12,500                             │  ← Deal value (green, large)
├─────────────────────────────────────┤
│ 🏷️ Referral   🏷️ Commercial Auto   │  ← Strategic tags
├─────────────────────────────────────┤
│ 📅 Overdue (3d)          [JD]       │  ← Due date (red) + Avatar
├─────────────────────────────────────┤
│ ⏱️ 12d in stage   📧 Activity       │  ← Health indicators
└─────────────────────────────────────┘
```

### Opportunity Card Example
```
┌─────────────────────────────────────┐
│ [WARM] Global Tech - Q3 Renewal     │  ← Priority badge + Name
├─────────────────────────────────────┤
│ 👤 Global Tech Inc.                 │  ← Account name
│ $8,750                              │  ← Deal value (green, large)
├─────────────────────────────────────┤
│ 🏷️ Workers Comp   🏷️ Renewal       │  ← LOB + Business type
├─────────────────────────────────────┤
│ 📅 Today                  [SM]      │  ← Due date (amber) + Avatar
├─────────────────────────────────────┤
│ ⏱️ 5d in stage                      │  ← Health indicators
└─────────────────────────────────────┘
```

---

## 9. Pro Tips for Card Weight Management

### Keep It Lean
- **Only show what matters**: If a field is empty, it won't render
- **Conditional visibility**: Use EspoCRM's `dynamicLogic` to hide fields based on stage
- **Progressive disclosure**: Front of card = quick scan; Inside card = deep dive

### Recommended Filters
Use the Kanban view filters to focus:
- **Only My**: Show only your assigned records
- **Open**: Exclude won/lost deals
- **Stalled**: Focus on deals that haven't moved in 14+ days

### Future Enhancements
1. **Activity Counter**: Integrate with `Email` and `Call` entities to show "3 Emails, 1 Call"
2. **Checklist Progress**: Add visual progress bar for checklist fields
3. **Stagnation Automation**: Trigger workflow to notify owner after 14 days in same stage
4. **Card Actions**: Add quick-action buttons (call, email, task) directly on card

---

## 10. Troubleshooting

### Cards Not Showing New Fields
- **Solution**: Clear cache + rebuild in Admin panel
- **Verify**: Check `kanban.json` has the field names listed

### Styling Not Applying
- **Solution**: Hard refresh browser (Ctrl+Shift+R / Cmd+Shift+R)
- **Verify**: CSS file is loaded (check browser dev tools > Network tab)

### Custom View Not Rendering
- **Solution**: Check file paths in `clientDefs` match actual file locations
- **Verify**: Browser console for JavaScript errors

### Missing Fields on Card
- **Solution**: Ensure fields exist in `entityDefs` and have data
- **Verify**: Open record detail view to confirm field values exist

---

## Questions?

This implementation is specific to your EspoCRM instance with custom entities (Lead, Opportunity, Renewal, Policy). If you're adapting this to another CRM platform, the concepts translate but the implementation details will differ.

**Key concepts that apply to any CRM:**
1. **Card Layout File**: Controls which fields appear (like `kanban.json`)
2. **Client Configuration**: Wires up custom views (like `clientDefs`)
3. **CSS Styling**: Visual treatment (like `momentum.css`)
4. **Template File**: HTML structure of the card (like `.tpl` files)
5. **View Logic**: JavaScript for dynamic data (like `.js` files)
