<?php

namespace Database\Factories;

use App\Enums\UserStatus;
use App\Enums\UserType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'phone_number' => '2189' . fake()->unique()->numerify('########'),
            'password' => static::$password ??= Hash::make('password'),
            'city_id' => null,
            'date_of_birth' => fake()->optional()->date(),
            'status' => UserStatus::Active,
            'type' => UserType::User,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user is a resort owner.
     */
    public function resortOwner(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => UserType::ResortOwner,
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => UserType::Admin,
        ]);
    }

    /**
     * Indicate that the user is a super admin.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => UserType::SuperAdmin,
        ]);
    }

    /**
     * Indicate that the user is banned.
     */
    public function banned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::Banned,
        ]);
    }

    /**
     * Indicate that the user is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::Inactive,
        ]);
    }

    /**
     * Indicate that the user is a traveler (regular user).
     */
    public function traveler(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => UserType::User,
        ]);
    }
}
