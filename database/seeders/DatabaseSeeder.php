<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            GeographySeeder::class,
            TaxonomySeeder::class,
            SettingsSeeder::class,
            PromotionPackageSeeder::class,
            DemoContentSeeder::class,
        ]);
    }
}
