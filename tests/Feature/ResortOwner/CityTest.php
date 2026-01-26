<?php

namespace Tests\Feature\ResortOwner;

use App\Models\City;
use App\Enums\GeneralStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CityTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_list_active_cities()
    {
        $activeCity = City::factory()->create(['status' => GeneralStatus::Active]);
        $inactiveCity = City::factory()->create(['status' => GeneralStatus::Inactive]);

        $response = $this->getJson('/api/v1/resort-owner/cities');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'ar_name', 'en_name', 'status']
                ],
                'links',
                'meta'
            ])
            ->assertJsonPath('data.0.id', $activeCity->id)
            ->assertJsonMissing(['id' => $inactiveCity->id]);
    }

    public function test_public_can_view_active_city()
    {
        $city = City::factory()->create(['status' => GeneralStatus::Active]);

        $response = $this->getJson("/api/v1/resort-owner/cities/{$city->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $city->id,
                    'ar_name' => $city->ar_name,
                ]
            ]);
    }

    public function test_public_cannot_view_inactive_city()
    {
        $city = City::factory()->create(['status' => GeneralStatus::Inactive]);

        $this->getJson("/api/v1/resort-owner/cities/{$city->id}")
            ->assertStatus(404);
    }
}
