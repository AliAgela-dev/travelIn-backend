<?php

namespace Tests\Feature\ResortOwner;

use App\Enums\UserType;
use App\Models\Resort;
use App\Models\Unit;
use App\Models\UnitPricing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_list_unit_pricing_rules()
    {
        $owner = User::factory()->resortOwner()->create();
        $resort = Resort::factory()->create(['owner_id' => $owner->id]);
        $unit = Unit::factory()->create(['resort_id' => $resort->id]);
        UnitPricing::factory()->count(3)->create(['unit_id' => $unit->id]);

        $response = $this->actingAs($owner)
            ->getJson("/api/v1/resort-owner/units/{$unit->id}/pricing");

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_owner_can_create_pricing_rule()
    {
        $owner = User::factory()->resortOwner()->create();
        $resort = Resort::factory()->create(['owner_id' => $owner->id]);
        $unit = Unit::factory()->create(['resort_id' => $resort->id]);

        $payload = [
            'start_date' => now()->addDays(10)->format('Y-m-d'),
            'end_date' => now()->addDays(20)->format('Y-m-d'),
            'price_per_night' => 200.00,
            'label' => 'Holiday Rate',
        ];

        $response = $this->actingAs($owner)
            ->postJson("/api/v1/resort-owner/units/{$unit->id}/pricing", $payload);

        $response->assertCreated()
            ->assertJsonFragment(['label' => 'Holiday Rate']);

        $this->assertDatabaseHas('unit_pricing', ['unit_id' => $unit->id, 'label' => 'Holiday Rate']);
    }

    public function test_owner_can_delete_pricing_rule()
    {
        $owner = User::factory()->resortOwner()->create();
        $resort = Resort::factory()->create(['owner_id' => $owner->id]);
        $unit = Unit::factory()->create(['resort_id' => $resort->id]);
        $pricing = UnitPricing::factory()->create(['unit_id' => $unit->id]);

        $response = $this->actingAs($owner)
            ->deleteJson("/api/v1/resort-owner/units/{$unit->id}/pricing/{$pricing->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('unit_pricing', ['id' => $pricing->id]);
    }

    public function test_non_owner_cannot_manage_pricing()
    {
        $owner = User::factory()->resortOwner()->create();
        $otherOwner = User::factory()->resortOwner()->create();
        $resort = Resort::factory()->create(['owner_id' => $owner->id]);
        $unit = Unit::factory()->create(['resort_id' => $resort->id]);

        $response = $this->actingAs($otherOwner)
            ->postJson("/api/v1/resort-owner/units/{$unit->id}/pricing", [
                'start_date' => now()->addDays(5)->format('Y-m-d'),
                'end_date' => now()->addDays(15)->format('Y-m-d'),
                'price_per_night' => 150.00,
            ]);

        $response->assertForbidden();
    }
}
