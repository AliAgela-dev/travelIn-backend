<?php

namespace Tests\Feature\User;

use App\Enums\ResortStatus;
use App\Models\Resort;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResortSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_active_resorts()
    {
        Resort::factory()->create(['status' => ResortStatus::Active]);
        Resort::factory()->create(['status' => ResortStatus::Pending]);

        $response = $this->getJson('/api/v1/user/resorts');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_filter_resorts_by_city()
    {
        $resort1 = Resort::factory()->create(['status' => ResortStatus::Active]);
        $resort2 = Resort::factory()->create(['status' => ResortStatus::Active]);

        $response = $this->getJson("/api/v1/user/resorts?filter[city_id]={$resort1->city_id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $resort1->id]);
    }

    public function test_user_can_search_resorts_by_name()
    {
        Resort::factory()->create(['status' => ResortStatus::Active, 'en_name' => 'Beach Paradise']);
        Resort::factory()->create(['status' => ResortStatus::Active, 'en_name' => 'Mountain View']);

        $response = $this->getJson('/api/v1/user/resorts?filter[search]=Beach');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
