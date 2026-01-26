<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
    /**
     * Get date format SQL for current database driver.
     */
    private function dateFormatSql(string $column, string $period): string
    {
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'sqlite') {
            return match ($period) {
                'daily' => "strftime('%Y-%m-%d', {$column})",
                'yearly' => "strftime('%Y', {$column})",
                default => "strftime('%Y-%m', {$column})",
            };
        }
        
        // MySQL / MariaDB
        return match ($period) {
            'daily' => "DATE_FORMAT({$column}, '%Y-%m-%d')",
            'yearly' => "DATE_FORMAT({$column}, '%Y')",
            default => "DATE_FORMAT({$column}, '%Y-%m')",
        };
    }

    /**
     * Revenue report with period aggregation.
     */
    public function revenueReport(string $period = 'monthly', ?Carbon $from = null, ?Carbon $to = null): array
    {
        $from = $from ?? now()->subYear();
        $to = $to ?? now();

        $query = Booking::whereIn('status', [BookingStatus::Confirmed, BookingStatus::Completed])
            ->whereBetween('created_at', [$from, $to]);

        $totalRevenue = (clone $query)->sum('total_price');

        $dateFormat = $this->dateFormatSql('created_at', $period);
        $periodData = (clone $query)
            ->select(DB::raw("{$dateFormat} as period"), DB::raw('SUM(total_price) as revenue'))
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'total_revenue' => $totalRevenue,
            'period' => $period,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'period_data' => $periodData,
        ];
    }

    /**
     * Bookings report with status breakdown.
     */
    public function bookingsReport(?Carbon $from = null, ?Carbon $to = null): array
    {
        $from = $from ?? now()->subYear();
        $to = $to ?? now();

        $query = Booking::whereBetween('created_at', [$from, $to]);

        $totalBookings = (clone $query)->count();

        $byStatus = (clone $query)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $dateFormat = $this->dateFormatSql('created_at', 'monthly');
        $periodData = (clone $query)
            ->select(DB::raw("{$dateFormat} as period"), DB::raw('COUNT(*) as count'))
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'total_bookings' => $totalBookings,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'by_status' => $byStatus,
            'period_data' => $periodData,
        ];
    }

    /**
     * Users report with type breakdown.
     */
    public function usersReport(?Carbon $from = null, ?Carbon $to = null): array
    {
        $from = $from ?? now()->subYear();
        $to = $to ?? now();

        $totalUsers = User::count();
        $newUsersPeriod = User::whereBetween('created_at', [$from, $to])->count();

        $byType = User::select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type');

        $dateFormat = $this->dateFormatSql('created_at', 'monthly');
        $periodData = User::whereBetween('created_at', [$from, $to])
            ->select(DB::raw("{$dateFormat} as period"), DB::raw('COUNT(*) as count'))
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'total_users' => $totalUsers,
            'new_users_period' => $newUsersPeriod,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'by_type' => $byType,
            'period_data' => $periodData,
        ];
    }

    /**
     * Export data to CSV.
     */
    public function exportToCsv(string $type, array $data): StreamedResponse
    {
        $filename = "{$type}_report_" . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Period', 'Value']);
            foreach ($data['period_data'] as $row) {
                $value = $row['revenue'] ?? $row['count'] ?? 0;
                fputcsv($handle, [$row['period'], $value]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
