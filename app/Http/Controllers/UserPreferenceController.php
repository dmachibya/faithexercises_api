<?php

namespace App\Http\Controllers;

use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserPreferenceController extends Controller
{
    /**
     * Get the authenticated user's preferences.
     */
    public function show(): JsonResponse
    {
        $user = Auth::user();
        $preferences = UserPreference::where('user_id', $user->id)->first();

        if (!$preferences) {
            return response()->json(['message' => 'No preferences found'], 404);
        }

        return response()->json($preferences);
    }

    /**
     * Create or update the authenticated user's preferences.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'learning_frequency' => ['required', Rule::in(UserPreference::getFrequencyOptions())],
            'journaling_frequency' => ['required', Rule::in(UserPreference::getFrequencyOptions())],
            'bible_study_time' => 'required|date_format:H:i',
            'prayer_time' => 'required|date_format:H:i',
            'meditation_time' => 'required|date_format:H:i',
            'learning_cues' => 'array',
            'learning_cues.*' => Rule::in(UserPreference::getCueOptions()),
            'journaling_cues' => 'array',
            'journaling_cues.*' => Rule::in(UserPreference::getCueOptions()),
            'bible_study_reminder' => 'boolean',
            'prayer_reminder' => 'boolean',
            'meditation_reminder' => 'boolean',
        ]);

        $validated['user_id'] = $user->id;

        $preferences = UserPreference::updateOrCreate(
            ['user_id' => $user->id],
            $validated
        );

        return response()->json($preferences, $preferences->wasRecentlyCreated ? 201 : 200);
    }

    /**
     * Update the authenticated user's preferences.
     */
    public function update(Request $request): JsonResponse
    {
        $user = Auth::user();
        $preferences = UserPreference::where('user_id', $user->id)->first();

        if (!$preferences) {
            return response()->json(['message' => 'No preferences found'], 404);
        }

        $validated = $request->validate([
            'learning_frequency' => [Rule::in(UserPreference::getFrequencyOptions())],
            'journaling_frequency' => [Rule::in(UserPreference::getFrequencyOptions())],
            'bible_study_time' => 'date_format:H:i',
            'prayer_time' => 'date_format:H:i',
            'meditation_time' => 'date_format:H:i',
            'learning_cues' => 'array',
            'learning_cues.*' => Rule::in(UserPreference::getCueOptions()),
            'journaling_cues' => 'array',
            'journaling_cues.*' => Rule::in(UserPreference::getCueOptions()),
            'bible_study_reminder' => 'boolean',
            'prayer_reminder' => 'boolean',
            'meditation_reminder' => 'boolean',
        ]);

        $preferences->update($validated);

        return response()->json($preferences);
    }

    /**
     * Delete the authenticated user's preferences.
     */
    public function destroy(): JsonResponse
    {
        $user = Auth::user();
        $preferences = UserPreference::where('user_id', $user->id)->first();

        if (!$preferences) {
            return response()->json(['message' => 'No preferences found'], 404);
        }

        $preferences->delete();

        return response()->json(['message' => 'Preferences deleted successfully']);
    }

    /**
     * Get available options for preferences.
     */
    public function options(): JsonResponse
    {
        return response()->json([
            'frequency_options' => UserPreference::getFrequencyOptions(),
            'cue_options' => UserPreference::getCueOptions(),
        ]);
    }
}
