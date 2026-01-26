<?php

namespace App\Http\Controllers\Api\V1\ResortOwner;

use App\Enums\BookingStatus;
use App\Enums\ResortStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Resort;
use App\Models\Unit;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics for the authenticated resort owner.
     */
    public function stats()
    {
        $ownerId = auth()->id();

        // Get owner's resorts
        $resorts = Resort::where('owner_id', $ownerId);
        $resortIds = $resorts->pluck('id');

        // Get units for owner's resorts
        $units = Unit::whereIn('resort_id', $resortIds);

        // Get bookings for owner's units
        $unitIds = $units->pluck('id');
        $bookings = Booking::whereIn('unit_id', $unitIds);

        // Calculate revenue
        $totalRevenue = Booking::whereIn('unit_id', $unitIds)
            ->whereIn('status', [BookingStatus::Confirmed, BookingStatus::Completed])
            ->sum('total_price');

        $thisMonthRevenue = Booking::whereIn('unit_id', $unitIds)
            ->whereIn('status', [BookingStatus::Confirmed, BookingStatus::Completed])
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total_price');

        return $this->success([
            'resorts' => [
                'total' => Resort::where('owner_id', $ownerId)->count(),
                'active' => Resort::where('owner_id', $ownerId)->where('status', ResortStatus::Active)->count(),
                'pending' => Resort::where('owner_id', $ownerId)->where('status', ResortStatus::Pending)->count(),
            ],
            'units' => [
                'total' => Unit::whereIn('resort_id', $resortIds)->count(),
            ],
            'bookings' => [
                'total' => Booking::whereIn('unit_id', $unitIds)->count(),
                'pending' => Booking::whereIn('unit_id', $unitIds)->where('status', BookingStatus::Pending)->count(),
                'confirmed' => Booking::whereIn('unit_id', $unitIds)->where('status', BookingStatus::Confirmed)->count(),
                'completed' => Booking::whereIn('unit_id', $unitIds)->where('status', BookingStatus::Completed)->count(),
                'cancelled' => Booking::whereIn('unit_id', $unitIds)->where('status', BookingStatus::Cancelled)->count(),
            ],
            'revenue' => [
                'total' => (float) $totalRevenue,
                'this_month' => (float) $thisMonthRevenue,
            ],
        ]);
    }

    /**
     * Get recent activity (recent bookings) for the resort owner.
     */
    public function recentActivity()
    {
        $limit = request()->get('limit', 10);
        $ownerId = auth()->id();

        // Get owner's resort IDs
        $resortIds = Resort::where('owner_id', $ownerId)->pluck('id');
        
        // Get unit IDs for owner's resorts
        $unitIds = Unit::whereIn('resort_id', $resortIds)->pluck('id');

        // Get recent bookings
        $bookings = Booking::whereIn('unit_id', $unitIds)
            ->with(['unit:id,ar_name,en_name,resort_id', 'unit.resort:id,ar_name,en_name', 'user:id,full_name'])
            ->latest()
            ->take(min($limit, 50))
            ->get();

        $activities = $bookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'type' => 'booking_' . $booking->status->value,
                'message' => $this->getActivityMessage($booking),
                'booking' => [
                    'id' => $booking->id,
                    'status' => $booking->status->value,
                    'check_in' => $booking->check_in->format('Y-m-d'),
                    'check_out' => $booking->check_out->format('Y-m-d'),
                    'total_price' => (float) $booking->total_price,
                    'unit' => $booking->unit ? [
                        'id' => $booking->unit->id,
                        'ar_name' => $booking->unit->ar_name,
                        'en_name' => $booking->unit->en_name,
                    ] : null,
                    'user' => $booking->user ? [
                        'id' => $booking->user->id,
                        'full_name' => $booking->user->full_name,
                    ] : null,
                ],
                'created_at' => $booking->created_at->toISOString(),
            ];
        });

        return $this->success($activities);
    }

    /**
     * Get revenue chart data for the past N months.
     */
    public function revenueChart()
    {
        $months = min(request()->get('months', 6), 12);
        $ownerId = auth()->id();

        // Get owner's resort IDs
        $resortIds = Resort::where('owner_id', $ownerId)->pluck('id');
        
        // Get unit IDs for owner's resorts
        $unitIds = Unit::whereIn('resort_id', $resortIds)->pluck('id');

        $labels = [];
        $values = [];

        // Generate data for each month
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');
            
            $revenue = Booking::whereIn('unit_id', $unitIds)
                ->whereIn('status', [BookingStatus::Confirmed, BookingStatus::Completed])
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('total_price');
            
            $values[] = (float) $revenue;
        }

        return $this->success([
            'labels' => $labels,
            'values' => $values,
            'currency' => 'LYD',
        ]);
    }

    /**
     * Get a human-readable message for booking activity.
     */
    private function getActivityMessage(Booking $booking): string
    {
        $userName = $booking->user?->full_name ?? 'A guest';
        
        return match ($booking->status) {
            BookingStatus::Pending => "New booking from {$userName}",
            BookingStatus::Confirmed => "Booking confirmed for {$userName}",
            BookingStatus::Completed => "Stay completed for {$userName}",
            BookingStatus::Cancelled => "Booking cancelled by {$userName}",
            default => "Booking update for {$userName}",
        };
    }
}
