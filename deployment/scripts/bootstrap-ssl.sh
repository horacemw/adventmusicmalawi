#!/usr/bin/env bash
#
# First-deploy SSL bootstrap. Run once on a fresh server before switching to
# the full HTTPS nginx config.
#
# Sequence:
#   1. Swap nginx to the HTTP-only bootstrap config so it can boot without certs.
#   2. Bring up the whole compose stack.
#   3. Wait for nginx to serve /up on port 80.
#   4. Run certbot certonly --webroot to get real certs into the shared volume.
#   5. Swap back to the full site.conf (HTTP + HTTPS + redirect).
#   6. Reload nginx.
#
# Usage: ./deployment/scripts/bootstrap-ssl.sh <letsencrypt-email>

set -euo pipefail

EMAIL="${1:-}"
if [ -z "$EMAIL" ]; then
    echo "Usage: $0 <letsencrypt-email>" >&2
    exit 1
fi

cd "$(dirname "$0")/../.."

DOMAIN="malawiadventistmusic.com"
NGINX_DIR="deployment/nginx"

echo "==> Swapping to HTTP-only bootstrap nginx config"
cp "${NGINX_DIR}/site.conf" "${NGINX_DIR}/site.conf.https-full"
cp "${NGINX_DIR}/site-http.conf" "${NGINX_DIR}/site.conf"

echo "==> Building + booting stack"
docker compose -f docker-compose.prod.yml up -d --build

echo "==> Waiting for nginx to serve /up"
for i in $(seq 1 60); do
    if curl -fs --max-time 3 http://127.0.0.1/up >/dev/null 2>&1; then
        echo "==> Nginx up"
        break
    fi
    if [ "$i" = "60" ]; then
        echo "!!  Nginx never became healthy — aborting SSL bootstrap"
        exit 1
    fi
    sleep 2
done

echo "==> Requesting Let's Encrypt certificate for ${DOMAIN} and www.${DOMAIN}"
docker compose -f docker-compose.prod.yml run --rm --entrypoint "\
    certbot certonly --webroot \
        -w /var/www/certbot \
        --email ${EMAIL} \
        --agree-tos --no-eff-email \
        -d ${DOMAIN} -d www.${DOMAIN}" certbot

echo "==> Swapping to full HTTPS nginx config"
cp "${NGINX_DIR}/site.conf.https-full" "${NGINX_DIR}/site.conf"
rm "${NGINX_DIR}/site.conf.https-full"

echo "==> Reloading nginx"
docker compose -f docker-compose.prod.yml exec nginx nginx -s reload

echo "==== SSL bootstrap complete ==="
echo "Site is live at https://${DOMAIN}"
