# Commission Workbench Design

## Goal

Replace the default working surface at `#Commission` with a Commission Workbench that helps Lamar and Gretchen reconcile commissions faster without leaving EspoCRM.

This directly supports revenue and retention operations by making unpaid, unmatched, disputed, and reconciled commissions easier to review and clear.

## Current State

The Commission module already has the fields needed for reconciliation:

- `reconciliationStatus`: `Unreconciled`, `Reconciled`, `Disputed`
- `status`: `Estimated`, `Posted`, `Overdue`
- money fields: `writtenPremium`, `estimatedCommission`, `postedAmount`, `varianceAmount`, `variancePercent`
- workflow fields: `expectedPaymentDate`, `paymentReceivedDate`, `ledgerSyncStatus`

The current `#Commission` page renders the standard Espo list table. It is sortable, but it does not act like a reconciliation workspace.

## Recommended Approach

Build the workbench as an EspoCRM custom list view for the `Commission` scope, not as an external standalone app.

The workbench should render inside the existing `#Commission` route, use Espo's current user session, and call Espo's normal REST API for reads and updates.

This keeps permissions, record links, creation, editing, and deployment inside the current CRM stack.

## Screen Layout

The top of `#Commission` should show a compact dashboard band:

- Unreconciled total count and estimated/postable dollar total
- Reconciled total count and posted dollar total
- Disputed count and variance total
- Overdue/attention count based on `status`, `expectedPaymentDate`, and `ledgerSyncStatus`

Below that, the workbench should show section tabs:

- `Unreconciled`
- `Reconciled`
- `Disputed`
- `All`

Each section should render the same dense sortable ledger table with columns:

- Account
- Policy
- Type
- Carrier
- Effective Date
- Expected Payment
- Estimated Commission
- Posted Amount
- Variance
- Status
- Ledger Sync
- Producer
- Actions

## Sorting And Filtering

Rows should be sortable client-side after each API fetch for common review fields:

- Expected Payment Date
- Estimated Commission
- Posted Amount
- Variance
- Account
- Carrier
- Producer

The page should keep Espo's normal search/filter controls where practical. The first release should prioritize the workbench's own tabs and sorting over a full custom advanced-search builder.

## Quick Actions

The first release should include safe row-level actions:

- Open record
- Mark Reconciled
- Mark Unreconciled
- Mark Disputed

Quick actions should patch only `reconciliationStatus`.

If a row is currently `Disputed`, moving it to `Reconciled` should be allowed but visually obvious.

No action should change `status`, money fields, policy links, or ledger sync fields in the first release.

## Data Flow

The custom list view should fetch commissions from:

`GET /api/v1/Commission`

The view should request only fields needed by the workbench and should use pagination compatible with the current 654-record list.

Quick actions should update one record at a time:

`PATCH /api/v1/Commission/{id}`

with payload:

```json
{"reconciliationStatus": "Reconciled"}
```

After a successful quick action, the row should update locally and the dashboard counts should recalculate. A manual refresh should also re-fetch from the server.

## Files To Touch

Expected implementation files:

- `custom/Espo/Custom/Resources/metadata/clientDefs/Commission.json`
- `client/custom/src/views/commission/list.js`
- `client/custom/css/momentum.css`

Optional if needed:

- `client/custom/res/templates/commission/list.tpl`
- `custom/Espo/Custom/Resources/i18n/en_US/Commission.json`

## Error Handling

If the workbench fetch fails, show a concise error panel and keep a refresh action available.

If a quick action fails, keep the row in its original state and show a small error message.

If no commissions exist in a selected section, show an empty state with the selected reconciliation status.

## Testing

Validate before deploy:

- JavaScript syntax check for `client/custom/src/views/commission/list.js`
- JSON validation for changed metadata/i18n files
- Browser smoke test on `#Commission`
- Verify each tab filters correctly
- Verify sorting works for date, money, and text columns
- Verify quick actions patch only `reconciliationStatus`

## Out Of Scope For First Release

- Bulk reconciliation
- Importing carrier statements
- Editing money amounts inline
- Changing ledger sync state
- External dashboard outside EspoCRM
- New database fields
