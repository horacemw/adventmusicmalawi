# Powerful Admin Panel — Build Prompt

Use this as the input for a future Claude Code session focused on building out the full Filament admin panel. Everything below assumes the current codebase state at commit `5edf755` or later.

---

## Context

- Laravel 12 + Filament v3 already installed. Panel is at `/admin`, provider is `app/Providers/Filament/AdminPanelProvider.php`, brand colour is green.
- Only `SubmissionResource` exists so far. All other entities are unmanaged.
- User model implements `FilamentUser` and gates access to `super-admin | admin | music-moderator`.
- Database schema is complete (30+ tables). Models already have relationships, sluggable, soft deletes.

Skim `docs/database.md` before starting.

---

## Goal

Build a complete Filament admin panel where a moderator can run the platform end-to-end without ever touching the database directly.

---

## Resources to create (in this order)

Each is a `App\Filament\Resources\<Name>Resource` with `Pages\List<Name>`, `Pages\Create<Name>`, `Pages\Edit<Name>`, and where useful a `Pages\View<Name>`.

### 1. `UserResource`
- List: name, email, roles (badges), verified (checkmark), created_at. Filter by role.
- Row actions: view, edit, **impersonate** (via `stechstudio/laravel-php-impersonate` or a simple session swap), suspend (soft-delete), send password reset link.
- Bulk actions: assign role, remove role, verify email, deactivate.
- Form: name, email, phone, avatar, roles multi-select (from `Role::all()`), `is_active`. Password field on create.
- Do NOT show password hash. Password only settable on create + through explicit "Reset password" action.

### 2. `SongResource`
- List: artwork thumb, title, artist/group/church (auto-computed via `displayArtist()`), status (badge), plays, likes, published_at.
- Filters: status, category, occasion, language, has_audio.
- **Direct upload form** (this is the key one):
  - Title, description, lyrics.
  - **Autocomplete-search selects** for: Artist, Music Group, Church, Album, Language, Genre.
    - Search matches partial names (`->searchable(['name'])`), returns options as-you-type.
    - Each has a "Create new" button next to it that opens a modal to create the entity inline without leaving the song form.
  - Categories, occasions, moods as multi-select chips.
  - **Audio file** dropzone — direct file picker, no PayChangu, uploads straight to disk.
  - **Artwork** dropzone.
  - Copyright block: owner, rights holder, permission status, distribution / monetization toggles.
  - Publish immediately toggle.
- Row actions: view, edit, publish/unpublish, suspend, delete.

### 3. `AlbumResource`
- List: cover, title, artist/group, release year, published, track count.
- Form: title, description, cover artwork, release year, primary language, artist/group/church autocomplete.
- Related: Songs (belongs-to-album) — add a relation manager so you can attach/detach songs from the album detail page.

### 4. `ArtistResource`
- List: photo, name, stage_name, verified, featured, active, song count.
- Form: name, stage name, bio, image, cover image, gender, phone, email, church (autocomplete), region/district (dependent selects), social_links (JSON as multi-input), verified/featured/active toggles, link to user account (autocomplete on user email).
- Relation managers: Songs (owned by this artist), Albums (owned by this artist).

### 5. `MusicGroupResource`
- Same shape as ArtistResource plus a `type` select (choir / quartet / acapella / youth / children / pathfinder / adventurer / men / women / ministry / other).
- Members relation manager: name, role, voice_part, is_leader.
- Songs + Albums relation managers.

### 6. `ChurchResource`
- List: photo, name, region, district, verified.
- Form: name, description, image, cover, address, phone, email, website, region/district (dependent selects), verified/featured/active toggles.
- Relation managers: MusicGroups, Artists, Songs, Albums, Events.

### 7. `HymnBookResource` + `HymnResource`
- HymnBook list: name, language, publisher, published_year, public_domain, is_active, hymn count.
- HymnBook form: name, description, publisher, published_year, language, cover, copyright_notice, license_type, is_public_domain, is_active.
- Hymn as a relation manager on HymnBook, plus its own resource for full management.
- HymnRecordings relation manager on Hymn (links to Songs).

### 8. Taxonomy resources (small, similar shape)
- `CategoryResource`, `OccasionResource`, `MoodResource`, `LanguageResource`, `GenreResource`, `TagResource`.
- Basic name/slug/sort_order/is_active management. Colour + icon for categories/moods.

### 9. `PaymentResource` (read-only)
- List: user, provider_reference, amount, status, provider, payable_type, created_at.
- Filters: status, provider, payable_type.
- View page: full transaction log (relation manager on `PaymentTransaction`).
- Bulk action: mark refunded (with reason).

### 10. `PromotionPackageResource` + `PromotionResource`
- Package: name, placement, duration_days, price, currency, perks (JSON), is_active.
- Promotion: user, package, promotable_type + id, status, starts_at, ends_at, impressions, clicks (read-only), payment link.

### 11. `AdvertisementCampaignResource` + `AdvertisementResource`
- Campaign: user, name, advertiser info, budget, currency, status, starts_at, ends_at.
- Advertisement as a relation manager on the campaign.

### 12. `EventResource`
- List: image, title, type, date, venue, is_published.
- Form: title, description, type, venue, address, image, dates, ticket URL, is_free/ticket_price, church/artist/group associations, region/district.

### 13. `CopyrightReportResource`
- List: reference, reporter, target, status, assigned_to.
- Actions: assign to me, mark valid + suspend target, mark invalid, resolve.
- Full audit trail via `activity_log`.

### 14. `SettingResource`
- Key/value editor grouped by `group` column.
- Type-safe rendering per `cast`: text, integer, boolean, JSON (Monaco editor).
- Special one-line settings page for the most common ones: submission fees, upload limits, streaming threshold, cooldown.

---

## Cross-cutting features

### Global search
Enable Filament's global search (top-right search icon) across users, songs, artists, groups, churches, hymns.

### Activity log
Wire spatie/activitylog into every resource. Each resource's Edit page gets a "History" tab showing all changes.

### Nav grouping
Group resources in the sidebar:
- **Content**: Songs, Albums, Artists, Groups, Churches, Hymn Books, Events
- **Moderation**: Submissions, Copyright Reports
- **Monetisation**: Payments, Promotion Packages, Promotions, Ad Campaigns
- **Taxonomy**: Categories, Occasions, Moods, Languages, Genres, Tags
- **Users**: Users
- **System**: Settings

### Widgets on the dashboard
Filament dashboard should show:
- Stat cards: Total users, active users (7-day), total songs, published songs, pending submissions, MTD revenue.
- Chart: Streams last 30 days.
- Chart: New submissions last 30 days.
- Table: Pending submissions (top 5) with quick Approve/Reject.
- Table: Recent payments.

### Admin direct song upload (the key UX ask)
The `SongResource` create form is the most-used single screen. Design goals:
- **No PayChangu flow** — admin uploads directly.
- **Autocomplete searches** for church, artist, group so names always link to existing entities and metadata stays consistent.
- **Inline "Create new"** next to each autocomplete — opens a modal, creates the entity, selects it, closes the modal — never leaves the song upload page.
- **Region → district dependent select.**
- **Drag-and-drop audio** file upload with progress and duration auto-detection (via `getid3` package or client-side `HTMLAudioElement.duration`).
- **Publish immediately** toggle at the bottom.

### Permissions per resource
- `super-admin`: everything.
- `admin`: everything except managing users' passwords and settings.
- `music-moderator`: only Submissions, Songs, Copyright Reports.

Wire via Filament's `->authorize()` on resource actions + `viewAny`/`create`/`update`/`delete` policies.

---

## Deliverables checklist

- [ ] Resources #1–#14 above, all with list + create + edit + view where applicable.
- [ ] Relation managers wired between related entities.
- [ ] Global search enabled.
- [ ] Activity log tab on every edit page.
- [ ] Nav grouping matching the structure above.
- [ ] Dashboard widgets rendered.
- [ ] Admin direct-song-upload form with entity autocomplete + inline-create modals.
- [ ] Permissions enforced per resource + per action.
- [ ] Screenshots of each resource's list + form for verification.
- [ ] Update `docs/architecture.md` with a section describing the admin panel structure.

---

## Not in scope

- Building the listener-facing discovery / detail pages (that's a separate session).
- Streaming analytics pipeline (also separate).
- REST API for the mobile app (already deferred).
- Rewriting the moderation flow — `SubmissionResource` already exists and works; extend it, don't rewrite.
