#!/usr/bin/env bash
#
# Roll back to the last known good commit. Snapshotted by deploy.sh into .last-good-commit.
#
set -euo pipefail

cd "$(dirname "$0")/.."

if [ ! -s .last-good-commit ]; then
    echo "ERROR: no .last-good-commit file — nothing to roll back to." >&2
    exit 1
fi

LAST=$(cat .last-good-commit)
echo "==> Rolling back to ${LAST}"
git checkout --force "$LAST"

docker compose -f docker-compose.prod.yml build app
docker compose -f docker-compose.prod.yml up -d --remove-orphans

echo "==== Rolled back to ${LAST:0:12} ==="
