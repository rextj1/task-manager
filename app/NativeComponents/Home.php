<?php

namespace App\NativeComponents;

use App\Services\TaskService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;
use Throwable;

class Home extends NativeComponent
{
    // Form fields
    public string $title = '';

    public string $description = '';

    public string $category = '';

    public string $dueDate = '';

    public string $priority = 'medium';

    // Editing
    public ?int $editingTaskId = null;

    // Search and filters
    public string $search = '';

    public string $statusFilter = 'all';

    // Screen data
    public array $tasks = [];

    public int $totalTasks = 0;

    public int $completedTasks = 0;

    public int $pendingTasks = 0;

    public int $overdueTasks = 0;

    public int $progress = 0;

    public string $formMessage = '';

    public function mount(): void
    {
        $this->refreshScreen();
    }

    public function onResume(): void
    {
        $this->refreshScreen();
    }

    public function saveTask(): void
    {
        $service = app(TaskService::class);

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'due_date' => $this->dueDate,
            'priority' => $this->priority,
        ];

        try {
            if ($this->editingTaskId !== null) {
                $service->updateTask($this->editingTaskId, $data);

                $message = 'Task updated successfully.';
            } else {
                $service->createTask($data);

                $message = 'Task created successfully.';
            }

            $this->clearForm();

            $this->formMessage = $message;

            $this->refreshScreen();
        } catch (ValidationException $e) {
            $messages = $e->errors();

            $this->formMessage = collect($messages)
                ->flatten()
                ->first() ?? 'Please check your task details.';
        } catch (Throwable $e) {
            Log::error('Task save failed', [
                'error' => $e->getMessage(),
            ]);

            $this->formMessage = 'Unable to save this task.';
        }
    }

    public function editTask(int $id): void
    {
        $task = app(TaskService::class)->findTask($id);

        $this->editingTaskId = $task->id;
        $this->title = $task->title;
        $this->description = $task->description ?? '';
        $this->category = $task->category ?? '';
        $this->priority = $task->priority ?? 'medium';
        $this->dueDate = $task->due_date?->format('Y-m-d') ?? '';

        $this->formMessage = 'Editing task #'.$task->id;
    }

    public function toggleTask(int $id): void
    {
        $service = app(TaskService::class);

        $task = $service->findTask($id);

        $service->setCompletion(
            $id,
            ! $task->is_completed
        );

        $this->refreshScreen();
    }

    public function deleteTask(int $id): void
    {
        app(TaskService::class)->deleteTask($id);

        if ($this->editingTaskId === $id) {
            $this->clearForm();
        }

        $this->formMessage = 'Task deleted.';

        $this->refreshScreen();
    }

    public function cancelEdit(): void
    {
        $this->clearForm();

        $this->formMessage = 'Editing cancelled.';
    }

    public function clearDueDate(): void
    {
        $this->dueDate = '';
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

    public function showAllTasks(): void
    {
        $this->statusFilter = 'all';
        $this->refreshTasks();
    }

    public function showPendingTasks(): void
    {
        $this->statusFilter = 'pending';
        $this->refreshTasks();
    }

    public function showCompletedTasks(): void
    {
        $this->statusFilter = 'completed';
        $this->refreshTasks();
    }

    public function updatedSearch(string $value): void
    {
        $this->refreshTasks();
    }

    private function refreshScreen(): void
    {
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
        $this->tasks = app(TaskService::class)->getTasks(
            $this->search,
            $this->statusFilter
        );
    }

    private function clearForm(): void
    {
        $this->title = '';
        $this->description = '';
        $this->category = '';
        $this->dueDate = '';
        $this->priority = 'medium';
        $this->editingTaskId = null;
    }

    public function render(): View
    {
        return view('native.home');
    }
}
