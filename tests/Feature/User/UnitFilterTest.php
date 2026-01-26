<?php

namespace Tests\Feature\User;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Resort;
use App\Models\Unit;
use App\Models\UnitAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_filter_units_by_capacity()
    {
        $resort = Resort::factory()->create();
        Unit::factory()->create(['resort_id' => $resort->id, 'capacity' => 2]);
        Unit::factory()->create(['resort_id' => $resort->id, 'capacity' => 6]);

        $response = $this->getJson("/api/v1/user/resorts/{$resort->id}/units?filter[guests]=4");

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_filter_units_by_availability()
    {
        $resort = Resort::factory()->create();
        $availableUnit = Unit::factory()->create(['resort_id' => $resort->id]);
        $blockedUnit = Unit::factory()->create(['resort_id' => $resort->id]);

        // Block one unit
        UnitAvailability::factory()->create([
            'unit_id' => $blockedUnit->id,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(10),
        ]);

        $response = $this->getJson("/api/v1/user/resorts/{$resort->id}/units?check_in=" . now()->addDays(6)->format('Y-m-d') . "&check_out=" . now()->addDays(8)->format('Y-m-d'));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $availableUnit->id]);
    }

    public function test_user_can_filter_units_excluding_booked_dates()
    {
        $resort = Resort::factory()->create();
        $availableUnit = Unit::factory()->create(['resort_id' => $resort->id]);
        $bookedUnit = Unit::factory()->create(['resort_id' => $resort->id]);

        // Book one unit
        Booking::factory()->create([
            'unit_id' => $bookedUnit->id,
            'check_in' => now()->addDays(5),
            'check_out' => now()->addDays(10),
            'status' => BookingStatus::Confirmed,
        ]);

        $response = $this->getJson("/api/v1/user/resorts/{$resort->id}/units?check_in=" . now()->addDays(6)->format('Y-m-d') . "&check_out=" . now()->addDays(8)->format('Y-m-d'));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $availableUnit->id]);
    }
}
