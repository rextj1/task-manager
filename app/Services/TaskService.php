<?php

namespace App\Services;

use App\Models\Task;
use BloomRiver\TaskReminders\TaskReminders;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TaskService
{
    public const DEFAULT_CATEGORIES = ['Work', 'Personal', 'Study', 'Shopping', 'Health', 'Finance', 'Other'];

    /** @return array<int, array<string, mixed>> */
    public function getTasks(
        string $search = '',
        string $status = 'all',
        ?string $category = null,
        ?string $priority = null,
        string $sort = 'due_date'
    ): array {
        return $this->filteredQuery($search, $status, $category, $priority)
            ->tap(fn (Builder $query) => $this->applySort($query, $sort))
            ->get()
            ->map(fn (Task $task): array => $this->present($task))
            ->all();
    }

    /** @return Collection<int, Task> */
    public function allTasks(): Collection
    {
        return Task::query()->latest()->get();
    }

    public function createTask(array $data): Task
    {
        $task = Task::query()->create($this->validateTaskData($data));
        app(TaskReminderService::class)->synchronize($task);

        return $task;
    }

    public function findTask(int $id): Task
    {
        return Task::query()->findOrFail($id);
    }

    public function updateTask(int $id, array $data): Task
    {
        $task = $this->findTask($id);
        $task->update($this->validateTaskData($data));
        $task = $task->fresh();
        app(TaskReminderService::class)->synchronize($task);

        return $task;
    }

    public function deleteTask(int $id): void
    {
        $task = $this->findTask($id);
        app(TaskReminders::class)->cancel($id);
        $task->delete();
    }

    public function setCompletion(int $id, bool $completed): Task
    {
        $task = $this->findTask($id);
        $task->update([
            'is_completed' => $completed,
            'completed_at' => $completed ? now() : null,
            'reminder_at' => $completed ? null : $task->reminder_at,
        ]);
        $task = $task->fresh();
        app(TaskReminderService::class)->synchronize($task);

        return $task;
    }

    public function completeTask(int $id): Task
    {
        return $this->setCompletion($id, true);
    }

    public function reopenTask(int $id): Task
    {
        return $this->setCompletion($id, false);
    }

    public function searchTasks(string $search): Collection
    {
        return $this->filteredQuery($search, 'all')->get();
    }

    public function filterByCompletion(bool $completed): Collection
    {
        return Task::query()->where('is_completed', $completed)->latest()->get();
    }

    public function filterByPriority(string $priority): Collection
    {
        return Task::query()->where('priority', $priority)->latest()->get();
    }

    public function filterByCategory(string $category): Collection
    {
        return Task::query()->where('category', $category)->latest()->get();
    }

    public function getSummary(): array
    {
        $today = today()->toDateString();
        $counts = Task::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completed')
            ->selectRaw('SUM(CASE WHEN is_completed = 0 THEN 1 ELSE 0 END) as pending')
            ->selectRaw('SUM(CASE WHEN is_completed = 0 AND due_date IS NOT NULL AND due_date < ? THEN 1 ELSE 0 END) as overdue', [$today])
            ->first();

        $total = (int) ($counts->total ?? 0);
        $completed = (int) ($counts->completed ?? 0);

        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => (int) ($counts->pending ?? 0),
            'overdue' => (int) ($counts->overdue ?? 0),
            'progress' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
        ];
    }

    public function overdueTasks(): Collection
    {
        return Task::query()->where('is_completed', false)->whereDate('due_date', '<', today())->orderBy('due_date')->get();
    }

    public function dueToday(): Collection
    {
        return Task::query()->where('is_completed', false)->whereDate('due_date', today())->orderBy('due_date')->get();
    }

    public function dueTomorrow(): Collection
    {
        return Task::query()->where('is_completed', false)->whereDate('due_date', today()->addDay())->orderBy('due_date')->get();
    }

    public function upcomingTasks(): Collection
    {
        return Task::query()->where('is_completed', false)
            ->whereDate('due_date', '>=', today()->addDay()->toDateString())
            ->whereDate('due_date', '<=', today()->addDays(7)->toDateString())
            ->orderBy('due_date')->get();
    }

    /** @return array<int, string> */
    public function categories(): array
    {
        return Task::query()->whereNotNull('category')->where('category', '<>', '')
            ->distinct()->orderBy('category')->pluck('category')->all();
    }

    private function filteredQuery(string $search, string $status, ?string $category = null, ?string $priority = null): Builder
    {
        $query = Task::query();
        $term = trim($search);

        if ($term !== '') {
            $query->where(fn (Builder $q) => $q->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('category', 'like', "%{$term}%"));
        }

        match ($status) {
            'pending' => $query->where('is_completed', false),
            'completed' => $query->where('is_completed', true),
            'overdue' => $query->where('is_completed', false)->whereNotNull('due_date')->whereDate('due_date', '<', today()),
            'today' => $query->where('is_completed', false)->whereDate('due_date', today()),
            'upcoming' => $query->where('is_completed', false)
                ->whereDate('due_date', '>=', today()->addDay()->toDateString())
                ->whereDate('due_date', '<=', today()->addDays(7)->toDateString()),
            default => null,
        };

        if ($category !== null && $category !== '') {
            $query->where('category', $category);
        }
        if ($priority !== null && $priority !== '') {
            $query->where('priority', $priority);
        }

        return $query;
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'priority' => $query->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")->orderBy('due_date'),
            'oldest' => $query->oldest(),
            'alphabetical' => $query->orderBy('title'),
            'newest' => $query->latest(),
            default => $query->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')->orderBy('due_date')->latest(),
        };
    }

    private function present(Task $task): array
    {
        $supporting = array_filter([
            $task->category,
            ucfirst($task->priority).' priority',
            $task->due_date ? 'Due '.$task->due_date->format('M j, Y') : null,
        ]);

        return [
            'id' => (int) $task->id,
            'title' => $task->title,
            'description' => Str::limit($task->description ?? '', 160),
            'category' => $task->category ?? '',
            'priority' => $task->priority,
            'priority_label' => ucfirst($task->priority),
            'due_date' => $task->due_date?->format('Y-m-d') ?? '',
            'reminder_at' => $task->reminder_at?->format('Y-m-d H:i') ?? '',
            'is_completed' => (bool) $task->is_completed,
            'supporting' => implode(' · ', $supporting),
        ];
    }

    private function validateTaskData(array $data): array
    {
        foreach (['description', 'category', 'due_date', 'reminder_at'] as $field) {
            $data[$field] ??= null;
        }
        foreach (['title', 'description', 'category', 'due_date', 'reminder_at'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = trim($data[$field]);
            }
        }
        if (is_string($data['reminder_at'] ?? null)) {
            $data['reminder_at'] = str_replace('T', ' ', $data['reminder_at']);
        }
        foreach (['description', 'category', 'due_date', 'reminder_at'] as $field) {
            if (($data[$field] ?? null) === '') {
                $data[$field] = null;
            }
        }

        $validated = Validator::make($data, [
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['nullable', 'string', 'max:80'],
            'priority' => ['required', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date_format:Y-m-d'],
            'reminder_at' => ['nullable', 'date_format:Y-m-d H:i'],
        ])->validate();

        $reminder = isset($validated['reminder_at'])
            ? CarbonImmutable::parse($validated['reminder_at'])->toDateTimeString()
            : null;

        return [
            'title' => $validated['title'],
            'description' => $validated['description'] ?: null,
            'category' => $validated['category'] ? ucfirst($validated['category']) : null,
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?: null,
            'reminder_at' => $reminder,
        ];
    }
}
