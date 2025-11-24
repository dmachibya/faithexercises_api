<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\JournalEntry;
use App\Models\Task;
use App\Models\TaskProgress;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function index(): Response
    {
        $users = User::query()
            ->select(['id','name','email','avatar','created_at'])
            ->selectSub('SELECT COUNT(*) FROM task_progresses tp WHERE tp.user_id = users.id', 'tasks_done')
            ->selectSub('SELECT COUNT(*) FROM journal_entries je WHERE je.user_id = users.id', 'journals')
            ->latest('created_at')
            ->get();

        return Inertia::render('admin/users/index', [
            'users' => $users,
        ]);
    }

    public function show(Request $request, User $user)
    {
        $user->load([]);

        $tasksDone = TaskProgress::where('user_id', $user->id)->count();
        $journals = JournalEntry::where('user_id', $user->id)->count();

        $recentActivity = TaskProgress::query()
            ->with(['task:id,title,exercise_id','task.exercise:id,title'])
            ->where('user_id', $user->id)
            ->latest('done_at')
            ->limit(25)
            ->get(['id','task_id','period','period_key','done_at']);

        $recentJournals = JournalEntry::query()
            ->with(['task:id,title,exercise_id','task.exercise:id,title'])
            ->where('user_id', $user->id)
            ->latest('entry_date')
            ->limit(10)
            ->get(['id','task_id','entry_date']);

        $payload = [
            'user' => $user,
            'stats' => [
                'tasksDone' => $tasksDone,
                'journals' => $journals,
            ],
            'recentActivity' => $recentActivity,
            'recentJournals' => $recentJournals,
        ];

        if ($request->wantsJson()) {
            return response()->json($payload);
        }

        return Inertia::render('admin/users/show', $payload);
    }
}
