# Data Model

## Engineering Guardrails

- Never write directly to production.
- Never change database schema without a migration file.
- Never modify EspoCRM core files.
- Only use `custom/` and `client/custom/` for EspoCRM customizations.
- All sync changes must include logging, rollback notes, and test cases.
- All Supabase writes must respect RLS and use service-role keys only on backend services.
- All client data must be treated as confidential.

## Enforcement Checklist (PR + CI Hints)

- [ ] Data-model changes include migration files with rollback notes.
- [ ] No direct production writes.
- [ ] No EspoCRM core file modifications.
- [ ] Sync/data changes include logging impact and test cases.
- [ ] Supabase write flows enforce RLS and backend-only service-role keys.
