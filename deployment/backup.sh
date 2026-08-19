#!/usr/bin/env bash
#
# Nightly backup: mysqldump + storage snapshot.
# Run via cron on the host:
#   0 3 * * * /srv/malawiadventistmusic/deployment/backup.sh >> /var/log/mam-backup.log 2>&1
#
# Env vars:
#   BACKUP_DIR       — where to store backups (default: /srv/backups/malawiadventistmusic)
#   RETENTION_DAYS   — how many days of daily backups to keep (default: 30)
#   RSYNC_TARGET     — optional user@host:/path for offsite rsync (Hetzner Storage Box)

set -euo pipefail

cd "$(dirname "$0")/.."

BACKUP_DIR="${BACKUP_DIR:-/srv/backups/malawiadventistmusic}"
RETENTION_DAYS="${RETENTION_DAYS:-30}"
RSYNC_TARGET="${RSYNC_TARGET:-}"

STAMP=$(date -u +%Y%m%dT%H%M%SZ)
mkdir -p "$BACKUP_DIR"

# Load .env for DB credentials
if [ -f .env ]; then
    set -a
    . ./.env
    set +a
fi

echo "==> Dumping MySQL"
docker compose -f docker-compose.prod.yml exec -T mysql \
    mysqldump -u root -p"${DB_ROOT_PASSWORD}" --single-transaction --quick --routines --triggers "$DB_DATABASE" \
    | gzip -9 > "${BACKUP_DIR}/db-${STAMP}.sql.gz"

echo "==> Archiving uploaded storage"
docker compose -f docker-compose.prod.yml run --rm -T --entrypoint sh app \
    -c "tar -czf - -C /var/www/html/storage/app/public ." \
    > "${BACKUP_DIR}/storage-${STAMP}.tar.gz"

echo "==> Pruning backups older than ${RETENTION_DAYS} days"
find "$BACKUP_DIR" -type f -name "*.gz" -mtime "+${RETENTION_DAYS}" -delete

if [ -n "$RSYNC_TARGET" ]; then
    echo "==> Offsite rsync -> ${RSYNC_TARGET}"
    rsync -az --delete "$BACKUP_DIR/" "$RSYNC_TARGET/"
fi

echo "==== Backup ${STAMP} complete ==="
