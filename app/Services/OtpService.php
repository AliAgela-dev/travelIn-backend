<?php

namespace App\Services;

use App\Enums\UserType;
use App\Models\PhoneVerification;
use App\Models\User;

class OtpService
{
    /**
     * OTP length.
     */
    private const OTP_LENGTH = 6;

    /**
     * OTP expiry in minutes.
     */
    private const OTP_EXPIRY_MINUTES = 5;

    /**
     * Verification window in minutes (how long after verification the OTP is valid for use).
     */
    private const VERIFICATION_WINDOW_MINUTES = 30;

    public function __construct(
        private SmsService $smsService
    ) {}

    /**
     * Handle OTP request logic including validation.
     */
    public function requestOtp(string $phoneNumber, string $purpose, UserType $expectedRole): void
    {
        // For registration, check if user already exists
        if ($purpose === 'registration') {
            if (User::where('phone_number', $phoneNumber)->exists()) {
                abort(422, 'This phone number is already registered.');
            }
        }

        // For login/reset, check if user exists and has correct type
        if (in_array($purpose, ['login', 'reset'])) {
            $user = User::where('phone_number', $phoneNumber)->first();
            
            if (!$user) {
                abort(404, 'No account found with this phone number.');
            }

            // Super Admin can login as Admin
            if ($expectedRole === UserType::Admin && $user->type === UserType::SuperAdmin) {
                // Allow (no op)
            } elseif ($user->type !== $expectedRole) {
                 abort(403, 'Invalid account type for this endpoint.');
            }
        }

        $this->generate($phoneNumber, $purpose);
    }

    /**
     * Generate and send an OTP for the given phone number and purpose.
     */
    public function generate(string $phoneNumber, string $purpose): PhoneVerification
    {
        // Invalidate any existing OTPs for this phone and purpose
        $this->invalidateAll($phoneNumber, $purpose);

        // Generate a new OTP
        $otpCode = $this->generateOtpCode();

        // Create the verification record
        $verification = PhoneVerification::create([
            'phone_number' => $phoneNumber,
            'otp_code' => $otpCode,
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
        ]);

        // Send the OTP via SMS
        $this->smsService->sendOtp($phoneNumber, $otpCode);

        return $verification;
    }

    /**
     * Verify an OTP for the given phone number and purpose.
     */
    public function verify(string $phoneNumber, string $otpCode, string $purpose): bool
    {
        $verification = PhoneVerification::where('phone_number', $phoneNumber)
            ->where('purpose', $purpose)
            ->where('otp_code', $otpCode)
            ->where('is_verified', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            return false;
        }

        $verification->markAsVerified();

        return true;
    }

    /**
     * Check if there is a valid (recent) verification for the given phone number and purpose.
     */
    public function hasValidVerification(string $phoneNumber, string $purpose): bool
    {
        return PhoneVerification::where('phone_number', $phoneNumber)
            ->where('purpose', $purpose)
            ->where('is_verified', true)
            ->where('verified_at', '>=', now()->subMinutes(self::VERIFICATION_WINDOW_MINUTES))
            ->exists();
    }

    /**
     * Invalidate all pending OTPs for the given phone number and purpose.
     */
    public function invalidateAll(string $phoneNumber, string $purpose): void
    {
        PhoneVerification::where('phone_number', $phoneNumber)
            ->where('purpose', $purpose)
            ->where('is_verified', false)
            ->update(['expires_at' => now()]);
    }

    /**
     * Generate a random OTP code.
     */
    private function generateOtpCode(): string
    {
        return str_pad((string) random_int(0, 999999), self::OTP_LENGTH, '0', STR_PAD_LEFT);
    }
}
