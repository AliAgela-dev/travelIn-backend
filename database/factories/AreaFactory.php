<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Area>
 */
class AreaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'city_id' => \App\Models\City::factory(),
            'ar_name' => $this->faker->streetName() . ' AR',
            'en_name' => $this->faker->streetName() . ' EN',
            'status' => \App\Enums\GeneralStatus::Active,
        ];
    }
}
