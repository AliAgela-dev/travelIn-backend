<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Update Profile Tests
    |--------------------------------------------------------------------------
    */

    public function test_user_can_update_profile()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->putJson('/api/v1/user/profile', [
            'full_name' => 'New Name',
            'email' => 'newemail@example.com',
        ]);

        if ($response->status() !== 200) {
            dump($response->json());
        }

        $response->assertOk()
            ->assertJsonPath('data.user.full_name', 'New Name')
            ->assertJsonPath('data.user.email', 'newemail@example.com');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'full_name' => 'New Name',
            'email' => 'newemail@example.com',
        ]);
    }

    public function test_user_cannot_update_email_to_existing_one()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create(['email' => 'existing@example.com']);
        $this->actingAs($user1);

        $response = $this->putJson('/api/v1/user/profile', [
            'full_name' => 'New Name',
            'email' => 'existing@example.com',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    /*
    |--------------------------------------------------------------------------
    | Change Password Tests
    |--------------------------------------------------------------------------
    */

    public function test_user_can_change_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old_password'),
        ]);
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/user/change-password', [
            'current_password' => 'old_password',
            'new_password' => 'new_password123',
            'new_password_confirmation' => 'new_password123',
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Password changed successfully.']);

        $user->refresh();
        $this->assertTrue(Hash::check('new_password123', $user->password));
    }

    public function test_user_cannot_change_password_with_wrong_current()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old_password'),
        ]);
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/user/change-password', [
            'current_password' => 'wrong_password',
            'new_password' => 'new_password123',
            'new_password_confirmation' => 'new_password123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_user_cannot_change_password_with_mismatched_confirmation()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old_password'),
        ]);
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/user/change-password', [
            'current_password' => 'old_password',
            'new_password' => 'new_password123',
            'new_password_confirmation' => 'mismatch',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['new_password']);
    }
}
