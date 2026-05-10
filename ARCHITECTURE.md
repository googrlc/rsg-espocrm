# Architecture

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
- [ ] Schema changes include migration files and rollback notes.
- [ ] Espo customizations remain inside `custom/` and `client/custom/`.
- [ ] Sync updates include logging and test cases.
- [ ] Supabase write operations respect RLS and backend-only service-role usage.
- [ ] Required validation checks run in CI before merge.
