<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FcmService
{
    public function __construct(protected Messaging $messaging)
    {
    }

    /**
     * Send a notification to a specific user.
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): array
    {
        $tokens = $user->fcmTokens()->pluck('token')->toArray();

        if (empty($tokens)) {
            return ['success' => 0, 'failure' => 0];
        }

        $message = CloudMessage::new()
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        $report = $this->messaging->sendMulticast($message, $tokens);

        // Optional: Handle invalid tokens here (delete them)
        if ($report->hasFailures()) {
            foreach ($report->failures() as $failure) {
                $target = $failure->target();
                Log::error('FCM failure: ' . $target->value());
            }
        }

        return [
            'success' => $report->successes()->count(),
            'failure' => $report->failures()->count(),
        ];
    }

    /**
     * Send a notification to a topic.
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): void
    {
        $message = CloudMessage::new()->toTopic($topic)
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        $this->messaging->send($message);
    }

    /**
     * Subscribe tokens to a topic. 
     */
    public function subscribeToTopic(array $tokens, string $topic): void
    {
        if (empty($tokens)) {
            return;
        }

        $this->messaging->subscribeToTopic($topic, $tokens);
    }

    /**
     * Unsubscribe tokens from a topic.
     */
    public function unsubscribeFromTopic(array $tokens, string $topic): void
    {
        if (empty($tokens)) {
            return;
        }

        $this->messaging->unsubscribeFromTopic($topic, $tokens);
    }
}
