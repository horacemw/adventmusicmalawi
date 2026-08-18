# Copyright policy

Hymnody and church music include material with real copyright holders. The platform must never assume public-domain status.

## Metadata on every song

Every `songs` row has a `song_copyrights` row with:
- `copyright_owner`
- `rights_holder`
- `permission_status` — `owned | licensed | permission_granted | public_domain | unknown`
- `license_type` — `all_rights_reserved | cc_by | cc_by_sa | cc0 | custom`
- `distribution_allowed` (bool)
- `monetization_allowed` (bool)
- `permission_document_path` — uploaded supporting document
- `notes`

## Submissions

The submission form requires the submitter to affirm:
1. They own the recording **or** have permission to distribute it.
2. They have authority to submit it on behalf of the copyright holder.
3. The platform is permitted to stream it.
4. All submitted information is accurate.

Uploaded permission documents are stored on `submission_files.kind = permission_document`.

## Hymn books

Hymn books are a first-class entity (`hymn_books`) with their own `copyright_notice`, `license_type`, and `is_public_domain` flag. Do NOT assume any hymn book is public domain — even if the underlying hymn is, the specific published edition may not be. When creating a `hymn_recording`, the platform relies on the submitter's declared permission status for the specific recording, not the book.

## Copyright reports

`copyright_reports` table captures rights-holder claims:
- Polymorphic `target` (song / album / hymn / hymn_recording)
- Reporter identity + organisation
- Claim text and evidence document
- Status machine: `received → under_review → valid | invalid → resolved`
- Assignment to a moderator
- Resolution notes

Workflow (to be implemented in the copyright phase):
1. Public form + authenticated form.
2. Notify assigned moderator on creation.
3. Moderator can suspend the targeted song (`songs.status = suspended`) while the claim is under review.
4. Full audit trail via `activity_log`.
5. Resolved decisions retained indefinitely.

## Never
- Auto-publish a submission without moderator review, even if payment succeeded.
- Enable `monetization_allowed=true` by default. Default must be `false` and require explicit rights holder action.
- Remove copyright metadata on republish — copy forward.
