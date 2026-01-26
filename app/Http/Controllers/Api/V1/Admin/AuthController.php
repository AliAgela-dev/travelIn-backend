<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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
     * Request an OTP for password reset.
     */
    public function requestOtp(RequestOtpRequest $request): JsonResponse
    {
        if ($request->validated('purpose') !== 'reset') {
            return $this->error('Admins can only request OTP for password reset.', 422);
        }

        $this->otpService->requestOtp(
            $request->validated('phone_number'),
            'reset',
            UserType::Admin
        );

        return $this->success(null, 'OTP sent successfully.');
    }

    /**
     * Verify an OTP code (for password reset only).
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ($validated['purpose'] !== 'reset') {
            return $this->error('Admins can only verify OTP for password reset.', 422);
        }

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
     * Login an admin.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('phone_number', $validated['phone_number'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return $this->unauthorized('Invalid credentials.');
        }

        if (!in_array($user->type, [UserType::Admin, UserType::SuperAdmin])) {
            return $this->forbidden('Invalid account type for this endpoint.');
        }

        $user->ensureActive();

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

        if (!$this->otpService->hasValidVerification($validated['phone_number'], 'reset')) {
            return $this->error('Invalid or expired OTP. Please request a new one.', 422);
        }

        $user = User::where('phone_number', $validated['phone_number'])->first();

        if (!in_array($user->type, [UserType::Admin, UserType::SuperAdmin])) {
            return $this->forbidden('Invalid account type for this endpoint.');
        }

        $user->update(['password' => $validated['password']]);
        $user->tokens()->delete();

        return $this->success(null, 'Password reset successful. Please login with your new password.');
    }

    /**
     * Get current admin.
     */
    public function me(Request $request): JsonResponse
    {
        return $this->success(['user' => new UserResource($request->user()->load('city'))]);
    }

    /**
     * Logout admin.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully.');
    }
}
