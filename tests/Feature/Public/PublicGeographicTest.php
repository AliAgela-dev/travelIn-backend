<?php

namespace Tests\Feature\Public;

use App\Enums\GeneralStatus;
use App\Models\Area;
use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicGeographicTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_active_cities()
    {
        City::create(['ar_name' => 'Active', 'en_name' => 'Active', 'status' => GeneralStatus::Active]);
        City::create(['ar_name' => 'Inactive', 'en_name' => 'Inactive', 'status' => GeneralStatus::Inactive]);

        $response = $this->getJson('/api/v1/user/cities');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['en_name' => 'Active'])
            ->assertJsonMissing(['en_name' => 'Inactive']);
    }

    public function test_user_can_list_active_areas()
    {
        $city = City::create(['ar_name' => 'Tripoli', 'en_name' => 'Tripoli', 'status' => GeneralStatus::Active]);
        Area::create(['city_id' => $city->id, 'ar_name' => 'Active', 'en_name' => 'Active', 'status' => GeneralStatus::Active]);
        Area::create(['city_id' => $city->id, 'ar_name' => 'Inactive', 'en_name' => 'Inactive', 'status' => GeneralStatus::Inactive]);

        $response = $this->getJson('/api/v1/user/areas');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['en_name' => 'Active'])
            ->assertJsonMissing(['en_name' => 'Inactive']);
    }

    public function test_resort_owner_can_list_active_cities_and_areas()
    {
        $city = City::create(['ar_name' => 'Active', 'en_name' => 'Active', 'status' => GeneralStatus::Active]);
        Area::create(['city_id' => $city->id, 'ar_name' => 'Active', 'en_name' => 'Active', 'status' => GeneralStatus::Active]);

        $this->getJson('/api/v1/resort-owner/cities')
            ->assertOk()
            ->assertJsonFragment(['en_name' => 'Active']);

        $this->getJson('/api/v1/resort-owner/areas')
            ->assertOk()
            ->assertJsonFragment(['en_name' => 'Active']);
    }

    public function test_public_user_cannot_view_inactive_city()
    {
        $city = City::create(['ar_name' => 'Inactive', 'en_name' => 'Inactive', 'status' => GeneralStatus::Inactive]);

        $this->getJson("/api/v1/user/cities/{$city->id}")->assertNotFound();
    }
}
