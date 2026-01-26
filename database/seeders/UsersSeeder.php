<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Seed users of all types.
     */
    public function run(): void
    {
        $tripoli = City::where('en_name', 'Tripoli')->first();

        // 1. Super Admin (fixed credentials)
        User::create([
            'full_name' => 'Super Admin',
            'phone_number' => '218910000000',
            'password' => Hash::make('password'),
            'city_id' => $tripoli?->id,
            'type' => UserType::SuperAdmin,
            'status' => UserStatus::Active,
        ]);
        $this->command->info('✓ Created Super Admin (218910000000 / password)');

        // 2. Regular Admins
        User::factory(3)->admin()->create([
            'city_id' => $tripoli?->id,
        ]);
        $this->command->info('✓ Created 3 Admins');

        // 3. Resort Owners
        $cities = City::all();
        for ($i = 0; $i < 10; $i++) {
            User::factory()->resortOwner()->create([
                'city_id' => $cities->random()->id,
                'full_name' => "Resort Owner " . ($i + 1),
            ]);
        }
        $this->command->info('✓ Created 10 Resort Owners');

        // 4. Travelers (mix of statuses)
        User::factory(45)->traveler()->create([
            'city_id' => fn() => $cities->random()->id,
        ]);
        User::factory(3)->traveler()->banned()->create();
        User::factory(2)->traveler()->inactive()->create();
        $this->command->info('✓ Created 50 Travelers (45 active, 3 banned, 2 inactive)');
    }
}
