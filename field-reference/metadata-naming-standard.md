# CRM Metadata Naming Standard

Use this for new field labels, dropdown options, and future cleanup passes.

## Field Labels

- Use Title Case for anything users see.
- Prefer plain English over internal shorthand.
- Keep labels short, but not cryptic.
- Match the same concept everywhere.
- Use `Phone Call`, not `Phone` in one place and `Call` in another.
- Use `Text Message`, not `Text` in one place and `SMS` in another.
- Expand abbreviations unless they are genuinely standard for the team.
- Good exceptions: `FEIN`, `VIN`, `PPO`, `HMO`, `EPO`, `POS`, `LLC`.

## Dropdown Options

- Do not change stored option keys casually; prefer updating display labels in i18n.
- Use Title Case for option labels.
- Keep status labels as statuses and category labels as categories.
- Avoid mixing shorthand and expanded wording in the same list.
- Order options logically for the user, not alphabetically by accident.
- Severity order: `Critical`, `High`, `Medium`, `Low`.
- Lifecycle order should follow the real workflow.

## Insurance Terms

- Prefer `Line of Business` over `LOB` in UI labels.
- Prefer `Workers' Compensation` over `Workers Comp` in display labels.
- Prefer `Business Owners Policy` over `BOP` in display labels.
- Prefer `Homeowners`, `Personal Auto`, `Phone Call`, and `Text Message` consistently.
- Use `Medicare Advantage`, `Medicare Supplement`, and `Prescription Drug Plan` when expanding plan types.

## Entity Patterns

- Lead and pipeline stages should read like workflow states.
- Contact method fields should use the same option labels across entities.
- Risk, urgency, and retention fields should use the same severity scale everywhere possible.
- Industry and LOB options should only be as granular as reporting actually needs.

## Before Shipping

- Validate edited JSON.
- Check for unrelated local changes before deploying.
- Deploy only the intended metadata files when the worktree is dirty.
