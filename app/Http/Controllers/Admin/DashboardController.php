<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\JournalEntry;
use App\Models\Task;
use App\Models\TaskProgress;
use App\Models\User;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $now = now();
        $sevenDaysAgo = $now->copy()->subDays(7);
        $monthStart = $now->copy()->startOfMonth();

        $totals = [
            'users' => User::count(),
            'exercises' => Exercise::count(),
            'tasks' => Task::count(),
            'taskCompletions' => TaskProgress::count(),
            'journalEntries' => JournalEntry::count(),
            'activeUsers7d' => TaskProgress::where('done_at', '>=', $sevenDaysAgo)->distinct('user_id')->count('user_id'),
            'journalers' => JournalEntry::distinct('user_id')->count('user_id'),
            'taskCompletionsThisMonth' => TaskProgress::where('done_at', '>=', $monthStart)->count(),
            'journalEntriesThisMonth' => JournalEntry::where('entry_date', '>=', $monthStart)->count(),
        ];

        $usersByExerciseReads = Exercise::query()
            ->select(['exercises.id', 'exercises.title'])
            ->selectSub(
                "SELECT COUNT(DISTINCT tp.user_id) FROM tasks t JOIN task_progresses tp ON tp.task_id = t.id WHERE t.exercise_id = exercises.id",
                'readers_count'
            )
            ->orderByDesc('readers_count')
            ->limit(10)
            ->get();

        $topJournalers = JournalEntry::query()
            ->selectRaw('user_id, count(*) as entries_count')
            ->groupBy('user_id')
            ->orderByDesc('entries_count')
            ->limit(10)
            ->get();

        $topDoers = TaskProgress::query()
            ->selectRaw('user_id, count(*) as done_count')
            ->groupBy('user_id')
            ->orderByDesc('done_count')
            ->limit(10)
            ->get();

        $recentUsers = User::query()
            ->select(['id','name','email','avatar','created_at'])
            ->selectSub(
                'SELECT COUNT(*) FROM task_progresses tp WHERE tp.user_id = users.id',
                'tasks_done_count'
            )
            ->selectSub(
                'SELECT COUNT(*) FROM journal_entries je WHERE je.user_id = users.id',
                'journals_count'
            )
            ->latest('created_at')
            ->limit(8)
            ->get();

        return Inertia::render('admin/dashboard', [
            'totals' => $totals,
            'usersByExerciseReads' => $usersByExerciseReads,
            'topJournalers' => $topJournalers,
            'topDoers' => $topDoers,
            'recentUsers' => $recentUsers,
            'generatedAt' => $now->toDateTimeString(),
        ]);
    }
}
