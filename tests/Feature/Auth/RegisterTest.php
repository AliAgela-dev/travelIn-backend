<?php

use App\Models\User;
use App\Models\PhoneVerification;
use App\Enums\UserType;
use App\Enums\UserStatus;

describe('User Registration', function () {
    it('can register with valid OTP', function () {
        // Create verified OTP
        PhoneVerification::create([
            'phone_number' => '218912345678',
            'otp_code' => '123456',
            'purpose' => 'registration',
            'is_verified' => true,
            'verified_at' => now(),
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/v1/user/register', [
            'phone_number' => '218912345678',
            'otp_code' => '123456',
            'full_name' => 'Test User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'full_name', 'phone_number', 'type', 'status'],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'phone_number' => '218912345678',
            'full_name' => 'Test User',
            'type' => 'user',
            'status' => 'active',
        ]);
    });

    it('cannot register with expired OTP', function () {
        PhoneVerification::create([
            'phone_number' => '218912345678',
            'otp_code' => '123456',
            'purpose' => 'registration',
            'is_verified' => true,
            'verified_at' => now()->subMinutes(35), // Expired
            'expires_at' => now()->subMinutes(30),
        ]);

        $response = $this->postJson('/api/v1/user/register', [
            'phone_number' => '218912345678',
            'otp_code' => '123456',
            'full_name' => 'Test User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Invalid or expired OTP. Please request a new one.']);
    });

    it('cannot register with unverified OTP', function () {
        PhoneVerification::create([
            'phone_number' => '218912345678',
            'otp_code' => '123456',
            'purpose' => 'registration',
            'is_verified' => false,
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/v1/user/register', [
            'phone_number' => '218912345678',
            'otp_code' => '123456',
            'full_name' => 'Test User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Invalid or expired OTP. Please request a new one.']);
    });

    it('cannot register with already used phone number', function () {
        User::factory()->create([
            'phone_number' => '218912345678',
        ]);

        $response = $this->postJson('/api/v1/user/register', [
            'phone_number' => '218912345678',
            'otp_code' => '123456',
            'full_name' => 'Test User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number']);
    });

    it('fails validation for weak password', function () {
        $response = $this->postJson('/api/v1/user/register', [
            'phone_number' => '218912345678',
            'otp_code' => '123456',
            'full_name' => 'Test User',
            'password' => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });

    it('fails validation for mismatched password confirmation', function () {
        $response = $this->postJson('/api/v1/user/register', [
            'phone_number' => '218912345678',
            'otp_code' => '123456',
            'full_name' => 'Test User',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });

    it('creates user with correct type and status', function () {
        PhoneVerification::create([
            'phone_number' => '218912345678',
            'otp_code' => '123456',
            'purpose' => 'registration',
            'is_verified' => true,
            'verified_at' => now(),
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->postJson('/api/v1/user/register', [
            'phone_number' => '218912345678',
            'otp_code' => '123456',
            'full_name' => 'Test User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('phone_number', '218912345678')->first();

        expect($user->type)->toBe(UserType::User);
        expect($user->status)->toBe(UserStatus::Active);
    });

    it('returns auth token on successful registration', function () {
        PhoneVerification::create([
            'phone_number' => '218912345678',
            'otp_code' => '123456',
            'purpose' => 'registration',
            'is_verified' => true,
            'verified_at' => now(),
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/v1/user/register', [
            'phone_number' => '218912345678',
            'otp_code' => '123456',
            'full_name' => 'Test User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
        expect($response->json('data.token'))->not->toBeNull();
    });
});
