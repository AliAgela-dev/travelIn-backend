<?php

namespace Tests\Feature\ResortOwner;

use App\Enums\UserType;
use App\Models\Resort;
use App\Models\Unit;
use App\Models\UnitAvailability;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_list_blocked_dates()
    {
        $owner = User::factory()->resortOwner()->create();
        $resort = Resort::factory()->create(['owner_id' => $owner->id]);
        $unit = Unit::factory()->create(['resort_id' => $resort->id]);
        UnitAvailability::factory()->count(2)->create(['unit_id' => $unit->id]);

        $response = $this->actingAs($owner)
            ->getJson("/api/v1/resort-owner/units/{$unit->id}/availability");

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_owner_can_block_dates()
    {
        $owner = User::factory()->resortOwner()->create();
        $resort = Resort::factory()->create(['owner_id' => $owner->id]);
        $unit = Unit::factory()->create(['resort_id' => $resort->id]);

        $payload = [
            'start_date' => now()->addDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(7)->format('Y-m-d'),
            'reason' => 'Maintenance',
        ];

        $response = $this->actingAs($owner)
            ->postJson("/api/v1/resort-owner/units/{$unit->id}/availability", $payload);

        $response->assertCreated()
            ->assertJsonFragment(['reason' => 'Maintenance']);

        $this->assertDatabaseHas('unit_availability', ['unit_id' => $unit->id, 'reason' => 'Maintenance']);
    }

    public function test_owner_can_unblock_dates()
    {
        $owner = User::factory()->resortOwner()->create();
        $resort = Resort::factory()->create(['owner_id' => $owner->id]);
        $unit = Unit::factory()->create(['resort_id' => $resort->id]);
        $availability = UnitAvailability::factory()->create(['unit_id' => $unit->id]);

        $response = $this->actingAs($owner)
            ->deleteJson("/api/v1/resort-owner/units/{$unit->id}/availability/{$availability->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('unit_availability', ['id' => $availability->id]);
    }
}
