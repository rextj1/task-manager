<?php

namespace App\Services;

use App\Models\Task;
use BloomRiver\TaskReminders\TaskReminders;
use Illuminate\Support\Facades\Log;

class TaskReminderService
{
    public function synchronizeAll(): void
    {
        Task::query()->whereNotNull('reminder_at')->get()->each(fn (Task $task) => $this->synchronize($task));
    }

    public function synchronize(Task $task): void
    {
        $reminders = app(TaskReminders::class);

        if ($task->is_completed || $task->reminder_at === null || ! app(SettingsService::class)->notificationsEnabled()) {
            $reminders->cancel((int) $task->id);

            return;
        }

        try {
            $reminders->requestPermission();
            if (! $reminders->schedule((int) $task->id, $task->title, $task->reminder_at)) {
                Log::warning('Task reminder was not scheduled by the native platform.', ['task_id' => $task->id]);
            }
        } catch (\Throwable $exception) {
            Log::warning('Task reminder scheduling failed.', [
                'task_id' => $task->id,
                'exception_type' => $exception::class,
            ]);
        }
    }
}
