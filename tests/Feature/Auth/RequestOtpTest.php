<?php

use App\Models\User;
use App\Models\PhoneVerification;
use App\Enums\UserType;
use App\Enums\UserStatus;

beforeEach(function () {
    // Fresh database for each test
});

describe('User Request OTP', function () {
    it('can request OTP for registration with new phone number', function () {
        $response = $this->postJson('/api/v1/user/request-otp', [
            'phone_number' => '218912345678',
            'purpose' => 'registration',
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'OTP sent successfully.']);

        $this->assertDatabaseHas('phone_verifications', [
            'phone_number' => '218912345678',
            'purpose' => 'registration',
            'is_verified' => false,
        ]);
    });

    it('can request OTP for login with existing user', function () {
        User::factory()->create([
            'phone_number' => '218912345678',
            'type' => UserType::User,
        ]);

        $response = $this->postJson('/api/v1/user/request-otp', [
            'phone_number' => '218912345678',
            'purpose' => 'login',
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'OTP sent successfully.']);
    });

    it('fails registration OTP for already registered phone number', function () {
        User::factory()->create([
            'phone_number' => '218912345678',
        ]);

        $response = $this->postJson('/api/v1/user/request-otp', [
            'phone_number' => '218912345678',
            'purpose' => 'registration',
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'This phone number is already registered.']);
    });

    it('fails login OTP for non-existent phone number', function () {
        $response = $this->postJson('/api/v1/user/request-otp', [
            'phone_number' => '218912345678',
            'purpose' => 'login',
        ]);

        $response->assertStatus(404)
            ->assertJson(['message' => 'No account found with this phone number.']);
    });

    it('fails validation for invalid phone format', function () {
        $response = $this->postJson('/api/v1/user/request-otp', [
            'phone_number' => '123456',
            'purpose' => 'registration',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number']);
    });

    it('fails validation for missing purpose', function () {
        $response = $this->postJson('/api/v1/user/request-otp', [
            'phone_number' => '218912345678',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['purpose']);
    });

    it('fails validation for invalid purpose', function () {
        $response = $this->postJson('/api/v1/user/request-otp', [
            'phone_number' => '218912345678',
            'purpose' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['purpose']);
    });

    it('creates OTP with correct expiry time', function () {
        $this->postJson('/api/v1/user/request-otp', [
            'phone_number' => '218912345678',
            'purpose' => 'registration',
        ]);

        $verification = PhoneVerification::where('phone_number', '218912345678')->first();

        expect($verification->expires_at)->toBeGreaterThan(now());
        expect($verification->expires_at)->toBeLessThanOrEqual(now()->addMinutes(6));
    });
});
