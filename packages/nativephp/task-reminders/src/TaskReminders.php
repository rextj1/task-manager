<?php

namespace BloomRiver\TaskReminders;

use Carbon\CarbonInterface;

class TaskReminders
{
    public function requestPermission(): bool
    {
        return $this->call('TaskReminder.RequestPermission', [])['requested'] ?? false;
    }

    public function schedule(int $taskId, string $title, CarbonInterface $at): bool
    {
        return $this->call('TaskReminder.Schedule', [
            'task_id' => $taskId,
            'title' => $title,
            'trigger_at' => $at->getTimestamp() * 1000,
        ])['scheduled'] ?? false;
    }

    public function cancel(int $taskId): bool
    {
        return $this->call('TaskReminder.Cancel', ['task_id' => $taskId])['cancelled'] ?? false;
    }

    private function call(string $function, array $parameters): array
    {
        if (! function_exists('nativephp_call')) {
            return [];
        }

        $response = nativephp_call($function, json_encode($parameters, JSON_THROW_ON_ERROR));
        $decoded = json_decode($response ?: '{}', true);

        return is_array($decoded['data'] ?? null) ? $decoded['data'] : [];
    }
}
