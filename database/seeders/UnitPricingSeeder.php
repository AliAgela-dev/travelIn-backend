<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\UnitPricing;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UnitPricingSeeder extends Seeder
{
    /**
     * Seed pricing rules for units.
     */
    public function run(): void
    {
        $units = Unit::all();

        if ($units->isEmpty()) {
            $this->command->warn('⚠ No units found. Run UnitsSeeder first.');
            return;
        }

        $count = 0;

        foreach ($units as $unit) {
            // Summer/Peak Season pricing (higher)
            UnitPricing::create([
                'unit_id' => $unit->id,
                'start_date' => Carbon::now()->addMonths(3)->startOfMonth(),
                'end_date' => Carbon::now()->addMonths(5)->endOfMonth(),
                'price_per_night' => $unit->price_per_night * 1.3, // 30% higher
                'label' => 'Summer Peak Season',
            ]);
            $count++;

            // Holiday pricing (even higher)
            UnitPricing::create([
                'unit_id' => $unit->id,
                'start_date' => Carbon::now()->addMonths(6)->startOfMonth(),
                'end_date' => Carbon::now()->addMonths(6)->startOfMonth()->addDays(14),
                'price_per_night' => $unit->price_per_night * 1.5, // 50% higher
                'label' => 'Holiday Special',
            ]);
            $count++;
        }

        $this->command->info("✓ Created {$count} pricing rules");
    }
}
