<?php

namespace Database\Seeders;

use App\Enums\GeneralStatus;
use App\Models\Area;
use App\Models\City;
use Illuminate\Database\Seeder;

class AreasSeeder extends Seeder
{
    /**
     * Seed areas for each city.
     */
    public function run(): void
    {
        $areasData = [
            'Tripoli' => [
                ['ar_name' => 'المدينة القديمة', 'en_name' => 'Old City'],
                ['ar_name' => 'جنزور', 'en_name' => 'Janzour'],
                ['ar_name' => 'تاجوراء', 'en_name' => 'Tajoura'],
                ['ar_name' => 'عين زارة', 'en_name' => 'Ain Zara'],
            ],
            'Benghazi' => [
                ['ar_name' => 'الصابري', 'en_name' => 'Al-Sabri'],
                ['ar_name' => 'السلماني', 'en_name' => 'Salmani'],
                ['ar_name' => 'البركة', 'en_name' => 'Al-Berka'],
            ],
            'Misrata' => [
                ['ar_name' => 'المدينة', 'en_name' => 'Downtown'],
                ['ar_name' => 'قصر أحمد', 'en_name' => 'Qasr Ahmed'],
                ['ar_name' => 'الزروق', 'en_name' => 'Zrouq'],
            ],
            'Sabha' => [
                ['ar_name' => 'المنشية', 'en_name' => 'Al-Manshiya'],
                ['ar_name' => 'القرضة', 'en_name' => 'Al-Qarda'],
            ],
            'Zawiya' => [
                ['ar_name' => 'وسط المدينة', 'en_name' => 'City Center'],
                ['ar_name' => 'الحرشة', 'en_name' => 'Al-Harsha'],
                ['ar_name' => 'الجديدة', 'en_name' => 'Al-Jadida'],
            ],
        ];

        $count = 0;
        foreach ($areasData as $cityName => $areas) {
            $city = City::where('en_name', $cityName)->first();
            if (!$city) continue;

            foreach ($areas as $area) {
                Area::create([
                    'city_id' => $city->id,
                    ...$area,
                    'status' => GeneralStatus::Active,
                ]);
                $count++;
            }
        }

        $this->command->info("✓ Created {$count} areas");
    }
}
