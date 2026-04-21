#!/bin/bash
# deploy-to-crm.sh — Push latest changes to CRM server via SSH

set -e

HOST="root@rrespocrm-rsg-u69864.vm.elestio.app"
SSH_KEY="$HOME/.ssh/rsg_crm_deploy"
if [[ ! -f "$SSH_KEY" ]]; then
    SSH_KEY="$HOME/.ssh/elestio_espocrm"
fi
REMOTE_APP_PATH="/opt/app/espocrm"
SSH_OPTS=(-i "$SSH_KEY" -o BatchMode=yes -o StrictHostKeyChecking=accept-new)
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

if [[ ! -f "$SSH_KEY" ]]; then
    echo "SSH key not found: $SSH_KEY" >&2
    exit 1
fi

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

ssh "${SSH_OPTS[@]}" "$HOST" << EOF
    set -e
    chown -R www-data:www-data "$REMOTE_APP_PATH/custom" "$REMOTE_APP_PATH/client/custom"
    cd /opt/app
    docker exec -u www-data app-espocrm-1 php command.php clear-cache
    docker exec -u www-data app-espocrm-1 php command.php rebuild
EOF

echo "Deployment complete."
