<?php

namespace Tests\Feature\User;

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

    public function test_user_can_create_booking()
    {
        $user = User::factory()->traveler()->create();
        $resort = Resort::factory()->create();
        $unit = Unit::factory()->create(['resort_id' => $resort->id, 'price_per_night' => 100, 'capacity' => 4]);

        $checkIn = now()->addDays(10)->format('Y-m-d');
        $checkOut = now()->addDays(13)->format('Y-m-d'); // 3 nights

        $response = $this->actingAs($user)
            ->postJson('/api/v1/user/bookings', [
                'unit_id' => $unit->id,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'guests' => 2,
                'children' => 1,
            ]);

        $response->assertCreated()
            ->assertJsonFragment(['status' => 'pending']);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'unit_id' => $unit->id,
        ]);
    }

    public function test_user_can_list_own_bookings()
    {
        $user = User::factory()->traveler()->create();
        Booking::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->getJson('/api/v1/user/bookings');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_cancel_pending_booking()
    {
        $user = User::factory()->traveler()->create();
        $booking = Booking::factory()->create([
            'user_id' => $user->id,
            'status' => BookingStatus::Pending,
        ]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/v1/user/bookings/{$booking->id}");

        $response->assertOk();
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => BookingStatus::Cancelled->value,
        ]);
    }

    public function test_booking_blocked_for_unavailable_dates()
    {
        $user = User::factory()->traveler()->create();
        $unit = Unit::factory()->create(['capacity' => 4]);

        // Create blocking period
        $unit->blockedDates()->create([
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(15),
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/user/bookings', [
                'unit_id' => $unit->id,
                'check_in' => now()->addDays(12)->format('Y-m-d'),
                'check_out' => now()->addDays(14)->format('Y-m-d'),
                'guests' => 2,
            ]);

        $response->assertStatus(422);
    }
}
