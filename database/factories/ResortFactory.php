<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Resort>
 */
class ResortFactory extends Factory
{
    protected $model = \App\Models\Resort::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => \App\Models\User::factory()->resortOwner(),
            'city_id' => \App\Models\City::factory(),
            'area_id' => \App\Models\Area::factory(),
            'ar_name' => $this->faker->company() . ' AR',
            'en_name' => $this->faker->company() . ' EN',
            'ar_description' => $this->faker->paragraph(),
            'en_description' => $this->faker->paragraph(),
            'phone_number' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'status' => \App\Enums\ResortStatus::Pending,
        ];
    }
}
