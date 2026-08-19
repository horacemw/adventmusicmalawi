#!/usr/bin/env bash
#
# One-time Let's Encrypt certificate issuance for malawiadventistmusic.com + www.
# Run this AFTER:
#   1. Namecheap A records for @ and www point to the server IP
#   2. `docker compose up -d nginx` is running so the ACME challenge can be served
#
# Usage: ./deployment/scripts/certbot-init.sh you@example.com
#
set -euo pipefail

EMAIL="${1:-}"
if [ -z "$EMAIL" ]; then
    echo "Usage: $0 <letsencrypt-email>" >&2
    exit 1
fi

DOMAIN="malawiadventistmusic.com"

echo "==> Requesting cert for ${DOMAIN} and www.${DOMAIN}"
docker compose -f docker-compose.prod.yml run --rm --entrypoint "\
    certbot certonly --webroot \
        -w /var/www/certbot \
        --email ${EMAIL} \
        --agree-tos --no-eff-email \
        -d ${DOMAIN} -d www.${DOMAIN}" certbot

echo "==> Reloading nginx"
docker compose -f docker-compose.prod.yml exec nginx nginx -s reload

echo "==== Cert issued. Nginx serving HTTPS now. ==="
