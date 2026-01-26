<?php

namespace Database\Factories;

use App\Models\Unit;
use App\Models\UnitPricing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UnitPricing>
 */
class UnitPricingFactory extends Factory
{
    protected $model = UnitPricing::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('+1 week', '+1 month');
        $endDate = $this->faker->dateTimeBetween($startDate, '+2 months');

        return [
            'unit_id' => Unit::factory(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'price_per_night' => $this->faker->randomFloat(2, 100, 500),
            'label' => $this->faker->randomElement(['Holiday Rate', 'Summer Special', 'Weekend Rate']),
        ];
    }
}
