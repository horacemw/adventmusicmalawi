<?php

namespace App\Services\Songs;

use App\Models\Song;
use App\Models\SongCopyright;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Creates a Song directly from the admin panel (skips PayChangu).
 * Called by the Filament SongResource create form.
 */
class AdminSongUploaderService
{
    /**
     * Create or update a Song row with its copyright metadata and pivots.
     * Handles the file-path resolution so nothing here calls Storage::disk('public')->url().
     *
     * @param  array<string,mixed>  $data  fields from the Filament form
     */
    public function create(array $data, int $uploaderId): Song
    {
        return DB::transaction(function () use ($data, $uploaderId) {
            $categoryIds = $data['category_ids'] ?? [];
            $occasionIds = $data['occasion_ids'] ?? [];
            $moodIds = $data['mood_ids'] ?? [];
            $featuredArtistIds = $data['featured_artist_ids'] ?? [];

            $copyright = [
                'copyright_owner' => $data['copyright_owner'] ?? null,
                'rights_holder' => $data['rights_holder'] ?? null,
                'permission_status' => $data['permission_status'] ?? 'owned',
                'license_type' => $data['license_type'] ?? null,
                'distribution_allowed' => $data['distribution_allowed'] ?? true,
                'monetization_allowed' => $data['monetization_allowed'] ?? false,
                'notes' => $data['copyright_notes'] ?? null,
            ];

            // Strip fields that aren't on the songs table
            $songData = collect($data)->except([
                'category_ids', 'occasion_ids', 'mood_ids', 'featured_artist_ids',
                'copyright_owner', 'rights_holder', 'permission_status', 'license_type',
                'distribution_allowed', 'monetization_allowed', 'copyright_notes',
                'publish_immediately',
            ])->toArray();

            $publishImmediately = (bool) ($data['publish_immediately'] ?? true);
            $songData['uploader_id'] = $uploaderId;
            $songData['status'] = $publishImmediately ? Song::STATUS_PUBLISHED : Song::STATUS_DRAFT;
            $songData['published_at'] = $publishImmediately ? now() : null;

            // If a music group was picked but no church, inherit the group's church
            if (!empty($songData['music_group_id']) && empty($songData['church_id'])) {
                $group = \App\Models\MusicGroup::find($songData['music_group_id']);
                if ($group) {
                    $songData['church_id'] = $group->church_id;
                }
            }

            $song = Song::create($songData);

            SongCopyright::create(['song_id' => $song->id] + $copyright);

            if (!empty($categoryIds)) $song->categories()->sync($categoryIds);
            if (!empty($occasionIds)) $song->occasions()->sync($occasionIds);
            if (!empty($moodIds)) $song->moods()->sync($moodIds);
            if (!empty($featuredArtistIds)) {
                $song->featuredArtists()->sync(collect($featuredArtistIds)
                    ->mapWithKeys(fn ($id) => [$id => ['role' => 'featured']])
                    ->all());
            }

            return $song->refresh();
        });
    }

    public function update(Song $song, array $data): Song
    {
        return DB::transaction(function () use ($song, $data) {
            $categoryIds = $data['category_ids'] ?? null;
            $occasionIds = $data['occasion_ids'] ?? null;
            $moodIds = $data['mood_ids'] ?? null;
            $featuredArtistIds = $data['featured_artist_ids'] ?? null;

            $copyright = collect([
                'copyright_owner' => $data['copyright_owner'] ?? null,
                'rights_holder' => $data['rights_holder'] ?? null,
                'permission_status' => $data['permission_status'] ?? null,
                'license_type' => $data['license_type'] ?? null,
                'distribution_allowed' => $data['distribution_allowed'] ?? null,
                'monetization_allowed' => $data['monetization_allowed'] ?? null,
                'notes' => $data['copyright_notes'] ?? null,
            ])->filter(fn ($v) => $v !== null)->toArray();

            $songData = collect($data)->except([
                'category_ids', 'occasion_ids', 'mood_ids', 'featured_artist_ids',
                'copyright_owner', 'rights_holder', 'permission_status', 'license_type',
                'distribution_allowed', 'monetization_allowed', 'copyright_notes',
                'publish_immediately',
            ])->toArray();

            $song->update($songData);

            if (!empty($copyright)) {
                if ($song->copyright) {
                    $song->copyright->update($copyright);
                } else {
                    SongCopyright::create(['song_id' => $song->id] + $copyright);
                }
            }

            if ($categoryIds !== null) $song->categories()->sync($categoryIds);
            if ($occasionIds !== null) $song->occasions()->sync($occasionIds);
            if ($moodIds !== null) $song->moods()->sync($moodIds);
            if ($featuredArtistIds !== null) {
                $song->featuredArtists()->sync(collect($featuredArtistIds)
                    ->mapWithKeys(fn ($id) => [$id => ['role' => 'featured']])
                    ->all());
            }

            return $song->refresh();
        });
    }
}
