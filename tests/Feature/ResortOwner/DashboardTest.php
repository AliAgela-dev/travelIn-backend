<?php

namespace Tests\Feature\ResortOwner;

use App\Enums\BookingStatus;
use App\Enums\ResortStatus;
use App\Enums\UserType;
use App\Models\Booking;
use App\Models\Resort;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_dashboard_stats()
    {
        $owner = User::factory()->resortOwner()->create();
        $resort = Resort::factory()->create(['owner_id' => $owner->id, 'status' => ResortStatus::Active]);
        $unit = Unit::factory()->create(['resort_id' => $resort->id]);
        
        // Create bookings with different statuses
        Booking::factory()->count(2)->create(['unit_id' => $unit->id, 'status' => BookingStatus::Pending]);
        Booking::factory()->count(3)->create(['unit_id' => $unit->id, 'status' => BookingStatus::Confirmed, 'total_price' => 100]);
        Booking::factory()->create(['unit_id' => $unit->id, 'status' => BookingStatus::Completed, 'total_price' => 150]);

        $response = $this->actingAs($owner)
            ->getJson('/api/v1/resort-owner/dashboard/stats');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'resorts' => ['total', 'active', 'pending'],
                    'units' => ['total'],
                    'bookings' => ['total', 'pending', 'confirmed', 'completed', 'cancelled'],
                    'revenue' => ['total', 'this_month'],
                ]
            ])
            ->assertJsonPath('data.resorts.total', 1)
            ->assertJsonPath('data.units.total', 1)
            ->assertJsonPath('data.bookings.total', 6)
            ->assertJsonPath('data.bookings.pending', 2)
            ->assertJsonPath('data.bookings.confirmed', 3);
    }

    public function test_stats_only_include_owners_data()
    {
        $owner = User::factory()->resortOwner()->create();
        $otherOwner = User::factory()->resortOwner()->create();
        
        // Owner's resort and bookings
        $resort = Resort::factory()->create(['owner_id' => $owner->id]);
        $unit = Unit::factory()->create(['resort_id' => $resort->id]);
        Booking::factory()->count(2)->create(['unit_id' => $unit->id]);
        
        // Other owner's data (should not be included)
        $otherResort = Resort::factory()->create(['owner_id' => $otherOwner->id]);
        $otherUnit = Unit::factory()->create(['resort_id' => $otherResort->id]);
        Booking::factory()->count(5)->create(['unit_id' => $otherUnit->id]);

        $response = $this->actingAs($owner)
            ->getJson('/api/v1/resort-owner/dashboard/stats');

        $response->assertOk()
            ->assertJsonPath('data.resorts.total', 1)
            ->assertJsonPath('data.bookings.total', 2);
    }
}
