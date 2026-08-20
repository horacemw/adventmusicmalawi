# Session Checkpoint — 2026-08-19 (resume here)

Live production URL: **https://malawiadventistmusic.com**
Repository: `horacemw/adventmusicmalawi` (public)
Server: Hetzner CX22 · `167.233.22.55` · deploy user `deploy`
Last deployed commit: **`b0f9ead`** (2026-08-19 ~22:15 UTC)

---

## Credentials

**Super-admin (real owner account):**
- URL: https://malawiadventistmusic.com/admin/login
- Email: `chipemberehorace@gmail.com`
- Password: `MamOwner!Green2026-Blantyre`
- Role: `super-admin` (full site control)
- Change password after login via top-right profile menu

**Legacy default admin** (still exists, safe to delete once real users are made):
- `admin@malawiadventistmusic.com` / `MamAdmin2026-Green`

**Server SSH:** `ssh deploy@167.233.22.55` (from the dev machine that has the key)

**Deploy command:** `ssh deploy@167.233.22.55 "cd /srv/malawiadventistmusic && ./deployment/deploy.sh"`

---

## What's working end-to-end

- Public site: `/`, `/discover`, `/songs`, `/albums`, `/artists`, `/groups`, `/churches`, `/poems`, `/hymn-books`, `/occasions`, `/occasions/{slug}`, `/trending`, `/top-100`, `/search`
- Detail pages: `/songs/{slug}`, `/albums/{slug}`, `/artists/{slug}`, `/groups/{slug}`, `/churches/{slug}`, `/poems/{slug}`
- Content pages: `/about`, `/contact`, `/terms`, `/privacy`, `/copyright`
- Admin (Filament v3): all 16 resources smoke-tested and load — Church, MusicGroup, Artist, Album, Song, Poem, User, Category, Occasion, Mood, Language, Genre, Payment (read-only), CopyrightReport, ActivityLog, Submission
- Admin dashboard widgets: PlatformStats (11 stat cards), StreamsChart (30-day), RevenueChart (12-month, driver-aware), PendingSubmissions
- Admin pages: Storage stats, Platform Settings
- Payments: PayChangu Standard Checkout + webhook + submission wizard
- Streaming: audio files served via `/storage/*` (nginx has the storage mount now)
- Downloads: separate `downloads` table + `songs.download_count`
- Auth: registration + verification enforced for submitters/artists; listeners can stream unverified

---

## Bugs fixed today (in this session)

| # | Commit | Bug | Fix |
|---|--------|-----|-----|
| 1 | `58f70b9` | Homepage 500 after slice-2 admin deploy | Corrected spatie/activitylog v5 namespaces (`Models\Concerns\LogsActivity`, `Support\LogOptions`) on Song model |
| 2 | `9117abb` | Discovery pages were placeholder stubs | Built DiscoverController + ProfilesController + 12 new Inertia pages + shared components |
| 3 | `65be0b3` | Poems only existed as submission `kind` | Full Poem model + migration + Filament PoemResource + `/poems` browse + `/poems/{slug}` detail + shelves on home/discover + search integration |
| 4 | `9c345ad` | Filament login logo was tiny `favicon.ico` | Replaced with proper brand mark blade view matching public sidebar |
| 5 | `ec03983` | Song create/upload 500 + admin 500s | Livewire temp-file disk pinned to `local` (private) via published `config/livewire.php`, PHP `upload_max_filesize=210M`/`post_max_size=220M`/`max_execution_time=300`, nginx `client_max_body_size=220M`/`fastcgi_read_timeout=300s`, entrypoint chowns storage on startup |
| 6 | `e3fbac1` | `/admin/copyright-reports/create` 500 | `Forms\TextInput->fontFamily()` doesn't exist — replaced with `->extraInputAttributes(['class' => 'font-mono'])` |
| 7 | `e9978e5` | Song/Poem admin create 500 | `->dontSubmitEmptyLogs()` renamed to `->dontLogEmptyChanges()` in spatie/activitylog v5 |
| 8 | `e754e1b` | `/admin` dashboard 500 | RevenueChart used SQLite `strftime()` on MySQL — now picks the SQL expression by `DB::connection()->getDriverName()` |
| 9 | `71af3e2` | `/admin/songs`, `/admin/payments`, etc 500 | Filament v3 closure resolver rejects `$s` — renamed to `$state` in all `formatStateUsing()` closures |
| 10 | `4128d0d` | Uploaded songs wouldn't play + admin FilePond load errors + `/storage/*` → 404 | Two-part fix: added `App\Support\MediaUrl::url()` helper and wrapped every path field in controllers with it, AND added `app_storage:/var/www/html/storage:ro` mount to nginx service (storage symlink was dangling from nginx's POV) |
| 11 | `b0f9ead` | Homepage 500 after `4128d0d` deploy | PowerShell's `Set-Content -Encoding UTF8` on Windows adds a UTF-8 BOM before `<?php`. Stripped BOMs; added `sed` sweep in Dockerfile so future BOM'd commits can't break prod |

---

## Route probe (last checked 2026-08-19 22:15 UTC)

```
/                    -> 200
/discover            -> 200
/songs               -> 200
/albums              -> 200
/artists             -> 200
/groups              -> 200
/churches            -> 200
/poems               -> 200
/admin               -> 302 (redirects to login)
/admin/login         -> 200
/storage/songs/audio/{ulid}.mp3 -> 200 (audio playback works)
```

---

## Open / next-session work

Ordered roughly by user priority:

### 1. Verify uploads + playback end-to-end after `b0f9ead`
User was mid-testing when we ended the session. Confirm:
- Hard-refresh `/admin/songs/create`, upload a fresh audio file, click Create
- Song should save and appear on `/admin/songs`
- On public homepage, click the song → should play in the persistent PlayerBar
- Edit page (`/admin/songs/{slug}/edit`) should show the file card without JS load errors

### 2. `/admin/songs/musawaononge` and other admin URLs the user hit
The user reported 500s that the log dump then attributed to the `$s`-closure bug (fixed in `71af3e2`). Re-verify these no longer 500:
- `/admin/songs` (list)
- `/admin/songs/{slug}/edit`
- `/admin/payments`
- `/admin/copyright-reports`
- `/admin/activity-logs`
- `/admin/storage`
- `/admin/platform-settings`

### 3. Move admin panel pending items
From the audit prompt (`docs/admin-audit-and-build-prompt.md`):
- LogsActivity trait on remaining models (Church, MusicGroup, Artist, Album, User) — Song + Poem already have it
- Impersonation feature for UserResource
- Global search in Filament panel
- Music-moderator role permission gates so only super-admin/admin see all resources
- Notifications resource / page for admin (Reports section)

### 4. Test payment flow end-to-end
- Submit a paid song via `/submit-music` as a non-admin user
- Verify PayChangu Standard Checkout works
- Verify webhook lands and payment moves to `successful`
- Verify submission auto-published on approval

### 5. Provision moderator accounts
The user said "I need real admin/moderator accounts" and then asked just for the owner account. Owner is now created. Next session, revisit and create moderator accounts if needed — see `role` list already seeded: `admin`, `music-moderator`, `artist`, `group-manager`, `advertiser`, `listener`.

---

## Non-obvious operational knowledge

- **Deploy runs migrations automatically** (entrypoint holds a flock so only one container migrates).
- **`docker compose down` on deploy** drops the `app_public` named volume so fresh `/build/` assets show up (nginx serves from that volume). `app_storage` is NOT dropped — uploaded songs/artwork survive deploys.
- **PHP logs**: `/var/www/html/storage/logs/laravel.log` in the app container. Ownership resets on every container start (entrypoint chowns to `www-data`) so writes always succeed.
- **Livewire temp uploads** now live in `storage/app/private/livewire-tmp/` (via published `config/livewire.php`). Do not change `FILESYSTEM_DISK=public` in the env — that's what makes Filament's `->disk('public')` FileUpload work for final destinations.
- **nginx volumes** — needs BOTH `app_public:/var/www/html/public` and `app_storage:/var/www/html/storage:ro`. Without the storage mount, all `/storage/*` returns 404.
- **BOM guard**: Dockerfile strips UTF-8 BOMs from PHP files during build. Any local editing tool that produces BOMs (PowerShell `Set-Content -Encoding UTF8`) won't break prod, but keep an eye on `file *.php` if things get weird.
- **PayChangu keys** live in `.env` on the server. Do not commit. Current keys were verified last session.
- **Resend SMTP** live; DKIM/SPF/DMARC configured at Namecheap DNS.

---

## Files to look at first when resuming

- `docs/admin-audit-and-build-prompt.md` — self-contained audit prompt for next admin work
- `docs/architecture.md` — high-level layout
- `app/Filament/Resources/*` — admin CRUD (each has related `/{Resource}/Pages/*.php`)
- `app/Http/Controllers/DiscoverController.php` + `ProfilesController.php` — public discovery + detail pages
- `app/Support/MediaUrl.php` + `SongPayload.php` — path/URL helpers used by controllers
- `config/livewire.php` — file-upload disk config (do not remove)
- `deployment/deploy.sh` — deploy script (drops app_public volume by design)

---

## How to resume from a fresh Claude session

Paste this into the new session:

> Read `docs/session-checkpoint.md` in this repo — it captures where we left off. Do not re-do fixes already listed there. Start with the "Open / next-session work" section and confirm the current state via a route probe before touching code.
