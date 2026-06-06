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
| Database | MySQL / MariaDB-compatible, running in Docker as `app-mysql-1` |
| Metadata/Config | JSON |
| Workflows | n8n JSON exports |
| Deployment | Bash + rsync + SSH |

### Runtime Database

- EspoCRM stores application data in a MySQL-compatible database.
- The Elestio Docker runtime uses the database container/service `app-mysql-1`.
- The active database name is `espocrm`.
- Do not commit database passwords or live secrets. Keep them in 1Password, Elestio settings, or local `.env` files only.

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

#### SSH Access — EspoCRM Server

**Direct (Elestio public IP):**
```bash
ssh root@rrespocrm-rsg-u69864.vm.elestio.app
```
> Port 22 is IP-restricted on the EspoCRM box — you may need to whitelist your current IP in the Elestio dashboard first.

**Via Tailscale (preferred, no IP restriction):**
```bash
ssh espocrm-ts
# or directly: ssh root@100.117.239.109
```

**SSH key:** `RSG Elestio EspoCRM` in 1Password. Ensure it is loaded before connecting:
```bash
ssh-add -l   # check loaded keys
```
`~/.ssh/config` has aliases set up (`espocrm-ts`, `n8n-ts`, `n8n-elestio`) — those shorthand names work if the config is intact.

#### API Deploy (layouts + metadata only, no SSH needed)

> ⚠️ The hardcoded `API_KEY` below is frequently **stale (returns 401)** — verify or rotate it before relying on the API. For read-only work, use the cache-pull method in the next section instead (no key needed).

```bash
API_KEY="e5df7c321b47427d24046bab814dbb58"
CRM_URL="https://rrespocrm-rsg-u69864.vm.elestio.app"

# Deploy a layout (example: Account detail)
curl -X PUT "$CRM_URL/api/v1/Layout/Account/detail" \
  -H "X-Api-Key: $API_KEY" -H "Content-Type: application/json" \
  -d @custom/Espo/Custom/Resources/layouts/Account/detail.json

# Deploy entity metadata (example: Account entityDefs)
curl -X PUT "$CRM_URL/api/v1/Metadata/entityDefs/Account" \
  -H "X-Api-Key: $API_KEY" -H "Content-Type: application/json" \
  -d @custom/Espo/Custom/Resources/metadata/entityDefs/Account.json
```
> CSS, JS, and PHP files must still be deployed via SSH (`deploy-to-crm.sh`).

#### Read-only metadata / DB review (no API key needed)

When the API key is stale, pull straight from the live container caches over Tailscale (`ssh espocrm-ts`). Containers: `app-espocrm-1` (app, docroot `/var/www/html`), `app-mysql-1` (DB `espocrm`), `app-espocrm-daemon-1`, `app-espocrm-websocket-1`.

Merged, already-resolved data lives in the cache (rebuilt on every `rebuild`):

- `data/cache/application/metadata.php` — merged metadata (`entityDefs`, `scopes`, `clientDefs`, …); **current, snake_case** field names (the committed `metadata/full-metadata.json` can lag)
- `data/cache/application/languages/en_US.php` — merged i18n (entity scopes + a `Global` scope holding common/address-subfield labels)
- `data/cache/application/ormMetadata.php` — ORM attribute→column map

Dump any of them as JSON:

```bash
ssh espocrm-ts "docker exec -u www-data app-espocrm-1 \
  php -r 'echo json_encode((include \"/var/www/html/data/cache/application/metadata.php\")[\"entityDefs\"][\"Account\"]);'"
```

Real DB column types come from MySQL — **credentials live in `data/config-internal.php`**, not `config.php` (merge both). The field-inventory report (`exports/crm_fields_*.csv`) and the per-module Field Guide (`field-reference/modules/`) are regenerated from such a pull via `tools/build-crm-field-inventory.py` and `tools/build-module-docs.py` (inputs vendored under `metadata/live-pull/`).

### Important Notes

- No `package.json`, `composer.json`, or `requirements.txt` exists — no package manager is needed.
- Python scripts in `tools/` use only stdlib (json, pathlib, importlib) — no pip install required.
- The JavaScript files use EspoCRM's AMD `define()` pattern; they are not CommonJS/ESM modules.
- JSON files under `entity-defs/`, `metadata/`, and `metadata/live-pull/` are read-only metadata dumps for reference/report generation, not deployed files.
- The `n8n/` directory contains workflow JSON exports; they are imported into n8n's UI, not executed locally.
