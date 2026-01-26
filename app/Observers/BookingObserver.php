<?php

namespace App\Observers;

use App\Enums\BookingStatus;
use App\Events\BookingConfirmed;
use App\Events\BookingRejected;
use App\Models\Booking;

class BookingObserver
{
    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        if ($booking->wasChanged('status')) {
            match ($booking->status) {
                BookingStatus::Confirmed => BookingConfirmed::dispatch($booking),
                BookingStatus::Rejected => BookingRejected::dispatch($booking),
                default => null,
            };
        }
    }
}
