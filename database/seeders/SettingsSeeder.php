<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'platform.name', 'value' => 'Malawi Adventist Music', 'group' => 'platform', 'is_public' => true, 'description' => 'Public platform name'],
            ['key' => 'platform.tagline', 'value' => 'Many Voices. One Adventist Sound.', 'group' => 'platform', 'is_public' => true, 'description' => 'Public tagline'],
            ['key' => 'submissions.fee_amount', 'value' => '15000', 'cast' => 'integer', 'group' => 'submissions', 'description' => 'Submission fee in the platform currency'],
            ['key' => 'submissions.fee_currency', 'value' => 'MWK', 'group' => 'submissions', 'description' => 'Currency for submission fees'],
            ['key' => 'submissions.allow_refund_on_reject', 'value' => '0', 'cast' => 'boolean', 'group' => 'submissions', 'description' => 'Automatically refund fee when submission is rejected'],
            ['key' => 'uploads.max_audio_mb', 'value' => '50', 'cast' => 'integer', 'group' => 'uploads', 'description' => 'Max audio upload size in megabytes'],
            ['key' => 'uploads.max_image_mb', 'value' => '5', 'cast' => 'integer', 'group' => 'uploads', 'description' => 'Max image upload size in megabytes'],
            ['key' => 'uploads.audio_mime_types', 'value' => '["audio/mpeg","audio/mp4","audio/aac","audio/wav","audio/x-wav"]', 'cast' => 'json', 'group' => 'uploads', 'description' => 'Allowed audio MIME types'],
            ['key' => 'streams.min_seconds_to_count', 'value' => '30', 'cast' => 'integer', 'group' => 'streams', 'description' => 'Minimum playback seconds for a stream to count'],
            ['key' => 'streams.cooldown_seconds', 'value' => '120', 'cast' => 'integer', 'group' => 'streams', 'description' => 'Cooldown between counted plays of the same song per session'],
        ];

        foreach ($defaults as $s) {
            Setting::updateOrCreate(
                ['key' => $s['key']],
                [
                    'value' => $s['value'],
                    'cast' => $s['cast'] ?? 'string',
                    'group' => $s['group'] ?? 'general',
                    'description' => $s['description'] ?? null,
                    'is_public' => $s['is_public'] ?? false,
                ]
            );
        }
    }
}
