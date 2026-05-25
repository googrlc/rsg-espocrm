#!/bin/bash
# deploy-to-crm.sh — Push latest changes to CRM server via SSH

set -e

HOST="espocrm-ts"
REMOTE_APP_PATH="/opt/app/espocrm"
SSH_OPTS=(-o BatchMode=yes -o StrictHostKeyChecking=accept-new)
DRY_RUN=0
RSYNC_FLAGS=(-avz)

usage() {
    cat <<'EOF'
Usage: ./deploy-to-crm.sh [--dry-run]

Options:
  --dry-run   Preview rsync changes and skip rebuild.
  -h, --help  Show this help message.
EOF
}

while [[ $# -gt 0 ]]; do
    case "$1" in
        --dry-run)
            DRY_RUN=1
            RSYNC_FLAGS+=(-n)
            shift
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        *)
            echo "Unknown option: $1" >&2
            usage >&2
            exit 1
            ;;
    esac
done

echo "Deploying to CRM server..."

echo "Running preflight checks..."
ssh "${SSH_OPTS[@]}" "$HOST" "
    set -e
    test -d '$REMOTE_APP_PATH'
    test -d '$REMOTE_APP_PATH/custom'
    test -d '$REMOTE_APP_PATH/client/custom'
    docker ps --format '{{.Names}}' | grep -qx 'app-espocrm-1'
"

echo "Syncing custom backend files..."
rsync "${RSYNC_FLAGS[@]}" \
    --exclude '.git' \
    --exclude 'node_modules' \
    --exclude '.vscode' \
    --exclude '.claude' \
    --exclude '.temp' \
    -e "ssh ${SSH_OPTS[*]}" \
    ./custom/ \
    "$HOST:$REMOTE_APP_PATH/custom/"

echo "Syncing custom frontend files..."
rsync "${RSYNC_FLAGS[@]}" \
    --exclude '.git' \
    --exclude 'node_modules' \
    --exclude '.vscode' \
    --exclude '.claude' \
    --exclude '.temp' \
    -e "ssh ${SSH_OPTS[*]}" \
    ./client/custom/ \
    "$HOST:$REMOTE_APP_PATH/client/custom/"

if [[ "$DRY_RUN" -eq 1 ]]; then
    echo "Dry run complete. No rebuild was executed."
    exit 0
fi

echo "Fixing ownership and rebuilding EspoCRM..."

ssh "${SSH_OPTS[@]}" "$HOST" << 'DEPLOY_EOF'
    set -e
    chown -R www-data:www-data /opt/app/espocrm/custom /opt/app/espocrm/client/custom
    cd /opt/app
    docker exec -u www-data app-espocrm-1 php command.php clear-cache
    docker exec -u www-data app-espocrm-1 php command.php rebuild
    echo "Bumping appTimestamp to bust JS cache..."
    docker exec -u www-data app-espocrm-1 php -r '
        $now = time();
        foreach (["data/state.php", "data/config.php"] as $f) {
            $path = "/var/www/html/" . $f;
            if (!is_file($path)) continue;
            $data = include $path;
            $data["appTimestamp"] = $now;
            $data["cacheTimestamp"] = $now;
            file_put_contents($path, "<?php\nreturn " . var_export($data, true) . ";\n");
        }
        echo "appTimestamp set to: $now\n";
    '
    docker exec app-espocrm-1 apache2ctl graceful 2>/dev/null || true
DEPLOY_EOF

echo "Deployment complete."
