<?php

namespace App\Http\Controllers;

use App\Services\FcmV1Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BlogNotificationController extends Controller
{
    public function __construct(
        private FcmV1Service $fcm
    ) {}

    /**
     * Receive blog post notification from WordPress and send FCM push
     */
    public function notify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'url' => 'required|url',
            'image_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            Log::warning('BlogNotification: Validation failed', [
                'errors' => $validator->errors()->toArray(),
            ]);
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        Log::info('BlogNotification: Received blog post', [
            'post_id' => $data['post_id'],
            'title' => $data['title'],
        ]);

        // Prepare notification
        $title = 'New Article';
        $body = $data['title'];
        
        // Truncate excerpt for notification body if available
        if (!empty($data['excerpt'])) {
            $body = $data['title'] . ': ' . mb_substr(strip_tags($data['excerpt']), 0, 100);
        }

        // FCM data payload for navigation
        $fcmData = [
            'type' => 'blog',
            'post_id' => (string) $data['post_id'],
            'url' => $data['url'],
            'title' => $data['title'],
            'image_url' => $data['image_url'] ?? '',
        ];

        // Send to all_users topic
        $success = $this->fcm->sendToTopic('all_users', $title, $body, $fcmData);

        if ($success) {
            Log::info('BlogNotification: FCM sent successfully', [
                'post_id' => $data['post_id'],
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Notification sent',
            ]);
        }

        Log::error('BlogNotification: FCM send failed', [
            'post_id' => $data['post_id'],
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Failed to send notification',
        ], 500);
    }
}
