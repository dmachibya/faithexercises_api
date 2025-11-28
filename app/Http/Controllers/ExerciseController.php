<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    public function index(Request $request)
    {
        $exercises = Exercise::orderBy('sort_order')->orderBy('id')->get();

        $user = $request->user('sanctum');
        if ($user) {
            $exercises->each(function ($exercise) use ($user) {
                $taskIds = $exercise->tasks()->pluck('id');
                
                // Today's progress
                $totalTasks = $taskIds->count();
                $completedToday = 0;
                
                if ($totalTasks > 0) {
                     $completedToday = \App\Models\TaskProgress::whereIn('task_id', $taskIds)
                        ->where('user_id', $user->id)
                        ->whereDate('done_at', now()->toDateString())
                        ->count();
                }

                $exercise->user_progress = [
                    'total_tasks' => $totalTasks,
                    'completed_today' => $completedToday,
                    'percentage' => $totalTasks > 0 ? round(($completedToday / $totalTasks) * 100) : 0,
                    'streak' => $this->calculateStreak($user->id, $taskIds),
                ];
            });
        }

        return $exercises;
    }

    private function calculateStreak($userId, $taskIds)
    {
        if ($taskIds->isEmpty()) return 0;

        $dates = \App\Models\TaskProgress::whereIn('task_id', $taskIds)
            ->where('user_id', $userId)
            ->selectRaw('DATE(done_at) as date')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->pluck('date');

        if ($dates->isEmpty()) return 0;

        $streak = 0;
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        
        // Check if the most recent date is today or yesterday
        $firstDate = $dates->first();
        if ($firstDate !== $today && $firstDate !== $yesterday) {
            return 0;
        }

        $expectedDate = \Carbon\Carbon::parse($firstDate);

        foreach ($dates as $dateStr) {
            $date = \Carbon\Carbon::parse($dateStr);
            
            // Allow for small time diffs or just use isSameDay
            if ($date->isSameDay($expectedDate)) {
                $streak++;
                $expectedDate->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    public function show(Exercise $exercise)
    {
        return $exercise;
    }

    public function tasks(Exercise $exercise)
    {
        return $exercise->tasks()->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();
    }
}
