# Database schema

MySQL 8 in production, SQLite in local development. All tables use foreign keys and appropriate indexes. Soft deletes on entities that need to survive accidental removal.

## Core entities

### Geography
- `regions` (Northern / Central / Southern)
- `districts` (all 28 Malawi districts, seeded)

### Taxonomy
- `languages` — Chichewa, English, Chitumbuka, Chiyao, Chitonga, Chisena, Chilomwe, Other
- `categories` — Hymns, Worship, Gospel, Contemporary, Acapella, Instrumental, Children's Music, Youth Music, Choir Music, Solo Music, Quartet, Sabbath Songs, Evangelism, Special Music
- `occasions` — Wedding, Marriage, Funeral, Bereavement, Graduation, Birthday, Anniversary, Christmas, Easter, Sabbath, Evangelism, Thanksgiving, Church Celebration, Youth Programs, Camp Meeting, Baptism, Communion
- `moods` — Joyful, Peaceful, Inspirational, Reflective, Sad, Comforting, Hopeful, Worshipful, Celebratory
- `genres`, `tags`

### Content
- `churches` — SDA churches, tied to region/district
- `artists` — Solo artists (optionally linked to a user account)
- `music_groups` — Choirs, quartets, youth groups, pathfinders, acapella ensembles, etc. (`type` column)
- `group_members` — Named members of a music_group
- `albums` — Belongs to artist/group/church
- `songs` — Central table. Belongs to artist/group/church, may belong to an album, has language/genre/uploader
- `song_copyrights` — 1:1 with song. Rights owner, permission status, license, distribution and monetization flags

### Song pivots
- `category_song`, `occasion_song`, `mood_song`, `song_tag`, `artist_song` (featured artists)

### Hymnody
- `hymn_books` — With language, publisher, published_year, copyright_notice, is_public_domain
- `hymns` — Number + title, unique per (hymn_book, number)
- `hymn_recordings` — Links a `song` to a `hymn`

### Social
- `playlists` — User-owned, private/public/unlisted
- `playlist_song` — Ordered pivot
- `likes` — Polymorphic (song, album, artist, music_group, church, playlist)
- `follows` — Polymorphic (artist, music_group, church, user)

### Analytics
- `streams` — Every play attempt. Includes `counted` flag (only counted plays roll into `songs.stream_count` via a nightly job)

### Submissions
- `submissions` — Multi-step music submission. Denormalised fields until approval; on approval a `songs` row is materialised and `submission.song_id` is set.
- `submission_files` — Audio, artwork, artist image, permission document
- `submission_reviews` — Reviewer actions and reasons
- `submission_category`, `submission_occasion`, `submission_mood` — Selected taxonomies

### Payments
- `payments` — Polymorphic `payable`. Provider is `paychangu` by default.
- `payment_transactions` — Audit log of every payment event (initiate/callback/verify/refund/webhook)

### Monetisation
- `promotion_packages` — Admin-configured pricing/duration
- `promotions` — Purchased promotion, polymorphic `promotable`
- `advertisement_campaigns` — Advertiser + budget + window
- `advertisements` — Individual ads within a campaign, keyed by placement

### Events & copyright
- `events` — Concerts, weddings, camp meetings, evangelism, launches
- `copyright_reports` — Polymorphic `target`, status machine (received → under_review → valid/invalid → resolved)

### System
- `settings` — Key/value config with cast type (string/int/bool/json), cached in Redis
- `activity_log` — spatie/laravel-activitylog audit trail
- `notifications` — Standard Laravel notifications table
- `personal_access_tokens` — Sanctum
- `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` — spatie/laravel-permission

## Naming conventions

- Table names: `snake_case`, plural.
- Pivot tables: alphabetical order of the two entities, singular. Exceptions where the pivot has its own identity: `playlist_song`, `hymn_recordings`, `group_members`.
- Foreign keys: `<entity>_id`, cascade delete when the parent is required; `nullOnDelete` when the child can outlive its parent.
- Timestamps: `created_at`, `updated_at`, and where soft delete applies `deleted_at`.
