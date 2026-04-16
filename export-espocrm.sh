#!/bin/bash
# RSG EspoCRM Auto-Export Script
# Exports metadata via API + syncs custom PHP files via Tailscale SSH

ESPO_KEY=$(op item get 2clmog73qj45np6a5tswavfaza --fields claude_api 2>/dev/null)
BASE="https://rrespocrm-rsg-u69864.vm.elestio.app/api/v1"
TAILSCALE_IP="100.117.239.109"
REPO="/Users/lamarcoates/Documents/GitHub/rsg-espocrm"
LOG="$REPO/export.log"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Starting EspoCRM export..." >> $LOG

# --- Part 1: API Metadata (works over public HTTPS, no Tailscale needed) ---
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Pulling metadata via API..." >> $LOG
curl -s -H "X-Api-Key: $ESPO_KEY" "$BASE/Metadata" \
  -o $REPO/metadata/full-metadata.json >> $LOG 2>&1

for entity in Account Contact Lead Opportunity Task Policy Renewal; do
  curl -s -H "X-Api-Key: $ESPO_KEY" "$BASE/Metadata?scope=$entity" \
    -o $REPO/entity-defs/$entity.json >> $LOG 2>&1
done

# --- Part 2: Custom files via Tailscale SSH (PHP + client JS/CSS/templates) ---
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Syncing custom files via Tailscale..." >> $LOG
if ping -c 1 -W 2 $TAILSCALE_IP > /dev/null 2>&1; then
  SSH_AUTH_SOCK="$HOME/Library/Group Containers/2BUA8C4S2C.com.1password/t/agent.sock"
  CONTAINER=$(SSH_AUTH_SOCK="$SSH_AUTH_SOCK" ssh -o StrictHostKeyChecking=no root@$TAILSCALE_IP \
    "docker ps -q --filter 'name=app-espocrm-1'" 2>/dev/null)
  if [ -n "$CONTAINER" ]; then
    # Export server-side custom (PHP, metadata, layouts)
    SSH_AUTH_SOCK="$SSH_AUTH_SOCK" ssh -o StrictHostKeyChecking=no root@$TAILSCALE_IP \
      "docker exec $CONTAINER tar czf /tmp/espocrm-custom.tar.gz -C /var/www/html custom/" 2>/dev/null
    SSH_AUTH_SOCK="$SSH_AUTH_SOCK" scp -o StrictHostKeyChecking=no \
      root@$TAILSCALE_IP:/tmp/espocrm-custom.tar.gz /tmp/ 2>/dev/null
    tar xzf /tmp/espocrm-custom.tar.gz -C $REPO/ 2>/dev/null
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ✅ Custom PHP/metadata synced" >> $LOG

    # Export client-side custom (JS views, CSS, templates)
    SSH_AUTH_SOCK="$SSH_AUTH_SOCK" ssh -o StrictHostKeyChecking=no root@$TAILSCALE_IP \
      "docker exec $CONTAINER sh -c 'if [ -d /var/www/html/client/custom ]; then tar czf /tmp/espocrm-client-custom.tar.gz -C /var/www/html client/custom/; fi'" 2>/dev/null
    SSH_AUTH_SOCK="$SSH_AUTH_SOCK" scp -o StrictHostKeyChecking=no \
      root@$TAILSCALE_IP:/tmp/espocrm-client-custom.tar.gz /tmp/ 2>/dev/null 2>&1
    if [ -f /tmp/espocrm-client-custom.tar.gz ]; then
      tar xzf /tmp/espocrm-client-custom.tar.gz -C $REPO/ 2>/dev/null
      echo "[$(date '+%Y-%m-%d %H:%M:%S')] ✅ Client-side custom (JS/CSS/templates) synced" >> $LOG
    else
      echo "[$(date '+%Y-%m-%d %H:%M:%S')] ℹ️ No client/custom/ directory found on server" >> $LOG
    fi
  fi
else
  echo "[$(date '+%Y-%m-%d %H:%M:%S')] ⚠️ Tailscale not reachable — skipping file sync" >> $LOG
fi

# --- Part 3: Commit and push ---
cd $REPO
git add -A
git diff --cached --quiet || git commit -m "auto: EspoCRM backup $(date '+%Y-%m-%d')" >> $LOG 2>&1
git push origin main >> $LOG 2>&1

echo "[$(date '+%Y-%m-%d %H:%M:%S')] ✅ Export complete" >> $LOG
