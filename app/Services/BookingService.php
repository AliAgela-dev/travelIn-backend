<?php

namespace App\Services;

use App\DataTransferObjects\BookingData;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;

class BookingService
{
    /**
     * Calculate total price for a booking period.
     */
    public function calculateTotalPrice(Unit $unit, Carbon $checkIn, Carbon $checkOut): float
    {
        $total = 0;
        $current = $checkIn->copy();

        while ($current < $checkOut) {
            $total += $unit->getPriceForDate($current);
            $current->addDay();
        }

        return $total;
    }

    /**
     * Validate availability for a unit during given dates.
     */
    public function validateAvailability(Unit $unit, Carbon $checkIn, Carbon $checkOut): bool
    {
        // Check blocked dates
        if (!$unit->isAvailableForDates($checkIn, $checkOut)) {
            return false;
        }

        // Check existing confirmed/pending bookings
        return !$this->hasBookingConflict($unit, $checkIn, $checkOut);
    }

    /**
     * Check if unit has conflicting bookings.
     */
    public function hasBookingConflict(Unit $unit, Carbon $checkIn, Carbon $checkOut, ?int $excludeBookingId = null): bool
    {
        $query = Booking::where('unit_id', $unit->id)
            ->whereIn('status', [BookingStatus::Pending, BookingStatus::Confirmed])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereBetween('check_in', [$checkIn, $checkOut->copy()->subDay()])
                  ->orWhereBetween('check_out', [$checkIn->copy()->addDay(), $checkOut])
                  ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                      $q2->where('check_in', '<=', $checkIn)
                         ->where('check_out', '>=', $checkOut);
                  });
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->exists();
    }

    /**
     * Create a new booking using DTO.
     */
    public function createFromData(User $user, BookingData $data): Booking
    {
        $unit = Unit::findOrFail($data->unitId);

        // Validate capacity
        if ($data->guests + $data->children > $unit->capacity) {
            throw new \Exception('Number of guests exceeds unit capacity.');
        }

        // Validate availability
        if (!$this->validateAvailability($unit, $data->checkIn, $data->checkOut)) {
            throw new \Exception('Unit is not available for the selected dates.');
        }

        // Calculate price
        $totalPrice = $this->calculateTotalPrice($unit, $data->checkIn, $data->checkOut);

        return Booking::create([
            'user_id' => $user->id,
            'unit_id' => $unit->id,
            'check_in' => $data->checkIn,
            'check_out' => $data->checkOut,
            'guests' => $data->guests,
            'children' => $data->children,
            'total_price' => $totalPrice,
            'status' => BookingStatus::Pending,
            'notes' => $data->notes,
        ]);
    }

    /**
     * Create a new booking (legacy method for backward compatibility).
     */
    public function createBooking(
        User $user,
        Unit $unit,
        Carbon $checkIn,
        Carbon $checkOut,
        int $guests,
        int $children = 0,
        ?string $notes = null
    ): Booking {
        // Validate capacity
        if ($guests + $children > $unit->capacity) {
            throw new \Exception('Number of guests exceeds unit capacity.');
        }

        // Validate availability
        if (!$this->validateAvailability($unit, $checkIn, $checkOut)) {
            throw new \Exception('Unit is not available for the selected dates.');
        }

        // Calculate price
        $totalPrice = $this->calculateTotalPrice($unit, $checkIn, $checkOut);

        return Booking::create([
            'user_id' => $user->id,
            'unit_id' => $unit->id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guests' => $guests,
            'children' => $children,
            'total_price' => $totalPrice,
            'status' => BookingStatus::Pending,
            'notes' => $notes,
        ]);
    }
}
