<?php

namespace Database\Seeders;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Category;
use App\Models\Church;
use App\Models\District;
use App\Models\Genre;
use App\Models\HymnBook;
use App\Models\Language;
use App\Models\MusicGroup;
use App\Models\Occasion;
use App\Models\Region;
use App\Models\Song;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds demo data flagged with `is_demo`-style names so it's obvious.
 * Do NOT use fabricated real-world people/organizations.
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $listener = User::firstOrCreate(
            ['email' => 'listener@demo.local'],
            ['name' => 'Demo Listener', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $listener->assignRole('listener');

        $artist = User::firstOrCreate(
            ['email' => 'artist@demo.local'],
            ['name' => 'Demo Artist', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $artist->assignRole('artist');

        $admin = User::firstOrCreate(
            ['email' => 'admin@demo.local'],
            ['name' => 'Demo Admin', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $admin->assignRole('super-admin');

        $central = Region::where('name', 'Central Region')->first();
        $lilongweDistrict = District::where('name', 'Lilongwe')->first();

        $church = Church::firstOrCreate(['name' => 'Demo Central SDA Church'], [
            'region_id' => $central?->id,
            'district_id' => $lilongweDistrict?->id,
            'description' => 'Sample church profile — demo data.',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $chichewa = Language::where('name', 'Chichewa')->first();
        $gospel = Genre::where('name', 'Gospel')->first();

        // Demo groups (choir, acapella, youth, children, quartet)
        $groupDefs = [
            ['name' => 'Demo Central Choir', 'type' => 'choir'],
            ['name' => 'Demo Youth Voices', 'type' => 'youth'],
            ['name' => 'Demo Acapella Ensemble', 'type' => 'acapella'],
            ["Demo Children's Chorus", 'children'],
            ['name' => 'Demo Quartet', 'type' => 'quartet'],
        ];

        $groups = collect();
        foreach ($groupDefs as $g) {
            $name = $g['name'] ?? $g[0];
            $type = $g['type'] ?? $g[1];
            $groups->push(MusicGroup::firstOrCreate(['name' => $name], [
                'church_id' => $church->id,
                'region_id' => $central?->id,
                'district_id' => $lilongweDistrict?->id,
                'type' => $type,
                'is_verified' => true,
                'is_active' => true,
                'founded_year' => 2010 + rand(0, 14),
            ]));
        }

        // Demo solo artist tied to demo artist user
        $demoArtist = Artist::firstOrCreate(['name' => 'Demo Solo Artist'], [
            'user_id' => $artist->id,
            'region_id' => $central?->id,
            'district_id' => $lilongweDistrict?->id,
            'church_id' => $church->id,
            'stage_name' => 'Demo Solo',
            'bio' => 'Demo solo artist profile.',
            'gender' => 'female',
            'is_active' => true,
            'is_verified' => true,
        ]);

        $worshipCat = Category::where('name', 'Worship')->first();
        $hymnsCat = Category::where('name', 'Hymns')->first();
        $sabbathOcc = Occasion::where('name', 'Sabbath')->first();
        $weddingOcc = Occasion::where('name', 'Wedding')->first();

        $songTitles = [
            'Muli Bwino',
            'Tikuyimba Kwa Yehova',
            'Nyimbo Zauzimu',
            'Ndakupatsani Chikondi',
            'Mzimu Woyera',
            'Mtendere wa Mulungu',
            'Yesu Ali Nane',
            'Tsiku la Sabata',
            'Kwaya ya Mulungu',
            'Ulendo wa Moyo',
        ];

        foreach ($songTitles as $i => $title) {
            $group = $groups->random();
            $song = Song::firstOrCreate(['title' => $title], [
                'music_group_id' => $group->id,
                'church_id' => $church->id,
                'language_id' => $chichewa?->id,
                'genre_id' => $gospel?->id,
                'uploader_id' => $admin->id,
                'status' => Song::STATUS_PUBLISHED,
                'published_at' => now()->subDays(rand(1, 90)),
                'release_year' => 2024,
                'duration_seconds' => rand(180, 300),
                'stream_count' => rand(100, 5000),
                'like_count' => rand(10, 500),
                'is_featured' => $i < 3,
            ]);

            if ($worshipCat && $hymnsCat) {
                $song->categories()->syncWithoutDetaching([$worshipCat->id, $hymnsCat->id]);
            }
            if ($sabbathOcc) {
                $song->occasions()->syncWithoutDetaching([$sabbathOcc->id]);
                if ($i % 3 === 0 && $weddingOcc) {
                    $song->occasions()->syncWithoutDetaching([$weddingOcc->id]);
                }
            }
        }

        // Demo album
        $album = Album::firstOrCreate(['title' => 'Demo Praise Volume 1'], [
            'music_group_id' => $groups->first()->id,
            'church_id' => $church->id,
            'primary_language_id' => $chichewa?->id,
            'release_year' => 2024,
            'is_published' => true,
        ]);

        Song::where('music_group_id', $groups->first()->id)
            ->limit(4)
            ->get()
            ->each(function (Song $s, int $idx) use ($album) {
                if (!$s->album_id) {
                    $s->update(['album_id' => $album->id, 'track_number' => $idx + 1]);
                }
            });

        // Demo hymn book
        HymnBook::firstOrCreate(['name' => 'Demo SDA Hymnal (Chichewa)'], [
            'language_id' => $chichewa?->id,
            'description' => 'Sample hymn book — demo entry.',
            'publisher' => 'Demo Publisher',
            'published_year' => 1985,
            'is_public_domain' => false,
            'copyright_notice' => 'Sample copyright entry — verify before use.',
        ]);
    }
}
