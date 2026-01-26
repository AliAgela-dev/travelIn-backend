<?php

namespace App\Observers;

use App\Enums\BookingStatus;
use App\Events\BookingConfirmed;
use App\Events\BookingRejected;
use App\Models\Booking;

class BookingObserver
{
    public function __construct(
        protected \App\Services\NotificationService $notificationService
    ) {}

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        if ($booking->wasChanged('status')) {
            match ($booking->status) {
                BookingStatus::Confirmed => $this->notificationService->bookingConfirmed($booking->user, $booking),
                BookingStatus::Rejected => $this->notificationService->bookingRejected($booking->user, $booking),
                BookingStatus::Cancelled => $this->notificationService->notify(
                    $booking->user,
                    \App\Enums\NotificationType::BookingCancelled->value,
                    'تم إلغاء الحجز',
                    'Booking Cancelled',
                    'تم إلغاء حجزك بنجاح.',
                    'Your booking has been cancelled successfully.',
                    $booking
                ),
                default => null,
            };
        }
    }
}
