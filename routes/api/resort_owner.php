<?php

use App\Http\Controllers\Api\V1\ResortOwner\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Resort Owner Routes
|--------------------------------------------------------------------------
|
| Routes for resort owners (property managers).
|
*/

Route::prefix('v1/resort-owner')->group(function () {
    // Public routes
    Route::post('/request-otp', [AuthController::class, 'requestOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // Public Resources
    Route::apiResource('cities', \App\Http\Controllers\Api\V1\ResortOwner\CityController::class)->only(['index', 'show']);
    Route::apiResource('areas', \App\Http\Controllers\Api\V1\ResortOwner\AreaController::class)->only(['index', 'show']);

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/uploads', [\App\Http\Controllers\Api\V1\UploadController::class, 'store']);
        Route::apiResource('resorts', \App\Http\Controllers\Api\V1\ResortOwner\ResortController::class);
        Route::apiResource('resorts.units', \App\Http\Controllers\Api\V1\ResortOwner\UnitController::class);
        Route::apiResource('units.pricing', \App\Http\Controllers\Api\V1\ResortOwner\UnitPricingController::class)->except(['show']);
        Route::apiResource('units.availability', \App\Http\Controllers\Api\V1\ResortOwner\UnitAvailabilityController::class)->only(['index', 'store', 'destroy']);
        Route::apiResource('bookings', \App\Http\Controllers\Api\V1\ResortOwner\BookingController::class)->only(['index', 'show', 'update']);
        Route::get('dashboard/stats', [\App\Http\Controllers\Api\V1\ResortOwner\DashboardController::class, 'stats']);
        Route::get('dashboard/recent-activity', [\App\Http\Controllers\Api\V1\ResortOwner\DashboardController::class, 'recentActivity']);
        Route::get('dashboard/revenue-chart', [\App\Http\Controllers\Api\V1\ResortOwner\DashboardController::class, 'revenueChart']);
        Route::get('reviews', [\App\Http\Controllers\Api\V1\ResortOwner\ReviewController::class, 'index']);
        Route::put('reviews/{review}', [\App\Http\Controllers\Api\V1\ResortOwner\ReviewController::class, 'update']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});
