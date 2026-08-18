# Malawi Adventist Music

**Many Voices. One Adventist Sound.**

Free music streaming platform for Seventh-day Adventist music in Malawi — songs, choirs, hymns, artists, groups, churches. Listeners stream for free; the platform monetises via paid submissions, promotions, ads, and event listings.

---

## Session 1 status

This session delivered the **deep foundation**. Later sessions will fill in the remaining phases from the master build spec.

### Implemented
- Laravel 12 + PHP 8.4 backend
- React 18 + Inertia.js 2 + TypeScript frontend
- Tailwind CSS design system with brand green palette
- Filament v3 admin panel scaffold at `/admin` (green primary)
- Laravel Sanctum for future mobile API tokens
- Spatie roles & permissions (7 roles seeded: super-admin, admin, music-moderator, artist, group-manager, advertiser, listener)
- Spatie sluggable + activitylog
- Breeze auth (login, register, forgot/reset password, email verify) — new registrations auto-assigned `listener` role
- Full production database schema — ~30 tables covering:
  - Geography (regions/districts of Malawi)
  - Taxonomy (languages, categories, occasions, moods, genres, tags)
  - Content (churches, artists, music_groups, group_members, albums, songs, song_copyrights)
  - Hymnody (hymn_books, hymns, hymn_recordings)
  - Social (playlists, playlist_song, polymorphic likes and follows)
  - Analytics (streams)
  - Submissions (submissions + files + reviews + pivots)
  - Payments (payments, payment_transactions — payable morphs to submission/promotion/etc)
  - Monetisation (promotion_packages, promotions, advertisement_campaigns, advertisements)
  - Events, copyright_reports, settings, activity_log
- Full Eloquent model layer with relationships, casts, scopes, soft deletes, slug generation
- Demo seeders: real Malawi regions/districts, 8 languages, 14 categories, 17 occasions, 9 moods, 4 promotion packages, sample settings, and demo content (users for each role, church, 5 groups of different types, 1 solo artist, 10 songs with Chichewa titles, 1 album, 1 hymn book — all clearly marked as *Demo* and never presenting fabricated real people)
- REST API scaffold at `/api/v1/*` (Sanctum-protected)
- Homepage matching the reference image: fixed left sidebar, top search bar, green hero, category chips, New Releases grid, Top Songs list, Browse by Occasion, Popular Groups, Trending This Week, right-side Now Playing panel, persistent bottom player
- Persistent HTML5-audio-backed React player (context) with play/pause/next/prev/seek/volume/mute/shuffle/repeat/queue
- Docker Compose for local dev (MySQL 8 + Redis 7 + Mailpit — currently falls back to SQLite + file cache + log mailer when Docker Desktop is not running)

### Not yet implemented (later sessions)
- Music submission form (Steps 1–6), payment flow via PayChangu, admin moderation UI
- Song detail / album detail / artist detail / group detail pages
- Discovery pages (per-category, per-occasion, per-language filters)
- Search backend + UI
- Streaming analytics recording + charts derivations
- User dashboard, artist/group dashboard content
- Full Filament resources for every entity (only the panel shell is up)
- Copyright report workflow
- Promotion checkout flow
- Advertisement placements
- REST API controllers for the listener endpoints
- Test suite
- Nginx / production Dockerfile / Hetzner deployment scripts

See `docs/` for the phase-by-phase plan.

---

## Getting started

### Prerequisites
- PHP 8.2+ (tested with 8.4)
- Node 20+ (tested with 24)
- Composer (bundled as `composer.phar` in the repo root)
- Docker Desktop (optional — for MySQL/Redis. Falls back to SQLite/file cache if unavailable)

### One-time setup

```bash
php composer.phar install
npm install

cp .env.example .env
php artisan key:generate

# Start dependencies (skip if Docker Desktop isn't running)
docker compose up -d

php artisan migrate --seed
```

### Run in development

```bash
php artisan serve --host=127.0.0.1 --port=8000
# in another terminal:
npm run dev
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000).

Demo accounts (from `DemoContentSeeder`):
- `admin@demo.local` / `password` — super-admin (Filament panel at `/admin`)
- `artist@demo.local` / `password` — artist role
- `listener@demo.local` / `password` — listener role

### Production build

```bash
npm run build
php artisan config:cache route:cache view:cache
```

### Switching to MySQL locally

When Docker Desktop is running, edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=malawiadventistmusic
DB_USERNAME=mam
DB_PASSWORD=mamsecret

CACHE_STORE=redis
MAIL_MAILER=smtp
```

Then `docker compose up -d && php artisan migrate:fresh --seed`.

---

## Project layout

- `app/Models/` — Eloquent models for every entity
- `app/Http/Controllers/` — HTTP controllers
- `app/Providers/Filament/AdminPanelProvider.php` — Filament v3 admin panel
- `database/migrations/` — DB schema
- `database/seeders/` — Roles, geography, taxonomy, settings, packages, demo content
- `resources/js/` — React + TypeScript frontend
- `routes/web.php` — Public + auth routes
- `routes/api.php` — REST API at `/api/v1/*`
- `docker-compose.yml` — Local MySQL/Redis/Mailpit

---

## Documentation

- [Architecture](docs/architecture.md)
- [Database schema](docs/database.md)
- [Deployment plan](docs/deployment.md)
- [PayChangu integration plan](docs/payments.md)
- [Copyright policy](docs/copyright.md)

---

## Licence

Proprietary. All rights reserved.
