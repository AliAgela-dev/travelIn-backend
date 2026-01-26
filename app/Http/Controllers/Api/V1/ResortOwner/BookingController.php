<?php

namespace App\Http\Controllers\Api\V1\ResortOwner;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\UpdateBookingStatusRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BookingController extends Controller
{
    /**
     * List bookings for owner's resorts.
     */
    public function index()
    {
        $bookings = QueryBuilder::for(Booking::class)
            ->forOwner(auth()->id())
            ->with(['unit.resort', 'user'])
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('unit_id'),
            ])
            ->latest()
            ->paginate();

        return $this->successCollection(BookingResource::collection($bookings));
    }

    /**
     * Show booking details.
     */
    public function show(Booking $booking)
    {
        $this->authorize('manage', $booking);

        return $this->success(new BookingResource($booking->load(['unit.resort', 'user'])));
    }

    /**
     * Update booking status (Confirm or Reject).
     */
    public function update(UpdateBookingStatusRequest $request, Booking $booking)
    {
        $this->authorize('updateStatus', $booking);

        $booking->update([
            'status' => $request->status,
            'owner_notes' => $request->owner_notes,
        ]);

        $message = $request->status === BookingStatus::Confirmed
            ? 'Booking confirmed.'
            : 'Booking rejected.';

        return $this->success(new BookingResource($booking->load(['unit.resort', 'user'])), $message);
    }
}
