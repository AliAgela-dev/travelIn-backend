<?php

namespace Tests\Feature\ResortOwner;

use App\Models\Area;
use App\Models\City;
use App\Enums\GeneralStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AreaTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_list_active_areas()
    {
        $city = City::factory()->create(['status' => GeneralStatus::Active]);
        $activeArea = Area::factory()->create(['city_id' => $city->id, 'status' => GeneralStatus::Active]);
        $inactiveArea = Area::factory()->create(['city_id' => $city->id, 'status' => GeneralStatus::Inactive]);

        $response = $this->getJson('/api/v1/resort-owner/areas');

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
            ->assertJsonPath('data.0.id', $activeArea->id)
            ->assertJsonMissing(['id' => $inactiveArea->id]);
    }

    public function test_public_can_view_active_area()
    {
        $city = City::factory()->create(['status' => GeneralStatus::Active]);
        $area = Area::factory()->create(['city_id' => $city->id, 'status' => GeneralStatus::Active]);

        $response = $this->getJson("/api/v1/resort-owner/areas/{$area->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $area->id,
                    'ar_name' => $area->ar_name,
                ]
            ]);
    }

    public function test_public_cannot_view_inactive_area()
    {
        $city = City::factory()->create(['status' => GeneralStatus::Active]);
        $area = Area::factory()->create(['city_id' => $city->id, 'status' => GeneralStatus::Inactive]);

        $this->getJson("/api/v1/resort-owner/areas/{$area->id}")
            ->assertStatus(404);
    }
}
