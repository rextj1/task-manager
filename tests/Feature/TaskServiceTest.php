<?php

use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('creates, retrieves, updates and deletes tasks through the service', function () {
    $service = app(TaskService::class);
    $task = $service->createTask([
        'title' => '  Review the launch plan  ',
        'description' => 'Read the final notes',
        'category' => 'work',
        'priority' => 'high',
        'due_date' => '2026-12-01',
        'reminder_at' => '2026-11-30 09:15',
    ]);

    expect($task->title)->toBe('Review the launch plan')
        ->and($task->category)->toBe('Work')
        ->and($task->due_date->format('Y-m-d'))->toBe('2026-12-01')
        ->and($task->reminder_at->format('Y-m-d H:i'))->toBe('2026-11-30 09:15')
        ->and($service->findTask($task->id)->is_completed)->toBeFalse();

    $updated = $service->updateTask($task->id, [
        'title' => 'Review launch plan',
        'description' => 'Final review',
        'category' => 'work',
        'priority' => 'medium',
        'due_date' => '',
        'reminder_at' => '',
    ]);

    expect($updated->title)->toBe('Review launch plan')
        ->and($updated->priority)->toBe('medium')
        ->and($updated->due_date)->toBeNull()
        ->and($updated->reminder_at)->toBeNull();

    $service->deleteTask($task->id);
    expect(Task::query()->count())->toBe(0);
});

it('validates task input and handles missing IDs', function () {
    $service = app(TaskService::class);

    foreach ([
        ['title' => '', 'priority' => 'medium'],
        ['title' => 'Invalid priority', 'priority' => 'urgent'],
        ['title' => 'Invalid date', 'priority' => 'low', 'due_date' => '2026-02-30'],
        ['title' => 'Invalid reminder', 'priority' => 'low', 'reminder_at' => 'not-a-date'],
        ['title' => 'Invalid reminder date', 'priority' => 'low', 'reminder_at' => '2026-02-30 10:00'],
        ['title' => str_repeat('x', 161), 'priority' => 'high'],
        ['title' => 'Long description', 'priority' => 'low', 'description' => str_repeat('x', 2001)],
    ] as $invalid) {
        expect(fn () => $service->createTask($invalid))->toThrow(ValidationException::class);
    }

    expect(fn () => $service->findTask(9999))->toThrow(ModelNotFoundException::class)
        ->and(fn () => $service->deleteTask(9999))->toThrow(ModelNotFoundException::class);
});

it('completes and reopens tasks while maintaining timestamps', function () {
    $service = app(TaskService::class);
    $task = $service->createTask(['title' => 'File taxes', 'priority' => 'medium']);

    $completed = $service->completeTask($task->id);
    expect($completed->is_completed)->toBeTrue()->and($completed->completed_at)->not->toBeNull();

    $reopened = $service->reopenTask($task->id);
    expect($reopened->is_completed)->toBeFalse()->and($reopened->completed_at)->toBeNull();
});

it('combines search, status, priority and category filters and computes dashboard counts', function () {
    $service = app(TaskService::class);
    $service->createTask(['title' => 'Prepare budget', 'description' => 'Quarterly review', 'category' => 'finance', 'priority' => 'high']);
    $done = $service->createTask(['title' => 'Read project brief', 'description' => 'Budget context', 'category' => 'work', 'priority' => 'medium']);
    $service->completeTask($done->id);
    $service->createTask(['title' => 'Old health reminder', 'category' => 'health', 'priority' => 'low', 'due_date' => today()->subDay()->format('Y-m-d')]);

    expect($service->searchTasks('budget')->count())->toBe(2)
        ->and($service->getTasks('budget', 'pending', 'Finance', 'high'))->toHaveCount(1)
        ->and($service->filterByCompletion(true))->toHaveCount(1)
        ->and($service->filterByPriority('low'))->toHaveCount(1)
        ->and($service->filterByCategory('Health'))->toHaveCount(1)
        ->and($service->getTasks('', 'overdue'))->toHaveCount(1)
        ->and($service->getSummary())->toMatchArray([
            'total' => 3,
            'completed' => 1,
            'pending' => 2,
            'overdue' => 1,
            'progress' => 33,
        ]);
});

it('finds tasks due today, tomorrow and within seven days', function () {
    $service = app(TaskService::class);
    $makeTask = fn (string $title, string $date) => $service->createTask([
        'title' => $title,
        'priority' => 'medium',
        'due_date' => $date,
    ]);

    $today = today();
    $makeTask('Today item', $today->format('Y-m-d'));
    $makeTask('Tomorrow item', $today->copy()->addDay()->format('Y-m-d'));
    $makeTask('Seven days item', $today->copy()->addDays(7)->format('Y-m-d'));
    $makeTask('Later item', $today->copy()->addDays(8)->format('Y-m-d'));

    expect($service->dueToday()->pluck('title')->all())->toBe(['Today item'])
        ->and($service->dueTomorrow()->pluck('title')->all())->toBe(['Tomorrow item'])
        ->and($service->upcomingTasks()->pluck('title')->all())->toBe(['Tomorrow item', 'Seven days item']);
});
