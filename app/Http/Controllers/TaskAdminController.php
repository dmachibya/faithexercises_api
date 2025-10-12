<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TaskAdminController extends Controller
{
    public function index(Exercise $exercise)
    {
        return $exercise->tasks()->orderBy('sort_order')->get();
    }

    public function store(Request $request, Exercise $exercise)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json([], Response::HTTP_FORBIDDEN);
        }
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'schedule' => 'required|in:single,daily,weekly',
            'duration_days' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);
        $data['exercise_id'] = $exercise->id;
        $task = Task::create($data);
        return response()->json($task, Response::HTTP_CREATED);
    }

    public function update(Request $request, Task $task)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json([], Response::HTTP_FORBIDDEN);
        }
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'schedule' => 'in:single,daily,weekly',
            'duration_days' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);
        $task->update($data);
        return $task;
    }

    public function destroy(Request $request, Task $task)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            return response()->json([], Response::HTTP_FORBIDDEN);
        }
        $task->delete();
        return response()->noContent();
    }
}
