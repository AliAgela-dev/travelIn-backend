<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\ResortStatus;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\City;
use App\Models\Resort;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    /**
     * Get system statistics for the admin dashboard.
     */
    public function stats()
    {
        Gate::authorize('viewDashboard');

        return $this->success([
            'users' => [
                'total' => User::count(),
                'admins' => User::where('type', UserType::Admin)->count(),
                'resort_owners' => User::where('type', UserType::ResortOwner)->count(),
                'travelers' => User::where('type', UserType::User)->count(),
            ],
            'resorts' => [
                'total' => Resort::count(),
                'active' => Resort::where('status', ResortStatus::Active)->count(),
                'pending' => Resort::where('status', ResortStatus::Pending)->count(),
                'rejected' => Resort::where('status', ResortStatus::Rejected)->count(),
                'inactive' => Resort::where('status', ResortStatus::Inactive)->count(),
            ],
            'geography' => [
                'cities' => City::count(),
                'areas' => Area::count(),
            ],
        ], 'Dashboard statistics retrieved successfully.');
    }
}
