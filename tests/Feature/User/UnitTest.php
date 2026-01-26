<?php

namespace Tests\Feature\User;

use App\Enums\ResortStatus;
use App\Enums\UserType;
use App\Models\Resort;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_units_for_a_resort()
    {
        $resort = Resort::factory()->create(['status' => ResortStatus::Active]);
        Unit::factory()->count(5)->create(['resort_id' => $resort->id]);

        $response = $this->getJson("/api/v1/user/resorts/{$resort->id}/units");

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    public function test_user_can_view_single_unit()
    {
        $resort = Resort::factory()->create(['status' => ResortStatus::Active]);
        $unit = Unit::factory()->create(['resort_id' => $resort->id]);

        $response = $this->getJson("/api/v1/user/resorts/{$resort->id}/units/{$unit->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $unit->id);
    }
}
