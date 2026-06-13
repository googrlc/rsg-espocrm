# Commission Workspace — Design (staging · calculation · reconciliation)

> **Design doc, not a build.** This describes the target architecture for a
> **separate commission platform/workspace** that runs off **live CRM data**, and
> how it reuses the commission engine work already in flight. No code changes here.

## Context — why a separate workspace

EspoCRM is the transactional CRM and NowCerts is the policy system of record.
Commission work — **staging** incoming closed policies, **calculating** expected
commission from rate cards, and **reconciling** expected vs. what carriers
actually pay — is a different job with a different audience (you/finance, not
sales). It needs its own UI, its own audit trail, and the freedom to recompute
without touching live CRM records.

That separate platform already exists in skeleton form: the **RSG Commission
Tracker** (`~/Documents/GitHub/Commsions`, React/Vite + Express `server/`),
repointed to canonical **Supabase** tables. This doc defines it as a system and
adds the **propose → approve → commit** safety gate from the Hermes design where
commission work genuinely needs a human in the loop.

What's already done (don't rebuild — see `COMMISSION-ENGINE-SUPABASE-CROSSWALK.md`
and the `project_commission-engine-bridge` memory):

- **EspoCRM → engine webhook** — `Policy AfterSave` (closed status) pushes raw
  policy inputs to the engine. Files: `custom/Espo/Custom/Hooks/Policy/SendPolicyClosedWebhook.php`,
  `custom/Espo/Custom/Classes/Policy/PolicyClosedWebhookDispatcher.php`. Contract: `COMMISSION-ENGINE-BRIDGE.md`.
- **Canonical Supabase schema** — `commission_rules` (216 rate cards, now with
  `commission_method`), `commission_ledger` (expected/actual/delta, now with
  `espocrm_policy_id` + RLS service-role-only). Migrations `2026060701*`.
- **Engine repointed** — reads `commission_rules` (most-specific lookup), writes
  `commission_ledger` (`statement_source='espo_bridge'`, idempotent on
  `espocrm_policy_id`).

What's **not** done: webhook live config (Step C), live engine→ledger write
e2e-test, and the reconciliation/human-gate UX this doc defines.

---

## Architecture

```
   LIVE CRM DATA                COMMISSION WORKSPACE (separate platform)
 ┌──────────────┐
 │  NowCerts    │  AMS = policy system of record
 │   (AMS)      │
 └──────┬───────┘
        │ sync
        ▼
 ┌──────────────┐   ① event push (Policy closed/corrected)
 │   EspoCRM    │ ─────────────────────────────────────┐
 │   (CRM)      │   ② reconciliation pull (read-only)   │
 └──────┬───────┘ ◄───────────────────────────┐        │
        │                                      │        ▼
        │                              ┌───────┴────────────────────┐
        │                              │  STAGING                    │
        │                              │  commission_ledger rows     │
        │                              │  reconciliation_status=     │
        │                              │   'expected', audit_status  │
        │                              └───────┬────────────────────┘
        │                                      ▼
        │                              ┌────────────────────────────┐
        │  carrier statements          │  CALCULATION                │
        │  (CSV / PDF / portal) ──────►│  commission_rules lookup    │
        │                              │  computeExpected()          │
        │                              └───────┬────────────────────┘
        │                                      ▼
        │                              ┌────────────────────────────┐
        │                              │  RECONCILIATION             │
        │                              │  expected vs actual, delta, │
        │                              │  chargeback, earned/unearned│
        │                              └───────┬────────────────────┘
        │                                      ▼
        │     write-back (gated) ◄──────┌──────┴───────┐
        └───────────────────────────────│ HUMAN GATE   │ approve / reject
              only approved, deterministic│ (you/finance)│ exceptions only
              code commits to CRM/ledger  └──────────────┘
```

**Direction of data:** the workspace is a **read-mostly consumer** of CRM data.
It pulls/receives policy facts and computes against its own rulebook. It writes
back to live CRM records **only** through the gated, deterministic committer —
never free-hand from the calculation layer.

---

## The three pillars

### 1. Staging — "what closed, and is it clean enough to cost?"

Incoming closed/corrected policies land as **expected** rows in
`commission_ledger` (`reconciliation_status='expected'`, `statement_source='espo_bridge'`,
idempotent on `espocrm_policy_id`). Staging's job is to flag rows that **can't be
costed cleanly** before they pollute the numbers:

- **Carrier name mismatch** — engine matches rules by `carrier_name`; if the AMS
  string (`AmTrust Financial`) ≠ rulebook (`AmTrust`), expected falls to **0**.
  Stage as `audit_status='carrier_unmatched'`, not a silent zero.
- **LOB vocabulary** — canonical is `commission_rules.lob` (60 values; Umbrella
  variants are **distinct**, do not merge). Unmapped LOB → flag.
- **Tier ambiguity** — when matched tiers carry different rates and no `tierLabel`
  was supplied, `computeExpected` already returns `ambiguous:true`; row gets
  `audit_status='tier_ambiguous'` + "verify rate" note.

These flags are exactly the **exceptions the human gate reviews** (below).

### 2. Calculation — "what should we be paid?"

Already built in the engine (`Commsions/server/commissionRules.js`):
most-specific lookup (`carrier_name`+`lob`+`state(ALL)`+`ee` band+`lookup_priority`),
`computeExpected` driven by `commission_method` / `nb_percent` / `renewal_percent`
/ `flat_fee`, timing from `commission_basis` (`as_earned`/`advance`). Writes
`expected_commission` (and RSG net via `revenue_split_percent`) to the ledger.

### 3. Reconciliation — "what did we actually get, and what's owed?"

The structural decision from the crosswalk: **ledger grain = one row per policy
per statement period**, folding expected + actual into the same row
(`expected_commission`, `actual_commission`, `delta`, `payment_received`,
`reconciliation_status`) plus accrual columns (`advance_amount`, `earned_to_date`,
`unearned_balance`, `chargeback_expiry_date`). Carrier statement imports update
`actual_commission`/`delta`; cancellations zero expected and follow the chargeback
path. This is where "what RSG is still owed" comes from (ties to the
`commission-reconciliation` skill).

---

## "Live data from the CRM" — the sync contract

Two complementary paths, because event-push alone isn't "live" for reconciliation:

1. **Event push (exists)** — `Policy AfterSave` on close/correction → expected row.
   Real-time for *new* commission obligations. Idempotent, so AMS corrections
   re-push safely.
2. **Reconciliation pull (to add)** — a scheduled read-only refresh (the
   workspace reading EspoCRM/Supabase) to (a) catch policies that closed while the
   webhook was misconfigured, (b) refresh client/policy facts that drift after
   close, and (c) drive the at-risk/owed views. Read-only → zero write risk.

Identity is carried end-to-end: `espocrm_policy_id` (idempotency key),
`nowcerts_policy_id` + `policy_number` (cross-system), registered in
`sync_mappings`.

---

## The safety gate — where a human approves (and why it matters here)

Commission work has **three failure modes that look like success**: a silent
expected-0 from a carrier-name mismatch, a wrong tier picked deterministically,
and a write-back to the CRM that the ACL silently drops. So the same
**propose → approve → commit** pattern applies — but scoped to *exceptions*, not
every row:

- **Auto-flow (no gate):** clean rows where carrier+LOB matched a single
  unambiguous rule. These compute and post to the ledger automatically.
- **Gated (human reviews):** rows with `audit_status` in
  `{carrier_unmatched, tier_ambiguous, lob_unmapped}`, large expected↔actual
  deltas, and **any write-back to a live CRM record** (e.g. stamping a resolved
  carrier alias or tier back onto the Policy). The agent/engine *proposes*; a
  deterministic committer applies the approved change with correct per-entity
  casing and a post-write read-back to detect silent drops.
- **Review surface:** the workspace's own exceptions tab (+ optional Slack digest
  to `#the-boss`). Bulk-approve as trust grows; high-confidence aliases can later
  auto-resolve.

This keeps the **separate workspace** autonomous for the 90% clean case while the
risky 10% — the cases that quietly corrupt commission numbers — wait for a human.

---

## Decommissioning the CRM Commission module (strangler-fig, read-only mirror)

**Decision:** the platform becomes the **system of record + calculator** for
commissions. The EspoCRM `Commission` entity is **not deleted** — it becomes a
**read-only mirror** that only the platform writes (via the API user). This keeps
everything the CRM-side *reads* intact while moving all *authoring/calculation*
out to the platform.

### The seam: retire the write side, keep the read side

The CRM Commission module splits cleanly into "things that author/compute
commissions" (retire) and "things that read/display them" (keep):

| Component | Disposition | Why |
|---|---|---|
| `Hooks/Opportunity/CreateCommissionLedger.php` | **Retire** | Platform now creates ledger rows from closed Policies, not Opp close. |
| `Hooks/Renewal/CreateCommissionLedger.php` | **Retire** | Same — calculation moves to the platform. |
| `Hooks/Commission/DeriveLedgerFields.php` | **Retire** | Platform owns derived values; CRM must not recompute over them. |
| `Classes/Commission/CommissionLedgerManager.php` | **Retire/neuter** | Authoring logic supplanted by the engine. |
| The 4 links (Policy/Renewal/Opportunity/Account) | **Keep** | Visibility + navigation; read-only. |
| The 13 select filters (Posted/Overdue/HighVariance/…) | **Keep** | Staff views still work against mirrored rows. |
| `Classes/Account/AccountValueManager.php` + `Hooks/Policy/RefreshAccountValue.php` | **Keep — but re-source** | See Account-value note below. |

### Enforcing read-only

- **ACL:** revoke `create`/`edit`/`delete` on `Commission` for all roles **except
  the platform's API user**; leave `read` for staff. (This is the same field-level
  ACL surface that has silently dropped writes before — here we use it deliberately.)
- Set the entity/fields `readOnly` in `clientDefs`/`entityDefs` so the UI stops
  offering edit, and disable the create button.
- Net effect: the Commission tab still shows live, accurate rows; nobody but the
  platform can change them.

### Account-value rollup — the one that breaks silently

Account "value" is derived **from Commission rows** by `AccountValueManager`
(triggered via `RefreshAccountValue`). Because we keep the rows (platform writes
them), the cleanest path is: **keep the rollup as a pure read-aggregation over the
mirrored Commission rows, and retire only the *derivation* (`DeriveLedgerFields`).**
After the platform upserts/Commission rows, trigger a recompute so Account value
updates — either a slim `Commission AfterSave` hook that *only* rolls up (no field
derivation), or a platform call that pokes the refresh. **Verify Account value is
non-zero after cutover** — this is the regression to watch (retention/at-risk views
depend on it).

### Writeback — three targets the platform feeds

1. **Commission records (per-statement)** — the platform upserts the detailed
   expected/actual/delta rows into the read-only Commission entity (idempotent on a
   stable key, e.g. `espocrm_policy_id` + statement period). Keeps full grain so the
   13 filters keep meaning.
2. **Summary fields on Policy** — roll expected/paid/owed onto each Policy where
   staff already work. ⚠️ **Policy is mixed-casing** (per the schema rules) — get
   each field's casing right or the write is silently dropped; **read-back after
   write** to confirm it stuck.
3. **Account value rollup** — restored as above so retention views don't blank out.

All three go through the **gated, deterministic committer** (correct per-entity
casing + post-write read-back), never free-hand from the calc layer.

### Cutover sequence (parity before flip)

1. **Mirror in parallel** — platform writes Commission rows alongside the existing
   CRM hooks; **reconcile** platform rows vs. the 307 existing `crm_commissions`
   until they match (this is the dual-write *check*, even though the end state is
   read-only).
2. **Freeze CRM authoring** — disable `CreateCommissionLedger` (Opp + Renewal) and
   `DeriveLedgerFields`; platform is now sole writer.
3. **Flip to read-only** — apply the ACL/`readOnly` changes.
4. **Re-source the rollup** — confirm Account value recomputes from mirrored rows.
5. **Decommission cleanly** — archive the retired hook/class files (don't just
   delete blind); the entity, links, and filters remain.

> Relationship to `COMMISSION-LEDGER-SYNC-CONTRACT.md`: that contract syncs the
> internal Commission ledger *outbound* to accounting. This reverses the
> *authoring* direction (platform → CRM mirror); the outbound accounting sync can
> read the same mirrored rows unchanged.

## Parity-reconciliation check (the cutover gate)

Before the CRM Commission module goes read-only, the platform must prove it
reproduces the CRM's existing numbers — or that every difference has a named
cause. This check is **read-only on both sides** (no writes, no risk) and its
pass/fail is the gate for cutover step 2.

### The two populations

- **CRM side** — the existing live Commission records (Supabase mirror
  `crm_commissions`, ~307 rows). Note these include **manual** entries
  (`postingSource='Manual'`) that have no policy/rule basis — handle separately.
- **Platform side** — rows the engine computes from `commission_rules` +
  closed Policies, landing in `commission_ledger`.

### Match key (how a CRM row pairs to a platform row)

1. **`ledgerKey`** if populated — it's the existing stable key (and `ledgerPayloadHash`
   can short-circuit "identical").
2. Else **`policy` link → `espocrm_policy_id`** + `commissionType` + statement
   period (`effectiveDate`).
3. Else (manual, no policy) — unmatchable by rule; routes to **CRM_ONLY** triage.

### Fields compared (real Commission fields → ledger)

| CRM `Commission` | Platform `commission_ledger` | Role | Tolerance |
|---|---|---|---|
| `writtenPremium` | `gross_premium` | input | exact (±$0.01) |
| `carrier` | `carrier_name` | input/match | normalized string-eq |
| `lineOfBusiness` | `lob` | input/match | canonical map |
| `commissionType` | `is_renewal` (+endorsement) | input | New/Renewal/**Endorsement** |
| `commissionRate` | rule `nb_/renewal_percent` | calc | ±0.001 |
| **`estimatedCommission`** | **`expected_commission`** | **headline** | ±$0.01 or ±1% |
| `postedAmount` | `actual_commission` | actual | exact |
| `varianceAmount` | `delta` | recon | exact |

`estimatedCommission` ↔ `expected_commission` is the number that matters; the
inputs above exist to *explain* any gap.

### Classification buckets (and what each forces)

- **MATCH** — within tolerance. No action.
- **EXPLAINED_DIFF** — gap with a named cause from the staging `audit_status`:
  `carrier_unmatched` (platform expected→0 until the carrier name is reconciled),
  `tier_ambiguous`, `lob_unmapped`, rule-updated-since, rounding. **Allowed past
  the gate only once the cause is recorded** — a carrier-name zero must be *fixed*,
  not waved through.
- **MISMATCH** — unexplained delta. **Blocks cutover.** Every one needs triage.
- **CRM_ONLY** — CRM record, no platform equivalent: usually a `Manual` entry or a
  policy the engine didn't see. Decide per row: migrate as-is into the platform, or
  accept the platform can't reproduce it (and keep it as a manual mirror row).
- **PLATFORM_ONLY** — platform computed it, CRM never had it: the CRM was *missing*
  commissions (often a feature, but confirm before counting it as found money).

### Gate criteria (all must hold to flip read-only)

1. **0 unexplained MISMATCH.**
2. Every **EXPLAINED_DIFF** has its cause logged; all `carrier_unmatched` zeros
   resolved (carrier vocabulary reconciled across the 4 layers).
3. Every **CRM_ONLY** triaged to migrate-or-accept (no silent drops of the 307).
4. **PLATFORM_ONLY** reviewed — confirmed genuinely-missing, not a double-count
   from the duplicate `commission_rules` rows.
5. Headline **Σ`estimatedCommission` ≈ Σ`expected_commission`** within agreed
   tolerance (book-level sanity, not just row-level).

### Output

A reconciliation report (Supabase table `commission_parity_report` or CSV):
one row per compared pair with bucket, both values, delta, and cause. Counts per
bucket + the book-level totals are the cutover scorecard. Re-runnable on demand
(it's read-only) so you can watch MISMATCH/CRM_ONLY drive to zero before flipping.

### Known noise to expect (don't mistake for failures)

- **Duplicate `commission_rules` rows** (carrier+lob+ALL) — the engine picks
  deterministically; can surface as small rate diffs. Reconcile the rule data, don't
  patch per-row.
- **Tiered rates** are *legit* (NEXT BOP Tier1/2/3, SIMPLICITY Whole Life) — a
  `tier_ambiguous` flag is expected, not a bug.
- **Endorsements** — `commissionType='Endorsement'` may have no engine path yet;
  count them so the gap is visible rather than silently CRM_ONLY.

## Reuse map (build nothing twice)

| Need | Use what exists | Gap to close |
|---|---|---|
| Rate cards | `commission_rules` (216 rows) | none — canonical |
| Expected/actual ledger | `commission_ledger` (+ `espocrm_policy_id`, RLS) | reconciliation write paths |
| Calculation | `Commsions/server/commissionRules.js` | none — done |
| Ledger writes | `Commsions/server/commissionLedgerStore.js` | reconciliation grain rows |
| CRM event feed | webhook + dispatcher (PHP) | **live config (Step C)** |
| Workspace UI | RSG Commission Tracker (React) | exceptions/approval tab |
| Reconciliation logic | `commission-reconciliation` skill | wire to ledger deltas |
| Analytics/snapshots | Supabase (KPI history) | point workspace reads here |

---

## Open decisions (carried from the crosswalk + new)

1. **Statement ingest format** — carrier statements arrive as CSV/PDF/portal.
   Define one column-mapping layer (the field-inventory CSV is *not* a record
   export — real carrier files have different headers).
2. **Reconciliation grain confirmed?** — one ledger row per policy-per-statement
   (recommended) vs. engine's WonPolicy+Statement split.
3. **Auto-approve threshold** — which `audit_status` / delta sizes ever
   auto-commit vs. always gate.
4. **Pull cadence** — how often the reconciliation pull runs (and whether it's a
   scheduled Claude job, the engine server, or a local worker).
5. **Net vs. gross** — surface `rsg_net_commission` (after split) alongside gross
   expected (recommended: both).

## Build sequence (when you green-light it)

- **C. Go live on the webhook** — set `commissionEngineWebhookUrl`/`Secret` in
  EspoCRM config, deploy the 2 PHP files, rebuild; e2e-test one closed policy
  lands an expected ledger row.
- **D. Reconciliation pull** — scheduled read-only refresh + statement import →
  `actual_commission`/`delta`.
- **E. Exceptions tab + gate** — surface `audit_status` rows for approval; wire
  the deterministic committer for any CRM write-back.
- **F. Owed/at-risk views** — drive from ledger deltas (feeds `commission-reconciliation`).

## Verification (per stage)

- **Staging:** close a test policy with a deliberately mismatched carrier → row
  appears with `audit_status='carrier_unmatched'`, expected not silently 0.
- **Calculation:** a clean AmTrust/Workers Comp/New policy → expected matches the
  rule's `nb_percent`.
- **Reconciliation:** import a statement with a short payment → `delta` and
  "owed" reflect it; a cancellation zeroes expected and badges "Reversed".
- **Gate:** a tier-ambiguous row stays out of "final" totals until approved.
