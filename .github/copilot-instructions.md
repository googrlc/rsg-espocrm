# Copilot Instructions

## Engineering Guardrails

- Never write directly to production.
- Never change database schema without a migration file.
- Never modify EspoCRM core files.
- Only use `custom/` and `client/custom/` for EspoCRM customizations.
- All sync changes must include logging, rollback notes, and test cases.
- All Supabase writes must respect RLS and use service-role keys only on backend services.
- All client data must be treated as confidential.

## Enforcement Checklist (PR + CI Hints)

- [ ] No direct production writes.
- [ ] Migration file + rollback notes for schema changes.
- [ ] No EspoCRM core edits; use `custom/` and `client/custom/`.
- [ ] Sync changes include logging and test cases.
- [ ] Supabase writes respect RLS; service-role keys backend-only.
