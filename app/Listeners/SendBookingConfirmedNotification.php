<?php

namespace App\Listeners;

use App\Events\BookingConfirmed;
use App\Services\NotificationService;

class SendBookingConfirmedNotification
{
    public function __construct(protected NotificationService $notificationService)
    {
    }

    public function handle(BookingConfirmed $event): void
    {
        $this->notificationService->bookingConfirmed($event->booking->user, $event->booking);
    }
}
