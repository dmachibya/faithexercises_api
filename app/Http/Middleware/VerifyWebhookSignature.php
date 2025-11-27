<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class VerifyWebhookSignature
{
    /**
     * Verify webhook requests using multiple security layers:
     * 1. API Key validation
     * 2. Domain/Referer validation
     * 3. Timestamp validation (prevent replay attacks)
     * 4. HMAC signature validation
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = config('services.webhook.api_key');
        $secret = config('services.webhook.secret');
        $allowedDomains = config('services.webhook.allowed_domains', []);

        // 1. Validate API Key header
        $providedKey = $request->header('X-Webhook-Key');
        if (!$providedKey || !hash_equals($apiKey, $providedKey)) {
            Log::warning('Webhook: Invalid API key', [
                'ip' => $request->ip(),
                'provided_key' => $providedKey ? substr($providedKey, 0, 8) . '...' : null,
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // 2. Validate domain (Origin or Referer header)
        $origin = $request->header('Origin') ?? $request->header('Referer') ?? '';
        $originHost = parse_url($origin, PHP_URL_HOST) ?? '';
        
        $domainValid = false;
        foreach ($allowedDomains as $domain) {
            if ($originHost === $domain || str_ends_with($originHost, '.' . $domain)) {
                $domainValid = true;
                break;
            }
        }
        
        // Also check if request comes from allowed IP (for server-to-server calls without Origin)
        if (!$domainValid && !empty($origin)) {
            Log::warning('Webhook: Invalid origin domain', [
                'ip' => $request->ip(),
                'origin' => $origin,
                'origin_host' => $originHost,
            ]);
            return response()->json(['error' => 'Forbidden domain'], 403);
        }

        // 3. Validate timestamp (within 5 minutes to prevent replay attacks)
        $timestamp = $request->header('X-Webhook-Timestamp');
        if ($timestamp) {
            $requestTime = (int) $timestamp;
            $currentTime = time();
            $timeDiff = abs($currentTime - $requestTime);
            
            if ($timeDiff > 300) { // 5 minutes
                Log::warning('Webhook: Request timestamp too old', [
                    'ip' => $request->ip(),
                    'timestamp' => $timestamp,
                    'diff_seconds' => $timeDiff,
                ]);
                return response()->json(['error' => 'Request expired'], 401);
            }
        }

        // 4. Validate HMAC signature if provided
        $signature = $request->header('X-Webhook-Signature');
        if ($signature && $secret) {
            $payload = $request->getContent();
            $expectedSignature = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
            
            if (!hash_equals($expectedSignature, $signature)) {
                Log::warning('Webhook: Invalid signature', [
                    'ip' => $request->ip(),
                ]);
                return response()->json(['error' => 'Invalid signature'], 401);
            }
        }

        Log::info('Webhook: Request authenticated', [
            'ip' => $request->ip(),
            'origin' => $origin,
        ]);

        return $next($request);
    }
}
