<?php

namespace Tests\Feature\Admin;

use App\Enums\ResortStatus;
use App\Enums\UserType;
use App\Models\Area;
use App\Models\City;
use App\Models\Resort;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_dashboard_stats()
    {
        $admin = User::factory()->create(['type' => UserType::Admin]);

        // Seed Users
        User::factory()->count(10)->create(['type' => UserType::User]); // Travelers
        User::factory()->count(5)->create(['type' => UserType::ResortOwner]); // Owners
        // +1 admin created above

        // Seed Geographic Data
        $cities = City::factory()->count(3)->create();
        Area::factory()->count(10)->create(['city_id' => $cities->first()->id]);

        // Seed Resorts (Reuse owner and geography)
        $owner = User::where('type', UserType::ResortOwner)->first();
        $city = $cities->first();
        $area = Area::where('city_id', $city->id)->first();

        $resortOverrides = [
            'owner_id' => $owner->id,
            'city_id' => $city->id,
            'area_id' => $area->id,
        ];

        Resort::factory()->count(2)->create(array_merge($resortOverrides, ['status' => ResortStatus::Active]));
        Resort::factory()->count(3)->create(array_merge($resortOverrides, ['status' => ResortStatus::Pending]));
        Resort::factory()->count(1)->create(array_merge($resortOverrides, ['status' => ResortStatus::Rejected]));

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/dashboard/stats');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'users' => [
                        'total' => 1 + 10 + 5, // 16
                        'admins' => 1,
                        'resort_owners' => 5,
                        'travelers' => 10,
                    ],
                    'resorts' => [
                        'total' => 2 + 3 + 1, // 6
                        'active' => 2,
                        'pending' => 3,
                        'rejected' => 1,
                        'inactive' => 0,
                    ],
                    'geography' => [
                        'cities' => 3,
                        'areas' => 10,
                    ],
                ]
            ]);
    }

    public function test_non_admin_cannot_view_dashboard_stats()
    {
        $user = User::factory()->create(['type' => UserType::User]);

        // Assuming middleware or gate checks admin logic.
        // If route is just auth:sanctum, technically they can access.
        // BUT, usually we should protect it.
        // Since I haven't added specific middleware, this test EXPECTS failure (403 or 401) IF protected.
        // However, looking at routes:
        // Route::middleware('auth:sanctum')->prefix('v1/admin')...
        // It does NOT have 'check_admin' middleware yet.
        // So this test MIGHT FAIL (getting 200) if I don't add protection.
        
        // I will add a check in the controller just to be safe, OR I accept it's "Admin API" by convention.
        // Best practice: Check in controller or middleware.
        
        // Let's rely on standard Auth first. 
        // If I want to enforce strict Admin access, I should check it.
        
        $this->actingAs($user)
            ->getJson('/api/v1/admin/dashboard/stats')
             // If I don't protect it, this will be 200.
             // I'll update the controller to check permissions!
            ->assertStatus(403);
    }
}
