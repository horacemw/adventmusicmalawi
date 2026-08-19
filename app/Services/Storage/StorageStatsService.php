<?php

namespace App\Services\Storage;

use App\Models\Song;
use App\Models\SubmissionFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class StorageStatsService
{
    /**
     * Return storage stats. Cached for 5 minutes so hitting the storage page
     * doesn't scan disk on every request.
     */
    public function stats(): array
    {
        return Cache::remember('admin.storage.stats', 300, function () {
            $disk = Storage::disk('public');

            $audioBytes = $this->folderSize($disk, 'songs/audio')
                + $this->folderSize($disk, 'submissions');
            $artworkBytes = $this->folderSize($disk, 'songs/artwork')
                + $this->folderSize($disk, 'albums/artwork')
                + $this->folderSize($disk, 'artists/images')
                + $this->folderSize($disk, 'artists/covers')
                + $this->folderSize($disk, 'churches/images')
                + $this->folderSize($disk, 'churches/covers')
                + $this->folderSize($disk, 'groups/images')
                + $this->folderSize($disk, 'groups/covers')
                + $this->folderSize($disk, 'occasions')
                + $this->folderSize($disk, 'avatars');
            $otherBytes = 0; // could scan the root minus known folders — deferred

            $totalBytes = $audioBytes + $artworkBytes + $otherBytes;

            // Available disk space (host disk that the docker volume lives on)
            $free = @disk_free_space(storage_path()) ?: 0;
            $capacity = @disk_total_space(storage_path()) ?: 0;

            return [
                'audio_bytes' => $audioBytes,
                'artwork_bytes' => $artworkBytes,
                'other_bytes' => $otherBytes,
                'total_bytes' => $totalBytes,
                'free_bytes' => $free,
                'capacity_bytes' => $capacity,
                'used_percentage' => $capacity > 0 ? round(($capacity - $free) / $capacity * 100, 2) : null,
                'song_count' => Song::whereNotNull('audio_path')->count(),
                'submission_file_count' => SubmissionFile::count(),
                'largest_songs' => Song::whereNotNull('audio_size_bytes')
                    ->orderByDesc('audio_size_bytes')
                    ->limit(10)
                    ->get(['id', 'title', 'audio_size_bytes'])
                    ->map(fn ($s) => [
                        'id' => $s->id,
                        'title' => $s->title,
                        'size_bytes' => (int) $s->audio_size_bytes,
                        'size_mb' => round(($s->audio_size_bytes ?? 0) / 1024 / 1024, 2),
                    ])->all(),
            ];
        });
    }

    public function forget(): void
    {
        Cache::forget('admin.storage.stats');
    }

    private function folderSize($disk, string $folder): int
    {
        try {
            $total = 0;
            foreach ($disk->allFiles($folder) as $file) {
                $total += (int) $disk->size($file);
            }
            return $total;
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
