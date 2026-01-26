<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\FcmToken\DeleteFcmTokenRequest;
use App\Http\Requests\FcmToken\StoreFcmTokenRequest;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    /**
     * Register an FCM token.
     */
    public function store(StoreFcmTokenRequest $request)
    {

        auth()->user()->fcmTokens()->updateOrCreate(
            ['token' => $request->token],
            ['device_type' => $request->device_type]
        );

        return $this->success(null, 'FCM token registered.');
    }

    /**
     * Remove an FCM token.
     */
    public function destroy(DeleteFcmTokenRequest $request)
    {

        auth()->user()->fcmTokens()->where('token', $request->token)->delete();

        return $this->success(null, 'FCM token removed.');
    }
}
