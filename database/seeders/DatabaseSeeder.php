<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting comprehensive database seeding...');
        $this->command->newLine();

        // Order matters due to dependencies
        $this->call([
            CitiesSeeder::class,
            AreasSeeder::class,
            UsersSeeder::class,
            ResortsSeeder::class,
            UnitsSeeder::class,
            UnitPricingSeeder::class,
            BookingsSeeder::class,
            ReviewsSeeder::class,
            NotificationsSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('✅ Database seeding completed!');
        $this->command->info('📱 Super Admin: 218910000000 / password');
    }
}
