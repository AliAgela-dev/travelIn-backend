<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Otp\RequestOtpRequest;
use App\Http\Requests\Otp\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(
        private OtpService $otpService
    ) {}

    /**
     * Request an OTP for registration, login, or password reset.
     */
    public function requestOtp(RequestOtpRequest $request): JsonResponse
    {
        $this->otpService->requestOtp(
            $request->validated('phone_number'),
            $request->validated('purpose'),
            UserType::User
        );

        return $this->success(null, 'OTP sent successfully.');
    }

    /**
     * Verify an OTP code.
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $verified = $this->otpService->verify(
            $validated['phone_number'],
            $validated['otp_code'],
            $validated['purpose']
        );

        if (!$verified) {
            return $this->error('Invalid or expired OTP code.', 422);
        }

        return $this->success(null, 'OTP verified successfully.');
    }

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Verify OTP
        if (!$this->otpService->hasValidVerification($validated['phone_number'], 'registration')) {
            return $this->error('Invalid or expired OTP. Please request a new one.', 422);
        }

        // Create user
        $user = User::create([
            'full_name' => $validated['full_name'],
            'phone_number' => $validated['phone_number'],
            'password' => $validated['password'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'status' => UserStatus::Active,
            'type' => UserType::User,
            'email'=> $validated['full_name']
        ]);

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->created([
            'user' => new UserResource($user),
            'token' => $token,
        ], 'Registration successful.');
    }

    /**
     * Login a user.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('phone_number', $validated['phone_number'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return $this->unauthorized('Invalid credentials.');
        }

        if (!$user->isType(UserType::User)) {
            return $this->forbidden('Invalid account type for this endpoint.');
        }

        $user->ensureActive();

        // Revoke existing tokens and create a new one
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => new UserResource($user),
            'token' => $token,
        ], 'Login successful.');
    }

    /**
     * Reset password.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Verify OTP
        if (!$this->otpService->hasValidVerification($validated['phone_number'], 'reset')) {
            return $this->error('Invalid or expired OTP. Please request a new one.', 422);
        }

        $user = User::where('phone_number', $validated['phone_number'])->first();

        if ($user->type !== UserType::User) {
            return $this->forbidden('Invalid account type for this endpoint.');
        }

        $user->update([
            'password' => $validated['password'],
        ]);

        // Revoke all tokens
        $user->tokens()->delete();

        return $this->success(null, 'Password reset successful. Please login with your new password.');
    }

    /**
     * Update user profile.
     */
    public function updateProfile(\App\Http\Requests\Auth\UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        return $this->success(
            ['user' => new UserResource($user)],
            'Profile updated successfully.'
        );
    }

    /**
     * Change password for logged-in user.
     */
    public function changePassword(\App\Http\Requests\Auth\ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        
        $user->update([
            'password' => $request->new_password,
        ]);

        // Revoke other tokens if needed, but usually keep current session active
        // $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

        return $this->success(null, 'Password changed successfully.');
    }

    /**
     * Get current user.
     */
    public function me(Request $request): JsonResponse
    {
        return $this->success(['user' => new UserResource($request->user()->load('city'))]);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully.');
    }
}
