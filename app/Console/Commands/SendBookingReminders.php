<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendBookingReminders extends Command
{
    protected $signature = 'bookings:send-reminders';

    protected $description = 'Send reminder notifications for bookings with check-in tomorrow';

    public function __construct(protected NotificationService $notificationService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        $bookings = Booking::with('user')
            ->where('status', BookingStatus::Confirmed)
            ->whereDate('check_in', $tomorrow)
            ->get();

        $count = 0;
        foreach ($bookings as $booking) {
            $this->notificationService->bookingReminder($booking->user, $booking);
            $count++;
        }

        $this->info("Sent {$count} booking reminders.");

        return Command::SUCCESS;
    }
}
