#!/usr/bin/env bash
set -euo pipefail

# Runs on every container start (app / queue-worker / scheduler).
# The APP_ROLE env var picks the workload; default is php-fpm.

cd /var/www/html

# Make sure the storage symlink is present (public/storage → storage/app/public).
if [ ! -L public/storage ] || [ ! -e public/storage ]; then
    php artisan storage:link --force || true
fi

# One-off migrations. Guard so multiple containers don't race.
if [ "${SKIP_MIGRATIONS:-0}" != "1" ]; then
    if [ "${APP_ROLE:-app}" = "app" ]; then
        # A simple mysql-based advisory lock via MIGRATION_LOCK file; only the first `app` container migrates.
        (
            flock -n 200 || { echo "[entrypoint] another container is migrating; skipping"; exit 0; }
            echo "[entrypoint] running migrations"
            php artisan migrate --force
        ) 200>/tmp/mam-migrate.lock || true
    fi
fi

# Cache config/routes/views/events on every start. Cheap and keeps things fast.
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache || true

case "${APP_ROLE:-app}" in
    queue)
        echo "[entrypoint] starting queue worker"
        exec php artisan queue:work redis --sleep=1 --tries=3 --max-time=3600 --timeout=120
        ;;
    scheduler)
        echo "[entrypoint] starting scheduler loop"
        # Poll every minute; artisan schedule:run is idempotent by design.
        while true; do
            php artisan schedule:run --no-interaction >> /proc/self/fd/2 2>&1
            sleep 60
        done
        ;;
    app|*)
        echo "[entrypoint] starting php-fpm"
        exec "$@"
        ;;
esac
