# Architecture

## High level

```
                   ┌────────────────────┐
                   │      Nginx         │  (SSL termination, static assets, reverse proxy)
                   └─────────┬──────────┘
                             │
              ┌──────────────┴───────────────┐
              │                              │
        ┌─────▼──────┐                ┌──────▼───────┐
        │  Laravel   │                │  Vite build  │  (SPA assets served from public/build)
        │  (PHP-FPM) │                └──────────────┘
        └─────┬──────┘
              │
     ┌────────┼─────────┬──────────┐
     │        │         │          │
  ┌──▼──┐  ┌──▼──┐  ┌───▼───┐  ┌───▼────┐
  │MySQL│  │Redis│  │Queue  │  │Storage │
  └─────┘  └─────┘  │workers│  │(disk / │
                    └───────┘  │ S3    )│
                               └────────┘
```

- **Web** — Laravel 12 renders Inertia pages (`resources/js/Pages/*.tsx`) to a React 18 SPA. Same Laravel serves the REST API at `/api/v1/*` for the future React Native mobile app.
- **Admin** — Filament v3 panel mounted at `/admin`. Green primary. Auth gated to `super-admin | admin | music-moderator`.
- **Auth** — Session auth for browser + Sanctum personal access tokens for the mobile client. New registrations get the `listener` role.
- **Authorisation** — spatie/laravel-permission. Roles: super-admin, admin, music-moderator, artist, group-manager, advertiser, listener.
- **Queues** — Redis-backed queues for email dispatch, audio metadata extraction, notifications, scheduled analytics rollups.
- **Cache** — Redis in production, file locally.
- **Media storage** — Laravel Storage abstraction. Local disk in dev; Hetzner Object Storage (S3-compatible) in production, fronted by a CDN in a later phase.

## Frontend structure

- `resources/js/Layouts/AppLayout.tsx` — persistent shell with sidebar, top bar, main content, optional right panel, and bottom player.
- `resources/js/Pages/*` — one component per route.
- `resources/js/Components/App/*` — chrome (Sidebar, TopBar, PlayerBar, NowPlayingPanel).
- `resources/js/Components/Home/*` — homepage sections.
- `resources/js/Contexts/PlayerContext.tsx` — global audio state (single `<audio>` element).
- `resources/js/types/*.d.ts` — TS types shared with backend responses.

## Backend structure

- `app/Http/Controllers/*` — HTTP controllers. Thin — logic lives in services and jobs.
- `app/Models/*` — Eloquent models with relationships, scopes, casts.
- `app/Providers/Filament/AdminPanelProvider.php` — Filament panel config.
- `routes/web.php` — public + auth routes.
- `routes/api.php` — REST API for the mobile client.
- `bootstrap/app.php` — application configuration (middleware, routing, exceptions).

## Cross-cutting concerns

- **Sluggable** — every user-visible entity uses `Spatie\Sluggable\HasSlug`; slugs are unique.
- **Soft deletes** — churches, artists, music_groups, albums, songs, submissions, playlists, events, campaigns.
- **Audit** — spatie/laravel-activitylog is installed; wire log traits into critical models in the moderation phase.
- **Polymorphism** — `likes`, `follows`, `payments.payable`, `promotions.promotable`, `copyright_reports.target`.

## Deployment target

Single Hetzner Cloud server (Ubuntu LTS) running Docker Compose with services for `app` (php-fpm), `nginx`, `mysql`, `redis`, `queue`, `scheduler`. Sized to comfortably serve tens of thousands of monthly users. See [`deployment.md`](deployment.md).
