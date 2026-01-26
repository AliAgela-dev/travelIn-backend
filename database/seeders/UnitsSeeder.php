<?php

namespace Database\Seeders;

use App\Enums\GeneralStatus;
use App\Models\Resort;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UnitsSeeder extends Seeder
{
    /**
     * Seed units for each resort with images.
     */
    public function run(): void
    {
        $resorts = Resort::all();

        if ($resorts->isEmpty()) {
            $this->command->warn('⚠ No resorts found. Run ResortsSeeder first.');
            return;
        }

        $unitTypes = [
            ['ar' => 'غرفة ديلوكس', 'en' => 'Deluxe Room', 'price' => [150, 250], 'capacity' => [2, 2]],
            ['ar' => 'جناح عائلي', 'en' => 'Family Suite', 'price' => [300, 450], 'capacity' => [4, 6]],
            ['ar' => 'شاليه مطل على البحر', 'en' => 'Sea View Chalet', 'price' => [400, 600], 'capacity' => [4, 6]],
            ['ar' => 'غرفة اقتصادية', 'en' => 'Economy Room', 'price' => [80, 120], 'capacity' => [2, 2]],
            ['ar' => 'فيلا خاصة', 'en' => 'Private Villa', 'price' => [500, 800], 'capacity' => [6, 10]],
        ];

        $features = [
            ['wifi', 'tv', 'air_conditioning'],
            ['wifi', 'tv', 'air_conditioning', 'kitchen', 'balcony'],
            ['wifi', 'tv', 'air_conditioning', 'sea_view', 'private_pool'],
            ['wifi', 'tv'],
            ['wifi', 'tv', 'air_conditioning', 'kitchen', 'private_pool', 'garden'],
        ];

        $totalUnits = 0;

        foreach ($resorts as $resort) {
            // 3-5 units per resort
            $unitCount = rand(3, 5);

            for ($i = 0; $i < $unitCount; $i++) {
                $typeIndex = $i % count($unitTypes);
                $type = $unitTypes[$typeIndex];

                $unit = Unit::create([
                    'resort_id' => $resort->id,
                    'ar_name' => $type['ar'] . ' #' . ($i + 1),
                    'en_name' => $type['en'] . ' #' . ($i + 1),
                    'ar_description' => 'وصف ' . $type['ar'] . ' في ' . $resort->ar_name,
                    'en_description' => 'Description of ' . $type['en'] . ' at ' . $resort->en_name,
                    'price_per_night' => fake()->randomFloat(2, $type['price'][0], $type['price'][1]),
                    'capacity' => rand($type['capacity'][0], $type['capacity'][1]),
                    'room_count' => rand(1, 3),
                    'features' => $features[$typeIndex],
                    'status' => GeneralStatus::Active,
                ]);

                // Add 2-4 images
                $imageCount = rand(2, 4);
                for ($j = 0; $j < $imageCount; $j++) {
                    try {
                        $unit->addMediaFromUrl('https://picsum.photos/seed/' . Str::random(8) . '/800/600')
                             ->toMediaCollection('images');
                    } catch (\Exception $e) {
                        // Skip if image download fails
                    }
                }

                $totalUnits++;
            }
        }

        $this->command->info("✓ Created {$totalUnits} units with images");
    }
}
