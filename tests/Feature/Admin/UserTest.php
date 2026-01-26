<?php

namespace Tests\Feature\Admin;

use App\Enums\GeneralStatus;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $resortOwner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['type' => UserType::Admin]);
        sleep(1);
        $this->user = User::factory()->create(['type' => UserType::User, 'full_name' => 'Alice Traveler']);
        sleep(1);
        $this->resortOwner = User::factory()->create(['type' => UserType::ResortOwner, 'full_name' => 'Bob Owner']);
    }

    public function test_admin_can_list_users()
    {
        Sanctum::actingAs($this->admin);

        $this->getJson('/api/v1/admin/users')
            ->assertOk()
            ->assertJsonStructure(['success', 'message', 'data', 'links', 'meta'])
            ->assertJsonPath('data.0.full_name', 'Bob Owner') // Sort order changed, Bob is last created so first
            // Actually defaultSort is -created_at
            // Bob created last -> first. Alice created 2nd -> 2nd. Admin -> ?
            // Let's use assertJsonFragment but careful with data wrapper
            // If successCollection wraps data, 'data' => [data=>[...]]
            // wait, successCollection does: 'data' => $collection->getData(true) => ['data'=>[...]]
            // AND merges success/message.
            // So: { success: true, message: null, data: [...], links: {...}, meta: {...} }
            // Wait, array_merge([success..], $data) where $data is ['data'=>.., 'links'=>..]
            // Result: { success:true, message:null, data:[...], links:..., meta:... }
            // So 'data' key contains the items.
            ->assertJsonFragment(['full_name' => 'Alice Traveler']);
    }

    public function test_admin_can_filter_users_by_type()
    {
        Sanctum::actingAs($this->admin);

        $this->getJson('/api/v1/admin/users?filter[type]=resort_owner')
            ->assertOk()
            ->assertJsonFragment(['full_name' => 'Bob Owner'])
            ->assertJsonMissing(['full_name' => 'Alice Traveler']);
    }

    public function test_admin_can_search_users()
    {
        Sanctum::actingAs($this->admin);

        $this->getJson('/api/v1/admin/users?filter[search]=Alice')
            ->assertOk()
            ->assertJsonFragment(['full_name' => 'Alice Traveler'])
            ->assertJsonMissing(['full_name' => 'Bob Owner']);
    }

    public function test_admin_can_update_user_status()
    {
        Sanctum::actingAs($this->admin);

        $this->putJson("/api/v1/admin/users/{$this->user->id}", ['status' => UserStatus::Banned->value])
            ->assertOk()
            ->assertJsonPath('data.status', UserStatus::Banned->value);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'status' => UserStatus::Banned->value,
        ]);
    }

    public function test_admin_can_view_user_details()
    {
        Sanctum::actingAs($this->admin);

        $this->getJson("/api/v1/admin/users/{$this->user->id}")
            ->assertOk()
            ->assertJsonPath('data.full_name', 'Alice Traveler');
    }

    public function test_non_admin_cannot_manage_users()
    {
        Sanctum::actingAs($this->user);

        $this->getJson('/api/v1/admin/users')->assertForbidden();
        $this->putJson("/api/v1/admin/users/{$this->resortOwner->id}", ['status' => UserStatus::Banned->value])->assertForbidden();
    }
}
