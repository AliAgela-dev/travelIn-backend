<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhoneVerification extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'phone_number',
        'otp_code',
        'purpose',
        'is_verified',
        'expires_at',
        'verified_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Check if the OTP is still valid (not expired and not verified).
     */
    public function isValid(): bool
    {
        return !$this->is_verified && $this->expires_at->isFuture();
    }

    /**
     * Mark the OTP as verified.
     */
    public function markAsVerified(): void
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);
    }

    /**
     * Scope to find valid (recent) verification for a phone number.
     * Valid means: verified within the last 30 minutes.
     */
    public function scopeHasValidVerification($query, string $phoneNumber, string $purpose)
    {
        return $query->where('phone_number', $phoneNumber)
            ->where('purpose', $purpose)
            ->where('is_verified', true)
            ->where('verified_at', '>=', now()->subMinutes(30));
    }

    /**
     * Scope to find pending (unverified, not expired) OTPs.
     */
    public function scopePending($query, string $phoneNumber, string $purpose)
    {
        return $query->where('phone_number', $phoneNumber)
            ->where('purpose', $purpose)
            ->where('is_verified', false)
            ->where('expires_at', '>', now());
    }
}
