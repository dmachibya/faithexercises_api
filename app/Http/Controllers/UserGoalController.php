<?php

namespace App\Http\Controllers;

use App\Models\UserGoal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserGoalController extends Controller
{
    /**
     * Get all goals for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = UserGoal::where('user_id', $user->id);

        // Filter by type if provided
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        // Filter by category if provided
        if ($request->has('category')) {
            $query->ofCategory($request->category);
        }

        // Filter by completion status if provided
        if ($request->has('completed')) {
            if ($request->boolean('completed')) {
                $query->completed();
            } else {
                $query->active();
            }
        }

        // Filter overdue goals if requested
        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        $goals = $query->orderBy('target_date', 'asc')->get();

        return response()->json($goals);
    }

    /**
     * Create a new goal for the authenticated user.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => ['required', Rule::in(UserGoal::getTypeOptions())],
            'category' => ['required', Rule::in(UserGoal::getCategoryOptions())],
            'target_date' => 'required|date|after:today',
            'progress' => 'integer|min:0|max:100',
            'is_completed' => 'boolean',
        ]);

        $validated['user_id'] = $user->id;
        $validated['progress'] = $validated['progress'] ?? 0;
        $validated['is_completed'] = $validated['is_completed'] ?? false;

        $goal = UserGoal::create($validated);

        return response()->json($goal, 201);
    }

    /**
     * Get a specific goal for the authenticated user.
     */
    public function show(int $id): JsonResponse
    {
        $user = Auth::user();
        $goal = UserGoal::where('user_id', $user->id)->findOrFail($id);

        return response()->json($goal);
    }

    /**
     * Update a specific goal for the authenticated user.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $goal = UserGoal::where('user_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'type' => [Rule::in(UserGoal::getTypeOptions())],
            'category' => [Rule::in(UserGoal::getCategoryOptions())],
            'target_date' => 'date|after:today',
            'progress' => 'integer|min:0|max:100',
            'is_completed' => 'boolean',
        ]);

        // Auto-complete goal if progress reaches 100%
        if (isset($validated['progress']) && $validated['progress'] >= 100) {
            $validated['is_completed'] = true;
        }

        $goal->update($validated);

        return response()->json($goal);
    }

    /**
     * Delete a specific goal for the authenticated user.
     */
    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();
        $goal = UserGoal::where('user_id', $user->id)->findOrFail($id);

        $goal->delete();

        return response()->json(['message' => 'Goal deleted successfully']);
    }

    /**
     * Update the progress of a specific goal.
     */
    public function updateProgress(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $goal = UserGoal::where('user_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'progress' => 'required|integer|min:0|max:100',
            'is_completed' => 'boolean',
        ]);

        // Auto-complete goal if progress reaches 100%
        if ($validated['progress'] >= 100) {
            $validated['is_completed'] = true;
        }

        $goal->update($validated);

        return response()->json($goal);
    }

    /**
     * Toggle the completion status of a specific goal.
     */
    public function toggleCompletion(int $id): JsonResponse
    {
        $user = Auth::user();
        $goal = UserGoal::where('user_id', $user->id)->findOrFail($id);

        $goal->update([
            'is_completed' => !$goal->is_completed,
            'progress' => $goal->is_completed ? 0 : 100,
        ]);

        return response()->json($goal);
    }

    /**
     * Get available options for goals.
     */
    public function options(): JsonResponse
    {
        return response()->json([
            'type_options' => UserGoal::getTypeOptions(),
            'category_options' => UserGoal::getCategoryOptions(),
        ]);
    }

    /**
     * Get goal statistics for the authenticated user.
     */
    public function statistics(): JsonResponse
    {
        $user = Auth::user();

        $totalGoals = UserGoal::where('user_id', $user->id)->count();
        $completedGoals = UserGoal::where('user_id', $user->id)->completed()->count();
        $activeGoals = UserGoal::where('user_id', $user->id)->active()->count();
        $overdueGoals = UserGoal::where('user_id', $user->id)->overdue()->count();

        $completionRate = $totalGoals > 0 ? round(($completedGoals / $totalGoals) * 100, 2) : 0;

        // Goals by type
        $goalsByType = UserGoal::where('user_id', $user->id)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        // Goals by category
        $goalsByCategory = UserGoal::where('user_id', $user->id)
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category');

        return response()->json([
            'total_goals' => $totalGoals,
            'completed_goals' => $completedGoals,
            'active_goals' => $activeGoals,
            'overdue_goals' => $overdueGoals,
            'completion_rate' => $completionRate,
            'goals_by_type' => $goalsByType,
            'goals_by_category' => $goalsByCategory,
        ]);
    }
}
