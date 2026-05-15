---
name: espocrm-query-patterns
scope: Load when building CRM searches, filters, or field lookups
priority: low
token_cost: ~250
---

# RSG EspoCRM — Query Patterns

Common patterns for searching and filtering CRM records via MCP tools.

---

## General search principles

- Always filter by `accountId` when looking up records belonging to a specific client.
- For opt-out checks before outreach: check `doNotContact` on Account, `doNotCall` on Contact/Lead, `emailAddressIsOptedOut` and `phoneNumberIsOptedOut` on Contact.
- Enum fields must receive exactly one listed option string in write operations.
- Multi-Enum fields accept a JSON array of option strings.
- Date fields use `YYYY-MM-DD`; DateTime fields use `YYYY-MM-DDTHH:MM:SSZ` (UTC).

---

## Common lookup patterns

### Active clients with upcoming renewals
Filter Accounts where `daysToRenewal <= 60` and `accountStatus = Active`. Sort by `daysToRenewal` ascending.

### Open opportunities in pipeline
Filter Opportunities where `stage` NOT IN (`Closed Won`, `Closed Lost`). Sort by `closeDate` ascending.

### Overdue tasks
Filter Tasks where `isOverdue = true` and `status` NOT IN (`Completed`, `Cancelled`). Sort by `dateEnd` ascending.

### Policies expiring soon
Filter Policies where `expirationDate` within next 90 days and `status` IN (`Active`, `Up for Renewal`). Sort by `expirationDate` ascending.

### Commissions with variance
Filter Commissions where `variancePercent` absolute value > 5 and `status = Posted`. Sort by `varianceAmount` descending.

### High-risk accounts needing attention
Filter Accounts where `retentionRisk = High` OR `scoreTier = Critical`. Sort by `accountScore` ascending.

---

## Field name conventions

- camelCase field names map to the EspoCRM API (e.g., `accountType`, `effectiveDate`).
- Relationship fields use the related entity name (e.g., `accountId`, `policyId`, `opportunityId`).
- Computed/rollup fields are read-only and cannot be used as write targets (e.g., `activePolicyCount`, `accountScore`, `gapCount`).
- External ID fields are immutable and cannot be used as write targets (e.g., `momentumClientId`, `momentumPolicyId`).

---

## Polymorphic parent field (Task)

Task records use a `Link Parent` field called `parent`. When querying tasks for a specific record, filter by both `parentType` (entity name, e.g., `"Account"`) and `parentId` (the record ID).
