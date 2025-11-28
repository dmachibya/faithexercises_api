<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomNotification;
use App\Services\FcmV1Service;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomNotificationController extends Controller
{
    public function __construct(
        private FcmV1Service $fcm
    ) {}

    public function index()
    {
        $notifications = CustomNotification::orderBy('created_at', 'desc')->paginate(10);
        return Inertia::render('admin/notifications/index', [
            'notifications' => $notifications
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/notifications/create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'content' => 'required|string',
            'image_url' => 'nullable|string',
        ]);

        $notification = CustomNotification::create(array_merge($validated, [
            'sent_at' => now(),
        ]));

        // Send FCM
        $this->sendFcm($notification);

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Notification created and sent.');
    }

    private function sendFcm(CustomNotification $notification)
    {
        $fcmData = [
            'type' => 'custom_notification',
            'id' => (string) $notification->id,
            'title' => $notification->title,
        ];

        $body = $notification->description ?? substr(strip_tags($notification->content), 0, 100);

        $this->fcm->sendToTopic(
            'all_users',
            $notification->title,
            $body,
            $fcmData,
            $notification->image_url
        );
    }
}
