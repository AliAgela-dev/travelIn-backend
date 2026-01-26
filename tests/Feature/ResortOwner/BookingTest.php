<?php

namespace Tests\Feature\ResortOwner;

use App\Enums\BookingStatus;
use App\Enums\UserType;
use App\Models\Booking;
use App\Models\Resort;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_list_bookings_for_their_resorts()
    {
        $owner = User::factory()->resortOwner()->create();
        $resort = Resort::factory()->create(['owner_id' => $owner->id]);
        $unit = Unit::factory()->create(['resort_id' => $resort->id]);
        Booking::factory()->count(3)->create(['unit_id' => $unit->id]);

        $response = $this->actingAs($owner)
            ->getJson('/api/v1/resort-owner/bookings');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_owner_can_confirm_booking()
    {
        $owner = User::factory()->resortOwner()->create();
        $resort = Resort::factory()->create(['owner_id' => $owner->id]);
        $unit = Unit::factory()->create(['resort_id' => $resort->id]);
        $booking = Booking::factory()->create([
            'unit_id' => $unit->id,
            'status' => BookingStatus::Pending,
        ]);

        $response = $this->actingAs($owner)
            ->putJson("/api/v1/resort-owner/bookings/{$booking->id}", [
                'status' => 'confirmed',
                'owner_notes' => 'Welcome!',
            ]);

        $response->assertOk()
            ->assertJsonFragment(['status' => 'confirmed']);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => BookingStatus::Confirmed->value,
        ]);
    }

    public function test_owner_can_reject_booking()
    {
        $owner = User::factory()->resortOwner()->create();
        $resort = Resort::factory()->create(['owner_id' => $owner->id]);
        $unit = Unit::factory()->create(['resort_id' => $resort->id]);
        $booking = Booking::factory()->create([
            'unit_id' => $unit->id,
            'status' => BookingStatus::Pending,
        ]);

        $response = $this->actingAs($owner)
            ->putJson("/api/v1/resort-owner/bookings/{$booking->id}", [
                'status' => 'rejected',
            ]);

        $response->assertOk()
            ->assertJsonFragment(['status' => 'rejected']);
    }

    public function test_non_owner_cannot_manage_booking()
    {
        $owner = User::factory()->resortOwner()->create();
        $otherOwner = User::factory()->resortOwner()->create();
        $resort = Resort::factory()->create(['owner_id' => $owner->id]);
        $unit = Unit::factory()->create(['resort_id' => $resort->id]);
        $booking = Booking::factory()->create(['unit_id' => $unit->id]);

        $response = $this->actingAs($otherOwner)
            ->putJson("/api/v1/resort-owner/bookings/{$booking->id}", [
                'status' => 'confirmed',
            ]);

        $response->assertForbidden();
    }
}
