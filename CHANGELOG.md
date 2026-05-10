# Changelog

## Engineering Guardrails

- Never write directly to production.
- Never change database schema without a migration file.
- Never modify EspoCRM core files.
- Only use `custom/` and `client/custom/` for EspoCRM customizations.
- All sync changes must include logging, rollback notes, and test cases.
- All Supabase writes must respect RLS and use service-role keys only on backend services.
- All client data must be treated as confidential.

## Entry Requirements

- Reference migration files for any schema change entries.
- Include rollback notes for sync-impacting changes.
- Include test evidence for sync or data-flow changes.
