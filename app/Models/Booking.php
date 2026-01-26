<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'unit_id',
        'check_in',
        'check_out',
        'guests',
        'children',
        'total_price',
        'status',
        'notes',
        'owner_notes',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'guests' => 'integer',
        'children' => 'integer',
        'total_price' => 'decimal:2',
        'status' => BookingStatus::class,
    ];

    /**
     * Get the user who made the booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the unit being booked.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Scope to get bookings for a specific user.
     */
    public function scopeForUser(Builder $query, int $userId): void
    {
        $query->where('user_id', $userId);
    }

    /**
     * Scope to get bookings for a specific owner's resorts.
     */
    public function scopeForOwner(Builder $query, int $ownerId): void
    {
        $query->whereHas('unit.resort', function ($q) use ($ownerId) {
            $q->where('owner_id', $ownerId);
        });
    }

    /**
     * Scope to get pending bookings.
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', BookingStatus::Pending);
    }

    /**
     * Scope to get confirmed bookings.
     */
    public function scopeConfirmed(Builder $query): void
    {
        $query->where('status', BookingStatus::Confirmed);
    }

    /**
     * Scope to get active bookings (pending or confirmed).
     */
    public function scopeActive(Builder $query): void
    {
        $query->whereIn('status', [BookingStatus::Pending, BookingStatus::Confirmed]);
    }

    /**
     * Scope to get completed bookings.
     */
    public function scopeCompleted(Builder $query): void
    {
        $query->where('status', BookingStatus::Completed);
    }

    /**
     * Scope to get upcoming bookings.
     */
    public function scopeUpcoming(Builder $query): void
    {
        $query->where('check_in', '>', now());
    }
}
