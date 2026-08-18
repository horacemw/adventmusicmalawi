<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Region;
use Illuminate\Database\Seeder;

class GeographySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Northern Region' => ['Chitipa', 'Karonga', 'Rumphi', 'Mzimba', 'Nkhata Bay', 'Likoma'],
            'Central Region' => ['Kasungu', 'Nkhotakota', 'Ntchisi', 'Dowa', 'Salima', 'Lilongwe', 'Mchinji', 'Dedza', 'Ntcheu'],
            'Southern Region' => ['Mangochi', 'Machinga', 'Zomba', 'Chiradzulu', 'Blantyre', 'Mwanza', 'Neno', 'Thyolo', 'Mulanje', 'Phalombe', 'Chikwawa', 'Nsanje', 'Balaka'],
        ];

        foreach ($data as $regionName => $districts) {
            $region = Region::firstOrCreate(['name' => $regionName]);
            foreach ($districts as $districtName) {
                District::firstOrCreate([
                    'region_id' => $region->id,
                    'name' => $districtName,
                ]);
            }
        }
    }
}
