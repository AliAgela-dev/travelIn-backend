<?php

namespace Database\Factories;

use App\Models\Unit;
use App\Models\UnitAvailability;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UnitAvailability>
 */
class UnitAvailabilityFactory extends Factory
{
    protected $model = UnitAvailability::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('+1 day', '+2 weeks');
        $endDate = $this->faker->dateTimeBetween($startDate, '+1 month');

        return [
            'unit_id' => Unit::factory(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'reason' => $this->faker->randomElement(['Maintenance', 'Owner Use', 'Renovation', null]),
        ];
    }
}
