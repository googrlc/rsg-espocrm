# RSG Task Detail Redesign — DESIGN (EspoCRM 9.3.7)

> **REVISION (fixed-field model — authoritative):** S3 (Task Reference Details) and S4 (Confirmation Checklist) are implemented as **fixed fields on Task**, NOT as the `TaskConfirmation` / `TaskReference` related entities described later in this doc. The two-entity sections below are superseded. Actual implementation:
> - **S3 Reference** fields: `refLink`, `refLink2` (url), `refNumber`, `refNumber2`, `refAccessCode`, `refSupportContact` (varchar), `refNotes` (text).
> - **S4 Checklist**: **4 fixed slots** — `confirm{1..4}Label` (varchar), `confirm{1..4}Status` (enum Pending/Confirmed/Needs Follow-up, **audited** for stream logging), `confirm{1..4}Note` (text). Slots 2–4 are hidden via dynamicLogic until the prior slot's Label is filled (progressive disclosure). Close is blocked until every populated slot is `Confirmed`; `confirmationProgress` ("1/2") is maintained by a beforeSave hook. Per-item user+timestamp logging comes from the `audited` status fields (no separate confirmedBy/At).
> - No new entities, scopes, or child layouts. Hooks: `RequireConfirmationsBeforeClose`, `RefreshConfirmationProgress`, `SetCompletedDate` (the `TaskConfirmation/StampAndRollup` hook is removed).

**Status:** REVIEWABLE design spec. NOT deployed. All implementation artifacts will live under `custom/Espo/Custom/Resources/...` so they are **GLOBAL** (identical for every user — Lamar and Gretchen see the same thing). No per-user / per-team / portal layout overrides are used anywhere.

**Goal:** Move structured data out of the single `description` blob into labeled, collapsible sections and typed fields. Scannable in <10s.

**Important correction to the brief:** The brief said `layouts/Task/` is empty and no Task layout/metadata files exist. They DO already exist (`custom/Espo/Custom/Resources/layouts/Task/detail.json`, `metadata/entityDefs/Task.json`, `clientDefs/Task.json`, `scopes/Task.json`, `i18n/en_US/Task.json`). This redesign **rewrites** `detail.json`, **merges** new fields/enums into the existing `entityDefs/Task.json`, **extends** `clientDefs/Task.json` (dynamicLogic + relationshipPanels), and **adds** a new `side.json`. Existing custom record views (`detail-view`, `edit`) and the `assignedUser` custom field view are kept.

---

## 1. Section → Panel Map

| Spec | Section | EspoCRM implementation | Panel `name` | Collapsed by default? |
|------|---------|------------------------|--------------|------------------------|
| S1 | Task Details (top) | `layouts/Task/detail.json` panel (no header label, default panel) | `taskDetails` | No (open) |
| S2 | Policy & Insured Info (read-only card) | detail.json panel, all fields `readOnly` via layout cell + dynamicLogic; auto-populated by hook from linked `policy` | `policyInsuredInfo` | No |
| S3 | Task Reference Details (flexible key-value) | **Relationship/bottom panel** for `taskReferences` (one-to-many → new entity `TaskReference`). Defined in `clientDefs.bottomPanels.detail`, NOT in detail.json rows | bottom panel `taskReferences` | No |
| S4 | Action Required — Confirmation Checklist | **Relationship/bottom panel** for `taskConfirmations` (one-to-many → new entity `TaskConfirmation`), rendered with a custom panel view for prominence; progress mirrored to `confirmationProgress` field shown in S1 | bottom panel `taskConfirmations` | No |
| S5 | Triage Info | detail.json panel | `triageInfo` | No (open) — **urgency removed from here** |
| S6 | Notes from [Creator] | detail.json panel containing `managerNotes` (wysiwyg); read-only-for-assignee via dynamicLogic; styled callout via panel `style` | `managerNotesPanel` | No |
| S7 | Source Email (conditional) | detail.json panel; `dynamicLogicVisible` on `originalEmail` not-empty; fields read from linked Email via custom panel view; link cell to the Email record | `sourceEmail` | **Yes (collapsed)** |
| S8 | Additional Notes (was Description) | detail.json panel, positioned LAST; `description` relabeled "Additional Notes" | `additionalNotes` | No |
| Side a | Assignment | `layouts/Task/side.json` panel | `assignment` | No |
| Side b | Record Dates | `layouts/Task/side.json` panel (separate header) | `recordDates` | No |

Note on EspoCRM mechanics: one-to-many child collections (S3, S4) are rendered as **bottom relationship panels** driven by `clientDefs` + the link + the child entity layouts — they are never field rows inside `detail.json`. S2 reference fields ARE plain fields on Task (auto-populated), so they sit in a `detail.json` panel.

---

## 2. Field Schema

Legend: **NEW** = added by this redesign; **existing** = already in schema, reused (do not recreate).

### Task panel fields (main form)

| Field | E/N | Type | Section | Notes |
|-------|-----|------|---------|-------|
| `name` | existing | varchar | S1 | required |
| `status` | existing | enum | S1 | keep enum (Inbox…Cancelled). Close blocked by hook until all confirmations Confirmed |
| `taskType` | existing | enum | S1 | **enum extended +3** (see §3) |
| `priority` | existing (core) | enum | S1 | keep |
| `taskSource` | existing | enum | S1 | label "Source". Keep options ('',Account,Contact,Opportunity,Policy) |
| `syncSource` | existing | enum | S1 | **enum changed** Gmail→Email, +API (see §3) |
| `urgency` | existing | enum | S1 | **ONLY appearance** (see §4). Removed from Triage |
| `confirmationProgress` | **NEW** | varchar(16), readOnly, layoutMassUpdateDisabled | S1 | e.g. "1/2"; maintained by hook from `taskConfirmations`. Read-only display chip |
| `account` | existing (link) | link | S2 | label "Insured" in S2 context |
| `contact` | existing (link) | link | S2 + Side a | shown read-only in S2; editable in Assignment side block |
| `policy` | **NEW link** | belongsTo Policy | S2 | many-to-one; the auto-populate source for S2 |
| `policyType` | **NEW** | varchar(100) | S2 | auto-populated from Policy `line_of_business`; read-only display |
| `policyNumber` | **NEW** | varchar(100) | S2 | auto-populated from Policy `policy_number` |
| `carrier` | **NEW** | varchar(150) | S2 | auto-populated from Policy `carrier` (no prior carrier field on Task) |
| `policyEffectiveDate` | **NEW** | date | S2 | Policy Period start; from Policy `effective_date` |
| `policyExpirationDate` | **NEW** | date | S2 | Policy Period end; from Policy `expiration_date` |
| `triageSummary` | existing | text | S5 | keep |
| `triageReason` | existing | text | S5 | keep |
| `managerNotes` | **NEW** | wysiwyg | S6 | read-only for assignee, editable by creator/manager (see §5) |
| `originalEmail` | existing (link to Email) | link | S7 | drives S7 visibility; link to the Email record |
| `description` | existing | text | S8 | **relabeled "Additional Notes"**, moved LAST |
| `assignedUser` | existing | link | Side a | required; keeps custom view `custom:views/task/fields/assigned-user` |
| `dateEnd` | existing | datetime | Side a | label **"Date Due"**. Lives in Assignment ONLY — not duplicated in Record Dates |
| `dateStart` | existing | datetime | Side b | Record Dates |
| `dateCompleted` | existing | datetime | Side b | label **"Completed"**; auto-set by hook on status→Completed; manually editable |
| `createdAt` | existing | datetime | Side b | label "Created", read-only |
| `modifiedAt` | existing | datetime | Side b | label "Modified", read-only; show modifier name (`modifiedBy`) |

Fields intentionally NOT placed on the main form (kept off-layout / system): `dateStartDate`, `dateEndDate`, `isOverdue`, `reminders` (reminders stay in edit view), `parent`, `teams`, `attachments`, `createdBy`, `modifiedBy` (modifier name surfaced via `modifiedAt` cell), `momentumTaskId`, `momentumLastSynced`, `sourceActivityLogId`, `automationKey`, `streamUpdatedAt`. No date fields scattered through the main form — all dates are in the side panel per spec.

### NEW entity: `TaskConfirmation` (S4)

| Field | Type | Notes |
|-------|------|-------|
| `name` | varchar, required | the action label |
| `status` | enum [Pending, Confirmed, Needs Follow-up], default Pending | `displayAsLabel:true`, style {Pending:default, Confirmed:success, "Needs Follow-up":warning} |
| `note` | text, rowsMin 1 | optional inline note |
| `confirmedBy` | link User | who set the current status (stamped by hook) |
| `confirmedAt` | datetime, readOnly | stamped by hook when status changes |
| `order` | int | collection orderBy `order` asc |
| link `task` | belongsTo Task → `taskConfirmations` | |

### NEW entity: `TaskReference` (S3)

| Field | Type | Notes |
|-------|------|-------|
| `name` | varchar, required | the label/key (e.g. "Carrier Portal", "Claim #", "Support PIN") |
| `value` | text, rowsMin 1 | the value / URL / PIN / contact |
| `kind` | enum [Link, Number, PIN, Contact, Email, Text], default Text | `displayAsLabel:true`, style {Link:primary, Number:info, PIN:warning, Contact:success, Email:info, Text:default} |
| `order` | int | collection orderBy `order` asc |
| link `task` | belongsTo Task → `taskReferences` | |

---

## 3. Enum Changes

**`taskType` — add 3 options** (appended after existing domain types, before `Other`):
```
before: Client Service, Policy Change, Renewal, New Business, Follow Up, Onboarding, Claims, Commission, Admin, Other
after:  Client Service, Policy Change, Renewal, New Business, Follow Up, Onboarding, Claims, Commission, Admin,
        Underwriting Request, Underwriter Follow-up, Agency Admin, Other
```
("Other" stays last; the 3 new values are grouped with the operational types.)

**`syncSource` — Gmail→Email, +API:**
```
before: Manual, Gmail, Momentum, n8n, Hermes
after:  Manual, Email, API, Momentum, n8n, Hermes
```
Migration note: existing rows storing `Gmail` should be data-migrated to `Email` (one-time UPDATE in the deploy plan). `API` is new.

No change to `status`, `taskSource`, or `urgency` enums.

---

## 4. Urgency De-dup Statement

`urgency` appears **exactly once** — in **S1 Task Details only**. It is explicitly **removed** from the S5 Triage Info panel (current `detail.json` has it under "Triage Info"; that cell is deleted). It does not appear in the side panel, S2, or any other section. Single source of truth.

---

## 5. Dynamic Logic Rules

1. **S7 Source Email panel visibility** — panel `sourceEmail` is visible only when `originalEmail` is set. Implemented via `clientDefs.dynamicLogic.panels.sourceEmail.visible` with condition `originalEmail isNotEmpty`. Panel is `isCollapsed: true` in `detail.json` (collapsed by default). When no email source exists, the panel is hidden entirely.
2. **S6 managerNotes read-only for assignee** — `managerNotes` is editable by the creator/manager and read-only for the assignee. **Chosen approach:** `clientDefs.dynamicLogic.fields.managerNotes.readOnly` with condition `assignedUserId == $user.id` AND `createdById != $user.id`. This is the client-side enforcement; it is backed up server-side by a `beforeSave` hook (in the hooks generator) that rejects `managerNotes` changes unless the saving user is the creator or has the manager role. (Field-level ACL role is the alternative; dynamicLogic + hook is chosen because it needs the per-record creator/assignee relationship, which a static role cannot express.) The panel label renders "Notes from [Creator Name]" via the custom detail record view reading `createdBy`.
3. **S2 read-only reference card** — `policyType`, `policyNumber`, `carrier`, `policyEffectiveDate`, `policyExpirationDate`, and the S2 `account`/`contact` displays are read-only. Static (always read-only on detail) — set via the layout cell `readOnly:true` plus field-level `readOnly` is avoided (those fields are hook-populated, but may need manual override on tasks with no linked policy), so read-only is applied at the **panel/layout** level via dynamicLogic `readOnly` condition `policyId isNotEmpty` (editable only when there is no linked policy to source from).
4. **S4 close-block** — not pure dynamicLogic; enforced by PHP hook (see §8 / hooks generator): saving `status` into a completed/closed value is rejected unless `confirmationProgress` shows all items Confirmed.
5. **Section visibility by task type (optional, supported)** — panels may be conditionally shown per `taskType` via `clientDefs.dynamicLogic.panels.<name>.visible`. Default: all panels visible for all types. (e.g. could hide S2 Policy card for `Admin`/`Agency Admin` tasks — left as a config toggle, not enabled by default.)

---

## 6. Two New Entities — Rationale & Tradeoff

**Why `TaskConfirmation` and `TaskReference` are separate entities (one-to-many):**
- **S4 and S3 are variable-cardinality.** Each task has a different number of checklist items and reference rows. Fixed columns on Task can't represent "N items" without wasting most of them or capping the count.
- **Per-item state.** S4 requires per-item status toggle + inline note + `confirmedBy`/`confirmedAt` stamping. That is a record with its own fields and audit, not a string.
- **No schema change to add items (S3 requirement).** With child entities, adding a reference or checklist item is a data insert — zero metadata/rebuild. A fixed-field approach would require a schema + rebuild every time someone needs another row.
- **Progress + stamping** (S4) are naturally computed by aggregating child rows and mirrored to the `confirmationProgress` scalar on Task for S1 display and the close-block.

**Simpler alternative (rejected): fixed reference/checklist fields on Task.**
- e.g. `ref1Label`/`ref1Value`…`ref5*` and `check1Label`/`check1Status`…`check6*` directly on Task.
- **Pros:** no new scopes/entities; everything on one record; no relationship panels; slightly simpler ACL.
- **Cons:** hard cap on item count; adding capacity needs a metadata change + rebuild (violates S3 "no schema change to add items"); per-item note/`confirmedBy`/`confirmedAt` balloon the column count (3–4 columns × N); no clean per-item audit; messy layout.
- **Decision:** the child-entity model is worth the two extra Object-scope entities. Both are `tab:false`, `importDisabled`, scope type Object — they never clutter the nav and exist only as Task children.

---

## 7. Global Note

Every artifact in this design is written under `custom/Espo/Custom/Resources/...` (metadata, layouts, i18n) and `custom/Espo/Custom/Hooks/...` (PHP). These are **application-level** resources merged over core for **all users**. There are **no** layout records created in the `LayoutSet`/per-user/per-team/portal layers. Result: Lamar and Gretchen — and every other user — see the identical Task detail layout, the same panels, the same fields, the same collapse states. This is an explicit requirement and is satisfied by construction (Resources, not LayoutSet overrides).

---

## 8. Staged Deploy Plan

Stage artifacts in `_planned/task-redesign/` for review. On approval, copy into the live `custom/` tree in this order:

1. **New entities — scopes + entityDefs first.**
   - `metadata/scopes/TaskConfirmation.json`, `metadata/scopes/TaskReference.json` (entity:true, object:"Object", tab:false, importDisabled:true, type BasePlus, isCustom).
   - `metadata/entityDefs/TaskConfirmation.json`, `metadata/entityDefs/TaskReference.json` (fields + the `task` belongsTo link).
   - `i18n/en_US/TaskConfirmation.json`, `i18n/en_US/TaskReference.json`.
   - `layouts/TaskConfirmation/{detail,list}.json`, `layouts/TaskReference/{detail,list}.json`.
2. **Task entityDefs** — merge new fields (`policyType, policyNumber, carrier, policyEffectiveDate, policyExpirationDate, managerNotes, confirmationProgress`), new links (`policy` belongsTo Policy; `taskConfirmations` hasMany → TaskConfirmation.task; `taskReferences` hasMany → TaskReference.task), and the two enum edits (`taskType` +3, `syncSource` Gmail→Email +API).
3. **Rebuild** (`php command.php rebuild`) — creates the new tables, columns, and the relationship tables. Verify no errors before continuing.
4. **Data migration** — one-time `UPDATE task SET sync_source='Email' WHERE sync_source='Gmail';` (so existing rows match the renamed enum value).
5. **clientDefs + layouts** — update `clientDefs/Task.json` (add `dynamicLogic.panels.sourceEmail`, `dynamicLogic.fields.managerNotes`, `relationshipPanels`/`bottomPanels` for `taskConfirmations` + `taskReferences`, optional per-type panel logic). Write new `layouts/Task/detail.json` (S1,S2,S5,S6,S7,S8 panels) and new `layouts/Task/side.json` (Assignment + Record Dates). Update `i18n/en_US/Task.json` (label `description`→"Additional Notes", `dateEnd`→"Date Due", `dateCompleted`→"Completed", `createdAt`→"Created", `modifiedAt`→"Modified", new field labels, new enum option translations).
6. **Hooks** (separate generator) — `custom/Espo/Custom/Hooks/Task/`: (a) `PopulatePolicyFields` beforeSave — copy Policy `line_of_business`→`policyType`, `policy_number`→`policyNumber`, `carrier`→`carrier`, `effective_date`→`policyEffectiveDate`, `expiration_date`→`policyExpirationDate`, and `name`(Insured) into `account`/display when `policyId` set and field empty; (b) `ConfirmationProgress` (on TaskConfirmation afterSave/afterRemove) — recompute "X/Y" → parent Task `confirmationProgress`, stamp `confirmedBy`/`confirmedAt` on status change; (c) `BlockCloseUntilConfirmed` beforeSave on Task — reject status→Completed unless all confirmations Confirmed; auto-set `dateCompleted` on transition to Completed; (d) `ProtectManagerNotes` beforeSave — reject `managerNotes` change by non-creator/non-manager. (Policy field source names confirmed against live `entityDefs/Policy.json`: snake_case `policy_number`, `carrier`, `line_of_business`, `effective_date`, `expiration_date`, `name`.)
7. **Rebuild** again (`php command.php rebuild`) to register hooks/clientDefs and clear cache.
8. **Smoke test** — open a Task in detail; confirm: S1 shows urgency once + progress chip; S2 auto-fills from a linked Policy and is read-only; S3/S4 bottom panels accept add/edit; S4 blocks Completed until all Confirmed and stamps user/time; S5 has no urgency; S6 read-only for an assignee login, editable for the creator login; S7 hidden when no `originalEmail`, collapsed when present; S8 "Additional Notes" is last; side panel shows Assignment (Date Due) and Record Dates without duplicating Date Due. Verify Gretchen's login renders identically.

### Rollback

- **Fast (layout/clientDefs only):** restore prior `layouts/Task/detail.json` + `clientDefs/Task.json` + `i18n/en_US/Task.json` from git (or the `_backup_*` copies under `Resources/`), then `rebuild`. Reverts the UI without touching data.
- **Full:** `git revert` the deploy commit, remove `custom/Espo/Custom/Hooks/Task/*` new hooks, then `rebuild`. New Task columns and the two child tables are additive and harmless if left in place; to fully remove, disable the `TaskConfirmation`/`TaskReference` scopes (`disabled:true`) or drop via Entity Manager, and drop the new Task columns. Reverse the data migration only if needed: `UPDATE task SET sync_source='Gmail' WHERE sync_source='Email';` (note: this is lossy if `API` was also written — filter accordingly).
- Because everything is in `custom/` (Resources + Hooks) and version-controlled, rollback is a git revert + rebuild; no core files are touched.
