# Deployment

## SSH Access

- Host: `rrespocrm-rsg-u69864.vm.elestio.app`
- SSH key: `RSG Elestio EspoCRM` in 1Password
- Access note: Port 22 is IP-restricted; whitelist your current IP in Elestio first

## Engineering Guardrails

- Never write directly to production.
- Never change database schema without a migration file.
- Never modify EspoCRM core files.
- Only use `custom/` and `client/custom/` for EspoCRM customizations.
- All sync changes must include logging, rollback notes, and test cases.
- All Supabase writes must respect RLS and use service-role keys only on backend services.
- All client data must be treated as confidential.

## Enforcement Checklist (PR + CI Hints)

- [ ] No direct production edits; deploy via repository workflow only.
- [ ] Any database schema change includes a migration file and rollback notes.
- [ ] EspoCRM edits are limited to `custom/` and `client/custom/`.
- [ ] Sync changes include logging behavior and test cases.
- [ ] Supabase write paths enforce RLS; service-role keys are backend-only.
- [ ] CI runs syntax validation: PHP lint, JS syntax check, and JSON validation.
