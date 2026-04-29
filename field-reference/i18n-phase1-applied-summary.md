# Phase 1 applied — `en_US` field labels

**Date:** Applied in-repo. **`bin/command rebuild` not run** (per Pass 2).

Files touched:

- `custom/Espo/Custom/Resources/i18n/en_US/Account.json` — Major pass: Intel `… (AI)`, Group Benefits prefixes, removals of duplicate `intelConfidence`, new `csrName`, `lastContactDate`, `npsDate`, `npsScore`, `accountType.MGA`, `intelEntityType` spelled-out option labels.
- `Commission.json` — Extended `fields` with `carrier`, `commissionRate`, `effectiveDate`, `estimatedCommission`, `lineOfBusiness`, `writtenPremium`.
- `Lead.json` — `estimatedPremium`, `xDate`, `intelPackRun`, `medicareEligible`.
- `Opportunity.json` — Premiums/commission/FEIN, Group Benefits gb* alignment.
- `Policy.json` — `commissionRate`, `momentumLastSynced`, `premiumAmount`; `presetFilters` commAuto/genLiability/lifeInsurance.
- `Renewal.json` — `commissionRate`, `carrier`, `premiumChange`.
- `Task.json` — `momentumLastSynced`.
- `Contact.json` — `contactType`, plus `csrName`, `momentumClientId`, `momentumLastSynced`.
- `ActivityLog.json` — `dateTime`, `duration`.

Detailed row-by-row spec remains in `i18n-pass1-label-changes.md` (planned doc); implementations match that plan.
