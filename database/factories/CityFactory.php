<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\City>
 */
class CityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ar_name' => $this->faker->city() . ' AR',
            'en_name' => $this->faker->city() . ' EN',
            'status' => \App\Enums\GeneralStatus::Active,
        ];
    }
}
