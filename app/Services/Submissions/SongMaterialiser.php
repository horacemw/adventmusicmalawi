<?php

namespace App\Services\Submissions;

use App\Models\Song;
use App\Models\SongCopyright;
use App\Models\Submission;
use App\Models\SubmissionFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * On admin approval, materialise a Submission into a published Song.
 * Moves files from submissions/ to songs/ so the submission dir can be cleared later.
 */
class SongMaterialiser
{
    public function publish(Submission $submission): Song
    {
        return DB::transaction(function () use ($submission) {
            $audio = $submission->files()->where('kind', SubmissionFile::KIND_AUDIO)->firstOrFail();
            $artwork = $submission->files()->where('kind', SubmissionFile::KIND_ARTWORK)->first();

            $audioTarget = 'songs/audio/'.uniqid($submission->id.'_', true).'.'.pathinfo($audio->storage_path, PATHINFO_EXTENSION);
            Storage::disk('public')->move($audio->storage_path, $audioTarget);

            $artworkTarget = null;
            if ($artwork) {
                $artworkTarget = 'songs/artwork/'.uniqid($submission->id.'_', true).'.'.pathinfo($artwork->storage_path, PATHINFO_EXTENSION);
                Storage::disk('public')->move($artwork->storage_path, $artworkTarget);
            }

            $song = Song::create([
                'artist_id' => $submission->artist_id,
                'music_group_id' => $submission->music_group_id,
                'church_id' => $submission->church_id,
                'language_id' => $submission->language_id,
                'genre_id' => $submission->genre_id,
                'uploader_id' => $submission->user_id,
                'title' => $submission->song_title,
                'description' => $submission->description,
                'audio_path' => Storage::disk('public')->url($audioTarget),
                'audio_format' => $audio->mime_type,
                'audio_size_bytes' => $audio->size_bytes,
                'artwork_path' => $artworkTarget ? Storage::disk('public')->url($artworkTarget) : null,
                'release_year' => $submission->release_year,
                'status' => Song::STATUS_PUBLISHED,
                'published_at' => now(),
            ]);

            SongCopyright::create([
                'song_id' => $song->id,
                'copyright_owner' => $submission->copyright_owner,
                'rights_holder' => $submission->rights_holder,
                'permission_status' => $submission->permission_status,
                'distribution_allowed' => true,
                'monetization_allowed' => false,
                'notes' => $submission->copyright_notes,
            ]);

            $song->categories()->sync($submission->categories()->pluck('categories.id')->all());
            $song->occasions()->sync($submission->occasions()->pluck('occasions.id')->all());
            $song->moods()->sync($submission->moods()->pluck('moods.id')->all());

            // Update the audio file row to point at the new path so we retain the record.
            $audio->update(['storage_path' => $audioTarget]);
            if ($artwork && $artworkTarget) {
                $artwork->update(['storage_path' => $artworkTarget]);
            }

            $submission->update([
                'song_id' => $song->id,
                'status' => Submission::STATUS_PUBLISHED,
                'published_at' => now(),
            ]);

            return $song;
        });
    }
}
