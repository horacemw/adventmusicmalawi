# Deployment plan — Hetzner Cloud

**Status:** local Docker Compose config exists (`docker-compose.yml`) with MySQL 8, Redis 7, Mailpit. Production Dockerfile, nginx config, and Hetzner provisioning scripts are deferred to the deployment phase.

## Target topology

```
Internet
   │
   ▼
Hetzner Firewall (22 SSH allow-list, 80/443 open)
   │
   ▼
Hetzner Cloud Server (CX22 or CX32 — Ubuntu 24.04 LTS)
   │
   ▼
Docker Compose stack:
   - nginx           :80 :443
   - app (php-fpm)   internal
   - queue-worker    internal
   - scheduler       internal
   - mysql           127.0.0.1:3306 (not exposed publicly)
   - redis           127.0.0.1:6379 (not exposed publicly)
```

## Prerequisites (to be produced in the deployment phase)

- `Dockerfile` — Multi-stage PHP 8.4 + Node build. Alpine or slim Debian base.
- `docker-compose.prod.yml` — Production stack.
- `deployment/nginx/site.conf` — TLS, HTTP→HTTPS redirect, static asset caching, /admin protection.
- `deployment/provision.sh` — Initial server setup (Docker, firewall, user, swap).
- `deployment/deploy.sh` — Zero-downtime deploy: pull, build, migrate, restart, health check.
- `deployment/backup.sh` — Nightly `mysqldump` + storage rsync to Hetzner Storage Box.

## Environment

Copy `.env.example` to `.env` on the server and fill:
- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://<domain>`
- `DB_CONNECTION=mysql`, credentials
- `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`, `SESSION_DRIVER=redis`
- `MAIL_MAILER=smtp` (Mailgun / Postmark / Amazon SES)
- `FILESYSTEM_DISK=s3` (or `hetzner`), plus S3 credentials for Hetzner Object Storage
- `PAYCHANGU_*` — live keys

## SSL

Terminate at nginx via Let's Encrypt (`certbot --nginx`). Automatic renewal via systemd timer.

## Backups

- MySQL: nightly `mysqldump` gzipped, rotated 30 days, uploaded to Hetzner Storage Box.
- Media: rsync to Storage Box daily; long-term keep in Object Storage lifecycle rule.
- App restore drill: monthly, document RTO.

## Domain

`APP_URL` is the only place the public origin is set — no hardcoded localhost URLs elsewhere.

## Do NOT

- Expose MySQL or Redis ports to the internet.
- Commit `.env` or `PAYCHANGU_*` keys.
- Skip `--force` audit on production migrations.
- Deploy without running `php artisan config:cache route:cache view:cache`.
