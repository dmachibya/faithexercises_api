<?php

namespace App\Jobs;

use App\Models\Task;
use App\Services\FcmV1Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SendTaskNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $taskId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $taskId)
    {
        $this->taskId = $taskId;
        // Optionally set a queue name
        // $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(FcmV1Service $fcm): void
    {
        Log::info('SendTaskNotification started', ['task_id' => $this->taskId]);
        $task = Task::with('exercise:id,title')->find($this->taskId);
        if (!$task) {
            Log::warning('SendTaskNotification: task not found', ['task_id' => $this->taskId]);
            return;
        }

        // Guard: only active tasks
        if (!(bool) $task->is_active) {
            Log::info('SendTaskNotification: task inactive, skipping', ['task_id' => $task->id]);
            return;
        }

        // Guard: only if due now or past start_date
        if ($task->start_date) {
            $start = Carbon::parse($task->start_date);
            if ($start->isFuture()) {
                Log::info('SendTaskNotification: start_date in future, skipping for now', [
                    'task_id' => $task->id,
                    'start_date' => $task->start_date,
                ]);
                return;
            }
        }

        $exerciseTitle = optional($task->exercise)->title ?? 'Faith Exercise';
        $title = $task->title;
        $body = trim(($exerciseTitle ? ($exerciseTitle . ': ') : '') . ($task->description ?? ''));

        $data = [
            'type' => 'task',
            'task_id' => (string) $task->id,
            'exercise_id' => (string) $task->exercise_id,
            'schedule' => (string) ($task->schedule ?? ''),
        ];

        // Broadcast to all users via topic. Alternatively, gather tokens and call sendToTokens().
        $ok = $fcm->sendToTopic('all_users', $title, $body, $data);
        if ($ok) {
            Log::info('SendTaskNotification: FCM v1 topic send ok', [
                'task_id' => $task->id,
                'topic' => 'all_users',
            ]);
        } else {
            Log::error('SendTaskNotification: FCM v1 topic send failed', [
                'task_id' => $task->id,
                'topic' => 'all_users',
            ]);
        }
    }
}
