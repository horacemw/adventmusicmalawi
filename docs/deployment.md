# Deployment runbook — Hetzner Cloud + Namecheap

**Target host:** Hetzner Cloud CX22 (2 vCPU / 4 GB / 40 GB), Falkenstein, Germany, Ubuntu 24.04 LTS.
**Domain:** malawiadventistmusic.com (Namecheap).

The whole stack runs on one box under Docker Compose. Everything except nginx listens on private container networks.

```
Internet
   │
   ▼   HTTPS
Hetzner Firewall (22 SSH, 80/443 HTTP(S) open)
   │
   ▼
[ nginx :80 :443 ] ← Let's Encrypt (certbot sidecar)
   │
   ▼   fastcgi
[ app (php-fpm) ] ─── [ queue-worker ] ─── [ scheduler ]
   │      │      │
   │      ▼      ▼
[ mysql ]  [ redis ]        ← both private, no public port
```

---

## Phase 1 — Hetzner Cloud project + server

1. Go to [console.hetzner.cloud](https://console.hetzner.cloud) and sign in.
2. **New Project** → name it `malawiadventistmusic`.
3. Left sidebar → **Security** → **SSH Keys** → **Add SSH Key**.
   - Paste the contents of your `~/.ssh/id_ed25519.pub`.
   - Name: `dingdong-hetzner` (or however you want to label it).
4. Left sidebar → **Firewalls** → **Create Firewall**.
   - Name: `web`.
   - Inbound rules:
     - **SSH** — TCP 22, source `Any IPv4, Any IPv6` (harden later to a fixed IP if you have one).
     - **HTTP** — TCP 80, source Any.
     - **HTTPS** — TCP 443, source Any.
   - Outbound: leave defaults (all allowed).
5. Left sidebar → **Servers** → **Add Server**.
   - Location: **Falkenstein**.
   - Image: **Ubuntu 24.04**.
   - Type: **CX22** (Shared vCPU · x86).
   - Networking: keep the defaults (Public IPv4 + IPv6 both on).
   - SSH keys: check the key you just added.
   - Firewalls: attach the `web` firewall.
   - Volumes / Backups: skip for now (or enable Backups at ~20% extra if you want automated Hetzner snapshots — recommended once you have paying users).
   - Name: `mam-prod-1`.
   - Click **Create & Buy Now**.
6. Wait ~30 seconds. Once green, copy the **IPv4 address** from the server card.

---

## Phase 2 — DNS at Namecheap

Namecheap dashboard → **Domain List** → `malawiadventistmusic.com` → **Manage** → **Advanced DNS**.

Delete any existing default records (Namecheap creates parking records — `CNAME @ parkingpage.namecheap.com`, `URL Redirect @ www.malawiadventistmusic.com`, etc.). Then add:

| Type    | Host | Value               | TTL       |
|---------|------|---------------------|-----------|
| A Record| `@`  | `<HETZNER_IPV4>`    | Automatic |
| A Record| `www`| `<HETZNER_IPV4>`    | Automatic |

Save. Propagation is usually a minute or two; check with `nslookup malawiadventistmusic.com` from your laptop until it points at Hetzner's IP.

---

## Phase 3 — Provision the server

From your laptop:

```powershell
# Grab your public key so we can pass it into the provisioner
$pubkey = Get-Content ~/.ssh/id_ed25519.pub

# Copy the provisioner up
scp deployment/provision.sh root@<HETZNER_IPV4>:/tmp/

# Run it. This creates the `deploy` user with your key, installs Docker, hardens SSH,
# opens the firewall, adds swap, and refuses further root logins when it finishes.
ssh root@<HETZNER_IPV4> "DEPLOY_USER_PUBKEY='$pubkey' bash /tmp/provision.sh"
```

Log out and back in as the `deploy` user:

```powershell
ssh deploy@<HETZNER_IPV4>
```

---

## Phase 4 — First-time deploy

### 4a. Give the server read access to the private GitHub repo

The repo is private, so the server needs a **deploy key** to clone. On the server (`deploy@mam-prod-1`):

```bash
# Generate a repo-scoped SSH key
ssh-keygen -t ed25519 -N '' -C 'mam-prod-deploy-key' -f ~/.ssh/github_deploy
cat ~/.ssh/github_deploy.pub
```

Copy the printed public key line. In your browser:
- Open `https://github.com/horacemw/adventmusicmalawi/settings/keys/new`
- Title: `mam-prod-1`
- Key: paste the public key
- **Do NOT** tick "Allow write access" — read-only is enough
- Save

Then tell SSH to use this key for GitHub:

```bash
cat >> ~/.ssh/config <<'EOF'
Host github.com
    HostName github.com
    User git
    IdentityFile ~/.ssh/github_deploy
    IdentitiesOnly yes
EOF

# Trust GitHub's host key
ssh-keyscan github.com >> ~/.ssh/known_hosts

# Smoke-test the deploy key
ssh -T git@github.com
# Expected: "Hi horacemw/adventmusicmalawi! You've successfully authenticated, ..."
```

### 4b. Clone and configure

```bash
cd /srv/malawiadventistmusic
git clone git@github.com:horacemw/adventmusicmalawi.git .

# Create production .env
cp .env.production.example .env

# Generate an APP_KEY. Easiest: run in a throwaway container.
docker run --rm -v $PWD:/app -w /app php:8.4-cli \
    php -r "echo 'APP_KEY=base64:'.base64_encode(random_bytes(32)).PHP_EOL;" \
    | sed -i "0,/APP_KEY=/{s|APP_KEY=.*|$(cat)|}" .env

# Fill in the rest — passwords, PayChangu keys, mail credentials.
# NEVER paste production PayChangu keys anywhere except here.
chmod 600 .env
nano .env

# First build + boot
./deployment/deploy.sh
```

Wait for it to say `Deployed <sha>`. The site is now serving HTTP on the server IP but the domain won't work until we get SSL.

---

## Phase 5 — SSL via Let's Encrypt

Still on the server:

```bash
./deployment/scripts/certbot-init.sh <your-email>
```

Certbot writes the cert to a shared volume, then reloads nginx. HTTPS is now live at `https://malawiadventistmusic.com`.

The `certbot` service in `docker-compose.prod.yml` auto-renews every 12 hours.

---

## Phase 6 — Verify

From your laptop:

- `curl -fs https://malawiadventistmusic.com/up` → should return `200`.
- Browse to the homepage — should render exactly like localhost, with real seed data.
- Log in as an artist → `/submit-music` → walk through the wizard. Set `PAYCHANGU_FAKE=false` in `.env` first + `docker compose up -d app` to pick it up. Complete a small live PayChangu transaction to prove the loop works end-to-end.
- Hit `/admin` and log in as `admin@demo.local` (change the password immediately in Filament).

---

## Phase 7 — Backups

```bash
# Test the backup script once
sudo /srv/malawiadventistmusic/deployment/backup.sh

# Then wire it into cron
sudo crontab -e
# Add:
0 3 * * * /srv/malawiadventistmusic/deployment/backup.sh >> /var/log/mam-backup.log 2>&1
```

Optionally set `RSYNC_TARGET=u123456@u123456.your-storagebox.de:mam-backups` (a Hetzner Storage Box) so backups leave the server. Highly recommended.

---

## Ongoing deploys

From your laptop:

```powershell
git push origin main
ssh deploy@<HETZNER_IPV4> "cd /srv/malawiadventistmusic && ./deployment/deploy.sh"
```

Rollback:

```powershell
ssh deploy@<HETZNER_IPV4> "cd /srv/malawiadventistmusic && ./deployment/rollback.sh"
```

---

## Common issues

**500 on first request after deploy** — check `docker compose -f docker-compose.prod.yml logs app`. Usually a missing env var. Fix `.env`, `docker compose up -d app`.

**Nginx says "no certificate"** — cert issuance failed. Re-run `certbot-init.sh`. Confirm DNS actually points at the server (`dig +short malawiadventistmusic.com`).

**PayChangu webhook 401s** — signature mismatch. Confirm `PAYCHANGU_WEBHOOK_SECRET` in `.env` matches what you typed into the PayChangu dashboard. Rotate if unsure.

**MySQL container refuses to start** — usually the volume was created with different credentials. If you're OK losing local data: `docker compose down -v` then re-`up`. If you have prod data, restore from backup first.

---

## Do NOT

- Commit `.env`.
- Expose MySQL / Redis ports to the internet.
- Skip cert renewal — check certbot logs weekly for the first month.
- Deploy directly to `main` without a local `npm run build` + `php artisan test` first (once tests exist).
- Force-push to a shared branch after the site is live.
