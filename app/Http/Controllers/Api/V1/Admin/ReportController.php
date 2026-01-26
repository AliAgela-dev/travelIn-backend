<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Report\BookingsReportRequest;
use App\Http\Requests\Admin\Report\ExportReportRequest;
use App\Http\Requests\Admin\Report\RevenueReportRequest;
use App\Http\Requests\Admin\Report\UsersReportRequest;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reportService)
    {
    }

    /**
     * Revenue report.
     */
    public function revenue(RevenueReportRequest $request)
    {

        $from = $request->from ? Carbon::parse($request->from) : null;
        $to = $request->to ? Carbon::parse($request->to) : null;

        $data = $this->reportService->revenueReport(
            $request->period ?? 'monthly',
            $from,
            $to
        );

        return $this->success($data, 'Revenue report retrieved successfully.');
    }

    /**
     * Bookings report.
     */
    public function bookings(BookingsReportRequest $request)
    {

        $from = $request->from ? Carbon::parse($request->from) : null;
        $to = $request->to ? Carbon::parse($request->to) : null;

        $data = $this->reportService->bookingsReport($from, $to);

        return $this->success($data, 'Bookings report retrieved successfully.');
    }

    /**
     * Users report.
     */
    public function users(UsersReportRequest $request)
    {

        $from = $request->from ? Carbon::parse($request->from) : null;
        $to = $request->to ? Carbon::parse($request->to) : null;

        $data = $this->reportService->usersReport($from, $to);

        return $this->success($data, 'Users report retrieved successfully.');
    }

    /**
     * Export report to CSV.
     */
    public function export(ExportReportRequest $request)
    {

        $from = $request->from ? Carbon::parse($request->from) : null;
        $to = $request->to ? Carbon::parse($request->to) : null;

        $data = match ($request->type) {
            'revenue' => $this->reportService->revenueReport('monthly', $from, $to),
            'bookings' => $this->reportService->bookingsReport($from, $to),
            'users' => $this->reportService->usersReport($from, $to),
        };

        return $this->reportService->exportToCsv($request->type, $data);
    }
}
