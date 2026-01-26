<?php

namespace Tests\Feature\User;

use App\Models\Resort;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_review()
    {
        $user = User::factory()->traveler()->create();
        $resort = Resort::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/user/reviews', [
                'reviewable_type' => 'resort',
                'reviewable_id' => $resort->id,
                'rating' => 5,
                'comment' => 'Great place!',
            ]);

        $response->assertCreated()
            ->assertJsonFragment(['rating' => 5]);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'reviewable_id' => $resort->id,
        ]);
    }

    public function test_user_cannot_review_twice()
    {
        $user = User::factory()->traveler()->create();
        $resort = Resort::factory()->create();
        Review::factory()->create([
            'user_id' => $user->id,
            'reviewable_type' => Resort::class,
            'reviewable_id' => $resort->id,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/user/reviews', [
                'reviewable_type' => 'resort',
                'reviewable_id' => $resort->id,
                'rating' => 4,
            ]);

        $response->assertStatus(422);
    }

    public function test_user_can_delete_own_review()
    {
        $user = User::factory()->traveler()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/v1/user/reviews/{$review->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }
}
