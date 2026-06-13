# Commission Engine ↔ Supabase Crosswalk (working doc)

Purpose: decide how the standalone **RSG Commission Tracker** (React app) lines up
with the **existing** Supabase commission schema, instead of forking it. No code
has been changed based on this doc yet — it's for us to agree the mapping first.

Source of truth on the Supabase side (project `rsg-infrastructure`,
`wibscqhkvpijzqbhjphg`):
- **`commission_rules`** — 216 rows, the real rate cards (rate lookup).
- **`commission_ledger`** — 0 rows, expected-vs-actual per policy per statement.
- supporting: `crm_commissions` (307, Espo Commission mirror), `policy_facts`,
  `stg_nowcerts_policies` (NowCerts raw staging).

The engine currently has its own parallel model: `CarrierRule` (~70 hardcoded
`INITIAL_RULES`), `WonPolicy`, `ReconciliationStatement`, all in browser
localStorage. That model is essentially a **duplicate** of the two tables above.

---

## 1. Rate cards: engine `CarrierRule` ↔ `commission_rules`

| Engine `CarrierRule`          | `commission_rules`                  | Notes |
|-------------------------------|-------------------------------------|-------|
| `carrier`                     | `carrier_name` (+ `carrier_code`)   | vocabulary must match policies' `carrier` |
| `lineOfBusiness`              | `lob` (+ `sub_lob`)                 | **canonical = `commission_rules.lob`** (60 values; see §5) |
| `newRenewal: 'New'`           | `nb_percent`                        | engine splits New/Renewal into rows; Supabase has both % on one row |
| `newRenewal: 'Renewal'`       | `renewal_percent`                   | one rule row covers both |
| `method`                      | *(no column)*                       | **gap** — see §4. Supabase implies %-of-premium + `flat_fee` + `ee` bands |
| `ratePercentage`              | `nb_percent` / `renewal_percent`    | Supabase stores as percent numbers |
| `flatOrPerEmployeeAmount`     | `flat_fee` (+ `ee_min`/`ee_max`)    | per-employee = flat_fee within an EE band |
| `paymentTiming` `As Earned`/`In Advance` | `commission_basis` `as_earned`/`advance` | **direct match** (value rename) |
| `notes`                       | `notes`                             | |
| *(none)*                      | `mga_name`/`mga_code`, `state`, `plan_name`, `tier_label`, `lookup_priority` | richer lookup the engine has no concept of |
| *(none)*                      | `lamar_split`, `referral_split`, `revenue_split_percent`, `split_counterparty` | **splits** — engine has no net/split logic |
| *(none)*                      | `bonus_percent`, `advance_months`, `chargeback_period_months`, `payment_frequency`, `audit_adjustable`, `min_premium`/`max_premium`, `effective_date`/`expiration_date` | advanced terms |

**Lookup logic differs.** Engine = exact `carrier`+`lob`+`newRenewal`.
Supabase = filter by `carrier_name`+`lob`(+`state`,`ee` band,`mga`,`plan`) then
`ORDER BY lookup_priority ASC LIMIT 1` (most specific wins). Adopting Supabase
rules means adopting this richer match.

---

## 2. Won policy: engine `WonPolicy` ↔ `commission_ledger`

| Engine `WonPolicy`        | `commission_ledger`         | Notes |
|---------------------------|-----------------------------|-------|
| `crmPolicyId`             | *(no column)*               | **gap** — ledger has `espocrm_opportunity_id`, not policy id. Add `espocrm_policy_id`? |
| `momentumPolicyId`        | `nowcerts_policy_id`        | natural cross-system key |
| `policyNumber`            | `policy_number`             | |
| `clientName`              | `client_name`               | |
| `carrier`                 | `carrier_name`              | |
| `lineOfBusiness`          | `lob`                       | |
| `newRenewal` (enum)       | `is_renewal` (boolean)      | `'Renewal'` → `true`, `'New'` → `false` |
| `premiumAmount`           | `gross_premium`             | |
| (computed expected)       | `expected_commission`       | engine writes its calc here |
| `dateWon`                 | `policy_effective_date`?    | or a dedicated date — decide |
| `numberOfEmployees`       | `ee_count`                  | |
| `voided` / cancellation   | `reconciliation_status` / `chargeback_expiry_date` / `unearned_balance` | cancellation = chargeback path (see §3) |
| `paymentTiming`           | `commission_basis`          | |
| `notes`                   | `notes`                     | |
| *(none)*                  | `mga_name`, `state`, `plan_name`, `policy_year`, `statement_date`, `statement_source`, `commission_rule_id` | |
| *(none)*                  | `revenue_split_percent`, `rsg_net_commission`, `advance_amount`, `earned_to_date`, `unearned_balance` | RSG **net** after split — engine only does gross |

---

## 3. Reconciliation model — the biggest structural difference

- **Engine:** one `WonPolicy` (expected) + many `ReconciliationStatement` rows,
  each a `Payment` or `Chargeback` with `receivedAmount` and `statementMonth`.
  Carrier summaries derive expected/received/short/chargebacks.
- **`commission_ledger`:** one row **per policy per statement period** carrying
  both `expected_commission` and `actual_commission`, with `delta`,
  `payment_received`, `reconciliation_status`, plus accrual columns
  (`advance_amount`/`earned_to_date`/`unearned_balance`/`chargeback_expiry_date`).

So the ledger folds "expected" and "received" into one statement-grained row,
where the engine keeps them as separate records aggregated by month.
**Decision needed:** does the engine write one ledger row per
policy-per-statement (matching the ledger), or keep its WonPolicy + Statement
split and map on the way in/out?

---

## 4. Calculation method gap

Engine `method` ∈ {`% of Premium`, `% of Payroll`, `Flat $`, `Per Employee`,
`% of Monthly Premium`, `% of Admin Fee`, `Manual`}.
`commission_rules` has **no method column** — 202/216 rows are `as_earned`,
14 `advance` (that's *timing*), and the rate is expressed as
`nb_percent`/`renewal_percent` (% of premium) and/or `flat_fee` within `ee` bands.

**Implication:** the Supabase rate model is essentially "% of premium (+ flat fee,
+ EE-band tiering)". The engine's payroll / admin-fee / monthly-premium methods
have no home in `commission_rules` today. Decide: (a) add a `method`/`basis`
column to `commission_rules`, or (b) accept that Supabase rules are %-of-premium +
flat and keep the exotic methods engine-only/manual.

---

## 5. Value vocabulary (the "syntax" that must line up)

- **LOB — canonical is `commission_rules.lob`** (60 values incl. `Workers Comp`,
  `BOP`, `Commercial Auto`, **`General Liability`**, `Professional Liability`,
  `Commercial Property`, `Inland Marine`, `Commercial Cyber`, `Homeowners`,
  `Personal Auto`, `Commercial Umbrella`, `Personal Umbrella`, `Umbrella`,
  `Excess Liability`, `Commercial Package`, life/health lines, ...).
  - The engine's `Commercial GL` should be **`General Liability`**.
  - `Umbrella` / `Excess Liability` / `Commercial Umbrella` / `Personal Umbrella`
    are **distinct** here — do **not** merge them. (⚠️ the `LOB_ALIASES` I added
    to `src/utils.ts` earlier assumed otherwise and must be revised — see §6.)
- **Carrier:** `carrier_name` is canonical; engine + EspoCRM `carrier` strings
  must match it. Not aliased — reconcile names directly.
- **New/Renewal:** engine enum ↔ ledger `is_renewal` boolean.
- **Timing:** engine `As Earned`/`In Advance` ↔ `commission_basis`
  `as_earned`/`advance`.

---

## 6. Corrections to this session's code (pending our decision)

- `Commsions/src/utils.ts` `LOB_ALIASES` — revise to the **canonical
  `commission_rules.lob`** vocabulary (e.g. map engine `Commercial GL` →
  `General Liability`; drop the `Umbrella`→`Excess Liability` merge). Or remove
  aliases entirely if the engine sources LOB from `commission_rules`.
- `supabase/migrations/20260607000000_create_commission_won_policies.sql` —
  **draft, do not apply.** If we align to `commission_ledger`, this table is
  dropped. (It is currently NOT applied.)
- `Commsions/server/supabaseStore.js` — currently targets `commission_won_policies`;
  would repoint to `commission_ledger` (write) + `commission_rules` (read).

---

## 7. Open decisions (for our working session)

1. **Direction:** engine reads rules from `commission_rules` and writes
   expected/actual into `commission_ledger` (single source of truth) — yes?
2. **Policy identity:** add `espocrm_policy_id` to `commission_ledger`, or key on
   `nowcerts_policy_id` + `policy_number` only?
3. **Reconciliation grain:** one ledger row per policy-per-statement vs keep the
   engine's WonPolicy + Statement split?
4. **Method gap:** add a method/basis column to `commission_rules`, or accept
   %-of-premium + flat only?
5. **Splits/net:** should the engine surface `revenue_split_percent` /
   `rsg_net_commission`, or keep showing gross expected only?
6. **Rule lookup:** adopt Supabase's `state`/`ee`/`mga`/`lookup_priority`
   most-specific match, or keep the engine's exact carrier+lob+new/renewal?
7. **Security:** RLS is disabled on 92 public tables (incl. these). Decide RLS +
   policies before the engine connects with anything but the service-role key.

---

## 8. Recommendations (one per decision)

1. **Direction → YES, single source of truth.** Engine reads `commission_rules`
   and writes `commission_ledger`. Retire the hardcoded `INITIAL_RULES` and treat
   localStorage as an offline cache only. *Why:* the hardcoded ~70 rules are
   already stale vs the 216 real ones; one source kills drift.
2. **Identity → add `espocrm_policy_id` (nullable) to `commission_ledger`;**
   bridge upserts on it, falling back to `nowcerts_policy_id` + `policy_number`;
   register in `sync_mappings` (`object_type='policy'`). *Why:* the bridge fires
   on the Espo Policy and always has that id; the ledger's only Espo key today is
   `espocrm_opportunity_id` (wrong grain).
3. **Recon grain → adopt the ledger grain.** Closing a policy upserts ONE
   "expected" row (`reconciliation_status='expected'`, `payment_received=false`,
   `expected_commission` set); statements update `actual_commission`/`delta`/
   `payment_received` (as-earned monthly naturally yields multiple rows). Engine
   keeps its two-pane UX but persists to ledger rows. *Why:* the ledger is the
   accounting-correct model (delta, earned/unearned, chargeback).
4. **Method gap → add `commission_method` (nullable) to `commission_rules`,**
   default `'% of Premium'`; backfill the 216 rows to that. *Why:* you write real
   PEO/payroll deals (INVO/TrueCraft) needing `% of Payroll` / `% of Admin Fee` /
   `Per Employee`; a cheap column preserves them instead of dropping fidelity.
5. **Splits/net → show gross expected (parity) AND add RSG net.** Add a net
   column from `revenue_split_percent` / `rsg_net_commission`; keep gross. *Why:*
   net is what the business banks, but don't break the current gross view.
6. **Lookup → adopt most-specific (`lookup_priority ASC LIMIT 1`)** filtering
   `carrier_name`+`lob`(+`state`, `ee` band, `mga`, `plan`), with graceful
   fallback to `carrier`+`lob`+`new/renewal`. Compute in the engine server now; a
   Postgres RPC/view later. *Why:* the 216 rules are tiered and meant to be
   queried this way; exact-match would mis-pick.
7. **Security → enable RLS on `commission_ledger` + `commission_rules` now**
   with service-role-only access (engine uses the service-role key **server-side**;
   the browser never touches Supabase directly). Track the broader 92-table RLS
   gap as a **separate** security task — do not blanket-enable here (would break
   n8n/apps without policies). *Why:* scope the fix to what this project uses.

**Plus the correction from §6:** fix `LOB_ALIASES` to the canonical
`commission_rules.lob` vocabulary (or drop it once LOB comes from
`commission_rules`), and discard the `commission_won_policies` migration.

### Recommended sequence
- **A. Two small migrations:** add `espocrm_policy_id` + `commission_method`
  columns; enable RLS (+ service-role policy) on the two tables.
- **B. Repoint engine server:** read `commission_rules` (most-specific lookup) +
  write `commission_ledger`; fix LOB; delete the new-table migration.
- **C. Keep the Espo→engine webhook** — it now lands in `commission_ledger`.

## 9. Tiered rates (implemented A, ready for B)

`commission_rules` rows that share carrier+LOB+state but differ by `tier_label`
are **legitimate tiers, not duplicates**: NEXT BOP `Tier 1/2/3` (14.5/12.5/10.5%),
SIMPLICITY Whole Life issue-age / pay-schedule (35–70%), STEADILY Landlord by
property type (all 12/10). The engine policy carries no tier, so:

- **(a) done** — when matched tiers carry *different* rates and no `tierLabel` is
  given, `computeExpected` picks deterministically but returns `ambiguous: true`
  (+ `tierCount`, `tierLabel`); the ledger row gets `audit_status='tier_ambiguous'`
  and a "verify rate" note. Same-rate tiers (STEADILY) are **not** flagged.
  Verified against live data: NEXT/BOP + SIMPLICITY/Whole Life flag; ATTUNE/GL +
  STEADILY/Landlord don't.
- **(b) ready** — `WonPolicy.tierLabel` + `_candidates()` already filter to an
  exact tier when supplied; just needs the EspoCRM bridge to populate it (issue
  age / pay schedule / risk tier) to resolve those exactly.

No `commission_rules` cleanup needed — the rows are real tiers.
