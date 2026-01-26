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
            ->latest()
            ->paginate();

        return $this->successCollection(NotificationResource::collection($notifications));
    }

    /**
     * Create manual notification (broadcast or targeted).
     */
    public function store(StoreAdminNotificationRequest $request)
    {
        if ($request->user_ids) {
            $users = User::whereIn('id', $request->user_ids)->get();
        } else {
            // Broadcast to all users
            $users = User::where('type', 'user')->get();
        }

        $notifications = [];
        foreach ($users as $user) {
            $notifications[] = $this->notificationService->notify(
                $user,
                'admin_broadcast',
                $request->ar_title,
                $request->en_title,
                $request->ar_body,
                $request->en_body
            );
        }

        return $this->success(['count' => count($notifications)], 'Notifications sent successfully.');
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
