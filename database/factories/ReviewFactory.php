<?php

namespace Database\Factories;

use App\Models\Resort;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'reviewable_type' => Resort::class,
            'reviewable_id' => Resort::factory(),
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->optional()->paragraph(),
        ];
    }

    public function forResort(Resort $resort): static
    {
        return $this->state(fn(array $attributes) => [
            'reviewable_type' => Resort::class,
            'reviewable_id' => $resort->id,
        ]);
    }
}
