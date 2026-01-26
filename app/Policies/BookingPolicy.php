<?php

namespace App\Policies;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine if user can view the booking.
     */
    public function view(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id;
    }

    /**
     * Determine if user can cancel the booking.
     */
    public function cancel(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id
            && in_array($booking->status, [BookingStatus::Pending, BookingStatus::Confirmed]);
    }

    /**
     * Determine if resort owner can manage the booking.
     */
    public function manage(User $user, Booking $booking): bool
    {
        $booking->loadMissing('unit.resort');
        return $booking->unit->resort->owner_id === $user->id;
    }

    /**
     * Determine if resort owner can update the booking status.
     */
    public function updateStatus(User $user, Booking $booking): bool
    {
        return $this->manage($user, $booking) 
            && $booking->status === BookingStatus::Pending;
    }
}
