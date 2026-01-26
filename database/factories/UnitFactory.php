<?php

namespace Database\Factories;

use App\Models\Resort;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unit>
 */
class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        return [
            'resort_id' => Resort::factory(),
            'ar_name' => $this->faker->word() . ' غرفة',
            'en_name' => $this->faker->word() . ' Room',
            'ar_description' => $this->faker->sentence(),
            'en_description' => $this->faker->sentence(),
            'price_per_night' => $this->faker->randomFloat(2, 50, 500),
            'capacity' => $this->faker->numberBetween(1, 6),
            'room_count' => $this->faker->numberBetween(1, 3),
            'features' => ['wifi', 'breakfast_included'],
            'status' => \App\Enums\GeneralStatus::Active,
        ];
    }
}
