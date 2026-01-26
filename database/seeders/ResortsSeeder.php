<?php

namespace Database\Seeders;

use App\Enums\ResortStatus;
use App\Enums\UserType;
use App\Models\Area;
use App\Models\Resort;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ResortsSeeder extends Seeder
{
    /**
     * Seed resorts with images.
     */
    public function run(): void
    {
        $owners = User::where('type', UserType::ResortOwner)->get();
        $areas = Area::with('city')->get();

        if ($owners->isEmpty() || $areas->isEmpty()) {
            $this->command->warn('⚠ No owners or areas found. Run UsersSeeder and AreasSeeder first.');
            return;
        }

        $resortNames = [
            ['ar' => 'منتجع النخيل', 'en' => 'Palm Beach Resort'],
            ['ar' => 'منتجع الواحة', 'en' => 'Oasis Resort'],
            ['ar' => 'شاليهات البحر الأزرق', 'en' => 'Blue Sea Chalets'],
            ['ar' => 'منتجع الصحراء الذهبية', 'en' => 'Golden Desert Resort'],
            ['ar' => 'فندق الشاطئ', 'en' => 'Beach Hotel'],
            ['ar' => 'منتجع القمر', 'en' => 'Moon Resort'],
            ['ar' => 'شاليهات الشمس', 'en' => 'Sun Chalets'],
            ['ar' => 'منتجع الجنة', 'en' => 'Paradise Resort'],
            ['ar' => 'فيلا البحر', 'en' => 'Sea Villa'],
            ['ar' => 'منتجع النجوم', 'en' => 'Star Resort'],
        ];

        // Status distribution: 6 Active, 2 Pending, 1 Rejected, 1 Inactive
        $statuses = [
            ResortStatus::Active, ResortStatus::Active, ResortStatus::Active,
            ResortStatus::Active, ResortStatus::Active, ResortStatus::Active,
            ResortStatus::Pending, ResortStatus::Pending,
            ResortStatus::Rejected,
            ResortStatus::Inactive,
        ];

        foreach ($owners as $index => $owner) {
            $area = $areas->random();
            $names = $resortNames[$index] ?? ['ar' => 'منتجع ' . ($index + 1), 'en' => 'Resort ' . ($index + 1)];

            $resort = Resort::create([
                'owner_id' => $owner->id,
                'city_id' => $area->city_id,
                'area_id' => $area->id,
                'ar_name' => $names['ar'],
                'en_name' => $names['en'],
                'ar_description' => 'وصف تفصيلي لـ ' . $names['ar'] . '. نقدم أفضل الخدمات والمرافق للضيوف.',
                'en_description' => 'Detailed description of ' . $names['en'] . '. We offer the best services and amenities for our guests.',
                'phone_number' => '2189' . fake()->numerify('########'),
                'email' => Str::slug($names['en']) . '@example.com',
                'status' => $statuses[$index] ?? ResortStatus::Pending,
            ]);

            // Add 3-5 images
            $imageCount = rand(3, 5);
            for ($i = 0; $i < $imageCount; $i++) {
                try {
                    $resort->addMediaFromUrl('https://picsum.photos/seed/' . Str::random(8) . '/800/600')
                           ->toMediaCollection('images');
                } catch (\Exception $e) {
                    // Skip if image download fails
                }
            }
        }

        $this->command->info('✓ Created 10 resorts with images');
    }
}
