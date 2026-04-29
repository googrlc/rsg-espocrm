# Devin Review Findings — Passes 1-4 Snake Case Migration

Scope: read-only review of custom metadata, layouts, PHP, client JS, and Pass 4 SQL migration files for EspoCRM 8.x / MySQL 8.4 rebuild risk. No live EspoCRM commands were run.

## Executive Summary

The `claims_count_3yr` runtime error is consistent with metadata being deployed before the backing `account.claims_count_3yr` column was created/renamed. The repository has both the metadata definition and an idempotent migration for that column, but `php command.php rebuild` can fail first on MySQL row-size limits for the very wide `account` table.

The most likely rebuild blocker is the `account` table width: `Account.json` defines 287 fields, including 32 enums, 1 multiEnum, 16 varchar, 5 url, 1 email, and 1 phone field. In MySQL/utf8mb4, EspoCRM enum/url/email/phone/varchar columns are typically inline `VARCHAR`, so declared maximum inline row width can exceed InnoDB's 8126-byte limit even before counting all fixed and nullable columns. This matches the provided log text: `Row size too large (> 8126). ... In current row format, BLOB prefix of 0 bytes is stored inline.`

## Critical

| File | Line | Issue | Recommended Fix |
|---|---:|---|---|
| `custom/Espo/Custom/Resources/metadata/entityDefs/Account.json` | 1 | `Account` is a very wide entity (287 fields). It contains many inline string-ish columns: 32 `enum`, 1 `multiEnum`, 16 `varchar`, 5 `url`, 1 `email`, and 1 `phone`. Under MySQL 8.4/InnoDB, EspoCRM rebuild may attempt `ALTER TABLE account ...` and hit `Row size too large (> 8126)`, preventing creation of new snake_case columns such as `claims_count_3yr`. | Before rebuild, verify `SHOW TABLE STATUS LIKE 'account'` and convert the table to `ROW_FORMAT=DYNAMIC` using the existing migration in `tools/migrations/pass4_account_row_format_dynamic.sql`. If rebuild still fails, reduce inline `VARCHAR` pressure by converting long URL/email/phone/varchar-like fields and low-value enum fields to `text` or moving low-usage field groups to related child entities. |
| `custom/Espo/Custom/Resources/metadata/entityDefs/Account.json` | 1866 | `claims_count_3yr` exists in metadata, so EspoCRM SELECTs include `account.claims_count_3yr`; the live log shows the DB column is missing. This causes Account GET and email import 42S22 failures until the DB column is renamed/created. | Apply `tools/migrations/pass4_one_shot_claims_count_3yr.sql` as an emergency unblock, or apply the full `tools/migrations/pass4_account_rename_mysql8.sql`, then clear cache/rebuild once row-size issues are resolved. Confirm `SHOW COLUMNS FROM account LIKE 'claims_count_3yr';`. |
| `custom/Espo/Custom/Resources/layouts/Lead/detail.json` | 15 | Layout references `opportunityAmount`, but `Lead.json` does not define that custom field in this repo. Detail rendering can show an empty/broken cell or fail depending on EspoCRM metadata merge state. | Replace with an existing Lead field (`estimatedPremium`) or add a deliberate `opportunityAmount` field definition and label if that is still required. |
| `custom/Espo/Custom/Resources/layouts/Lead/detail.json` | 15 | Layout references `campaign`, but `Lead.json` has no corresponding custom field/link definition in this repo. | Remove the cell or define the `campaign` link/field intentionally in `Lead.json` and i18n. |
| `custom/Espo/Custom/Resources/layouts/Lead/detail.json` | 16 | Layout references `industry`, but `Lead.json` has no corresponding field definition in this repo. | Remove the cell or add a deliberate `industry` field and label. |

## High

| File | Line | Issue | Recommended Fix |
|---|---:|---|---|
| `custom/Espo/Custom/Hooks/Opportunity/ClosedLostRecycleValidation.php` | 21 | Reads `xDate`. The Pass 4 Account migration maps `xDate -> x_date`, and this hook is in the migration review surface. For `Opportunity`, metadata still defines `xDate`, so this is internally consistent today, but it is a remaining camelCase API dependency and will break if Opportunity is later migrated to snake_case. | If Pass 4 is intended to apply only to Account, document Opportunity as out-of-scope. If Opportunity is included in the snake_case migration, rename metadata/layout/i18n and update this call to `x_date`. |
| `custom/Espo/Custom/Hooks/Opportunity/RecycleClosedLostToLead.php` | 27 | Reads `xDate`; same remaining camelCase dependency as above. | Same as above; use `x_date` only after the Opportunity metadata/database migration is complete. |
| `custom/Espo/Custom/Hooks/Opportunity/RecycleClosedLostToLead.php` | 32 | Reads `lineOfBusiness`; Opportunity metadata currently defines this camelCase field, but migration guidance says renamed fields should use snake_case. | If Opportunity is in migration scope, migrate `lineOfBusiness` to `line_of_business` consistently across entityDefs, i18n, layouts, PHP, and JS. |
| `custom/Espo/Custom/Hooks/Opportunity/RecycleClosedLostToLead.php` | 87 | Writes Lead field `xDate`; Lead metadata currently defines `xDate`, but this remains a camelCase write path. | If Lead is in migration scope, migrate Lead `xDate` to `x_date` and update all write/read paths together. |
| `custom/Espo/Custom/Hooks/Opportunity/EnsureNameOnSave.php` | 19 | Reads Opportunity `lineOfBusiness`; same remaining camelCase dependency. | Migrate to `line_of_business` if Opportunity is in scope, or document Opportunity camelCase as intentionally out-of-scope. |
| `custom/Espo/Custom/Hooks/Opportunity/SeedDriverFromContact.php` | 34 | Reads Opportunity `lineOfBusiness`; same remaining camelCase dependency. | Same as above. |
| `custom/Espo/Custom/Hooks/OpportunityVehicle/ValidateVinForCommercial.php` | 25 | Reads OpportunityVehicle `lineOfBusiness`; if OpportunityVehicle participates in the same naming standard, this is another camelCase dependency. | Confirm OpportunityVehicle scope. If included, migrate `lineOfBusiness` to `line_of_business` across its metadata and hooks. |
| `custom/Espo/Custom/Classes/Commission/CommissionLedgerManager.php` | 109 | Reads Opportunity `businessType`; Opportunity metadata defines `businessType`, but this remains a camelCase field dependency. | If Opportunity is in scope, migrate to `business_type` and update the mapper call. |
| `custom/Espo/Custom/Classes/Commission/CommissionLedgerManager.php` | 110 | Reads Opportunity `effectiveDate`; remaining camelCase field dependency. | If Opportunity is in scope, migrate to `effective_date`. |
| `custom/Espo/Custom/Classes/Commission/CommissionLedgerManager.php` | 112 | Reads Opportunity `commissionRate`; remaining camelCase field dependency. | If Opportunity is in scope, migrate to `commission_rate`. |
| `custom/Espo/Custom/Classes/Commission/CommissionLedgerManager.php` | 113 | Reads Opportunity `writtenPremium`; remaining camelCase field dependency. | If Opportunity is in scope, migrate to `written_premium`. |
| `custom/Espo/Custom/Classes/Commission/CommissionLedgerManager.php` | 131 | Reads Opportunity `lineOfBusiness`; remaining camelCase field dependency. | If Opportunity is in scope, migrate to `line_of_business`. |
| `client/custom/src/views/opportunity/kanban-card.js` | 73 | Client JS reads `estimatedPremium`; Opportunity metadata currently defines `estimatedPremium`, but this is a remaining camelCase model read. | If Opportunity is in scope, migrate the field to `estimated_premium` and update the kanban card. |
| `client/custom/src/views/opportunity/kanban-card.js` | 84 | Client JS reads `lineOfBusiness`; remaining camelCase model read. | If Opportunity is in scope, migrate to `line_of_business`. |
| `client/custom/src/views/opportunity/kanban-card.js` | 87 | Client JS reads `businessType`; remaining camelCase model read. | If Opportunity is in scope, migrate to `business_type`. |
| `client/custom/src/views/lead/kanban-card.js` | 40 | Client JS reads `targetDate` and `xDate`; `Lead.json` defines `xDate` but not `targetDate`. The first branch is dead in this repo, and `xDate` remains camelCase. | Replace `targetDate` with a real field such as `callbackDate` if intended, and migrate `xDate` only if Lead is in scope for snake_case. |
| `client/custom/src/views/lead/kanban-card.js` | 78 | Client JS reads `estimatedPremium`; Lead metadata currently defines this camelCase field, but it is a remaining camelCase dependency. | If Lead is in scope, migrate to `estimated_premium`; otherwise document Lead as intentionally not migrated. |

## Medium

| File | Line | Issue | Recommended Fix |
|---|---:|---|---|
| `custom/Espo/Custom/Resources/layouts/Opportunity/detail.json` | 14 | Layout references `contacts`, but `Opportunity.json` does not define a `contacts` field/link in this repo. | Remove the `contacts` cell, replace it with the existing `contact`/`account` relationship if available from base metadata, or add a deliberate `contacts` link definition. |
| `custom/Espo/Custom/Resources/layouts/Commission/listSmall.json` | 3 | Layout references `expectedAmount`, but `Commission.json` defines `estimatedCommission`, `writtenPremium`, `postedAmount`, and variance fields; no `expectedAmount` field exists. | Replace `expectedAmount` with `estimatedCommission` or define `expectedAmount` intentionally. |
| `custom/Espo/Custom/Resources/metadata/entityDefs/Opportunity.json` | 1180 | Formula reads `estimatedPremium` and `estimatedPremiumCurrency`. This matches current Opportunity camelCase metadata, but it is a non-PHP/non-JS field reference that will be missed by simple `get`/`set` audits. | Include formula scripts in any future migration checklist. If Opportunity is migrated, update these formula attributes to the new API names. |
| `custom/Espo/Custom/Resources/metadata/entityDefs/Policy.json` | 384 | Formula writes `premiumAtRisk`, a custom camelCase Policy field. It is internally consistent today but remains a camelCase custom API name. | If Policy is included in snake_case migration, migrate `premiumAtRisk` and the formula together. |
| `custom/Espo/Custom/Resources/i18n/en_US/Account.json` | 1 | Account has 149 custom fields in `entityDefs` without matching i18n field labels. Examples: `claims_count_lifetime`, `do_not_contact`, `driver_count`, `last_claim_lob`, `last_claim_status`, `next_renewal_date`, `next_renewal_lob`, `policyAutoActive`, `policyHomeActive`. | Add labels for active custom fields. Do not remove `[OLD]` labels/legacy fields that are intentionally retained. |
| `custom/Espo/Custom/Resources/i18n/en_US/Account.json` | 1 | Account i18n contains six labels with no backing Account field: `annualPremiumConverted`, `annualPremiumCurrency`, `annualRevenueConverted`, `annualRevenueCurrency`, `estimatedPremiumConverted`, `estimatedRevenueConverted`. | Remove these labels only if they are not supplied by base EspoCRM currency companions in the runtime metadata; otherwise document them as generated companion labels. |
| `custom/Espo/Custom/Resources/i18n/en_US/Policy.json` | 1 | Four custom linkMultiple fields lack labels: `activityLogs`, `commissions`, `renewals`, `renewedFrom`. | Add i18n labels if these panels/relationships are user-visible. |
| `custom/Espo/Custom/Resources/i18n/en_US/Lead.json` | 1 | Six custom fields lack labels: `currentMedicarePlan`, `dateOfBirth`, `medicarePartADate`, `medicarePartBDate`, `source`, `t65AlertSent`. | Add labels or mark hidden/non-user-facing fields consistently. |
| `custom/Espo/Custom/Resources/i18n/en_US/Opportunity.json` | 1 | Thirty-nine custom Opportunity fields lack labels, including checklist fields (`chk*`), GL/WC fields, `policyStubId`, `policyStubStatus`, `propAddress`, `quotes`, and `umbrellaLimit`. | Add labels for visible fields; hide or document internal-only fields. |
| `custom/Espo/Custom/Resources/i18n/en_US/Contact.json` | 1 | Eight custom Contact fields lack labels: `accounts`, `activityLogs`, `clientType`, `commissions`, `dateOfBirth`, `householdRole`, `policies`, `renewals`. | Add labels if visible in layouts/panels. |
| `custom/Espo/Custom/Resources/i18n/en_US/Commission.json` | 1 | Ten custom Commission fields lack labels: `commissionNotes`, `commissionType`, `expectedPaymentDate`, `overdueFlag`, `paymentReceivedDate`, `postedAmount`, `producer`, `status`, `varianceAmount`, `variancePercent`. | Add labels for list/detail/reporting fields. |
| `tools/migrations/pass4_account_rename_mysql8.sql` | 30 | `agentOfAgencyCode -> agent_of_agency_code` is in the rename list, but `backup_20260329/Account.json` and current `Account.json` do not show `agentOfAgencyCode` as a field. The migration is idempotent and will skip it if absent, but it is likely not a real historical DB column from this repo snapshot. | Leave as harmless if supporting another deployment history; otherwise remove from the generated mapping to reduce operator confusion. |
| `tools/migrations/pass4_account_rename_mysql8.sql` | 31 | `agentOfRecordDate -> agent_of_record_date` is in the rename list but not present in the available Account metadata snapshots. | Same as above. |
| `tools/migrations/pass4_account_rename_mysql8.sql` | 38 | `carrierCode -> carrier_code` is in the rename list but not present in the available Account metadata snapshots. | Same as above. |
| `tools/migrations/pass4_account_rename_mysql8.sql` | 46 | `daysToRenewal -> days_to_renewal` is in the Account rename list; current metadata defines `days_to_renewal`, but available backup metadata should be checked against the live DB before assuming `daysToRenewal` exists. | Before full migration, run the script's information_schema checks or manually confirm old/new column existence. |
| `tools/migrations/pass4_account_rename_mysql8.sql` | 201 | The full migration contains no `DROP COLUMN`; the only post-call drop is `DROP PROCEDURE IF EXISTS esp_pass4_rename_account_columns`, which is not destructive to data. | No change required. Keep communicating this distinction in runbooks. |

## Low

| File | Line | Issue | Recommended Fix |
|---|---:|---|---|
| `custom/Espo/Custom/Resources/metadata/entityDefs/Account.json` | 430 | Custom Account entityDefs still contain many camelCase API names (143 custom camelCase fields). This conflicts with a strict "no camelCase remaining" standard, but many are household/personal-lines legacy fields and may be intentionally retained with `[OLD]` labels. | Maintain an explicit allowlist of intentionally retained legacy fields and migrate only the Pass 4 target list. Do not remove `[OLD]` fields from DB/entityDefs. |
| `custom/Espo/Custom/Resources/metadata/entityDefs/Policy.json` | 309 | Policy has 15 custom camelCase fields (`momentumPolicyId`, `commissionAmount`, `premiumAtRisk`, etc.). | Decide whether Policy is in the current migration scope. If not, document it as future work. |
| `custom/Espo/Custom/Resources/metadata/entityDefs/Lead.json` | 8 | Lead has 15 custom camelCase fields (`insuranceInterest`, `estimatedPremium`, `xDate`, etc.). | Same as above. |
| `custom/Espo/Custom/Resources/metadata/entityDefs/Opportunity.json` | 13 | Opportunity has 134 custom camelCase fields. | Same as above; avoid partial field renames without SQL/data migration. |
| `custom/Espo/Custom/Resources/metadata/entityDefs/Contact.json` | 8 | Contact has 26 custom camelCase fields. | Same as above. |
| `custom/Espo/Custom/Resources/metadata/entityDefs/Commission.json` | 8 | Commission has 20 custom camelCase fields. | Same as above. |
| `tools/migrations/pass4_account_row_format_dynamic.sql` | 1 | The row-format migration is marked optional, but the live log suggests row format/table width is the active rebuild blocker. | Promote this to a preflight step in deployment instructions for MySQL 8.4 before running rebuild on the wide Account table. |

## Rebuild Failure Triage Notes

1. **Primary candidate: Account row width.** The live error text directly points to InnoDB row size. The repo already acknowledges this in `tools/migrations/pass4_account_rename_mysql8.sql` lines 12-13 and provides `tools/migrations/pass4_account_row_format_dynamic.sql`.
2. **Secondary symptom: missing `claims_count_3yr`.** Once metadata includes `claims_count_3yr`, all Account SELECTs expect the DB column. If rebuild cannot add/rename the column because row-size fails first, Account views and email import will keep failing with 42S22.
3. **No duplicate JSON keys or missing `type` values were found in the reviewed entityDefs except base-style `Opportunity.name`, which is a deliberate override container and not a rebuild blocker by itself.
4. **No destructive account-column SQL was found.** `pass4_account_rename_mysql8.sql` uses information_schema guards and `RENAME COLUMN` only when old exists and new does not; it does not drop account data columns.

## Suggested Fix Order

1. Backup/snapshot the DB.
2. Verify/convert `account` row format to `DYNAMIC` and retry only on a non-live/tested path first.
3. Run the one-shot `claims_count_3yr` migration if immediate Account GET/email import unblock is required.
4. Run the full guarded Pass 4 Account rename migration.
5. Clear cache and rebuild.
6. Address layout cells pointing at missing fields.
7. Decide whether Lead/Opportunity/Policy/Contact/Commission camelCase fields are intentionally out-of-scope or need a separate, fully planned snake_case migration.
