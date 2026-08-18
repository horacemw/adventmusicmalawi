<?php

namespace Database\Seeders;

use App\Models\PromotionPackage;
use Illuminate\Database\Seeder;

class PromotionPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Song Feature — 7 days',
                'placement' => 'song_feature',
                'duration_days' => 7,
                'price' => 5000,
                'currency' => 'MWK',
                'description' => 'Promote a single song across the homepage and category pages for 7 days.',
                'perks' => ['homepage_row_placement', 'category_priority', 'social_share_asset'],
            ],
            [
                'name' => 'Song Feature — 30 days',
                'placement' => 'song_feature',
                'duration_days' => 30,
                'price' => 15000,
                'currency' => 'MWK',
                'description' => 'Extended song promotion across the platform for a full month.',
                'perks' => ['homepage_row_placement', 'category_priority', 'social_share_asset', 'newsletter_mention'],
            ],
            [
                'name' => 'Artist Spotlight — 30 days',
                'placement' => 'artist_feature',
                'duration_days' => 30,
                'price' => 25000,
                'currency' => 'MWK',
                'description' => 'Featured artist card and profile highlights for one month.',
                'perks' => ['artist_row_placement', 'search_priority', 'newsletter_feature'],
            ],
            [
                'name' => 'Homepage Hero — 7 days',
                'placement' => 'homepage_hero',
                'duration_days' => 7,
                'price' => 35000,
                'currency' => 'MWK',
                'description' => 'Prime hero placement at the top of the homepage.',
                'perks' => ['hero_placement', 'homepage_row_placement', 'social_share_asset'],
            ],
        ];

        foreach ($packages as $p) {
            PromotionPackage::updateOrCreate(['name' => $p['name']], $p + ['is_active' => true]);
        }
    }
}
