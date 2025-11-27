<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmV1Service
{
    private const FCM_SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    public function sendToTopic(string $topic, string $title, string $body, array $data = []): bool
    {
        Log::info('FCM v1: sendToTopic called', [
            'topic' => $topic,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);

        $message = [
            'topic' => $topic,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $this->stringifyData($data),
        ];

        Log::info('FCM v1: Prepared message payload', ['message' => $message]);

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
        Log::info('FCM v1: send() method called');

        $projectId = config('services.fcm_v1.project_id');
        Log::info('FCM v1: Project ID check', ['project_id' => $projectId ? 'SET ('. substr($projectId, 0, 10) .'...)' : 'NOT SET']);
        
        if (!$projectId) {
            Log::warning('FCM v1 project id missing');
            return false;
        }

        Log::info('FCM v1: Attempting to get access token...');
        $token = $this->getAccessToken();
        
        if (!$token) {
            Log::error('FCM v1: failed to obtain access token');
            return false;
        }
        Log::info('FCM v1: Access token obtained successfully', ['token_preview' => substr($token, 0, 20) . '...']);

        $url = sprintf('https://fcm.googleapis.com/v1/projects/%s/messages:send', $projectId);
        $payload = ['message' => $message];

        Log::info('FCM v1: Sending HTTP request', [
            'url' => $url,
            'payload' => $payload,
        ]);

        $res = Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $payload);

        Log::info('FCM v1: HTTP response received', [
            'status' => $res->status(),
            'successful' => $res->successful(),
        ]);

        if (!$res->successful()) {
            Log::error('FCM v1 send failed', [
                'status' => $res->status(),
                'body' => $res->body(),
            ]);
            return false;
        }
        Log::info('FCM v1 send success', [
            'status' => $res->status(),
            'response_body' => $res->body(),
        ]);
        return true;
    }

    private function getAccessToken(): ?string
    {
        Log::info('FCM v1: getAccessToken() called');

        // Prefer JSON in env, else file path
        $json = config('services.fcm_v1.credentials_json');
        $file = config('services.fcm_v1.credentials_file');

        Log::info('FCM v1: Credentials check', [
            'json_set' => $json ? 'YES (' . strlen($json) . ' chars)' : 'NO',
            'file_set' => $file ? 'YES (' . $file . ')' : 'NO',
            'file_readable' => $file ? (is_readable($file) ? 'YES' : 'NO') : 'N/A',
        ]);

        try {
            if ($json) {
                Log::info('FCM v1: Using JSON credentials from env');
                $creds = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
                Log::info('FCM v1: JSON parsed successfully', [
                    'project_id' => $creds['project_id'] ?? 'NOT SET',
                    'client_email' => $creds['client_email'] ?? 'NOT SET',
                ]);
            } elseif ($file && is_readable($file)) {
                Log::info('FCM v1: Using credentials file');
                $contents = file_get_contents($file);
                $creds = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
                Log::info('FCM v1: File parsed successfully', [
                    'project_id' => $creds['project_id'] ?? 'NOT SET',
                    'client_email' => $creds['client_email'] ?? 'NOT SET',
                ]);
            } else {
                Log::warning('FCM v1 credentials not configured - neither JSON nor file available');
                return null;
            }

            // Use google/auth to mint an access token
            // Requires composer package: google/auth
            Log::info('FCM v1: Creating ServiceAccountCredentials...');
            $scopes = [self::FCM_SCOPE];
            $credentials = new \Google\Auth\Credentials\ServiceAccountCredentials($scopes, $creds);
            
            Log::info('FCM v1: Fetching auth token...');
            $tokenArr = $credentials->fetchAuthToken();
            
            Log::info('FCM v1: Token fetch result', [
                'has_access_token' => isset($tokenArr['access_token']),
                'token_type' => $tokenArr['token_type'] ?? 'unknown',
                'expires_in' => $tokenArr['expires_in'] ?? 'unknown',
            ]);

            return $tokenArr['access_token'] ?? null;
        } catch (\Throwable $e) {
            Log::error('FCM v1 token error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
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
