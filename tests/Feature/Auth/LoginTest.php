<?php

use App\Models\User;
use App\Enums\UserType;
use App\Enums\UserStatus;

describe('User Login', function () {
    it('can login with correct credentials', function () {
        User::factory()->create([
            'phone_number' => '218912345678',
            'password' => bcrypt('password123'),
            'type' => UserType::User,
            'status' => UserStatus::Active,
        ]);

        $response = $this->postJson('/api/v1/user/login', [
            'phone_number' => '218912345678',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'full_name', 'phone_number', 'type', 'status'],
                    'token',
                ],
            ]);
    });

    it('cannot login with wrong password', function () {
        User::factory()->create([
            'phone_number' => '218912345678',
            'password' => bcrypt('password123'),
            'type' => UserType::User,
        ]);

        $response = $this->postJson('/api/v1/user/login', [
            'phone_number' => '218912345678',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid credentials.']);
    });

    it('cannot login with non-existent phone', function () {
        $response = $this->postJson('/api/v1/user/login', [
            'phone_number' => '218912345678',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid credentials.']);
    });

    it('cannot login if user is banned', function () {
        User::factory()->create([
            'phone_number' => '218912345678',
            'password' => bcrypt('password123'),
            'type' => UserType::User,
            'status' => UserStatus::Banned,
        ]);

        $response = $this->postJson('/api/v1/user/login', [
            'phone_number' => '218912345678',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Your account has been banned.']);
    });

    it('cannot login if user is inactive', function () {
        User::factory()->create([
            'phone_number' => '218912345678',
            'password' => bcrypt('password123'),
            'type' => UserType::User,
            'status' => UserStatus::Inactive,
        ]);

        $response = $this->postJson('/api/v1/user/login', [
            'phone_number' => '218912345678',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Your account is inactive.']);
    });

    it('cannot login user endpoint with resort_owner type', function () {
        User::factory()->create([
            'phone_number' => '218912345678',
            'password' => bcrypt('password123'),
            'type' => UserType::ResortOwner,
            'status' => UserStatus::Active,
        ]);

        $response = $this->postJson('/api/v1/user/login', [
            'phone_number' => '218912345678',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Invalid account type for this endpoint.']);
    });

    it('returns auth token on successful login', function () {
        User::factory()->create([
            'phone_number' => '218912345678',
            'password' => bcrypt('password123'),
            'type' => UserType::User,
            'status' => UserStatus::Active,
        ]);

        $response = $this->postJson('/api/v1/user/login', [
            'phone_number' => '218912345678',
            'password' => 'password123',
        ]);

        $response->assertOk();
        expect($response->json('data.token'))->not->toBeNull();
    });

    it('revokes existing tokens on new login', function () {
        $user = User::factory()->create([
            'phone_number' => '218912345678',
            'password' => bcrypt('password123'),
            'type' => UserType::User,
            'status' => UserStatus::Active,
        ]);

        // First login
        $user->createToken('auth_token');
        expect($user->tokens()->count())->toBe(1);

        // Second login
        $this->postJson('/api/v1/user/login', [
            'phone_number' => '218912345678',
            'password' => 'password123',
        ]);

        // Should have only one token (old one revoked)
        expect($user->fresh()->tokens()->count())->toBe(1);
    });
});
