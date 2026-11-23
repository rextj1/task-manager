<?php

namespace App\NativeComponents;

use App\Services\SettingsService;
use App\Services\TaskService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;
use Throwable;

class Home extends NativeComponent
{
    public string $title = '';

    public string $description = '';

    public string $category = '';

    public string $dueDate = '';

    public string $reminderAt = '';

    public string $priority = 'medium';

    public ?int $editingTaskId = null;

    public ?int $pendingDeleteId = null;

    public bool $formOpen = false;

    public string $search = '';

    public string $statusFilter = 'All';

    public string $categoryFilter = 'All categories';

    public string $priorityFilter = 'All priorities';

    public string $sort = 'Due date';

    public bool $settingsOpen = false;

    public string $defaultPriority = 'medium';

    public string $themePreference = 'system';

    public bool $notificationsEnabled = true;

    public array $tasks = [];

    public array $categoryOptions = [];

    public int $totalTasks = 0;

    public int $completedTasks = 0;

    public int $pendingTasks = 0;

    public int $overdueTasks = 0;

    public int $progress = 0;

    public string $formMessage = '';

    public function mount(): void
    {
        $settings = app(SettingsService::class);
        $this->defaultPriority = $settings->get('default_priority', 'medium');
        $this->themePreference = $settings->get('theme_preference', 'system');
        $this->notificationsEnabled = $settings->notificationsEnabled();
        $settings->applyTheme($this->themePreference);
        $this->categoryOptions = array_values(array_unique(array_merge(
            ['All categories'], TaskService::DEFAULT_CATEGORIES, app(TaskService::class)->categories()
        )));
        $this->refreshScreen();
    }

    public function onResume(): void
    {
        $this->refreshScreen();
    }

    public function updatedSearch(string $value): void
    {
        $this->refreshTasks();
    }

    public function openNewTask(): void
    {
        $this->clearForm();
        $this->formOpen = true;
    }

    public function closeForm(): void
    {
        $this->formOpen = false;
        $this->clearForm();
    }

    public function saveTask(): void
    {
        $service = app(TaskService::class);
        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'due_date' => $this->dueDate,
            'reminder_at' => str_replace('T', ' ', $this->reminderAt),
            'priority' => $this->priority,
        ];

        try {
            if ($this->editingTaskId !== null) {
                $service->updateTask($this->editingTaskId, $data);
                $this->formMessage = 'Task updated successfully.';
            } else {
                $service->createTask($data);
                $this->formMessage = 'Task created successfully.';
            }

            $this->formOpen = false;
            $this->clearForm();
            $this->refreshScreen();
        } catch (ValidationException $e) {
            $this->formMessage = collect($e->errors())->flatten()->first() ?? 'Please check your task details.';
        } catch (Throwable $e) {
            Log::error('Task save failed', ['exception_type' => $e::class]);
            $this->formMessage = 'Unable to save this task. Your entered details are still here.';
        }
    }

    public function editTask(int $id): void
    {
        try {
            $task = app(TaskService::class)->findTask($id);
            $this->editingTaskId = $task->id;
            $this->title = $task->title;
            $this->description = $task->description ?? '';
            $this->category = $task->category ?? '';
            $this->priority = $task->priority ?? 'medium';
            $this->dueDate = $task->due_date?->format('Y-m-d') ?? '';
            $this->reminderAt = $task->reminder_at?->format('Y-m-d\TH:i') ?? '';
            $this->formMessage = '';
            $this->formOpen = true;
        } catch (ModelNotFoundException) {
            $this->formMessage = 'That task is no longer available.';
            $this->refreshScreen();
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->pendingDeleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->pendingDeleteId = null;
    }

    public function deleteTask(): void
    {
        if ($this->pendingDeleteId === null) {
            return;
        }

        try {
            app(TaskService::class)->deleteTask($this->pendingDeleteId);
            if ($this->editingTaskId === $this->pendingDeleteId) {
                $this->closeForm();
            }
            $this->formMessage = 'Task deleted.';
        } catch (ModelNotFoundException) {
            $this->formMessage = 'That task is already unavailable.';
        } catch (Throwable $e) {
            Log::error('Task deletion failed', ['exception_type' => $e::class]);
            $this->formMessage = 'Unable to delete this task.';
        } finally {
            $this->pendingDeleteId = null;
            $this->refreshScreen();
        }
    }

    public function toggleTask(int $id): void
    {
        try {
            $task = app(TaskService::class)->findTask($id);
            app(TaskService::class)->setCompletion($id, ! $task->is_completed);
            $this->refreshScreen();
        } catch (ModelNotFoundException) {
            $this->formMessage = 'That task is no longer available.';
            $this->refreshScreen();
        } catch (Throwable $e) {
            Log::error('Task completion update failed', ['exception_type' => $e::class]);
            $this->formMessage = 'Unable to update this task.';
        }
    }

    public function setLowPriority(): void
    {
        $this->priority = 'low';
    }

    public function setMediumPriority(): void
    {
        $this->priority = 'medium';
    }

    public function setHighPriority(): void
    {
        $this->priority = 'high';
    }

    public function clearDueDate(): void
    {
        $this->dueDate = '';
    }

    public function clearReminder(): void
    {
        $this->reminderAt = '';
    }

    public function showAllTasks(): void
    {
        $this->statusFilter = 'All';
        $this->refreshTasks();
    }

    public function showPendingTasks(): void
    {
        $this->statusFilter = 'Pending';
        $this->refreshTasks();
    }

    public function showCompletedTasks(): void
    {
        $this->statusFilter = 'Completed';
        $this->refreshTasks();
    }

    public function showOverdueTasks(): void
    {
        $this->statusFilter = 'Overdue';
        $this->refreshTasks();
    }

    public function showTodayTasks(): void
    {
        $this->statusFilter = 'Today';
        $this->refreshTasks();
    }

    public function showUpcomingTasks(): void
    {
        $this->statusFilter = 'Upcoming';
        $this->refreshTasks();
    }

    public function updatedStatusFilter(string $value): void
    {
        $this->refreshTasks();
    }

    public function updatedCategoryFilter(string $value): void
    {
        $this->refreshTasks();
    }

    public function updatedPriorityFilter(string $value): void
    {
        $this->refreshTasks();
    }

    public function updatedSort(string $value): void
    {
        $this->refreshTasks();
    }

    public function openSettings(): void
    {
        $this->settingsOpen = true;
    }

    public function closeSettings(): void
    {
        $this->settingsOpen = false;
    }

    public function saveSettings(): void
    {
        try {
            app(SettingsService::class)->save([
                'default_priority' => $this->defaultPriority,
                'theme_preference' => $this->themePreference,
                'notifications_enabled' => $this->notificationsEnabled,
            ]);
            $this->formMessage = 'Preferences saved on this device.';
        } catch (Throwable $e) {
            Log::error('Settings save failed', ['exception_type' => $e::class]);
            $this->formMessage = 'Unable to save preferences.';
        }
    }

    private function refreshScreen(): void
    {
        $this->categoryOptions = array_values(array_unique(array_merge(
            ['All categories'], TaskService::DEFAULT_CATEGORIES, app(TaskService::class)->categories()
        )));
        $this->refreshTasks();
        $summary = app(TaskService::class)->getSummary();
        $this->totalTasks = $summary['total'];
        $this->completedTasks = $summary['completed'];
        $this->pendingTasks = $summary['pending'];
        $this->overdueTasks = $summary['overdue'];
        $this->progress = $summary['progress'];
    }

    private function refreshTasks(): void
    {
        $category = $this->categoryFilter === 'All categories' ? null : $this->categoryFilter;
        $priority = $this->priorityFilter === 'All priorities' ? null : $this->priorityFilter;
        $sort = match ($this->sort) {
            'Priority' => 'priority',
            'Newest' => 'newest',
            'Oldest' => 'oldest',
            'A-Z' => 'alphabetical',
            default => 'due_date',
        };

        $this->tasks = app(TaskService::class)->getTasks($this->search, strtolower($this->statusFilter), $category, $priority, $sort);
    }

    private function clearForm(): void
    {
        $this->title = '';
        $this->description = '';
        $this->category = '';
        $this->dueDate = '';
        $this->reminderAt = '';
        $this->priority = $this->defaultPriority;
        $this->editingTaskId = null;
    }

    public function render(): View
    {
        return view('native.home');
    }
}
