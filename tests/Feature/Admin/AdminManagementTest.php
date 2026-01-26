<?php

namespace Tests\Feature\Admin;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $admin;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create(['type' => UserType::SuperAdmin]);
        $this->admin = User::factory()->create(['type' => UserType::Admin]);
        $this->regularUser = User::factory()->create(['type' => UserType::User]);
    }

    public function test_super_admin_can_list_admins()
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson('/api/v1/admin/admins');

        $response->assertOk()
            ->assertJsonStructure(['success', 'message', 'data', 'links', 'meta'])
            ->assertJsonFragment(['id' => $this->admin->id]);
    }

    public function test_super_admin_can_create_admin()
    {
        $payload = [
            'full_name' => 'New Admin',
            'phone_number' => '999999999',
            'password' => 'password123',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->postJson('/api/v1/admin/admins', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.full_name', 'New Admin')
            ->assertJsonPath('data.type', 'admin');

        $this->assertDatabaseHas('users', ['phone_number' => '999999999', 'type' => 'admin']);
    }

    public function test_super_admin_can_update_admin()
    {
        $payload = ['full_name' => 'Updated Admin'];

        $response = $this->actingAs($this->superAdmin)
            ->putJson("/api/v1/admin/admins/{$this->admin->id}", $payload);

        $response->assertOk()
            ->assertJsonPath('data.full_name', 'Updated Admin');

        $this->assertDatabaseHas('users', ['id' => $this->admin->id, 'full_name' => 'Updated Admin']);
    }

    public function test_super_admin_can_delete_admin()
    {
        $response = $this->actingAs($this->superAdmin)
            ->deleteJson("/api/v1/admin/admins/{$this->admin->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $this->admin->id]);
    }

    public function test_regular_admin_cannot_manage_admins()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/admins');

        $response->assertForbidden();

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/admins', []);

        $response->assertForbidden();
    }

    public function test_regular_user_cannot_manage_admins()
    {
        $response = $this->actingAs($this->regularUser)
            ->getJson('/api/v1/admin/admins');

        // Middleware might return 401 or 403 depending on order, but role middleware usually 403.
        // Or if auth:sanctum fails role check
        $response->assertForbidden();
    }
}
