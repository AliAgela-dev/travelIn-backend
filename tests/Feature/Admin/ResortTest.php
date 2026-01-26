<?php

namespace Tests\Feature\Admin;

use App\Enums\ResortStatus;
use App\Models\Resort;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResortTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_resorts_with_filters()
    {
        $admin = User::factory()->admin()->create();
        Resort::factory()->create(['status' => ResortStatus::Pending]);
        Resort::factory()->create(['status' => ResortStatus::Active]);

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/admin/resorts?filter[status]=pending');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', ResortStatus::Pending->value);
    }

    public function test_admin_can_approve_resort()
    {
        $admin = User::factory()->admin()->create();
        $resort = Resort::factory()->create(['status' => ResortStatus::Pending]);

        $response = $this->actingAs($admin)
            ->putJson("/api/v1/admin/resorts/{$resort->id}", [
                'status' => ResortStatus::Active->value,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', ResortStatus::Active->value);

        $this->assertDatabaseHas('resorts', [
            'id' => $resort->id,
            'status' => ResortStatus::Active,
        ]);
    }

    public function test_non_admin_cannot_access_admin_routes()
    {
        $user = User::factory()->create();
        
        $this->actingAs($user)
            ->getJson('/api/v1/admin/resorts')
            ->assertStatus(403);
    }
}
