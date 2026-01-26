<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\UserType;
use App\Models\Booking;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BookingsSeeder extends Seeder
{
    /**
     * Seed bookings ensuring every traveler has one booking of each status type.
     */
    public function run(): void
    {
        $travelers = User::where('type', UserType::User)->get();
        $units = Unit::all();

        if ($travelers->isEmpty() || $units->isEmpty()) {
            $this->command->warn('⚠ No travelers or units found. Run UsersSeeder and UnitsSeeder first.');
            return;
        }

        $statuses = BookingStatus::cases();
        $bookingCount = 0;

        foreach ($travelers as $traveler) {
            // Shuffle units for variety per user
            $shuffledUnits = $units->shuffle();
            $unitIndex = 0;

            foreach ($statuses as $status) {
                // Pick a different unit for each booking when possible
                $unit = $shuffledUnits[$unitIndex % $shuffledUnits->count()];
                $unitIndex++;

                // Generate realistic date ranges based on status
                $checkIn = $this->getCheckInDate($status);
                $nights = rand(2, 7);
                $checkOut = $checkIn->copy()->addDays($nights);
                $guests = rand(1, $unit->capacity);
                $children = rand(0, 2);
                $totalPrice = $unit->price_per_night * $nights;

                Booking::create([
                    'user_id' => $traveler->id,
                    'unit_id' => $unit->id,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'guests' => $guests,
                    'children' => $children,
                    'total_price' => $totalPrice,
                    'status' => $status,
                    'notes' => rand(0, 1) ? fake()->sentence() : null,
                    'owner_notes' => $this->getOwnerNotes($status),
                ]);

                $bookingCount++;
            }
        }

        $statusCount = count($statuses);
        $travelerCount = $travelers->count();
        $this->command->info("✓ Created {$bookingCount} bookings ({$travelerCount} travelers × {$statusCount} statuses each)");
    }

    /**
     * Get appropriate check-in date based on booking status.
     */
    private function getCheckInDate(BookingStatus $status): Carbon
    {
        return match ($status) {
            BookingStatus::Completed => Carbon::now()->subDays(rand(30, 90)),
            BookingStatus::Confirmed => Carbon::now()->addDays(rand(5, 30)),
            BookingStatus::Pending => Carbon::now()->addDays(rand(7, 45)),
            BookingStatus::Cancelled => Carbon::now()->addDays(rand(-15, 30)),
            BookingStatus::Rejected => Carbon::now()->addDays(rand(-10, 20)),
        };
    }

    /**
     * Get owner notes based on booking status.
     */
    private function getOwnerNotes(BookingStatus $status): ?string
    {
        return match ($status) {
            BookingStatus::Confirmed => fake()->randomElement([
                'Welcome! Looking forward to hosting you.',
                'Booking confirmed. Check-in after 2 PM.',
                'Approved. Please contact us for any special requests.',
            ]),
            BookingStatus::Rejected => fake()->randomElement([
                'Sorry, the unit is not available for these dates.',
                'Unfortunately, we cannot accommodate this booking.',
                'Dates conflict with maintenance schedule.',
            ]),
            default => null,
        };
    }
}
