<?php

use App\Services\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Native\Mobile\Testing\Native;

uses(RefreshDatabase::class);

it('renders a valid native root screen', function () {
    $screen = Native::visit('/')
        ->assertSee('Task Manager')
        ->assertSee('Your tasks')
        ->assertSee('Overview')
        ->assertElement('column');

    expect($screen->tree())->not->toBeEmpty();
});

it('shows database tasks on the initial dashboard and keeps the new-task sheet closed', function () {
    app(TaskService::class)->createTask([
        'title' => 'Pack the demo kit',
        'description' => 'Charge the devices before leaving',
        'category' => 'work',
        'priority' => 'high',
    ]);

    Native::visit('/')
        ->assertSee('Pack the demo kit')
        ->assertSee('Charge the devices before leaving')
        ->assertElement('bottom_sheet', fn (array $node): bool => ($node['props']['visible'] ?? null) === false);
});

it('renders dropdown choices as separate NativePHP options with a selected value', function () {
    $screen = Native::visit('/');
    $tree = $screen->tree();
    $selects = [];
    $collectSelects = function (array $node) use (&$collectSelects, &$selects): void {
        if (($node['type'] ?? null) === 'select') {
            $selects[$node['props']['label'] ?? ''] = [
                'options' => $node['props']['options'] ?? [],
                'value' => $node['props']['value'] ?? null,
            ];
        }

        foreach ($node['children'] ?? [] as $child) {
            $collectSelects($child);
        }
    };
    $collectSelects($tree);

    expect($selects)->toMatchArray([
        'Show' => ['options' => ['All', 'Pending', 'Completed', 'Overdue', 'Today', 'Upcoming'], 'value' => 'All'],
        'Sort by' => ['options' => ['Due date', 'Priority', 'Newest', 'Oldest', 'A-Z'], 'value' => 'Due date'],
        'Category' => ['options' => ['All categories', ...TaskService::DEFAULT_CATEGORIES], 'value' => 'All categories'],
        'Priority' => ['options' => ['All priorities', 'low', 'medium', 'high'], 'value' => 'All priorities'],
    ]);

    $selects = [];
    $collectSelects($screen->tap('Settings')->tree());

    expect($selects)->toMatchArray([
        'Default task priority' => ['options' => ['low', 'medium', 'high'], 'value' => 'medium'],
        'Theme' => ['options' => ['system', 'light', 'dark'], 'value' => 'system'],
    ]);
});

it('supports the task flow through NativePHP screen events', function () {
    $screen = Native::visit('/')
        ->tap('+ New Task')
        ->assertElement('bottom_sheet', fn (array $node): bool => ($node['props']['visible'] ?? null) === true)
        ->input('title', 'Prepare the weekly plan')
        ->input('description', 'List the three most important outcomes')
        ->tap('Save Task')
        ->assertSee('Prepare the weekly plan')
        ->tap('Edit')
        ->input('title', 'Prepare the weekly work plan')
        ->tap('Update Task')
        ->assertSee('Prepare the weekly work plan')
        ->tap('Complete')
        ->assertSee('COMPLETED')
        ->tap('Reopen')
        ->assertSee('PENDING')
        ->tap('Delete')
        ->tap('Delete task')
        ->assertDontSee('Prepare the weekly work plan');

    expect($screen->tree())->not->toBeEmpty();
});
