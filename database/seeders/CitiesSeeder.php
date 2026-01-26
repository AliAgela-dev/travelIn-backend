<?php

namespace Database\Seeders;

use App\Enums\GeneralStatus;
use App\Models\City;
use Illuminate\Database\Seeder;

class CitiesSeeder extends Seeder
{
    /**
     * Seed Libyan cities.
     */
    public function run(): void
    {
        $cities = [
            ['ar_name' => 'طرابلس', 'en_name' => 'Tripoli'],
            ['ar_name' => 'بنغازي', 'en_name' => 'Benghazi'],
            ['ar_name' => 'مصراتة', 'en_name' => 'Misrata'],
            ['ar_name' => 'سبها', 'en_name' => 'Sabha'],
            ['ar_name' => 'الزاوية', 'en_name' => 'Zawiya'],
        ];

        foreach ($cities as $city) {
            City::create([
                ...$city,
                'status' => GeneralStatus::Active,
            ]);
        }

        $this->command->info('✓ Created 5 cities');
    }
}
