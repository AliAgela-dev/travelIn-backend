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
    public function __construct(protected \App\Services\FcmService $fcmService)
    {
    }

    /**
     * Register an FCM token.
     */
    public function store(StoreFcmTokenRequest $request)
    {
        auth()->user()->fcmTokens()->updateOrCreate(
            ['token' => $request->token],
            ['device_type' => $request->device_type]
        );

        // Auto-subscribe to default topics
        $topics = ['admin_broadcast'];
        
        // Add role-based topic
        $user = auth()->user();
        if ($user->type) {
            $topics[] = 'role_' . $user->type->value;
        }
        
        foreach ($topics as $topic) {
            $this->fcmService->subscribeToTopic([$request->token], $topic);
        }

        return $this->success(null, 'FCM token registered and subscribed.');
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
