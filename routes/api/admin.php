<?php

use App\Http\Controllers\Api\V1\Admin\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Routes for administrators.
| Note: Admins cannot self-register, they are created manually.
|
*/

Route::prefix('v1/admin')->group(function () {
    // Public routes (no register for admins)
    Route::post('/request-otp', [AuthController::class, 'requestOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // Authenticated routes
    Route::middleware(['auth:sanctum', 'role:admin,super_admin'])->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::apiResource('cities', \App\Http\Controllers\Api\V1\Admin\CityController::class);
        Route::apiResource('areas', \App\Http\Controllers\Api\V1\Admin\AreaController::class);
        Route::apiResource('users', \App\Http\Controllers\Api\V1\Admin\UserController::class)->only(['index', 'show', 'update']);
        Route::apiResource('resorts', \App\Http\Controllers\Api\V1\Admin\ResortController::class)->only(['index', 'show', 'update']);
        Route::apiResource('units', \App\Http\Controllers\Api\V1\Admin\UnitController::class)->only(['index', 'show', 'update']);
        Route::get('/dashboard/stats', [\App\Http\Controllers\Api\V1\Admin\DashboardController::class, 'stats']);
        Route::apiResource('notifications', \App\Http\Controllers\Api\V1\Admin\NotificationController::class)->only(['index', 'store', 'show', 'destroy']);
        
        // Reports
        Route::get('reports/revenue', [\App\Http\Controllers\Api\V1\Admin\ReportController::class, 'revenue']);
        Route::get('reports/bookings', [\App\Http\Controllers\Api\V1\Admin\ReportController::class, 'bookings']);
        Route::get('reports/users', [\App\Http\Controllers\Api\V1\Admin\ReportController::class, 'users']);
        Route::get('reports/export', [\App\Http\Controllers\Api\V1\Admin\ReportController::class, 'export']);

        // Super Admin only routes
        Route::middleware(['role:super_admin'])->group(function () {
            Route::apiResource('admins', \App\Http\Controllers\Api\V1\Admin\AdminManagementController::class);
        });
    });
});
