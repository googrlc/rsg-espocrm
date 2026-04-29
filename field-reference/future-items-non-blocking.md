# Future items (non-blocking)

## Account — Intel Pack / automation visibility (removed from default layout)

**Context:** The **`aiIntelPack`** layout panel was removed in the duplicate-field layout pass. Those cells (`intelPackRun`, `intelPackLastRun`, etc.) are **automation trigger flags**, not day-to-day account data.

**If you want them visible again for admins:**

- Prefer a **dedicated admin or Ops layout variant** / panel that only admins see, **or**
- Use **`layoutDetailDisabled: true`** (and optionally **`layoutListDisabled: true`**) on selected fields in `entityDefs` for default layouts, combined with **role-based layout overrides** where Espo supports them, **or**
- Restore a narrowly scoped tab/panel gated by team/role via **dynamic layouts** / custom client defs (Pass 3+),

…rather than returning them to the primary Account employee view.

This is documented as a **follow-up**, not required for Pass 3 blockers related to duplicates.

---

## `policyCountActive` vs `activePolicyCount` — backend contract

`PolicyAccountSync::refreshAccountMetricsById()` **writes both** fields to the **same integer**:

```157:158:custom/Espo/Modules/RsgCore/Classes/Policy/PolicyAccountSync.php
        $account->set('activePolicyCount', $activePolicyCount);
        $account->set('policyCountActive', $activePolicyCount);
```

So **`policyCountActive` should mirror `activePolicyCount`** after sync runs — switching the Coverage Overview layout cell to **`policyCountActive`** is aligned with the read-only/tooltip intent (computed aggregate).

**Manual visual check still recommended:** open a known multi-policy Account on Elestio and confirm the displayed count matches expectation. If **`policyCountActive` is blank or zero while `activePolicyCount` is populated**, that implies **another writer** skewing fields or stale records (Pass 3: normalize writers / single source of truth). If **both are wrong**, troubleshoot sync inputs (Policy `status`, `premiumAmount`, `accountId` linkage) before schema work.
