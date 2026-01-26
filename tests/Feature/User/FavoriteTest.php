<?php

namespace Tests\Feature\User;

use App\Models\Favorite;
use App\Models\Resort;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_resort_to_favorites()
    {
        $user = User::factory()->traveler()->create();
        $resort = Resort::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/user/favorites', [
                'favoritable_type' => 'resort',
                'favoritable_id' => $resort->id,
            ]);

        $response->assertCreated();
        $this->assertTrue($user->hasFavorited($resort));
    }

    public function test_user_can_add_unit_to_favorites()
    {
        $user = User::factory()->traveler()->create();
        $unit = Unit::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/user/favorites', [
                'favoritable_type' => 'unit',
                'favoritable_id' => $unit->id,
            ]);

        $response->assertCreated();
        $this->assertTrue($user->hasFavorited($unit));
    }

    public function test_user_can_list_favorites()
    {
        $user = User::factory()->traveler()->create();
        $resort = Resort::factory()->create();
        $user->favorite($resort);

        $response = $this->actingAs($user)
            ->getJson('/api/v1/user/favorites');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_remove_favorite()
    {
        $user = User::factory()->traveler()->create();
        $resort = Resort::factory()->create();
        $favorite = $user->favorite($resort);

        $response = $this->actingAs($user)
            ->deleteJson("/api/v1/user/favorites/{$favorite->id}");

        $response->assertOk();
        $this->assertFalse($user->hasFavorited($resort));
    }
}
