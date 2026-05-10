## Summary

Describe what changed and why.

## Enforcement Checklist

- [ ] No direct production writes; deployment remains git-driven.
- [ ] Any schema change includes a migration file.
- [ ] Rollback notes are documented for sync/data-impacting changes.
- [ ] No EspoCRM core files were modified.
- [ ] EspoCRM customizations are limited to `custom/` and `client/custom/`.
- [ ] Sync changes include logging behavior and test cases.
- [ ] Supabase writes respect RLS and keep service-role keys backend-only.
- [ ] Client data handling remains confidential and least-privilege.

## Validation

- [ ] PHP lint passed (`find custom -name "*.php" -exec php -l {} \;`)
- [ ] JS syntax check passed (`find client/custom/src -name "*.js" -exec node --check {} \;`)
- [ ] JSON validation passed (metadata/i18n/layout resources)
