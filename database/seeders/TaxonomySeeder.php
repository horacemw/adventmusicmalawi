<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Language;
use App\Models\Mood;
use App\Models\Occasion;
use Illuminate\Database\Seeder;

class TaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            ['name' => 'Chichewa', 'iso_code' => 'ny', 'sort_order' => 1],
            ['name' => 'English', 'iso_code' => 'en', 'sort_order' => 2],
            ['name' => 'Chitumbuka', 'iso_code' => 'tum', 'sort_order' => 3],
            ['name' => 'Chiyao', 'iso_code' => 'yao', 'sort_order' => 4],
            ['name' => 'Chitonga', 'iso_code' => 'tog', 'sort_order' => 5],
            ['name' => 'Chisena', 'iso_code' => 'seh', 'sort_order' => 6],
            ['name' => 'Chilomwe', 'iso_code' => 'ngl', 'sort_order' => 7],
            ['name' => 'Other', 'iso_code' => null, 'sort_order' => 99],
        ];
        foreach ($languages as $l) {
            Language::firstOrCreate(['name' => $l['name']], $l);
        }

        $categories = [
            ['name' => 'Hymns', 'icon' => 'book-open', 'color' => '#22c55e'],
            ['name' => 'Worship', 'icon' => 'heart', 'color' => '#a855f7'],
            ['name' => 'Gospel', 'icon' => 'music', 'color' => '#f59e0b'],
            ['name' => 'Contemporary', 'icon' => 'sparkles', 'color' => '#3b82f6'],
            ['name' => 'Acapella', 'icon' => 'mic', 'color' => '#ef4444'],
            ['name' => 'Instrumental', 'icon' => 'piano', 'color' => '#64748b'],
            ['name' => "Children's Music", 'icon' => 'smile', 'color' => '#fb7185'],
            ['name' => 'Youth Music', 'icon' => 'users', 'color' => '#06b6d4'],
            ['name' => 'Choir Music', 'icon' => 'users-group', 'color' => '#22c55e'],
            ['name' => 'Solo Music', 'icon' => 'user', 'color' => '#8b5cf6'],
            ['name' => 'Quartet', 'icon' => 'users', 'color' => '#f97316'],
            ['name' => 'Sabbath Songs', 'icon' => 'sun', 'color' => '#f59e0b'],
            ['name' => 'Evangelism', 'icon' => 'megaphone', 'color' => '#dc2626'],
            ['name' => 'Special Music', 'icon' => 'star', 'color' => '#eab308'],
        ];
        foreach ($categories as $i => $c) {
            Category::firstOrCreate(['name' => $c['name']], $c + ['sort_order' => $i]);
        }

        $occasions = [
            'Wedding', 'Marriage', 'Funeral', 'Bereavement', 'Graduation',
            'Birthday', 'Anniversary', 'Christmas', 'Easter', 'Sabbath',
            'Evangelism', 'Thanksgiving', 'Church Celebration',
            'Youth Programs', 'Camp Meeting', 'Baptism', 'Communion',
        ];
        foreach ($occasions as $i => $name) {
            Occasion::firstOrCreate(['name' => $name], ['sort_order' => $i]);
        }

        $moods = [
            ['name' => 'Joyful', 'color' => '#facc15'],
            ['name' => 'Peaceful', 'color' => '#22c55e'],
            ['name' => 'Inspirational', 'color' => '#3b82f6'],
            ['name' => 'Reflective', 'color' => '#8b5cf6'],
            ['name' => 'Sad', 'color' => '#64748b'],
            ['name' => 'Comforting', 'color' => '#06b6d4'],
            ['name' => 'Hopeful', 'color' => '#f59e0b'],
            ['name' => 'Worshipful', 'color' => '#a855f7'],
            ['name' => 'Celebratory', 'color' => '#ef4444'],
        ];
        foreach ($moods as $i => $m) {
            Mood::firstOrCreate(['name' => $m['name']], $m + ['sort_order' => $i]);
        }

        $genres = ['Gospel', 'Traditional', 'Contemporary', 'Acapella', 'Choral', 'Praise', 'Worship'];
        foreach ($genres as $name) {
            Genre::firstOrCreate(['name' => $name]);
        }
    }
}
