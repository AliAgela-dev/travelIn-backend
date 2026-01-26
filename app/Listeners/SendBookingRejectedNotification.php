<?php

namespace App\Listeners;

use App\Events\BookingRejected;
use App\Services\NotificationService;

class SendBookingRejectedNotification
{
    public function __construct(protected NotificationService $notificationService)
    {
    }

    public function handle(BookingRejected $event): void
    {
        $this->notificationService->bookingRejected($event->booking->user, $event->booking);
    }
}
