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
                    \App\Models\Notification::create([
                        'user_id' => $booking->user_id,
                        'type' => \App\Enums\NotificationType::BookingCancelled->value,
                        'ar_title' => 'تم إلغاء الحجز',
                        'en_title' => 'Booking Cancelled',
                        'ar_body' => 'تم إلغاء حجزك بنجاح.',
                        'en_body' => 'Your booking has been cancelled successfully.',
                        'notifiable_type' => get_class($booking),
                        'notifiable_id' => $booking->id,
                        'data' => [],
                    ])
                ),
                default => null,
            };
        }
    }
}
