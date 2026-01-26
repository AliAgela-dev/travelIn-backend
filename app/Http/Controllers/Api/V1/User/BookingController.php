<?php

namespace App\Http\Controllers\Api\V1\User;

use App\DataTransferObjects\BookingData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use App\Enums\BookingStatus;

class BookingController extends Controller
{
    public function __construct(protected BookingService $bookingService)
    {
    }

    /**
     * List user's own bookings.
     */
    public function index()
    {
        $bookings = Booking::forUser(auth()->id())
            ->with(['unit.resort'])
            ->latest()
            ->paginate();

        return $this->successCollection(BookingResource::collection($bookings));
    }

    /**
     * Create a new booking using DTO.
     */
    public function store(StoreBookingRequest $request)
    {
        try {
            $bookingData = BookingData::fromRequest($request);
            $booking = $this->bookingService->createFromData(auth()->user(), $bookingData);

            return $this->created(
                new BookingResource($booking->load(['unit.resort'])),
                'Booking created successfully.'
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Show booking details.
     */
    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        return $this->success(new BookingResource($booking->load(['unit.resort'])));
    }

    /**
     * Cancel a booking (only if Pending or Confirmed).
     */
    public function destroy(Booking $booking)
    {
        $this->authorize('cancel', $booking);

        $booking->update(['status' => BookingStatus::Cancelled]);

        return $this->success(null, 'Booking cancelled successfully.');
    }
}
