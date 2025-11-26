<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class BibleController extends Controller
{
    protected string $baseUrl = 'https://api.scripture.api.bible/v1';

    protected function client()
    {
        $apiKey = env('API_BIBLE_KEY');
        return Http::withHeaders([
            'api-key' => $apiKey,
            'Accept' => 'application/json',
        ])->baseUrl($this->baseUrl)->timeout(20);
    }

    public function versions(Request $request): JsonResponse
    {
        // Build query only with provided params to avoid invalid inputs
        $query = [];
        foreach (['language', 'abbreviation', 'name', 'page', 'pageSize'] as $key) {
            $value = $request->query($key);
            if (!is_null($value) && $value !== '') {
                $query[$key] = $value;
            }
        }
        if (!is_null($request->query('include_full_details'))) {
            $query['include-full-details'] = $request->boolean('include_full_details') ? 'true' : 'false';
        }

        $res = $this->client()->get('/bibles', $query);
        if ($res->failed()) {
            $body = json_decode($res->body(), true);
            return response()->json([
                'message' => 'Upstream error from API.Bible',
                'endpoint' => '/bibles',
                'sent' => $query,
                'upstream' => $body ?? $res->body(),
            ], $res->status());
        }

        $payload = $res->json();

        // Optional filtering of preferred/popular English versions
        $preferredFlag = $request->boolean('preferred', false) || $request->boolean('popular', false);
        if ($preferredFlag) {
            $preferred = ['KJV','NIV','ESV','NLT','NKJV','NRSV','CSB','NASB','MSG','ASV','AMP','GNT','NIrV'];

            // Helper: case-insensitive match via abbr/id/name
            $matches = function(array $item, string $abbr): bool {
                $abbrLower = strtolower($abbr);
                $id = isset($item['id']) ? strtolower((string)$item['id']) : '';
                $name = isset($item['name']) ? strtolower((string)$item['name']) : '';
                $a = isset($item['abbreviation']) ? strtolower((string)$item['abbreviation']) : '';
                return $a === $abbrLower || $id === $abbrLower || $name === $abbrLower
                    || ($a !== '' && str_contains($a, $abbrLower))
                    || ($name !== '' && str_contains($name, $abbrLower));
            };

            // Normalize list from payload
            $list = null;
            if (is_array($payload)) {
                if (array_is_list($payload)) {
                    $list = $payload;
                } elseif (isset($payload['data']) && is_array($payload['data'])) {
                    $list = $payload['data'];
                }
            }

            if (is_array($list)) {
                // Filter
                $filtered = array_values(array_filter($list, function ($item) use ($preferred, $matches) {
                    foreach ($preferred as $abbr) {
                        if ($matches((array)$item, $abbr)) return true;
                    }
                    return false;
                }));

                // Order by preferred list order
                $rankOf = function($item) use ($preferred, $matches) {
                    $best = PHP_INT_MAX;
                    foreach ($preferred as $i => $abbr) {
                        if ($matches((array)$item, $abbr)) {
                            $best = min($best, $i);
                        }
                    }
                    return $best;
                };
                usort($filtered, function($a, $b) use ($rankOf) {
                    return $rankOf($a) <=> $rankOf($b);
                });

                // Re-wrap into original shape
                if (array_is_list($payload)) {
                    $payload = $filtered;
                } else {
                    $payload['data'] = $filtered;
                }
            }
        }

        return response()->json($payload, $res->status());
    }

    public function books(Request $request): JsonResponse
    {
        $versionId = $request->query('versionId');
        if (!$versionId) {
            return response()->json(['message' => 'versionId is required'], 422);
        }

        $path = "/bibles/{$versionId}/books";
        $res = $this->client()->get($path);
        if ($res->failed()) {
            $body = json_decode($res->body(), true);
            return response()->json([
                'message' => 'Upstream error from API.Bible',
                'endpoint' => $path,
                'upstream' => $body ?? $res->body(),
            ], $res->status());
        }
        return response()->json($res->json(), $res->status());
    }

    public function chapters(Request $request): JsonResponse
    {
        $versionId = $request->query('versionId');
        $bookId = $request->query('bookId');
        if (!$versionId || !$bookId) {
            return response()->json(['message' => 'versionId and bookId are required'], 422);
        }

        $path = "/bibles/{$versionId}/books/{$bookId}/chapters";
        $res = $this->client()->get($path);
        if ($res->failed()) {
            $body = json_decode($res->body(), true);
            return response()->json([
                'message' => 'Upstream error from API.Bible',
                'endpoint' => $path,
                'upstream' => $body ?? $res->body(),
            ], $res->status());
        }
        return response()->json($res->json(), $res->status());
    }

    public function passage(Request $request): JsonResponse
    {
        $versionId = $request->query('versionId');
        $ref = $request->query('ref');
        if (!$versionId || !$ref) {
            return response()->json(['message' => 'versionId and ref are required'], 422);
        }

        $query = [
            'reference' => $ref,
            // common rendering options
            'content-type' => $request->query('content_type', 'html'),
            'include-notes' => $request->boolean('include_notes', false),
            'include-titles' => $request->boolean('include_titles', true),
            'include-chapter-numbers' => $request->boolean('include_chapter_numbers', true),
            'include-verse-numbers' => $request->boolean('include_verse_numbers', true),
            'include-verse-spans' => $request->boolean('include_verse_spans', false),
            'use-org-id' => $request->boolean('use_org_id', false),
        ];

        $path = "/bibles/{$versionId}/passages";
        $res = $this->client()->get($path, $query);
        if ($res->failed()) {
            $body = json_decode($res->body(), true);
            return response()->json([
                'message' => 'Upstream error from API.Bible',
                'endpoint' => $path,
                'sent' => $query,
                'upstream' => $body ?? $res->body(),
            ], $res->status());
        }
        return response()->json($res->json(), $res->status());
    }
}
