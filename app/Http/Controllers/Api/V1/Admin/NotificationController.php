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

            $notification = Notification::create([
                'type' => 'admin_broadcast',
                'ar_title' => $request->ar_title,
                'en_title' => $request->en_title,
                'ar_body' => $request->ar_body,
                'en_body' => $request->en_body,
                'data' => [
                    'is_broadcast' => true,
                ],
                'status' => \App\Enums\NotificationStatus::Pending,
            ]);

            if($status === \App\Enums\NotificationStatus::Sent->value){
                $this->notificationService->sendThroughFcm($notification);
                $notification->update(['status' => \App\Enums\NotificationStatus::Sent]);
            }
            return $this->success(new NotificationResource($notification), 'Notification created successfully as pending.');

    }

    /**
     * Send a pending notification.
     */
    public function send(Notification $notification)
    {
        if ($notification->status === \App\Enums\NotificationStatus::Sent) {
            return $this->error('Notification already sent.', 400);
        }
        $this->notificationService->sendThroughFcm($notification);
        $notification->update(['status' => \App\Enums\NotificationStatus::Sent]);
        return $this->success(new NotificationResource($notification), 'Notification sent successfully.');
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
