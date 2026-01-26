<?php

namespace Tests\Feature\User;

use App\Enums\GeneralStatus;
use App\Enums\ResortStatus;
use App\Models\Area;
use App\Models\City;
use App\Models\Resort;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Global Units Search Tests
    |--------------------------------------------------------------------------
    */

    public function test_can_search_units_globally(): void
    {
        $resort = Resort::factory()->create(['status' => ResortStatus::Active]);
        Unit::factory()->create([
            'resort_id' => $resort->id,
            'status' => GeneralStatus::Active,
            'en_name' => 'Deluxe Suite',
        ]);
        Unit::factory()->create([
            'resort_id' => $resort->id,
            'status' => GeneralStatus::Active,
            'en_name' => 'Standard Room',
        ]);

        $response = $this->getJson('/api/v1/user/search/units');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'en_name', 'price_per_night'],
                ],
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_units_by_search_term(): void
    {
        $resort = Resort::factory()->create(['status' => ResortStatus::Active]);
        Unit::factory()->create([
            'resort_id' => $resort->id,
            'status' => GeneralStatus::Active,
            'en_name' => 'Deluxe Suite',
        ]);
        Unit::factory()->create([
            'resort_id' => $resort->id,
            'status' => GeneralStatus::Active,
            'en_name' => 'Standard Room',
        ]);

        $response = $this->getJson('/api/v1/user/search/units?filter[search]=Deluxe');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['en_name' => 'Deluxe Suite']);
    }

    public function test_can_filter_units_by_city(): void
    {
        $city1 = City::factory()->create();
        $city2 = City::factory()->create();
        $resort1 = Resort::factory()->create(['status' => ResortStatus::Active, 'city_id' => $city1->id]);
        $resort2 = Resort::factory()->create(['status' => ResortStatus::Active, 'city_id' => $city2->id]);
        Unit::factory()->create(['resort_id' => $resort1->id, 'status' => GeneralStatus::Active]);
        Unit::factory()->create(['resort_id' => $resort2->id, 'status' => GeneralStatus::Active]);

        $response = $this->getJson("/api/v1/user/search/units?filter[city_id]={$city1->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_units_by_price_range(): void
    {
        $resort = Resort::factory()->create(['status' => ResortStatus::Active]);
        Unit::factory()->create([
            'resort_id' => $resort->id,
            'status' => GeneralStatus::Active,
            'price_per_night' => 50,
        ]);
        Unit::factory()->create([
            'resort_id' => $resort->id,
            'status' => GeneralStatus::Active,
            'price_per_night' => 200,
        ]);

        $response = $this->getJson('/api/v1/user/search/units?filter[min_price]=100');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_units_by_capacity(): void
    {
        $resort = Resort::factory()->create(['status' => ResortStatus::Active]);
        Unit::factory()->create([
            'resort_id' => $resort->id,
            'status' => GeneralStatus::Active,
            'capacity' => 2,
        ]);
        Unit::factory()->create([
            'resort_id' => $resort->id,
            'status' => GeneralStatus::Active,
            'capacity' => 6,
        ]);

        $response = $this->getJson('/api/v1/user/search/units?filter[guests]=4');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_excludes_units_from_inactive_resorts(): void
    {
        $activeResort = Resort::factory()->create(['status' => ResortStatus::Active]);
        $pendingResort = Resort::factory()->create(['status' => ResortStatus::Pending]);
        Unit::factory()->create(['resort_id' => $activeResort->id, 'status' => GeneralStatus::Active]);
        Unit::factory()->create(['resort_id' => $pendingResort->id, 'status' => GeneralStatus::Active]);

        $response = $this->getJson('/api/v1/user/search/units');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    /*
    |--------------------------------------------------------------------------
    | Metadata Endpoint Tests
    |--------------------------------------------------------------------------
    */

    public function test_metadata_returns_price_range(): void
    {
        $resort = Resort::factory()->create(['status' => ResortStatus::Active]);
        Unit::factory()->create([
            'resort_id' => $resort->id,
            'status' => GeneralStatus::Active,
            'price_per_night' => 50,
        ]);
        Unit::factory()->create([
            'resort_id' => $resort->id,
            'status' => GeneralStatus::Active,
            'price_per_night' => 300,
        ]);

        $response = $this->getJson('/api/v1/user/search/metadata');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'price_range' => ['min', 'max'],
                ],
            ]);

        $data = $response->json('data.price_range');
        $this->assertEquals(50, $data['min']);
        $this->assertEquals(300, $data['max']);
    }

    public function test_metadata_returns_defaults_when_no_units(): void
    {
        $response = $this->getJson('/api/v1/user/search/metadata');

        $response->assertOk();

        $data = $response->json('data.price_range');
        $this->assertEquals(0, $data['min']);
        $this->assertEquals(1000, $data['max']);
    }

    /*
    |--------------------------------------------------------------------------
    | Suggestions Endpoint Tests
    |--------------------------------------------------------------------------
    */

    public function test_suggestions_returns_empty_for_short_query(): void
    {
        $response = $this->getJson('/api/v1/user/search/suggestions?q=a');

        $response->assertOk()
            ->assertJsonPath('data.suggestions', []);
    }

    public function test_suggestions_returns_matching_cities(): void
    {
        City::factory()->create(['en_name' => 'Tripoli']);
        City::factory()->create(['en_name' => 'Benghazi']);

        $response = $this->getJson('/api/v1/user/search/suggestions?q=Trip');

        $response->assertOk()
            ->assertJsonCount(1, 'data.suggestions')
            ->assertJsonFragment([
                'type' => 'city',
                'en_name' => 'Tripoli',
            ]);
    }

    public function test_suggestions_returns_matching_areas(): void
    {
        $city = City::factory()->create();
        Area::factory()->create(['en_name' => 'Downtown', 'city_id' => $city->id]);

        $response = $this->getJson('/api/v1/user/search/suggestions?q=Down');

        $response->assertOk()
            ->assertJsonFragment([
                'type' => 'area',
                'en_name' => 'Downtown',
            ]);
    }

    public function test_suggestions_returns_matching_resorts(): void
    {
        Resort::factory()->create(['status' => ResortStatus::Active, 'en_name' => 'Beach Paradise']);
        Resort::factory()->create(['status' => ResortStatus::Pending, 'en_name' => 'Beach View']); // Should not appear

        $response = $this->getJson('/api/v1/user/search/suggestions?q=Beach');

        $response->assertOk()
            ->assertJsonCount(1, 'data.suggestions')
            ->assertJsonFragment([
                'type' => 'resort',
                'en_name' => 'Beach Paradise',
            ]);
    }

    public function test_suggestions_returns_combined_results(): void
    {
        City::factory()->create(['en_name' => 'Coastal City']);
        $city = City::factory()->create();
        Area::factory()->create(['en_name' => 'Coastal Area', 'city_id' => $city->id]);
        Resort::factory()->create(['status' => ResortStatus::Active, 'en_name' => 'Coastal Resort']);

        $response = $this->getJson('/api/v1/user/search/suggestions?q=Coastal');

        $response->assertOk()
            ->assertJsonCount(3, 'data.suggestions');
    }
}
