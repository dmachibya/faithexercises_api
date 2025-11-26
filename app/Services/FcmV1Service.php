<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmV1Service
{
    private const FCM_SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    public function sendToTopic(string $topic, string $title, string $body, array $data = []): bool
    {
        $message = [
            'topic' => $topic,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $this->stringifyData($data),
        ];
        return $this->send($message);
    }

    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): bool
    {
        if (empty($tokens)) return false;
        $message = [
            'token' => $tokens[0], // HTTP v1 supports one token per message; loop if needed
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $this->stringifyData($data),
        ];
        return $this->send($message);
    }

    private function send(array $message): bool
    {
        $projectId = config('services.fcm_v1.project_id');
        if (!$projectId) {
            Log::warning('FCM v1 project id missing');
            return false;
        }

        $token = $this->getAccessToken();
        if (!$token) {
            Log::error('FCM v1: failed to obtain access token');
            return false;
        }

        $url = sprintf('https://fcm.googleapis.com/v1/projects/%s/messages:send', $projectId);
        $payload = ['message' => $message];

        $res = Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $payload);

        if (!$res->successful()) {
            Log::error('FCM v1 send failed', [
                'status' => $res->status(),
                'body' => $res->body(),
            ]);
            return false;
        }
        Log::info('FCM v1 send success', [
            'status' => $res->status(),
        ]);
        return true;
    }

    private function getAccessToken(): ?string
    {
        // Prefer JSON in env, else file path
        $json = config('services.fcm_v1.credentials_json');
        $file = config('services.fcm_v1.credentials_file');
        try {
            if ($json) {
                $creds = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            } elseif ($file && is_readable($file)) {
                $contents = file_get_contents($file);
                $creds = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
            } else {
                Log::warning('FCM v1 credentials not configured');
                return null;
            }

            // Use google/auth to mint an access token
            // Requires composer package: google/auth
            $scopes = [self::FCM_SCOPE];
            $credentials = new \Google\Auth\Credentials\ServiceAccountCredentials($scopes, $creds);
            $tokenArr = $credentials->fetchAuthToken();
            return $tokenArr['access_token'] ?? null;
        } catch (\Throwable $e) {
            Log::error('FCM v1 token error: '.$e->getMessage());
            return null;
        }
    }

    private function stringifyData(array $data): array
    {
        // FCM data values must be strings
        $out = [];
        foreach ($data as $k => $v) {
            $out[$k] = is_scalar($v) ? (string)$v : json_encode($v);
        }
        return $out;
    }
}
