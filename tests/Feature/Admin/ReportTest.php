<?php

namespace Tests\Feature\Admin;

use App\Enums\BookingStatus;
use App\Enums\UserType;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_revenue_report()
    {
        $admin = User::factory()->admin()->create();
        Booking::factory()->count(5)->create([
            'status' => BookingStatus::Confirmed,
            'total_price' => 1000,
        ]);

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/reports/revenue');

        $response->assertOk()
            ->assertJsonStructure(['success', 'data' => ['total_revenue', 'period_data']]);

        $this->assertEquals(5000, $response->json('data.total_revenue'));
    }

    public function test_admin_can_view_bookings_report()
    {
        $admin = User::factory()->admin()->create();
        Booking::factory()->count(3)->create(['status' => BookingStatus::Confirmed]);
        Booking::factory()->count(2)->create(['status' => BookingStatus::Pending]);

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/reports/bookings');

        $response->assertOk()
            ->assertJsonStructure(['success', 'data' => ['total_bookings', 'by_status']]);

        $this->assertEquals(5, $response->json('data.total_bookings'));
    }

    public function test_admin_can_view_users_report()
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(5)->traveler()->create();
        User::factory()->count(2)->resortOwner()->create();

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/reports/users');

        $response->assertOk()
            ->assertJsonStructure(['success', 'data' => ['total_users', 'by_type']]);
    }

    public function test_admin_can_export_report()
    {
        $admin = User::factory()->admin()->create();
        Booking::factory()->count(3)->create(['status' => BookingStatus::Confirmed]);

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/reports/export?type=revenue');

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=utf-8');
    }
}
