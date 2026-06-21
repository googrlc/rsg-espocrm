# CRM Cleanup — Soft-Delete Review Record (2026-06-18)

**What:** Bucket B-SAFE record removals from `CRM_CLEANUP_BRIEF.md`, cleared by the audit's
adversarial pass. **All actions are soft-deletes** (`deleted = 1`) — fully recoverable.
Executed by Hermes (Claude Code) on the live `espocrm-ts` box, EspoCRM-only (NowCerts
writeback is off, so none of this propagates to the AMS).

**Pre-delete safety verified:** the 2 account stubs each had 0 policies / 0 contacts /
0 commissions; the Atlas opp loser had 0 commissions. Survivors retain all data.

---

## 1. Test renewals soft-deleted (4)

| id | name | stage | account_id | policy_id (kept, NOT deleted) |
|---|---|---|---|---|
| `69eba399b011a486b` | Dream Chaser Trucking - Other Renewal | Identified | 69bde6d091c5e3098 | 69eba393ad77131f2 |
| `6a1cd10456277e7c4` | Darren Boykin - Other Renewal | Identified | 6a16fc3b613751dd4 | 6a173d72588343902 |
| `6a1cd1078c1b9d179` | Sclafani Waste Services - General Liability Renewal | Identified | 6a173bed0e6a60b2e | 6a173d76609038f01 |
| `6a1cd107bea074a8c` | Zakir Hussain - Commercial Package Renewal | Identified | 69bde6d01631bb3fa | 6a173d72824384a07 |

Note: the auto-generated scaffolding (≈1 task + 1 note per renewal) was left in place — orphaned but harmless; clean separately if desired. The linked **policies were not touched**.

## 2. Duplicate-account stubs soft-deleted (2) — merge-in-place onto momentum survivor

| retired stub (deleted) | name | momentum_client_id | survivor (kept) | survivor momentum_client_id |
|---|---|---|---|---|
| `6a16fc618a7472181` | Micah Gray | (none) | `69bde6d06ff54a8c0` | 1c24534f-03fa-4e69-97c6-debe6e9c527a |
| `6a16fc19cc3ce9793` | Shamira Douglas | (none) | `6a173bf660434f2ad` | a0ca0deb-0c5c-4208-af88-88f9656eed2d |

Stubs were empty (verified 0 policies/contacts/commissions), so the "merge" is just retiring the empty duplicate — survivors already hold all records.

## 3. Duplicate opportunity soft-deleted (1)

| retired (deleted) | name | stage | account_id | kept |
|---|---|---|---|---|
| `6a0217673a4e111ad` | Atlas Portection Commercial Auto | Closed Lost | 6a16fc1965b8a6ef1 | `6a0215aaaaa171a90` |

(0 commissions on the loser; both Closed Lost on the same account. "Portection" is a typo in the opp name — the account name is correct; not changed here.)

---

## How to restore (any record)

Soft-deletes are reversible by flipping `deleted` back to 0 on the box:

```sql
-- renewals
UPDATE espocrm.renewal SET deleted=0 WHERE id IN ('69eba399b011a486b','6a1cd10456277e7c4','6a1cd1078c1b9d179','6a1cd107bea074a8c');
-- accounts
UPDATE espocrm.account SET deleted=0 WHERE id IN ('6a16fc618a7472181','6a16fc19cc3ce9793');
-- opportunity
UPDATE espocrm.opportunity SET deleted=0 WHERE id='6a0217673a4e111ad';
```
Then `php command.php clear-cache`. (Run inside `app-mysql-1` / `app-espocrm-1` on `espocrm-ts`.)

## NOT done (still queued / out of scope)
- Commission relink/repair — **infeasible as a relink** (428/445 orphans have no account; only 2 of 17 match a surviving policy). Real fix = purge-stale + regenerate-from-policies; deferred.
- B5 "27 dead Opportunity fields" — held; includes `gb_*`/`medicare` feature blocks that are likely intentional-but-unadopted (same as the Account feature blocks the brief said to keep).
- B-REVIEW human queue (84-dup policy adjudication, 13 Unknown-Client renewals re-link, Account column drop) — needs human review.
