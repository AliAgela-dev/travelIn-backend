<?php

namespace App\DataTransferObjects;

use App\Http\Requests\Booking\StoreBookingRequest;
use Carbon\Carbon;

readonly class BookingData
{
    public function __construct(
        public int $unitId,
        public Carbon $checkIn,
        public Carbon $checkOut,
        public int $guests,
        public int $children = 0,
        public ?string $notes = null,
    ) {}

    public static function fromRequest(StoreBookingRequest $request): self
    {
        return new self(
            unitId: $request->unit_id,
            checkIn: Carbon::parse($request->check_in),
            checkOut: Carbon::parse($request->check_out),
            guests: $request->guests,
            children: $request->children ?? 0,
            notes: $request->notes,
        );
    }
}
