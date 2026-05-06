# AGENTS.md

## Cursor Cloud specific instructions

### Repository Overview

This is a **configuration-as-code** repository for a customized EspoCRM instance (RSG insurance brokerage). It contains PHP server-side hooks, JavaScript client-side views, metadata/entity definitions, layouts, i18n translations, CSS theme, n8n workflow exports, and database migration scripts.

**There is no local application to run.** The CRM runs remotely on Elestio (Docker-hosted EspoCRM). Development consists of editing files locally and deploying via `rsync` + SSH to the remote server.

### Technology Stack

| Layer | Technology |
|-------|-----------|
| Backend (hooks/classes) | PHP 8.x (EspoCRM framework) |
| Frontend (views) | JavaScript (Backbone.js / AMD modules) |
| Templates | Handlebars (.tpl) |
| Theme | CSS (`client/custom/css/momentum.css`) |
| Migrations | Python 3 (stdlib only, no pip dependencies) |
| Metadata/Config | JSON |
| Workflows | n8n JSON exports |
| Deployment | Bash + rsync + SSH |

### Linting / Validation Commands

Since there is no package manager, build system, or test framework, validation is done via syntax checks:

```bash
# PHP lint (all 142 custom files)
find custom -name "*.php" -exec php -l {} \;

# JavaScript syntax check (all 15 client views)
find client/custom/src -name "*.js" -exec node --check {} \;

# JSON metadata validation
python3 -c "
import json, sys, os
errors = []
for root, dirs, files in os.walk('custom/Espo/Custom/Resources'):
    for f in files:
        if f.endswith('.json'):
            path = os.path.join(root, f)
            try:
                json.load(open(path))
            except Exception as e:
                errors.append(f'{path}: {e}')
if errors:
    for e in errors: print(e)
    sys.exit(1)
print('All JSON valid')
"
```

### Key Directories

- `custom/Espo/Custom/` — Server-side PHP (hooks, controllers, entities, jobs, services)
- `custom/Espo/Modules/RsgCore/` — Shared module for cross-entity orchestrators
- `client/custom/src/views/` — Frontend Backbone.js views (AMD modules)
- `client/custom/css/` — Custom "Momentum" theme
- `client/custom/res/templates/` — Handlebars templates
- `custom/Espo/Custom/Resources/metadata/` — Entity/field definitions (JSON)
- `custom/Espo/Custom/Resources/i18n/` — Translations (JSON)
- `custom/Espo/Custom/Resources/layouts/` — UI layouts (JSON)
- `n8n/` — Exported n8n workflow definitions
- `tools/` — Python migration scripts (no external dependencies)

### Deployment (reference only — requires SSH access)

Deployment is via `deploy-to-crm.sh` which rsyncs `custom/` and `client/custom/` to the remote server, then runs `clear-cache` and `rebuild` inside the Docker container. This requires SSH keys configured for the Elestio host.

### Important Notes

- No `package.json`, `composer.json`, or `requirements.txt` exists — no package manager is needed.
- Python scripts in `tools/` use only stdlib (json, pathlib, importlib) — no pip install required.
- The JavaScript files use EspoCRM's AMD `define()` pattern; they are not CommonJS/ESM modules.
- JSON files under `entity-defs/` and `metadata/` are API dumps for reference, not deployed files.
- The `n8n/` directory contains workflow JSON exports; they are imported into n8n's UI, not executed locally.
