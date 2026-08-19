# Prompt: Audit `malawiadventistmusic` and build the operator admin

You are a senior Laravel backend engineer joining a project that's already live in production. Your task has two phases — **audit first, then build**. Do NOT jump to writing Filament resources before completing the audit.

## Phase 0 — Load context

The project is a music streaming platform for Seventh-day Adventist music in Malawi. Read these files in this order before anything else:

1. `docs/architecture.md` — stack overview
2. `docs/database.md` — the ~30-table schema
3. `docs/deployment.md` — how it ships to production
4. `docs/admin-panel-prompt.md` — the aspirational admin spec (use as reference, not a strict blueprint)
5. `app/Providers/Filament/AdminPanelProvider.php` — current admin panel config
6. `app/Filament/Resources/SubmissionResource.php` — the one existing resource
7. `app/Models/User.php` — note the `FilamentUser` contract + `canAccessPanel` method + roles

Do not skim. This is a live production app at `https://malawiadventistmusic.com` — every change ships to real users.

## Phase 1 — Audit the live site and codebase

Produce a written audit report (`docs/audit-<date>.md`) covering:

### 1a. Route inventory
Run the `route:list` command through the running app container on the server. For every route, note:
- Whether it renders a real page or the `Placeholder` component
- What controller / action handles it
- What middleware applies (`auth`, `verified`, none)
- Whether the JS bundle has a matching `Pages/*.tsx` in the vite manifest

### 1b. Content inventory
Query production data:
```
Users, Submissions (by status), Songs (by status), Albums, Artists, MusicGroups,
Churches, HymnBooks, Payments (by status), Notifications sent
```
Note: this is a young production DB — most counts are single digits or zero.

### 1c. Frontend gaps
List every listener-facing route that currently renders the `Placeholder` component (there are ~11 of them). For each, note what data model powers it and whether the model has enough content to render a non-empty state today.

### 1d. Data-entry gaps
For each of these entities, note who can currently create a row and how:
Church, Artist, MusicGroup, GroupMember, Album, Song, HymnBook, Hymn, HymnRecording, Category, Occasion, Mood, Language, Genre, Tag, PromotionPackage, Promotion, AdvertisementCampaign, Advertisement, Event, CopyrightReport, User.

Almost all of these have no admin UI. That's your gap analysis.

### 1e. Broken or partially-wired flows
Confirm each of these actually works:
- PayChangu initiate + return + webhook (webhook not yet real-tested)
- Submission wizard step 1 through payment
- SongMaterialiser transactional publish
- Notifications dispatched via Resend
- The Now Playing player when audio paths are set
- Playlist add/remove-song endpoints (backend exists — is there a UI hook on song rows?)

### 1f. Security review
Check for:
- Any route with `withoutMiddleware` or CSRF bypass — is it justified?
- File upload MIME + size validation
- Where PayChangu keys live and how they're rotated
- Password reset + email verification enforcement
- Any raw SQL, string concatenation into queries, or unescaped Blade output
- Rate limits on `/register`, `/login`, `/contact`, `/payments/webhook/*`

## Phase 2 — Recommend priorities

At the end of the audit, produce a **prioritised backlog** with three tiers:

- **T1 — Blocking:** things that make the site feel broken to a real user (empty homepage, stub pages, admin can't add content).
- **T2 — Necessary before scale:** things you'd want before opening registration publicly (moderation dashboards, user management, curation controls, revenue visibility).
- **T3 — Nice to have:** bulk actions, analytics widgets, impersonation, audit-log UI, custom reports.

Do not build anything yet. Present the audit and priorities. Wait for the human to confirm scope before writing code.

## Phase 3 — Build the operator admin (only after Phase 2 sign-off)

Once the human confirms priorities, build **Filament v3 resources** in the order they signed off. For each resource:

### Rules of engagement
- Prefer editing existing files to creating new ones. `SubmissionResource` is a working example — follow its structure.
- Every list page must have: searchable columns, filters, sortable columns, bulk actions.
- Every form must have: inline validation, related-entity autocomplete-search (not blind dropdowns), and an **"Add new" button** next to each foreign-key select that opens a modal to create the linked entity inline.
- Every form that has a foreign key to Church, Artist, MusicGroup, Album, or User must use this autocomplete-plus-inline-create pattern. Duplicate entities are the enemy — the whole point of admin curation is that a submitted song ends up linked to the existing Lilongwe Central Choir, not a fresh duplicate named "Lilongwe Central Choir 2".
- Region → District dependent selects everywhere they appear.
- Filament navigation grouped as: Content, Moderation, Monetisation, Taxonomy, Users, System.
- Every mutation writes a `spatie/activitylog` entry via the `LogsActivity` trait on the model.
- Permissions gated by role: `super-admin` = everything; `admin` = everything except password reset for others + system settings; `music-moderator` = only Submissions, Songs, CopyrightReports.

### Admin direct song upload (highest-value single screen)

The Song create form is the single most-used admin screen. Requirements:

1. Bypass PayChangu — admin-uploaded songs land as `status=published`, `published_at=now()`, no `Payment` row.
2. Autocomplete: Church, Artist, MusicGroup, Album (with region→district dependency).
3. Each autocomplete has an "Add new" button that opens a Filament modal to create that entity without leaving the song form. On save, the new entity is auto-selected in the parent form.
4. Categories, occasions, moods as multi-select chip pickers.
5. Audio file upload (MP3 / AAC / WAV) with client-side duration extraction using `HTMLAudioElement.metadata`. Populate `duration_seconds` before submit.
6. Artwork upload (JPEG / PNG / WebP).
7. Lyrics as a rich-text textarea. Optional DOCX upload that gets parsed to text via `phpoffice/phpword`.
8. Copyright block (owner, rights holder, permission status, distribution flag, monetisation flag).
9. Publish immediately toggle (defaults on for admin uploads).
10. On save: create Song + SongCopyright + sync pivots in a single transaction. Redirect to the song view page with a success toast.

### Dashboard widgets

Filament dashboard should render:
- Stat cards: total users, verified users (%), published songs, pending submissions, MTD revenue.
- Chart: streams per day (last 30 days) — reads from `streams.counted=true`.
- Chart: submissions per day.
- Table: 5 most recent pending-review submissions, with inline Approve/Reject buttons.
- Table: 5 most recent successful payments.

## Non-goals

- Do NOT build the listener-facing discovery / detail pages. That's a separate session.
- Do NOT touch the submission wizard, payment services, or notification classes unless the audit finds a real bug.
- Do NOT introduce microservices, AI features, or third-party admin dashboards. Filament v3 only.
- Do NOT rewrite `SubmissionResource`. Extend it with more actions if needed, but the moderation flow already works.

## Constraints inherited from the project

- Laravel 12, PHP 8.4, MySQL 8, Redis 7, Filament v3, Inertia 2 + React 18 + TS.
- Spatie `laravel-permission` for roles; the 7 seeded roles are canonical.
- Spatie `laravel-sluggable` for URL slugs; every content entity slugs off its `name`/`title`.
- Spatie `laravel-activitylog` is installed — wire it into every model you touch.
- File storage on the `public` disk in dev; production uses the same disk today but the migration to Hetzner Object Storage is in-flight — write file paths through the `Storage` facade only, never with `public_path()`.
- Windows dev machine: run all shell commands through Git Bash / PowerShell semantics. Do not assume Docker Desktop is running locally — CI/deploy path is the Hetzner server.

## Success criteria

The human should be able to:
1. Log in at `/admin/login` as `admin@malawiadventistmusic.com`.
2. Create a Church, then a MusicGroup that references that Church, then an Artist that references that Church, then upload a Song that references the Group — all inside the admin, in one sitting, without duplicate entities.
3. See that same Song immediately appear on the public homepage.
4. Approve a paid submission from `/admin/submissions`, and see the resulting Song show up on the homepage with the same church/group linkage as the submission's metadata.
5. See the seeded catalogue reach at least 20 songs across 5 groups and 3 churches within one admin session.
6. Filter users by role and by verified state, and manually mark a user verified.
7. See daily revenue on the dashboard match `SELECT SUM(amount) FROM payments WHERE status='successful' AND DATE(completed_at)=CURDATE()`.

## First message to the human

Once you've completed the audit (Phase 1), your first message back to the human should be:
- One-paragraph summary of the site's current state
- The prioritised backlog from Phase 2
- Three specific yes/no questions that resolve the biggest ambiguities before you start building
- Estimated size of the work in resource-count and rough session count

Do not start writing resources until they answer.
