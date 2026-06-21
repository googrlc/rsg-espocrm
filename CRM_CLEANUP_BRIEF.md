# RSG EspoCRM — Consolidated Cleanup & Remediation Brief

**Date:** 2026-06-17
**Scope:** Account, Policy, Opportunity, Renewal, Commission (plus Contact/Lead consolidation map)
**Status:** Read-only audit complete. No live records or files have been modified. Every number below is from a live query against the `espocrm-ts` box (`app-mysql-1`), filtered `deleted=0`.

---

## 1. Executive Summary

Five EspoCRM modules were audited live and cross-checked against the prior brief. The prior brief's structural diagnosis holds, but **many of its headline numbers were wrong and several of its "destructive cleanup" recommendations are unsafe as scoped.** This brief supersedes it.

**The single biggest systemic problem is commission rate/amount handling.** Rate and amount are stored and computed four different ways across four entities, with two contradictory normalization functions and three live unit conventions (Policy stores *percent*, Renewal stores *fraction*, Commission stores a *mix* of both). One function fabricates a 0.10 house-default rate; the other refuses to fabricate. The result: 397 Policy rows carry a commission amount with no rate, 591 of 648 Commission rows have a NULL rate, and Account CLV rolls up from the wrong source. This is the highest-priority build item.

**The data is far cleaner than the prior brief assumed in one respect and far dirtier in another.** Account duplicates have already been cleaned (0 duplicate `momentum_client_id` groups now). But the Policy/Commission relink churn left **445 of 648 Commission rows pointing at policy IDs that no longer exist** and **468 with no account** — these must be repaired before any rollup can be trusted.

**The destructive recommendations from the prior brief mostly failed adversarial verification:**
- "Merge 84 duplicate policy groups (224 records)" — **REJECTED.** 69 of 84 groups are legitimate renewal chains. Only ~15 groups (~32 records) are genuine same-term collisions, and 6+ of those have conflicting premium/date data requiring NowCerts adjudication.
- "Drop 168 dead Account columns" — **REJECTED as scoped.** The count sweeps in the core `deleted` column, 24 working default-valued flags, and entire GB/Life/Medicare feature blocks the brief itself lists as live account types.
- "Merge duplicate-name pairs" — **MIXED.** Account pairs (Micah Gray, Shamira Douglas) cleared as safe. Opportunity "Jimmy Barfield" and "Atlas" cleared/flagged individually; "AF1-Ca" needs review.
- "Delete 13 Unknown Client renewals" — **REJECTED.** These are 13 real expiring clients whose renewal→policy chain was broken by a re-sync; backfill, do not delete.

**What IS safe and high-value right now:** a large batch of metadata/layout/i18n/code fixes (Bucket A) that de-clutter the UI, add missing labels, and unify the commission code path — with zero data loss. A small, well-bounded set of test-record and stub-account deletions cleared adversarial review (Bucket B). And the commission rollup redesign plus the Part C Household Profile build (Bucket C).

**Recommended first move:** Bucket A safe code/metadata fixes, then the Commission relink data repair (prerequisite for everything commission-related), then the confirmed deletions, then the builds.

---

## 2. Per-Module Findings (verified numbers only)

Severity and correction notes reflect the live re-audit. Where the prior brief was wrong, the live evidence is stated explicitly.

### 2.1 Policies (`policy`) — 409 live rows

| Prior claim | Verdict | Live evidence |
|---|---|---|
| ~409 policies | **Confirmed** | `COUNT(*) WHERE deleted=0 = 409` |
| 383/409 (94%) have amount but null rate | **CORRECTED** | Live: `commission_amount NOT NULL AND commission_rate NULL = 397 (97%)`. The 383 figure matches no variant (amount>0 & rate null = 362; amount NOT NULL total = 408). |
| Zero policies have both amount and rate | **REFUTED** | Live: **11** rows have both (10 rows rate=10, 1 row rate=11.25); all 11 amounts = premium×rate/100. |
| 84 dup policy numbers / 224 records (~55%) | **Confirmed (raw count)** | 84 groups, 224 members (54.8%), 0 null/blank policy numbers. **But see Bucket B — only ~15 groups are genuine dupes; 69 are renewal chains.** |
| Some dups differ only by LOB casing | **REFUTED** | 0 casing-only collisions at any level. 4 groups differ by LOB but those are label/normalization noise, not casing twins. |
| `line_of_business == line_of_business_raw` 100% | **Confirmed** | `SUM(lob <=> lob_raw) = 409/409`, both non-null all rows. Normalization map (GL→General Liability, Auto→Personal Auto, Home→Homeowners) is a no-op on current data. |
| daysRemaining / policy_health / statusLabel read-only computed | **Confirmed** | entityDefs marks all three readOnly. Live: policy_health 409/409, status_label 409/409, days_remaining 339/409 (70 null = null expiration_date). |
| A status/commission derivation fix was deployed | **Confirmed, but does not remediate existing data** | `PolicyAccountSync` now sets `commissionAmount = round(premium*normalizedRate,2)` and NULL when no rate ("do not fabricate"). **But it only fires on entity save**; `BackfillPolicyHealth` SKIP_HOOKS and writes only `policy_health`. Only 1 policy was modified 2026-06-17. The 397 amount-without-rate rows persist. |

Additional live findings: **40 orphaned-commission policies** (commission_amount populated, no valid account link); **38 of 392** policies with commission_amount have an orphaned/missing account link (leaks the Policy→Account rollup); **1 corrupt policy_number** `2.02521E+11` (Excel scientific-notation truncation); **7 non-canonical LOB strings** (data-quality, not casing dups).

### 2.2 Accounts (`account`) — 639 live rows

| Prior claim | Verdict | Live evidence |
|---|---|---|
| 279 fields | **CORRECTED** | 261 DB columns / 203 logical entityDefs fields. Neither is 279. |
| gb_* block (27 fields) all-zero | **CORRECTED (block dead, count wrong)** | gb_* = **23 DB cols / 19 fields**, all 0 across 639 rows. |
| gap_* block (15 fields) all-zero | **CORRECTED (block dead, count wrong)** | gap_* = **14 DB cols / 13 fields**, all 0 across 639 rows. |
| gb+gap = 42 dead fields | **CORRECTED** | gb(23)+gap(14)=37 DB cols all 0; as logical fields 19+13=32. Block 100% dead; 42 is wrong. |
| clv_with_cross_sell == clv_projected in ~564 rows | **CORRECTED** | Equality confirmed (0 differ), but **only 81 accounts** have CLV populated, not 564. The 564 figure belongs to `clv_retention_rate_applied` / `clv_last_calculated` (sibling columns). |
| Retention dist 0.4(481)/0.65(76)/0.85(5)/0.95(2) | **CORRECTED** | Live: 0.4=481, **0.65=75**, **0.85=6**, 0.95=2, **NULL=75** (the brief omitted the 75 NULLs). |
| 4 Inactive accounts carrying active premium | **Confirmed** | Wanda Tucker (65606), Velma Pearson (14844), Zackeeia Miller (7336.1), Sclafani Waste Services (1943). |
| Duplicates rare: 2 names / 4 records | **Confirmed** | "Micah Gray" (2), "Shamira Douglas" (2). **0 duplicate momentum_client_id groups** (cleaned since the 2026-05-29 inventory). |
| fein on 26, DOB on 82 | **Confirmed** | fein=26; date_of_birth=82 (primary_dob=0). |
| Redundant field pairs (policy_count_active/active_policy_count; next_x_date/next_renewal_date; next_x_date_lob/next_renewal_lob; standard `type`/`account_type`) | **Confirmed** | Counts in Appendix C. Live pair counts shifted slightly: pca=142, apc=143, nxd=95, nrd=91. |
| account_score vs score_total match in 1/639 | **Confirmed** | account_score pop=19, score_total pop=639; they genuinely diverge — **not** a clean dup. |

**154 non-currency columns are entirely 0-populated** (169 incl. _currency helpers and all-empty-string/zero). Full list in Appendix A.

### 2.3 Opportunity (`opportunity`) — 49 live rows

| Prior claim | Verdict | Live evidence |
|---|---|---|
| ~49 opportunities | **Confirmed** | 49 live (68 total incl. 19 soft-deleted). |
| 214 fields | **Confirmed** | Effective merged metadata = 214. (DB table = 202 cols; custom-only entityDefs = 157 declared.) |
| 88 LOB-specific question/checklist fields | **CORRECTED (undercount)** | Actual = **113 logical fields** (100 underwriting + 13 chk*), or 134 DB cols. Emptiness confirmed and understated — **0 Group Benefits and 0 Medicare opps** exist. |
| 13-field chk* register with ~0 usage | **Confirmed** | SUM of each chk* field = 0 across all 49. Total usage = 0. |
| chk* render as raw names (no i18n) | **Confirmed** | None of the 13 appear in `i18n/en_US/Opportunity.json`; they ARE wired into clientDefs dynamicLogic + detail layout, so they show raw camelCase. |
| amount 0/49; estimatedPremium/writtenPremium hold real money | **Confirmed** | `amount IS NULL` all 49 (formula-mirrored from estimatedPremium, never persisted). estimated_premium 22/49; written_premium 24/49 ($111.56–$25,000, avg $4,870.88). |
| Three carrier fields | **Confirmed** | carrier=19, current_carrier=10, target_carrier=11. Plus per-LOB carrier dupes compound the overlap. |
| stage == last_stage in 42/49 | **Confirmed** | The 7 differences are all Closed Lost retaining their last open stage (expected Espo behavior). |
| 3 duplicate names / 6 records | **Confirmed** | AF1-Ca (2), Atlas Portection Commercial Auto (2 — "Portection" is a typo of Protection), Jimmy Barfield (2). |

### 2.4 Renewals (`renewal`) — 87 live rows

| Prior claim | Verdict | Live evidence |
|---|---|---|
| ~87 renewals | **Confirmed** | `COUNT(*) WHERE deleted=0 = 87`. |
| 3 TEST records; Sclafani note "literally says test" | **CORRECTED** | The named trio IS correct (Darren Boykin, Sclafani, Zakir — all "ZZ TEST RECORD … safe to delete"). But the "literally says test" detail was mis-attributed: that exact-string `test` note belongs to a **4th record the brief missed** — Dream Chaser Trucking - Other Renewal (`69eba399b011a486b`). |
| 13 Unknown Client renewals, no account | **Confirmed (but NOT safe to delete — see Bucket B)** | 13 rows `name LIKE 'Unknown Client%'`, all `account_id IS NULL`, same set. |
| 81/87 stuck in "Identified" | **Confirmed** | Identified=81, Renewed-Won=3, Lost=2, Proposal Sent=1. |
| newPolicy and contact linked on 2 each | **Confirmed** | new_policy_id NOT NULL = 2; contact_id NOT NULL = 2. |
| 52/87 have rate but no expected_commission | **Confirmed** | rate non-zero AND expected_commission null/0 = 52 (0.10→25, 0.12→27). Only 2 records have expected_commission at all. |
| Lost/Won Details panels are empty headers | **REFUTED** | `layouts/Renewal/detail.json`: Lost Details holds `lost_reason` (enum), Won Details holds `newPolicy` (link). Both panels have a valid field. **No fix required.** |

### 2.5 Commissions (`commission`) — 648 live rows

| Prior claim | Verdict | Live evidence |
|---|---|---|
| ~648 commissions | **Confirmed** | 648. |
| 591/648 (91%) null rate | **Confirmed** | commission_rate IS NULL = 591 (91.2%). |
| 552 have written premium but zero/blank estimated commission | **Confirmed** | written_premium>0 AND estimated_commission null/0 = 552. (estimated_commission is never NULL — defaults to 0; 596 rows are 0, 52 are >0.) |
| Rate units inconsistent | **Confirmed** | Distinct rates: 0.1(×46), 0.12(×8), 11.5(×1), 0.1125(×1), 10(×1). 2 rows store whole-percent units → would compute ~100× high. |
| posted_amount / variance_amount 0 across all 648 | **Confirmed** | Both NULL on 648/648. |
| reconciliation_status "Unreconciled" everywhere (all 648) | **CORRECTED** | 647 Unreconciled, **1 Reconciled** (`6a0e0a9a40238cd73`) — which is also a rate outlier (11.5). |
| Parent linkage: 648 policy, ~16 opp, ~3 renewal | **Confirmed** | policy_id=648, opportunity_id=16, renewal_id=3. |

**Blocking integrity facts (live):** 445/648 (69%) reference a `policy_id` that no longer exists; 468/648 (72%) have no `account_id`; 8 rows have NULL `ledger_key` and 8 share duplicate ledger_keys; 477/648 are auto-"Overdue", 0 Posted (lifecycle never driven to Posted).

---

## 3. Action Buckets

### BUCKET A — SAFE auto-fixes (metadata / layout / i18n / code; no data loss)

These remove no data, break no FK/compute, and are reversible. They can be applied without per-record confirmation. (Rebuild + cache clear on the `espocrm-ts` box is required after metadata/JS changes.)

**A1. Add the 13 missing Opportunity chk* i18n labels.**
File: `custom/Espo/Custom/Resources/i18n/en_US/Opportunity.json`
Add labels for: chkQuoteSubmitted, chkProposalSent, chkSignedAppReceived, chkBound, chkUnderlyingLinked, chkUnderlyingConfirmed, chkMvrsPulled, chkDecPageDelivered, chkScopeOfAppt, chkPlanPresented, chkAppSubmitted, chkCmsConfirmation, chkWelcomeLetter. Fixes raw-camelCase display in the detail layout.

**A2. Hide the 154 zero-populated Account fields (+14 _currency helpers) from layouts.** (Hide only — do NOT drop columns; the drop is Bucket B and was rejected.)
File: `custom/Espo/Custom/Resources/metadata/entityDefs/Account.json` (visibility) + Account layout files. Prioritize the fully-dead gb_* (19) and gap_* (13) blocks. **Caveat from adversarial review:** do NOT hide the 24 populated default-flag columns (gap_umbrella, gap_life, gap_medicare, intel_pack_run, score_alert_sent, rate_increase_flag, renewal_quote_received, referrals_given, etc.) or the GB/Life/Medicare feature fields that are dynamic-logic-gated on `account_type` — they are intentional, awaiting their LOB. Full hide-eligible list = the 154 in Appendix A minus those flag/feature fields.

**A3. Extend Policy LOB normalization to fold the 7 non-canonical strings.**
File: `custom/Espo/Modules/RsgCore/Classes/Policy/PolicyAccountSync.php` (`normalizeLineOfBusinessValue` match() map — currently only GL/Auto/Home). Add: PersonalAuto→Personal Auto, work comp→Worker's Compensation, personal lines→(canonical), renters→Renters, Life insurance→Life, Builders risk / Home Construction→Builders Risk, Cyber/Network Liability→Cyber Liability.

**A4. Unify the commission rate function — delete the 0.10 house default.**
File: `custom/Espo/Custom/Classes/Commission/CommissionLedgerManager.php` (`normalizeRateOrNull`, ~line 276). Make it null-in→null-out to match `PolicyAccountSync::normalizeRate`. This 0.10 default is the single biggest source of fabricated amounts and directly contradicts the deployed Policy rule. Code-only.

**A5. Add a `0 ≤ rate ≤ 1` validation/clamp on commission rate writes.**
File: `custom/Espo/Custom/Resources/metadata/entityDefs/Commission.json` (+ the BeforeSave path). Replaces the brittle `>1 ? /100` runtime heuristic with normalize-on-write so the stored value is always a canonical fraction. Prevents future whole-percent entries computing ~100× high.

**A6. Reconcile redundant Account field pairs in sync/app code (write one, stop writing the twin).**
File: `custom/Espo/Custom/Resources/metadata/entityDefs/Account.json` + sync code. Canonical picks: `active_policy_count` over `policy_count_active`; `next_x_date` over `next_renewal_date`; `next_x_date_lob` over `next_renewal_lob`; `clv_projected` over `clv_with_cross_sell`; custom `account_type` over standard `type`. Non-destructive (hide the twin in layout; stop populating). Do NOT touch `account_score`/`score_total` — they genuinely diverge.

**A7. Consolidate/hide overlapping Opportunity premium + carrier + dead-block fields in layouts.**
Files: `custom/Espo/Custom/Resources/layouts/Opportunity/detail.json`, `custom/Espo/Custom/Resources/metadata/entityDefs/Opportunity.json`, `custom/Espo/Custom/Resources/layouts/Opportunity`. Keep estimatedPremium + writtenPremium; keep `amount` read-only/hidden (formula-mirrored). Display the 3 top-level carrier fields; hide per-LOB carrier dupes. Hide `c_renewal_date` (0). Layout-only.

**A8. Backfill Renewal `expected_commission` for the 52 rate-bearing rows (data-quality fill).**
`expected_commission = renewal_premium * commission_rate` (or current_premium where renewal_premium absent). Read-then-write script; non-destructive (adds a value to a null/zero field). Present sample before/after to owner.

**A9. Investigate/repair the 40 orphaned-commission Policy rows + fix corrupt policy_number.**
Files: `custom/Espo/Custom/Classes/Account/AccountValueManager.php`, `custom/Espo/Modules/RsgCore/Classes/Policy/PolicyAccountSync.php`. Re-link via insuredMomentumId/account_id so commission rolls up to `Account.total_active_premium`. Restore the full digits of `2.02521E+11` from AMS.

**A10. Normalize the 2 whole-percent Commission rate outliers** (`6a2991ac86793a8d5` → 0.10, `6a0e0a9a40238cd73` → 0.115). Technically a write to live data — present before/after and get a one-line confirm; trivial and low-risk, listed here because it's a deterministic correction, not a judgment call.

**A11. (Optional) Renewal Lost/Won panels need no fix** — they already hold `lost_reason` and `newPolicy`. Only act if richer capture is desired (add lost_to_competitor + decision_date / renewal_premium + commission_delta).
File: `custom/Espo/Custom/Resources/layouts/Renewal/detail.json`.

---

### BUCKET B — DESTRUCTIVE / data operations (each CONFIRM-REQUIRED)

Adversarial verification verdicts are binding. Items split into **cleared-safe** (adversarial pass cleared as safe-to-remove) and **needs human review** (rejected or conditional).

#### B-SAFE — cleared by adversarial review (still confirm before running)

**B1. Delete the 3 "ZZ TEST RECORD" renewals.** ✅ confirm-required
- `6a1cd10456277e7c4` Darren Boykin - Other Renewal
- `6a1cd1078c1b9d179` Sclafani Waste Services - GL Renewal
- `6a1cd107bea074a8c` Zakir Hussain - Commercial Package Renewal
All created 2026-06-01 by the `hermes_api` user, stage Identified, never worked. Linked policies are independent (own momentum_policy_id) and are NOT deleted. The 1 task + 1 note on each are auto-generated scaffolding.

**B2. Delete the 4th test renewal** `69eba399b011a486b` Dream Chaser Trucking - Other Renewal. ✅ confirm-required
Note literally = "test", created 2026-04-24 by admin (Gretchen). LOB/premium/expiration all NULL. Its linked policy is a deleted empty stub. It hangs off account "Tuff Stuff" (`69bde6d091c5e3098`), which has 2 OTHER real Commercial Auto renewals that are unaffected.

**B3. Merge the 2 duplicate-name Account pairs (merge-in-place onto the momentum-synced survivor).** ✅ confirm-required
- Micah Gray: keep survivor `69bde6d06ff54a8c0` (momentum_client_id, 2 policies, 1 contact, 5 notes); retire stub `6a16fc618a7472181` (api_hermes, 0 policies, audit-note only). Same email + phone → confirmed same person.
- Shamira Douglas: keep survivor `6a173bf660434f2ad` (momentum_client_id, 2 policies, 1 contact); retire stub `6a16fc19cc3ce9793` (api_hermes, 0 policies, audit-note only). Same phone → confirmed.

**B4. Merge the Atlas Opportunity pair.** ✅ confirm-required (with care)
Keep `6a0215aaaaa171a90` (older, 3 stream entries), retire `6a0217673a4e111ad`. Same account, same contact, identical $6,000 est, both Closed Lost, created 7 minutes apart by Lamar. No linked policies/commissions/tasks. (Opp name "Portection" is a typo; account name is correct.)

**B5. Retire 27 of the 32 dead Opportunity fields.** ✅ confirm-required
Files: `custom/Espo/Custom/Resources/metadata/entityDefs/Opportunity.json`, `custom/Espo/Custom/Resources/metadata/clientDefs/Opportunity.json`, `custom/Espo/Custom/Resources/layouts/Opportunity/detail.json`.
**Unambiguously safe (27):** the 13 chk* register + the 7 gb_* + the 7 med_*/medicare_eligible/medicare_part_b. 0/49 usage, no live writer (verified against all 5 Opportunity hooks, beforeSaveScript, n8n, and rsg-hermes), self-contained dynamicLogic only.

#### B-REVIEW — rejected or conditional (needs human review / NowCerts adjudication before any deletion or merge)

**B6. Policy dedup-merge of "84 groups / 224 records" — REJECTED as a class. NEEDS HUMAN REVIEW.** ❌
69 of 84 groups are legitimate renewal/term chains (distinct effective/expiration dates, distinct momentumPolicyId per term) and must NOT be touched. Only **15 groups (~32 records)** are genuine same-term collisions (Appendix B). Of those 15, at least 6–8 have intra-group premium/date conflicts requiring NowCerts adjudication (`862047701`, `862392084`, `6259704770`, `8035888767`, `865173784`, `871502820`, plus `975931554` and `GP 8812406` as distinct-momentum uncertainties). The only low-risk merges are the clean NULL-momentum copies (`02-CPK-116548-01`, `25SSJ231CM2155`, `100023140200`, `F4118564`, `Y017227627`) — and even those require **reparenting the dupe's Commission/Renewal child rows to the momentum-ID-bearing survivor before deletion**, or commission ledger data is silently orphaned. The prior brief's "4 legitimately-distinct LOB groups" caveat is inaccurate; discard it.

**B7. Drop 168 dead Account columns — REJECTED as scoped. NEEDS HUMAN REVIEW.** ❌
File: `custom/Espo/Custom/Resources/metadata/entityDefs/Account.json`.
The raw emptiness count is real, but the drop list sweeps in: the core `deleted` column; 24 fully-populated (639/639) default-valued boolean/int flags awaiting their trigger; and the entire gb_*/life_*/medicare_* feature blocks for LOBs RSG hasn't grown into (book = 387 Personal + 241 Commercial, only 1 Group Benefits, 0 Life, 0 Medicare). These fields are dynamic-logic-gated on `account_type`, referenced by live hooks (SyncCrossSellPlaybooks, RefreshAccountMetrics, SendAccountEnrichmentWebhook, RecalculateAccountScores, BackfillAccountValue), and referenced across 4–14 non-backup repo files each. No DB backup confirmed (the recommendation's own precondition is unmet). **Hiding from layouts (Bucket A2) is the reversible step; a permanent drop must be a hand-audited subset excluding all of the above, and only after a full mysqldump.**

**B8. Merge "AF1-Ca" Opportunity pair — NEEDS HUMAN REVIEW (do not blind-merge).** ⚠️
`6a2872e6eece70e80` (Proposal Presented, $11,721, State Farm, account-linked) vs `6a28771147d2e1344` (Quotes Complete, $16,530, target Progressive, no account link). Different premiums, stages, carriers; only one is account-linked. Looks like two distinct quote tracks. If merged, preserve the account link from record 1 and the Progressive/$16,530 data from record 2. Manual reconciliation required.

**B9. "Jimmy Barfield" Opportunity pair — DO NOT MERGE.** ❌
`6a2859c125848ae31` (policy 6260177321) and `6a2867c1c76c28152` (policy 6260179616) are **two separate bound Geico auto policies**, both Closed Won, both effective 2026-06-10, freshly bound and pre-AMS-sync (verified: neither number exists in NowCerts). Merging would permanently destroy one bound policy number.

**B10. The 13 "Unknown Client" renewals — DO NOT DELETE. Backfill/re-link instead.** ❌
These are 13 real expiring Personal/Home clients (Paul Scaglione, Gloria Freeman, Lula Middlebrook, Shallon Brown Johnson, Sarah L. Banks, Caleb Weeks, Mark Harrison, Danielle Coates, Anissa Tawiah, Lena Dawson, Assured Lolita Trucking, etc.) whose `account_id` went NULL because their stored `policy_id` points to a hard-deleted policy from a re-sync. Each maps to an existing policy by carrier + expiration_date + premium. All created 2026-03-23 by `api_update`. Deleting them erases real renewal-pipeline records — the exact retention work that is the agency's headline priority. IDs in Appendix D.

**B11. The 5 Medicare-sync-target Opportunity fields — CONDITIONAL.** ⚠️
`partAEffectiveDate`, `partBEffectiveDate`, `medicareHICN`, `drInformation`, `rxInformation`. All 0-populated with no live writer (data-safe to drop today), BUT `field-reference/nowcerts-to-espocrm-mapping.md` (lines 45-49, 168-182) documents a *designed* NowCerts→Opportunity Medicare sync targeting these exact 5 columns (not yet implemented). `medicareHICN` is also flagged sensitive ("will be purged"). Dropping them is a deliberate decision to abandon/redesign that planned sync — proceed only with sign-off, and update/delete that mapping-doc section in the same change.

**B12. Drop the 168 dead Account columns / 37 gb+gap columns at the SQL level — DEFER.** ⚠️
Even the fully-dead gb_*/gap_* blocks should be dropped only after Bucket A2 layout-hiding is confirmed stable in production and a full mysqldump is taken. Destructive-but-harmless on empty columns, but irreversible.

---

### BUCKET C — BUILD items

**C1. Commission rate-unit unification + atomic rate/amount derivation.**
- Enforce **FRACTION** (decimal, 0.10 = 10%) as the one true unit on every rate field across Policy, Renewal, Commission, Opportunity. Rationale: it's the form the math already assumes (`estimatedCommission = premium * rate`, no /100), and the majority live convention (Renewal 54/54 fraction, Commission 55/57 fraction). Only Policy (11 rows) and 2 Commission rows are outliers.
- Migrate the violating rows (÷100): the **11 Policy rows** (Appendix A — "policies_with both") and the **2 Commission rows** (`6a2991ac86793a8d5`, `6a0e0a9a40238cd73`).
- Compute rate and amount **atomically in one BeforeSave**, amount null-when-rate-null (never fabricated). A null rate yields null amount + a visible "rate missing" flag.
Files: `custom/Espo/Custom/Classes/Commission/CommissionLedgerManager.php` (lines 264-282), `custom/Espo/Modules/RsgCore/Classes/Policy/PolicyAccountSync.php` (lines 77-83, 277-285), entityDefs Policy/Opportunity/Renewal/Commission.

**C2. Commission backfill job for the 397 Policy amount-without-rate rows.**
`BackfillPolicyHealth` does NOT do this (SKIP_HOOKS, writes only policy_health). Build a dedicated job that re-saves each Policy (or mirrors PolicyAccountSync commission logic) under the no-fabrication rule. **Decision required:** backfill commission_rate from AMS, OR null out the 397 fabricated amounts.
Files: `custom/Espo/Modules/RsgCore/Classes/Policy/PolicyAccountSync.php`, `custom/Espo/Custom/Jobs/BackfillPolicyHealth.php`.

**C3. Make Commission the source of record; Policy/Opp/Renewal rate+amount become read-only rollups.**
- Mark `commission_rate`/amount fields **readOnly** on Policy, Opportunity, Renewal in entityDefs (Policy.commissionAmount already is). Today Policy.commission_rate, Opportunity.commissionRate/estimatedCommission, Renewal.commission_rate/expected_commission are editable — that's what lets conventions drift.
- Rollup via hook-driven recompute: on Commission afterSave, recompute the parent's fields from its `commissions` collection (all four parents already have the `commissions` hasMany link) and write back with SKIP_HOOKS. Reuses the existing RefreshAccountValue/RefreshAccountMetrics pattern.
- Repoint `AccountValueManager::getAnnualCommission` (line 109) to sum `estimatedCommission` over the Account's valid-linked `commissions` instead of `Policy.commissionAmount`.
**Blocking prerequisites (must finish first):** re-link the 445 orphaned-policy / 468 no-account Commission rows; fix the 8 null/8 duplicate ledger_keys. Otherwise the rollup double-counts or loses rows.

**C4. Build the commission reconciliation/posting pipeline.**
posted_amount, variance_amount, variance_percent are NULL on all 648; posting_source/ledger_* are unwired; 0 rows ever reached Posted. Build the AMS-Download → Supabase → auto-post flow described in the postingSource tooltip so the Posted/variance lifecycle populates.
File: `custom/Espo/Custom/Resources/metadata/entityDefs/Commission.json`.

**C5. Part C — Conditional Business / Household Profile + Tab Reorder.** (Full spec below.)

---

## 4. Part C — Household Profile + Tab Reorder (Build Spec)

### 4.1 Goal & grounding

Target tab order: **Overview → Business Profile / Household Profile → Contacts → Policies → Activity → Group Benefits → Internal.** The 2nd tab is conditional: **Business Profile** (Commercial Lines / Group Benefits) **or** Household Profile (Personal Lines / Medicare / Life Insurance), never both — exactly one renders, the other is fully removed (no half-rendered empty fields).

`account_type` enum options: `"" | Prospect | Commercial Lines | Personal Lines | Group Benefits | Medicare | Life Insurance | Carrier | MGA`.

**Load-bearing coupling:** tab visibility is hard-coded by index in `client/custom/src/views/account/record/detail.js` (`BUSINESS_PROFILE_TAB`, `GROUP_BENEFITS_TAB`, `bottomPanelTabMap`). Any tab insertion/reorder MUST be mirrored there or panel-sync breaks. `dynamicLogic.panels` hides panel *bodies*; the JS hides the clickable *tab pill*. Both layers are required.

### 4.2 Tab-order change

**`layouts/Account/detail.json`:** move the entire Business Profile block (`opportunitiesTab` tabBreak + `ai_assessment`, `businessIdentity`, `bbbReputation`, `fleetOperations`, `riskUnderwriting`, `coverageGapsCl`) to sit immediately after Overview (`carrierDetails`) and before `contactsAnchor`. Insert the new **Household Profile** tabBreak block in the same slot (index 1), right after the Business Profile block. Both tabBreaks occupy slot 1; only one renders via dynamicLogic (the existing `coverageGapsPl`-shares-Business-Profile-tab pattern, generalized). Relocate `coverageGapsPl` into the Household block.

Resulting top-level sequence:
1. Overview (tab 0, unchanged): accountInfo, coverageOverview, clientValue, carrierDetails
2. **Business Profile** (tab 1, moved up): opportunitiesTab + 6 CL panels
3. **Household Profile** (tab 1, same slot, NEW): householdProfile + householdMembers/Residence/VehiclesDrivers/Financial + coverageGapsPl + householdRetention
4. Contacts (tab 2) · 5. Policies (tab 3) · 6. Activity (tab 4) · 7. Group Benefits (tab 5) · 8. Internal (tab 6)

**`client/custom/src/views/account/record/detail.js` (MANDATORY):** rewrite header map; `bottomPanelTabMap`: contacts=2, policies/renewals=3, activityLogs/emails/meetings/calls/tasks=4, opportunities=1 (stream/clientNotes stay 0; cases/commissions stay 6). Constants: `BUSINESS_PROFILE_TAB=1`, add `HOUSEHOLD_PROFILE_TAB=1`, verify `GROUP_BENEFITS_TAB=5`. `syncConditionalTabs()`: Commercial Lines → Business Profile tab + hide Household + hide GB; Group Benefits → Business Profile + GB; Personal Lines/Medicare/Life Insurance → Household Profile + hide Business Profile + hide GB; everything else (Prospect/Carrier/MGA/empty) → hide both. Because both tabBreaks sit in slot 1, JS must `addClass("tab-hidden")` on the non-matching one or two slot-1 tabs appear.

### 4.3 `dynamicLogic.panels` mechanism (keyed on `account_type`)

Add a `dynamicLogic.panels` block to `metadata/clientDefs/Account.json` (currently none). Server-driven panel visibility → Espo removes the panel from the DOM when false (no half-rendered fields). Commercial set = `["Commercial Lines","Group Benefits"]` (matches existing conditions); `fleetOperations` stays CL-only. Household set = `["Personal Lines","Medicare","Life Insurance"]` (design decision: widen beyond today's Personal-Lines-only, since Medicare/Life accounts are also households — flag for sign-off). The two sets are mutually exclusive.

```json
"dynamicLogic": {
  "panels": {
    "opportunitiesTab":  { "visible": { "conditionGroup": [ { "type": "in", "attribute": "account_type", "value": ["Commercial Lines", "Group Benefits"] } ] } },
    "ai_assessment":     { "visible": { "conditionGroup": [ { "type": "in", "attribute": "account_type", "value": ["Commercial Lines", "Group Benefits"] } ] } },
    "businessIdentity":  { "visible": { "conditionGroup": [ { "type": "in", "attribute": "account_type", "value": ["Commercial Lines", "Group Benefits"] } ] } },
    "bbbReputation":     { "visible": { "conditionGroup": [ { "type": "in", "attribute": "account_type", "value": ["Commercial Lines", "Group Benefits"] } ] } },
    "fleetOperations":   { "visible": { "conditionGroup": [ { "type": "equals", "attribute": "account_type", "value": "Commercial Lines" } ] } },
    "riskUnderwriting":  { "visible": { "conditionGroup": [ { "type": "in", "attribute": "account_type", "value": ["Commercial Lines", "Group Benefits"] } ] } },
    "coverageGapsCl":    { "visible": { "conditionGroup": [ { "type": "in", "attribute": "account_type", "value": ["Commercial Lines", "Group Benefits"] } ] } },
    "householdProfile":        { "visible": { "conditionGroup": [ { "type": "in", "attribute": "account_type", "value": ["Personal Lines", "Medicare", "Life Insurance"] } ] } },
    "householdMembers":        { "visible": { "conditionGroup": [ { "type": "in", "attribute": "account_type", "value": ["Personal Lines", "Medicare", "Life Insurance"] } ] } },
    "householdResidence":      { "visible": { "conditionGroup": [ { "type": "in", "attribute": "account_type", "value": ["Personal Lines", "Medicare", "Life Insurance"] } ] } },
    "householdVehiclesDrivers":{ "visible": { "conditionGroup": [ { "type": "in", "attribute": "account_type", "value": ["Personal Lines", "Medicare", "Life Insurance"] } ] } },
    "householdFinancial":      { "visible": { "conditionGroup": [ { "type": "in", "attribute": "account_type", "value": ["Personal Lines", "Medicare", "Life Insurance"] } ] } },
    "coverageGapsPl":          { "visible": { "conditionGroup": [ { "type": "in", "attribute": "account_type", "value": ["Personal Lines", "Medicare", "Life Insurance"] } ] } },
    "householdRetention":      { "visible": { "conditionGroup": [ { "type": "in", "attribute": "account_type", "value": ["Personal Lines", "Medicare", "Life Insurance"] } ] } }
  }
}
```

**Decision to confirm:** adopt clientDefs `dynamicLogic.panels` (per task) AND strip the redundant in-layout `dynamicLogicVisible` from `detail.json` panels, vs. keep the already-proven layout-embedded form. Don't run both (two sources of truth). The JS update is still required either way.

### 4.4 Household Profile field set

**Casing rule:** new fields use **camelCase** + `isCustom: true` to match the dominant PL cluster (`primaryFirstName`, `mailingAddressSame`); snake_case only where reusing a snake_case neighbor (`property_*`). Six new fields already have orphan i18n labels pre-seeded (`dependentsCount`, `dependentAges`, `residenceType`, `yearsAtAddress`, `youthful_driver_flag`, `vehicleAdditionalNotes`) — labeling is half-done.

| Panel | Fields (NEW = create; rest reuse existing) |
|---|---|
| **householdProfile** (Members) | Reuse: primaryFirstName, primaryLastName, primaryDob, primaryGender, primaryOccupation, primaryPhone, primaryEmail, maritalStatus. NEW: coInsuredFirstName, coInsuredLastName, coInsuredDob, coInsuredOccupation, householdMembers (text), dependentsCount, dependentAges, primaryContactName (link→Contact) |
| **householdResidence** | Reuse: property_address, property_city, property_state, property_zip, mailingAddressSame, mailingAddress. NEW: ownOrRent (enum), residenceType (enum), yearBuilt, yearsAtAddress, dwellingSqFt |
| **householdVehiclesDrivers** | NEW: vehiclesCount, driversCount, youthfulDriverFlag (bool; rename orphan label key `youthful_driver_flag`→camel), vehicleAdditionalNotes |
| **householdFinancial** | NEW: householdIncomeBand (enum, band not exact figure). Reuse: primaryOccupation, coInsuredOccupation |
| **coverageGapsPl** (relocated) | Reuse all: gapUmbrella, gapUmbrellaReason, gapLife, gapLifeReason, gapLifeNeedEst, gapAutoUm, gapMedicare, gapMedicareEligible, gapRenters, gapRideshare, gapCount, coverage_gaps. NEW: existingPersonalPolicies (text) |
| **householdRetention** | Reuse: retention_risk, risk_score, claims_count_3yr, claims_open, last_claim_date, last_claim_type, last_claim_lob, last_claim_status, claims_notes, nps_score, nps_date. NEW: priorCarrier, priorPremium (currency) |

**Tally:** ~30 reusable fields (PL backbone exists) + **~22 new fields**: coInsuredFirstName, coInsuredLastName, coInsuredDob, coInsuredOccupation, householdMembers, dependentsCount, dependentAges, primaryContactName, ownOrRent, residenceType, yearBuilt, yearsAtAddress, dwellingSqFt, vehiclesCount, driversCount, youthfulDriverFlag, vehicleAdditionalNotes, householdIncomeBand, existingPersonalPolicies, priorCarrier, priorPremium. (Note: a fully itemized vehicle/driver list is better modeled as a child entity — flag if VIN-level data is wanted.)

### 4.5 Part C implementation checklist

1. `metadata/entityDefs/Account.json` — add the ~22 new fields (camelCase, isCustom:true).
2. `i18n/en_US/Account.json` — add labels for ~16 new fields (6 already exist).
3. `layouts/Account/detail.json` — reorder Business Profile to slot 1; add householdProfile tabBreak + panels; relocate coverageGapsPl.
4. `metadata/clientDefs/Account.json` — add `dynamicLogic.panels`; strip redundant in-layout `dynamicLogicVisible` if standardizing on clientDefs.
5. `client/custom/src/views/account/record/detail.js` — update header map, bottomPanelTabMap indices, BUSINESS_PROFILE_TAB=1, add HOUSEHOLD_PROFILE_TAB=1, extend syncConditionalTabs().
6. Rebuild: `php command.php rebuild` + clear cache on the espocrm-ts box; client build/cache bust for the JS change.

---

## 5. Sequenced Execution Order

1. **Bucket A code/metadata safe fixes first** — A4 (unify rate fn) and A5 (rate validation) before any commission data touch; A1 (chk* labels), A3 (LOB normalization), A6/A7 (field consolidation/hide), A2 (hide dead Account fields, excluding flags/feature blocks). All non-destructive, reversible. (rebuild + cache clear once.)
2. **Commission relink data repair (prerequisite for all rollups)** — re-link the 445 orphaned-policy and 468 no-account Commission rows; fix the 8 null + 8 duplicate ledger_keys. Also A9 (40 orphaned-commission policies + corrupt policy_number).
3. **Confirmed deletions / merges (Bucket B-SAFE)** — B1+B2 (4 test renewals), B3 (2 Account stub merges), B4 (Atlas opp merge), B5 (27 dead Opportunity fields). Each with a one-line confirm.
4. **Data-quality backfills** — A8 (Renewal expected_commission), A10 (2 Commission rate outliers), then C2 decision (backfill vs null the 397 Policy amounts) once C1 is deployed.
5. **Builds** — C1 (unit unification + atomic derivation) → C2 (Policy commission backfill) → C3 (Commission-as-source rollup + repoint AccountValueManager; only after step 2 relink is done) → C4 (reconciliation pipeline) → C5 (Part C Household Profile).
6. **Human-review queue (do NOT auto-run)** — B6 (15 genuine Policy dup candidates, adjudicate vs NowCerts; reparent children before any delete), B7/B12 (Account column drop — hand-audited subset only, after mysqldump), B8 (AF1-Ca opp reconcile), B10 (re-link the 13 Unknown Client renewals — retention priority), B11 (Medicare-sync fields — sign-off). B9 (Jimmy Barfield) is closed: DO NOT MERGE.

---

## Appendix A — Account & Policy ID/field lists

**Account: 154 zero-populated non-currency fields** (hide-eligible after excluding the populated default-flags and GB/Life/Medicare feature fields per B7):
aep_sep_date, agent_of_agency_code, agent_of_record_date, ai_assessment, annual_premium, annual_revenue, assessment_date, bbb_rating, best_time_to_call, billing_address_country, campaign_id, carrier, carrier_code, claims_count3yr, claims_count_3yr, claims_count_lifetime, claims_notes, claims_open, construction_type, coverage_gaps, credit_pull_date, credit_tier, do_not_contact, document_links, downloads_statements, employee_count, estimated_premium, estimated_revenue, gap_auto_um, gap_count, gap_final_expense, gap_landlord, gap_life, gap_life_need_est, gap_life_reason, gap_medicare, gap_medicare_eligible, gap_renters, gap_rideshare, gap_umbrella, gap_umbrella_reason, gb_census_date, gb_census_received, gb_dental_carrier, gb_dental_monthly_premium, gb_eligible_employees, gb_employer_contribution, gb_life_ad_carrier, gb_life_benefit_amount, gb_ltd_carrier, gb_medical_carrier, gb_medical_monthly_premium, gb_medical_plan_type, gb_medical_renewal_date, gb_notes, gb_participating_employees, gb_std_carrier, gb_vision_carrier, gb_vision_monthly_premium, gb_voluntary_benefits, gender, general_notes, homeowner_status, insight_objection, insight_opener, insight_relationship, insight_signal, intel_annual_revenue_est, intel_bbb_accredited, intel_bbb_complaints, intel_bbb_notes, intel_bbb_rating, intel_cargo_type, intel_confidence, intel_dba, intel_dot_incidents, intel_employee_count, intel_fleet_size, intel_growth_indicator, intel_legal_name, intel_linkedin_notes, intel_linkedin_url, intel_operating_radius, intel_osha_violations, intel_owner_operators, intel_pack_last_run, intel_pack_run, intel_pain_points, intel_signal_linkedin, intel_signal_news, intel_website, intel_website_notes, irma_applies, key_findings, last_claim_date, last_claim_lob, last_claim_status, last_claim_type, last_contact_by_id, last_contact_outcome, last_contact_type, life_annual_premium, life_beneficiary_on_file, life_coverage_in_force, life_face_amount, life_health_class, life_policy_type, linkedin_url, lob, mailing_address, marital_status, medicare_carrier, medicare_eligible, medicare_part_a_b, medicare_plan_type, nps_date, nps_score, occupation, outreach_attempts_current, preferred_contact, premium_change_amount, premium_change_pct, primary_dob, primary_gender, primary_occupation, prior_carrier, property_address, property_city, property_zip, rate_increase_flag, referrals_given, renewal_date, renewal_decision, renewal_decision_notes, renewal_outreach_stage, renewal_quote_amount, renewal_quote_carrier, renewal_quote_date, renewal_quote_received, retention_risk, risk_score, roof_age, score_alert_sent, shipping_address_city, shipping_address_country, shipping_address_postal_code, shipping_address_state, shipping_address_street, sq_footage, stage, total_annual_premium, what_you_have_today, x_date, year_built.

**Account: 14 zero-populated _currency helpers:** annual_premium_currency, annual_revenue_currency, estimated_premium_currency, estimated_revenue_currency, gap_life_need_est_currency, gb_dental_monthly_premium_currency, gb_life_benefit_amount_currency, gb_medical_monthly_premium_currency, gb_vision_monthly_premium_currency, life_annual_premium_currency, life_face_amount_currency, premium_change_amount_currency, renewal_quote_amount_currency, total_annual_premium_currency.

**Account: 4 Inactive-with-premium (id | name | total_active_premium):** `69bde6cfef5087845` Wanda Tucker 65606 · `69bde6cfe8e33c862` Velma Pearson 14844 · `69bde6d00e39f3166` Zackeeia Miller 7336.1 · `6a173bed0e6a60b2e` Sclafani Waste Services 1943.

**Account: 2 duplicate-name pairs (B3):** Micah Gray — survivor `69bde6d06ff54a8c0`, retire `6a16fc618a7472181`. Shamira Douglas — survivor `6a173bf660434f2ad`, retire `6a16fc19cc3ce9793`.

**Policy: 40 orphaned-commission IDs:** 69d3de04f40d4dd90, 69d3de060d543294a, 69d3de077e9d67cee, 69d3de07802a53987, 69d3de0782f7c51a0, 69d3de07a203b4c3e, 69d3de07c5e50df5d, 69d3de07d6ac146be, 69d3de080888af22f, 69d3de081cd4646a5, 69d3de0832291a716, 69d3de0837ad22b24, 69d3de08469143c7f, 69d3de085f819c45c, 69d3de088265b41ea, 69d3de088482ed0f7, 69d3de0885db03226, 69d3de08933317790, 69d3de0896ba6987f, 69d3de089f247b444, 69d3de08adf67fdbb, 69d3de08ca0af8dca, 69d3de08e9ba7bf15, 69d3de08eadaddeaa, 69d3de090824f51e2, 69d3de090fb363464, 69d3de09105263e9a, 69d3de0928a85e95c, 69d3de093e850ed0d, 69d3de09471c58c1e, 69d3de0949163c6fb, 69d3de094f6688128, 69d3de0957d40984d, 6a0e0c4567bea0e6d, 6a0e19a3300c72573, 6a173d763be64160c, 6a173d76620d19abf, 6a173d76676e5088a, 6a173d7718ba1281a, 6a21d8920ae89eeeb.

**Policy: 11 rows with both rate+amount (C1 migrate ÷100):** 69d3de079560580f3, 6a2983013c8a6edc1, 6a2983ba0285e54cf, 6a2984d9598eed530, 6a298600e9e217859, 6a298c2b7fc33a32f, 6a2c4370a9c281a6b, 6a2c44337a31a1ea8, 6a2c4d51c0bdc2e33, 6a2c556d555276b0b, 6a0e0c4567bea0e6d.

**Policy: 7 non-canonical LOB strings (A3):** PersonalAuto, personal lines, renters, work comp, Life insurance, "Builders risk / Home Construction", "Cyber/Network Liability". **Corrupt policy_number (A9):** `2.02521E+11`.

## Appendix B — Policy dedup (B6)

**84 dup policy_number groups (raw):** 02-CPK-116548-01, 100023140200, 10072782CA, 10126011CA, 10127426CA, 10145764CA, 10171613CB, 10172585WC, 2.02521E+11, 25SSJ231CM2155, 6259704770, 8035888705, 8035888767, 860754547, 862047701, 862392084, 864404311, 864561433, 865173784, 866198315, 867030527, 871502820, 944576728, 960689667, 964857897, 966656843, 969126179, 969769950, 969898455, 970235412, 971170598, 971667917, 972418478, 973233777, 973246783, 973304770, 973749874, 975931554, 976897710, 978989504, 980025522, 980042614, 980043890, 980057774, 980766194, 982515879, 982516234, 983522464, 988270978, 988312391, 990414352, 990774238, 991540615, 992819127, 992865630, 993674437, 994486230, 995074601, 995223411, 999429819, 999556278, ADJUSTMENT, BDG-3101878-01, CPS8134259, F3825959, F3904093, F3923616, F4118564, GP 8812406, OF2136467, OF2907611, OF3056607, OF3156692, OF3159486, OF3178660, OF3219534, OF3239390, OSIH3GA_01424857-2, OSIH3GA_01440523-2, QOUS-GA-0001607-00, WCV066095801, WCV066144101, Y014872212, Y017227627.

**15 GENUINE same-term collision candidates (the only merge-eligible subset):** 02-CPK-116548-01, 100023140200, 10072782CA, 25SSJ231CM2155, 6259704770, 8035888705, 8035888767, 862047701, 862392084, 865173784, 871502820, 975931554, F4118564, GP 8812406, Y017227627.
- **Conflict — adjudicate vs NowCerts (do not auto-merge):** 862047701 (premiums 12569/12463/24926, mixed Active/Cancelled), 862392084 (1453 vs 2930.08, mixes renewal term + same-term dupe), 6259704770 (6-mo vs 12-mo exp conflict), 8035888767 (bad date on one copy), 865173784 (6008 vs 8101), 871502820, plus 975931554 / GP 8812406 (distinct-momentum uncertainties).
- **Lower-risk clean NULL-momentum copies (still reparent Commission/Renewal children first):** 02-CPK-116548-01, 25SSJ231CM2155, 100023140200, F4118564, Y017227627.
- The other 69 groups are legitimate renewal chains — **do not touch.**

## Appendix C — Redundant field pairs (live pop counts)

| Entity | Keep (source of truth) | Retire/hide twin | Live pop |
|---|---|---|---|
| Account | active_policy_count | policy_count_active | 143 / 142 (all overlaps equal) |
| Account | next_x_date | next_renewal_date | 95 / 91 |
| Account | next_x_date_lob | next_renewal_lob | 97 / 93 (635 identical, 4 differ) |
| Account | clv_projected | clv_with_cross_sell | 81 (all equal) |
| Account | account_type | standard `type` | 637 / 1 |
| Account | total_active_premium | annual_premium(0)/estimated_premium(0)/total_annual_premium(0)/total_carrier_premium(1) | 93 |
| Account | (DECIDE scorer — do NOT auto-retire) | account_score(19) vs score_total(639) — diverge, match 1/639 | — |
| Policy | line_of_business | line_of_business_raw | 409 / 409 (identical) |
| Opportunity | stage | last_stage | 49 / 49 (differ 7) |
| Commission | estimated_commission | expected_amount/actual_amount/posted_amount/variance_amount | all twins 0 |

## Appendix D — Renewal IDs

**Test records to DELETE (B1/B2):** `6a1cd10456277e7c4` Darren Boykin · `6a1cd1078c1b9d179` Sclafani Waste Services · `6a1cd107bea074a8c` Zakir Hussain · `69eba399b011a486b` Dream Chaser Trucking (note="test").

**13 Unknown Client renewals — DO NOT DELETE, re-link (B10):** 69c170966ab2381a3, 69c17096e0ff3ef09, 69c17097285e348a8, 69c170990d187fb9d, 69c17099441b0517c, 69c17099d5b8f0803, 69c1709a251f9e12b, 69c1709a6b6eaef92, 69c1709ad78433bbb, 69c1709b1d4c5b03f, 69c1709b53a2808df, 69c1709bbf3433344, 69c1709bef0ec3726.

## Appendix E — Opportunity & Commission IDs

**Opportunity duplicate-name records (B4/B8/B9):** AF1-Ca `6a28771147d2e1344` / `6a2872e6eece70e80` (REVIEW); Atlas `6a0215aaaaa171a90` (keep) / `6a0217673a4e111ad` (retire); Jimmy Barfield `6a2859c125848ae31` / `6a2867c1c76c28152` (DO NOT MERGE).

**Opportunity 27 safe-to-remove dead fields (B5):** 13 chk* (chkQuoteSubmitted, chkProposalSent, chkSignedAppReceived, chkBound, chkUnderlyingLinked, chkUnderlyingConfirmed, chkMvrsPulled, chkDecPageDelivered, chkScopeOfAppt, chkPlanPresented, chkAppSubmitted, chkCmsConfirmation, chkWelcomeLetter) + 7 gb_* (gbCensusReceived, gbEligibleEmployees, gbEmployerContribution, gbMedicalCarrier, gbMedicalPlanType, gbMedicalRenewalDate, gbParticipatingEmployees) + 7 med/medicare (medCurrentCarrier, medCurrentPlan, medAepSepDate, medIrmaaApplies, medPlanType, medicareEligible, medicarePartB).
**Opportunity 5 CONDITIONAL Medicare-sync fields (B11):** partAEffectiveDate, partBEffectiveDate, medicareHICN, drInformation, rxInformation.

**Commission 2 rate outliers (A10/C1):** `6a2991ac86793a8d5` (rate=10→0.10) · `6a0e0a9a40238cd73` (rate=11.5→0.115; also the lone Reconciled row).

## Appendix F — Key files

- `custom/Espo/Custom/Resources/metadata/entityDefs/Account.json`
- `custom/Espo/Custom/Resources/metadata/entityDefs/Commission.json`
- `custom/Espo/Custom/Resources/metadata/entityDefs/Opportunity.json`
- `custom/Espo/Custom/Resources/metadata/clientDefs/Account.json`
- `custom/Espo/Custom/Resources/metadata/clientDefs/Opportunity.json`
- `custom/Espo/Custom/Resources/layouts/Account/detail.json`
- `custom/Espo/Custom/Resources/layouts/Opportunity/detail.json`
- `custom/Espo/Custom/Resources/layouts/Renewal/detail.json`
- `custom/Espo/Custom/Resources/i18n/en_US/Account.json`
- `custom/Espo/Custom/Resources/i18n/en_US/Opportunity.json`
- `custom/Espo/Modules/RsgCore/Classes/Policy/PolicyAccountSync.php`
- `custom/Espo/Custom/Classes/Commission/CommissionLedgerManager.php`
- `custom/Espo/Custom/Classes/Account/AccountValueManager.php`
- `custom/Espo/Custom/Jobs/BackfillPolicyHealth.php`
- `client/custom/src/views/account/record/detail.js`
- `field-reference/nowcerts-to-espocrm-mapping.md`
