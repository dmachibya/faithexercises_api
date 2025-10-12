<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TaskProgressController extends Controller
{
    protected function makePeriodKey(string $period, ?string $date): string
    {
        if ($period === 'single') {
            return 'single';
        }
        $d = $date ? Carbon::parse($date) : now();
        if ($period === 'daily') {
            return $d->toDateString();
        }
        if ($period === 'weekly') {
            return $d->format('o-\WW');
        }
        return 'single';
    }

    protected function assertWithinDuration(Task $task, string $period, ?string &$date): void
    {
        if ($period !== 'daily') {
            return; // only daily tasks are constrained by duration
        }
        if ($task->duration_days && $task->start_date) {
            $start = Carbon::parse($task->start_date)->startOfDay();
            $end = (clone $start)->addDays(((int) $task->duration_days) - 1)->endOfDay();
            $target = $date ? Carbon::parse($date) : now();
            // Default date to today if not provided
            if (!$date) {
                $date = $target->toDateString();
            }
            if ($target->lt($start) || $target->gt($end)) {
                abort(422, 'Date is outside of the allowed duration window for this daily task.');
            }
        }
    }

    public function toggle(Request $request, Task $task)
    {
        $data = $request->validate([
            'period' => 'required|in:single,daily,weekly',
            'date' => 'nullable|date',
        ]);
        $user = $request->user();
        $this->assertWithinDuration($task, $data['period'], $data['date']);
        $key = $this->makePeriodKey($data['period'], $data['date'] ?? null);
        $progress = TaskProgress::where('user_id', $user->id)
            ->where('task_id', $task->id)
            ->where('period', $data['period'])
            ->where('period_key', $key)
            ->first();
        if ($progress) {
            $progress->delete();
            return ['done' => false];
        }
        TaskProgress::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'period' => $data['period'],
            'period_key' => $key,
            'done_at' => now(),
        ]);
        return ['done' => true];
    }

    public function show(Request $request, Task $task)
    {
        $data = $request->validate([
            'period' => 'required|in:single,daily,weekly',
            'date' => 'nullable|date',
        ]);
        $user = $request->user();
        $this->assertWithinDuration($task, $data['period'], $data['date']);
        $key = $this->makePeriodKey($data['period'], $data['date'] ?? null);
        $exists = TaskProgress::where('user_id', $user->id)
            ->where('task_id', $task->id)
            ->where('period', $data['period'])
            ->where('period_key', $key)
            ->exists();
        return ['done' => $exists];
    }
}
