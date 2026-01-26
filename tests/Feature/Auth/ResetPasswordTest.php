<?php

use App\Models\User;
use App\Models\PhoneVerification;
use App\Enums\UserType;
use App\Enums\UserStatus;

describe('User Reset Password', function () {
    it('can reset password with valid OTP', function () {
        $user = User::factory()->create([
            'phone_number' => '218912345678',
            'password' => bcrypt('oldpassword'),
            'type' => UserType::User,
        ]);

        PhoneVerification::create([
            'phone_number' => '218912345678',
            'otp_code' => '123456',
            'purpose' => 'reset',
            'is_verified' => true,
            'verified_at' => now(),
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->postJson('/api/v1/user/reset-password', [
            'phone_number' => '218912345678',
            'otp_code' => '123456',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertOk()
            ->assertJson(['message' => 'Password reset successful. Please login with your new password.']);

        // Verify password was changed
        expect(password_verify('newpassword123', $user->fresh()->password))->toBeTrue();
    });

    it('cannot reset password with expired OTP', function () {
        User::factory()->create([
            'phone_number' => '218912345678',
            'type' => UserType::User,
        ]);

        PhoneVerification::create([
            'phone_number' => '218912345678',
            'otp_code' => '123456',
            'purpose' => 'reset',
            'is_verified' => true,
            'verified_at' => now()->subMinutes(35),
            'expires_at' => now()->subMinutes(30),
        ]);

        $response = $this->postJson('/api/v1/user/reset-password', [
            'phone_number' => '218912345678',
            'otp_code' => '123456',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Invalid or expired OTP. Please request a new one.']);
    });

    it('fails for non-existent phone number', function () {
        $response = $this->postJson('/api/v1/user/reset-password', [
            'phone_number' => '218912345678',
            'otp_code' => '123456',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number']);
    });

    it('revokes all tokens after password reset', function () {
        $user = User::factory()->create([
            'phone_number' => '218912345678',
            'type' => UserType::User,
        ]);

        $user->createToken('auth_token');
        expect($user->tokens()->count())->toBe(1);

        PhoneVerification::create([
            'phone_number' => '218912345678',
            'otp_code' => '123456',
            'purpose' => 'reset',
            'is_verified' => true,
            'verified_at' => now(),
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->postJson('/api/v1/user/reset-password', [
            'phone_number' => '218912345678',
            'otp_code' => '123456',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        expect($user->fresh()->tokens()->count())->toBe(0);
    });
});
