<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    private string $endpoint = 'https://fcm.googleapis.com/fcm/send';

    public function sendToTopic(string $topic, string $title, string $body, array $data = []): bool
    {
        return $this->send([
            'to' => '/topics/' . $topic,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
        ]);
    }

    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): bool
    {
        if (empty($tokens)) return false;
        return $this->send([
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
        ]);
    }

    private function send(array $payload): bool
    {
        $serverKey = config('services.fcm.server_key');
        if (!$serverKey) {
            Log::warning('FCM legacy server key missing');
            return false;
        }

        $res = Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type' => 'application/json',
        ])->post($this->endpoint, $payload);

        if (!$res->successful()) {
            Log::error('FCM legacy send failed', [
                'status' => $res->status(),
                'body' => $res->body(),
            ]);
            return false;
        }
        Log::info('FCM legacy send success', [
            'status' => $res->status(),
            'body' => $res->body(),
        ]);
        return true;
    }
}
