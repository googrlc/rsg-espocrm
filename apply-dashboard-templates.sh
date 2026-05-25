#!/bin/bash
# apply-dashboard-templates.sh — rebuild Espo metadata and apply dashboard templates on the CRM server

set -euo pipefail

HOST="espocrm-ts"
SSH_OPTS=(-o BatchMode=yes -o StrictHostKeyChecking=accept-new)

ssh "${SSH_OPTS[@]}" "$HOST" <<'EOF'
set -e
cd /opt/app
docker exec -u www-data app-espocrm-1 php command.php clear-cache
docker exec -u www-data app-espocrm-1 php command.php rebuild
docker exec -u www-data app-espocrm-1 php /var/www/html/custom/scripts/deploy-dashboard-templates.php
EOF

echo "Dashboard templates applied."
