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
        $res = $this->client()->get('/bibles', [
            // forward a few optional filters if provided
            'language' => $request->query('language'),
            'abbreviation' => $request->query('abbreviation'),
            'name' => $request->query('name'),
            'include-full-details' => $request->boolean('include_full_details', false),
            'page' => $request->query('page'),
            'pageSize' => $request->query('pageSize'),
        ]);

        return response()->json($res->json(), $res->status());
    }

    public function books(Request $request): JsonResponse
    {
        $versionId = $request->query('versionId');
        if (!$versionId) {
            return response()->json(['message' => 'versionId is required'], 422);
        }

        $res = $this->client()->get("/bibles/{$versionId}/books");
        return response()->json($res->json(), $res->status());
    }

    public function chapters(Request $request): JsonResponse
    {
        $versionId = $request->query('versionId');
        $bookId = $request->query('bookId');
        if (!$versionId || !$bookId) {
            return response()->json(['message' => 'versionId and bookId are required'], 422);
        }

        $res = $this->client()->get("/bibles/{$versionId}/books/{$bookId}/chapters");
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

        $res = $this->client()->get("/bibles/{$versionId}/passages", $query);
        return response()->json($res->json(), $res->status());
    }
}
