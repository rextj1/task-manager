<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TaskService
{
    public function getTasks(
        string $search = '',
        string $status = 'all'
    ): array {
        $query = Task::query();

        if (trim($search) !== '') {
            $term = trim($search);

            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('category', 'like', "%{$term}%");
            });
        }

        if ($status === 'pending') {
            $query->where('is_completed', false);
        }

        if ($status === 'completed') {
            $query->where('is_completed', true);
        }

        return $query
            ->orderBy('is_completed')
            ->orderByRaw(
                "CASE priority
                    WHEN 'high' THEN 1
                    WHEN 'medium' THEN 2
                    ELSE 3
                END"
            )
            ->orderByRaw(
                'CASE WHEN due_date IS NULL THEN 1 ELSE 0 END'
            )
            ->orderBy('due_date')
            ->latest()
            ->get()
            ->map(function (Task $task): array {
                $details = [];

                if ($task->category) {
                    $details[] = $task->category;
                }

                $details[] = ucfirst($task->priority ?? 'medium') . ' priority';

                if ($task->due_date) {
                    $details[] = 'Due ' . $task->due_date->format('M j, Y');
                }

                return [
                    'id' => (int) $task->id,
                    'title' => $task->title,
                    'description' => $task->description ?? '',
                    'category' => $task->category ?? '',
                    'priority' => $task->priority ?? 'medium',
                    'priority_label' => ucfirst($task->priority ?? 'medium'),
                    'due_date' => $task->due_date?->format('Y-m-d') ?? '',
                    'is_completed' => (bool) $task->is_completed,
                    'supporting' => implode(' · ', $details),
                ];
            })
            ->all();
    }

    public function getSummary(): array
    {
        $total = Task::count();

        $completed = Task::where('is_completed', true)->count();

        $pending = $total - $completed;

        $overdue = Task::where('is_completed', false)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->count();

        $progress = $total > 0
            ? (int) round(($completed / $total) * 100)
            : 0;

        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => $pending,
            'overdue' => $overdue,
            'progress' => $progress,
        ];
    }

    public function createTask(array $data): Task
    {
        return Task::create($this->validateTaskData($data));
    }

    public function updateTask(int $id, array $data): Task
    {
        $task = $this->findTask($id);

        $task->update($this->validateTaskData($data));

        return $task->fresh();
    }

    public function findTask(int $id): Task
    {
        return Task::findOrFail($id);
    }

    public function setCompletion(int $id, bool $completed): void
    {
        $task = $this->findTask($id);

        $task->update([
            'is_completed' => $completed,
        ]);
    }

    public function deleteTask(int $id): void
    {
        $this->findTask($id)->delete();
    }

    private function validateTaskData(array $data): array
    {
        $data['title'] = trim((string) ($data['title'] ?? ''));
        $data['description'] = trim((string) ($data['description'] ?? ''));
        $data['category'] = trim((string) ($data['category'] ?? ''));
        $data['due_date'] = trim((string) ($data['due_date'] ?? ''));

        $validated = Validator::make($data, [
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['nullable', 'string', 'max:80'],
            'priority' => ['required', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date_format:Y-m-d'],
        ])->validate();

        return [
            'title' => $validated['title'],
            'description' => $validated['description'] ?: null,
            'category' => $validated['category']
                ? ucfirst($validated['category'])
                : null,
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?: null,
        ];
    }
}