# Layout pass: hide duplicate Account fields (deliverable)

**Scope:** `custom/Espo/Custom/Resources/layouts/**` only. No `entityDefs`, PHP, JS, or DB. No field renames (layout references updated only where noted).

**Note:** The brief mentioned `layoutDetailDisabled` on `entityDefs`; per your **layout-only** scope, changes are **JSON layout removals/replacements** instead.

---

## `Entity | Field Name | Layout File | Action`

| Entity | Field Name | Layout File | Action |
|--------|------------|-------------|--------|
| Account | activePolicyCount | `layouts/Account/detail.json` | Replaced cell with **`policyCountActive`** (read-only computed aggregate). |
| Account | numberOfEmployees | `layouts/Account/detail.json` | Replaced cell with **`employeeCount`** (two occurrences: Business Profile + Business Identity sections). |
| Account | intelWebsite | `layouts/Account/detail.json` | Hidden: cell set to **`false`** (Business Identity row; core **`website`** remains on Business Profile tab). |
| Account | intelBbbRating | `layouts/Account/detail.json` | Replaced cell with **`bbbRating`** (BBB & Reputation panel). |
| Account | intelLinkedinUrl | `layouts/Account/detail.json` | Replaced cell with **`linkedinUrl`** (BBB & Reputation panel). |
| Account | intelPackLastRun | `layouts/Account/detail.json` | Hidden: second cell in AI Account Assessment row set to **`false`**. |
| Account | intelPackRun | `layouts/Account/detail.json` | Removed: entire **`aiIntelPack`** tab panel deleted (contained intel pack automation fields). |
| Account | intelPackLastRun | `layouts/Account/detail.json` | Removed: with **`aiIntelPack`** panel (same panel as `intelPackRun`). |
| Account | totalAnnualPremium | `layouts/Account/list.json` | Replaced list column with **`totalActivePremium`**. |
| Account | intelPackLastRun | `layouts/Account/detailCommercial.json` | Hidden: cell set to **`false`** in AI Account Assessment row. |
| Account | intelWebsite | `layouts/Account/detailCommercial.json` | Replaced cell with **`website`** (Business Identity row). |
| Account | intelLinkedinUrl | `layouts/Account/detailCommercial.json` | Replaced cell with **`linkedinUrl`** (BBB & Reputation row). |

---

## Not present in active layouts (no edit required here)

| Entity | Field Name | Notes |
|--------|------------|--------|
| Account | intelEmployeeCount | Not referenced in current `detail.json` / `detailCommercial.json` / `list.json`. |
| Account | intelAnnualRevenueEst | Not in active Account layouts. |
| Account | intelRun, intelRunDate, intelRunBy | Not in active Account layouts. |
| Account | totalAnnualPremium | Not on `detail.json` (only removed from **list**). |

---

## Other entities (Policy, Renewal, Opportunity, Lead, Contact)

**No changes.** Searched `layouts/` for the duplicate Account field API names; only **Account backup** trees contained them. No `detailSmall.json` exists for Account in this repo.

---

## Post-deploy (Elestio)

1. On the **Elestio** host, run **`bin/command rebuild`** after deploying these layout files.
2. Spot-check **Account detail** and **Account detail (Commercial)** on one real client record.

**This workspace did not run `bin/command rebuild`** (no local Espo instance assumed).

See also **[future-items-non-blocking.md](future-items-non-blocking.md)** (Intel pack admin follow-up + `policyCountActive` / sync confirmation).
