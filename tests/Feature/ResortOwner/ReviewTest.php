<?php

namespace Tests\Feature\ResortOwner;

use App\Models\Resort;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_list_reviews()
    {
        $owner = User::factory()->resortOwner()->create();
        $resort = Resort::factory()->create(['owner_id' => $owner->id]);
        Review::factory()->count(3)->create([
            'reviewable_type' => Resort::class,
            'reviewable_id' => $resort->id,
        ]);

        $response = $this->actingAs($owner)
            ->getJson('/api/v1/resort-owner/reviews');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_owner_can_reply_to_review()
    {
        $owner = User::factory()->resortOwner()->create();
        $resort = Resort::factory()->create(['owner_id' => $owner->id]);
        $review = Review::factory()->create([
            'reviewable_type' => Resort::class,
            'reviewable_id' => $resort->id,
        ]);

        $response = $this->actingAs($owner)
            ->putJson("/api/v1/resort-owner/reviews/{$review->id}", [
                'owner_reply' => 'Thank you for your feedback!',
            ]);

        $response->assertOk()
            ->assertJsonFragment(['owner_reply' => 'Thank you for your feedback!']);
    }
}
