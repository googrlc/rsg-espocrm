#!/usr/bin/env bash
# Apply Pass 4 Account column renames on Elestio-style Docker (mysql container: app-mysql-1).
#
# Usage (on the VM, from repo root or after copying SQL files):
#   export MYSQL_PWD='your_root_password'
#   ./scripts/apply-pass4-account-mysql-elestio.sh your_espocrm_database full
#
# Modes:
#   full   — run tools/migrations/pass4_account_rename_mysql8.sql (all RENAME pairs)
#   claims — run tools/migrations/pass4_one_shot_claims_count_3yr.sql only (fast unblock)
#
# Requires: mysql client on host OR use docker run --rm mysql:8 client.
# This script uses: docker exec -i app-mysql-1 mysql ...

set -euo pipefail

DB_NAME="${1:?Usage: $0 <database_name> [full|claims]}"
MODE="${2:-full}"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"

MYSQL_USER="${MYSQL_USER:-root}"
MYSQL_CONTAINER="${MYSQL_CONTAINER:-app-mysql-1}"

if [[ "$MODE" == "claims" ]]; then
  SQL="$ROOT/tools/migrations/pass4_one_shot_claims_count_3yr.sql"
elif [[ "$MODE" == "full" ]]; then
  SQL="$ROOT/tools/migrations/pass4_account_rename_mysql8.sql"
else
  echo "Second arg must be 'full' or 'claims'" >&2
  exit 1
fi

if [[ ! -f "$SQL" ]]; then
  echo "Missing SQL file: $SQL" >&2
  exit 1
fi

if ! docker ps --format '{{.Names}}' | grep -qx "$MYSQL_CONTAINER"; then
  echo "Container not running: $MYSQL_CONTAINER (set MYSQL_CONTAINER=...)" >&2
  exit 1
fi

PW="${MYSQL_ROOT_PASSWORD:-${MYSQL_PWD:-}}"
if [[ -z "$PW" ]]; then
  echo "Set MYSQL_ROOT_PASSWORD or MYSQL_PWD for mysql -p (or run the SQL manually)." >&2
  exit 1
fi

echo "Applying $SQL -> $MYSQL_CONTAINER / $DB_NAME (mode=$MODE)"
docker exec -i "$MYSQL_CONTAINER" mysql -u"$MYSQL_USER" -p"$PW" "$DB_NAME" < "$SQL"
echo "Done. Then on app-espocrm-1: php command.php clear-cache && php command.php rebuild"
