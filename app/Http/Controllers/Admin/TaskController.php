<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\Task;
use App\Jobs\SendTaskNotification;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(): \Inertia\Response
    {
        $tasks = Task::query()
            ->select(['id','exercise_id','title','schedule','duration_days','start_date','is_active','sort_order','created_at'])
            ->with(['exercise:id,title'])
            ->latest('created_at')
            ->get();

        return Inertia::render('admin/tasks/index', [
            'tasks' => $tasks,
        ]);
    }

    public function create(): Response
    {
        $exercises = Exercise::orderBy('sort_order')->orderBy('title')->get(['id','title']);

        return Inertia::render('admin/tasks/create', [
            'exercises' => $exercises,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'exercise_id' => ['required','exists:exercises,id'],
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'duration_days' => ['nullable','integer','min:0'],
            'start_date' => ['nullable','date'],
            'is_active' => ['sometimes','boolean'],
            'sort_order' => ['nullable','integer','min:0'],
            'schedule' => ['nullable','string','max:255'],
        ]);

        $data['is_active'] = (bool)($data['is_active'] ?? false);

        $task = Task::create($data);
        Log::info('Task created', [
            'task_id' => $task->id,
            'exercise_id' => $task->exercise_id,
            'is_active' => (bool) $task->is_active,
            'start_date' => $task->start_date,
        ]);

        // Notify users: send now if active and due; schedule if in the future
        if ($task->is_active) {
            $start = $task->start_date ? Carbon::parse($task->start_date) : null;
            if (!$start || $start->isPast()) {
                Log::info('Dispatching SendTaskNotification immediately', ['task_id' => $task->id]);
                dispatch(new SendTaskNotification($task->id));
            } else {
                Log::info('Dispatching SendTaskNotification with delay', [
                    'task_id' => $task->id,
                    'delay_at' => $start->toIso8601String(),
                ]);
                dispatch((new SendTaskNotification($task->id))->delay($start));
            }
        }

        return redirect()->route('admin.dashboard')
            ->with('success', 'Task created successfully.');
    }
}
