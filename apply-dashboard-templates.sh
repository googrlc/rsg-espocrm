#!/bin/bash
# apply-dashboard-templates.sh — rebuild Espo metadata and apply dashboard templates on the CRM server

set -euo pipefail

HOST="root@rrespocrm-rsg-u69864.vm.elestio.app"
SSH_KEY="$HOME/.ssh/rsg_crm_deploy"
SSH_OPTS=(-i "$SSH_KEY" -o BatchMode=yes -o StrictHostKeyChecking=accept-new)

if [[ ! -f "$SSH_KEY" ]]; then
    echo "SSH key not found: $SSH_KEY" >&2
    exit 1
fi

ssh "${SSH_OPTS[@]}" "$HOST" <<'EOF'
set -e
cd /opt/app
docker exec -u www-data app-espocrm-1 php command.php clear-cache
docker exec -u www-data app-espocrm-1 php command.php rebuild
docker exec -u www-data app-espocrm-1 php /var/www/html/custom/scripts/deploy-dashboard-templates.php
EOF

echo "Dashboard templates applied."
