# Changelog

## [Unreleased]

### Session 1 — 2026-08-18 — Deep foundation

**Backend**
- Scaffolded Laravel 12 with Breeze (React + Inertia + TypeScript starter).
- Installed spatie/laravel-permission, spatie/laravel-sluggable, spatie/laravel-activitylog, laravel/sanctum, intervention/image, filament/filament v3.
- Extended `users` table (username, phone, avatar, bio, is_active, last_login_at, soft deletes).
- Wrote 20 domain migrations covering regions/districts, taxonomies (languages, categories, occasions, moods, genres, tags), churches, artists, music_groups, group_members, albums, songs + copyright, song pivots, hymn_books/hymns/hymn_recordings, playlists + polymorphic likes/follows, streams, submissions + files + reviews + pivots, payments + transactions, promotion_packages + promotions, ads, events, copyright_reports, settings, notifications.
- Wrote Eloquent models for every entity with relationships, casts, scopes, soft deletes, sluggable, HasApiTokens, HasRoles.
- Configured Filament v3 panel at `/admin` (brand green primary).
- Wired REST API at `/api/v1/*` (Sanctum stateful).
- Assigned `listener` role to new registrations.
- Seeded roles (7), permissions (28), Malawi geography (3 regions + 28 districts), taxonomy defaults, settings defaults, promotion packages (4), demo content (3 users, 1 church, 5 groups, 1 artist, 10 songs, 1 album, 1 hymn book).

**Frontend**
- Extended Tailwind with brand green palette, Inter font, custom slider styling.
- Built `HomeController` returning hero, chips, new releases, top songs, occasions, trending, featured groups, now playing.
- Built `AppLayout` shell — fixed sidebar, top bar, main, right panel, persistent player.
- Built `Sidebar` (24 nav items across 3 sections + promo card).
- Built `TopBar` (search + notifications + user profile / sign-in-sign-up).
- Built `PlayerContext` — single HTML5 audio element, full transport controls, queue, shuffle, repeat, volume.
- Built `PlayerBar` and `NowPlayingPanel`.
- Built `Home` page composing `HeroCard`, `CategoryChips`, `NewReleases`, `SongList` (top & trending), `OccasionsGrid`, `FeaturedGroups`.
- Placeholder pages for every planned public route.

**Infra**
- `docker-compose.yml` with MySQL 8, Redis 7, Mailpit.
- `.env.example` with all future settings including PayChangu slots.

**Docs**
- `README.md`, `docs/architecture.md`, `docs/database.md`, `docs/payments.md`, `docs/deployment.md`, `docs/copyright.md`.

**Verified**
- `php artisan migrate --seed` succeeds cleanly from a fresh SQLite database.
- `npx tsc --noEmit` passes.
- `npm run build` succeeds (2804 modules).
- `php artisan serve` → homepage returns 200, Inertia props include real seeded data, headless-Chrome screenshot matches the reference image.

### Known gaps
- No test suite yet.
- Submission workflow, PayChangu integration, admin moderation, artist dashboard, streaming analytics logic, charts computation, search, copyright workflow all deferred to later sessions.
- Docker Compose is written but not tested against a live Docker daemon (Docker Desktop was not running on the dev machine — SQLite fallback used instead).
- Only `personal_access_tokens` from Sanctum's migration was published; API controllers are not implemented.
