#!/usr/bin/env bash
#
# Deploy the latest main branch on the server.
# Run as the `deploy` user inside /srv/malawiadventistmusic.
#
# Usage:
#   cd /srv/malawiadventistmusic
#   ./deployment/deploy.sh                 # deploy latest main
#   REF=abc1234 ./deployment/deploy.sh     # deploy specific commit/tag
#
# Env:
#   REF        — git ref to check out (default: origin/main)
#   COMPOSE    — compose file (default: docker-compose.prod.yml)

set -euo pipefail

REF="${REF:-origin/main}"
COMPOSE="${COMPOSE:-docker-compose.prod.yml}"

cd "$(dirname "$0")/.."

echo "==> Snapshotting current commit for rollback"
PREV_COMMIT=$(git rev-parse HEAD 2>/dev/null || echo "unknown")
echo "$PREV_COMMIT" > .last-good-commit

echo "==> Fetching latest"
git fetch --all --prune

echo "==> Checking out ${REF}"
git checkout --force "$REF"
NEW_COMMIT=$(git rev-parse HEAD)

if [ "$PREV_COMMIT" = "$NEW_COMMIT" ]; then
    echo "==> Already at $NEW_COMMIT — nothing to do"
    exit 0
fi

echo "==> Building images"
docker compose -f "$COMPOSE" build app

echo "==> Applying migrations + rolling out"
docker compose -f "$COMPOSE" up -d --remove-orphans

echo "==> Waiting for /up health"
for i in $(seq 1 30); do
    if curl -fs --max-time 5 -o /dev/null http://127.0.0.1/up; then
        echo "==> Healthy"
        break
    fi
    if [ "$i" = "30" ]; then
        echo "!!  Health check never passed — consider rollback (deployment/rollback.sh)"
        exit 1
    fi
    sleep 2
done

echo "==> Pruning old images"
docker image prune -f >/dev/null || true

echo
echo "==== Deployed ${NEW_COMMIT:0:12} (from ${PREV_COMMIT:0:12}) ==="
