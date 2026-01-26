<?php

use App\Http\Controllers\Api\V1\User\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
|
| Routes for regular users (travelers).
|
*/

Route::prefix('v1/user')->group(function () {
    // Public routes
    Route::post('/request-otp', [AuthController::class, 'requestOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // Public Resources
    Route::apiResource('cities', \App\Http\Controllers\Api\V1\User\CityController::class)->only(['index', 'show']);
    Route::apiResource('areas', \App\Http\Controllers\Api\V1\User\AreaController::class)->only(['index', 'show']);
    Route::apiResource('resorts', \App\Http\Controllers\Api\V1\User\ResortController::class)->only(['index', 'show']);
    Route::get('resorts/{resort}/units', [\App\Http\Controllers\Api\V1\User\UnitController::class, 'index']);
    Route::get('resorts/{resort}/units/{unit}', [\App\Http\Controllers\Api\V1\User\UnitController::class, 'show']);

    // Search endpoints
    Route::prefix('search')->group(function () {
        Route::get('units', [\App\Http\Controllers\Api\V1\User\SearchController::class, 'units']);
        Route::get('metadata', [\App\Http\Controllers\Api\V1\User\SearchController::class, 'metadata']);
        Route::get('suggestions', [\App\Http\Controllers\Api\V1\User\SearchController::class, 'suggestions']);
    });

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::apiResource('bookings', \App\Http\Controllers\Api\V1\User\BookingController::class)->only(['index', 'store', 'show', 'destroy']);
        Route::apiResource('reviews', \App\Http\Controllers\Api\V1\User\ReviewController::class)->only(['index', 'store', 'destroy']);
        
        // Notifications
        Route::get('notifications', [\App\Http\Controllers\Api\V1\User\NotificationController::class, 'index']);
        Route::post('notifications/{notification}/read', [\App\Http\Controllers\Api\V1\User\NotificationController::class, 'markAsRead']);
        Route::post('notifications/read-all', [\App\Http\Controllers\Api\V1\User\NotificationController::class, 'markAllAsRead']);
        Route::delete('notifications/{notification}', [\App\Http\Controllers\Api\V1\User\NotificationController::class, 'destroy']);
        
        // FCM Tokens
        Route::post('fcm-tokens', [\App\Http\Controllers\Api\V1\FcmTokenController::class, 'store']);
        Route::delete('fcm-tokens', [\App\Http\Controllers\Api\V1\FcmTokenController::class, 'destroy']);
        
        // Favorites
        Route::apiResource('favorites', \App\Http\Controllers\Api\V1\User\FavoriteController::class)->only(['index', 'store', 'destroy']);
        
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});
