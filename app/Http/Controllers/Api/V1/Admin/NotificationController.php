<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Notification\StoreAdminNotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $notificationService)
    {
    }

    /**
     * List all notifications.
     */
    public function index()
    {
        $notifications = Notification::with('user')
            ->where('type', 'admin_broadcast')
            ->latest()
            ->paginate();

        return $this->successCollection(NotificationResource::collection($notifications));
    }

    /**
     * Create manual notification (broadcast or targeted).
     */
    /**
     * Create manual notification (broadcast or targeted).
     */
    public function store(StoreAdminNotificationRequest $request)
    {
        $status = $request->status ?? \App\Enums\NotificationStatus::Pending->value;

        // If status is pending, just create the notification record
        if ($status === \App\Enums\NotificationStatus::Pending->value) {
            $notification = Notification::create([
                'type' => 'admin_broadcast',
                'ar_title' => $request->ar_title,
                'en_title' => $request->en_title,
                'ar_body' => $request->ar_body,
                'en_body' => $request->en_body,
                'data' => [
                    'user_ids' => $request->user_ids, // Store target users if any
                    'is_broadcast' => empty($request->user_ids),
                ],
                'status' => \App\Enums\NotificationStatus::Pending,
            ]);

            return $this->success(new NotificationResource($notification), 'Notification created successfully as pending.');
        }

        // If status is sent, send immediately
        $this->sendNotification($request->validated());

        return $this->success(null, 'Notifications sent successfully.');
    }

    /**
     * Send a pending notification.
     */
    public function send(Notification $notification)
    {
        if ($notification->status === \App\Enums\NotificationStatus::Sent) {
            return $this->error('Notification already sent.', 400);
        }

        $data = [
            'ar_title' => $notification->ar_title,
            'en_title' => $notification->en_title,
            'ar_body' => $notification->ar_body,
            'en_body' => $notification->en_body,
            'user_ids' => $notification->data['user_ids'] ?? null,
        ];

        $this->sendNotification($data);

        $notification->update(['status' => \App\Enums\NotificationStatus::Sent]);

        return $this->success(new NotificationResource($notification), 'Notification sent successfully.');
    }

    protected function sendNotification(array $data)
    {
        $users = [];
        if (!empty($data['user_ids'])) {
            $users = User::whereIn('id', $data['user_ids'])->get();
            foreach ($users as $user) {
            $this->notificationService->notify(
                $user,
                'admin_broadcast',
                $data['ar_title'],
                $data['en_title'],
                $data['ar_body'],
                $data['en_body']
            );
        }
        }
        else {
             $this->notificationService->notify(
                null,
                'admin_broadcast',
                $data['ar_title'],
                $data['en_title'],
                $data['ar_body'],
                $data['en_body']
            );
        }
        
    }

    /**
     * View a notification.
     */
    public function show(Notification $notification)
    {
        return $this->success(new NotificationResource($notification->load('user')));
    }

    /**
     * Delete a notification.
     */
    public function destroy(Notification $notification)
    {
        $notification->delete();

        return $this->success(null, 'Notification deleted.');
    }
}
