<native:column class="w-full h-full bg-theme-background safe-area">
    <native:scroll-view class="w-full flex-1">
        <native:column class="w-full gap-4 p-4">
            <native:row class="w-full items-center justify-between gap-3">
                <native:column class="flex-1 gap-1">
                    <native:text class="text-2xl font-bold text-theme-on-background">Task Manager</native:text>
                    <native:text class="text-sm text-theme-on-surface-variant">A calmer way to keep today on track.</native:text>
                </native:column>
                <native:button label="Settings" size="sm" variant="secondary" @press="openSettings" />
            </native:row>

            @if ($settingsOpen)
                <native:column class="w-full rounded-2xl bg-theme-surface p-4 gap-3">
                    <native:row class="w-full items-center justify-between">
                        <native:text class="text-lg font-semibold text-theme-on-surface">Settings</native:text>
                        <native:button label="Close" size="sm" variant="secondary" @press="closeSettings" />
                    </native:row>
                    <native:text class="text-sm text-theme-on-surface-variant">Preferences are stored locally on this device.</native:text>
                    <native:select label="Default task priority" :options="['low', 'medium', 'high']" native:model="defaultPriority" />
                    <native:toggle label="Task reminder notifications" native:model="notificationsEnabled" />
                    <native:text class="text-xs text-theme-on-surface-variant">Android may ask for notification permission the first time a reminder is saved.</native:text>
                    <native:select label="Theme" :options="['system', 'light', 'dark']" native:model="themePreference" />
                    <native:text class="text-xs text-theme-on-surface-variant">System theme follows Android appearance. Version 1.0.0 · SQLite on-device storage.</native:text>
                    <native:button label="Save preferences" variant="primary" @press="saveSettings" />
                </native:column>
            @endif

            <native:row class="w-full items-center justify-between gap-2">
                <native:text class="text-lg font-semibold text-theme-on-surface">Your tasks</native:text>
                <native:button label="+ New Task" variant="primary" @press="openNewTask" />
            </native:row>

            <native:column class="w-full rounded-2xl bg-theme-surface p-4 gap-3">
                <native:text class="text-base font-semibold text-theme-on-surface">Overview</native:text>
                <native:row class="w-full gap-2">
                    <native:column class="flex-1 rounded-xl bg-theme-surface-variant p-3 gap-1">
                        <native:text class="text-2xl font-bold text-theme-on-surface">{{ $totalTasks }}</native:text>
                        <native:text class="text-xs text-theme-on-surface-variant">Total</native:text>
                    </native:column>
                    <native:column class="flex-1 rounded-xl bg-theme-surface-variant p-3 gap-1">
                        <native:text class="text-2xl font-bold text-theme-on-surface">{{ $pendingTasks }}</native:text>
                        <native:text class="text-xs text-theme-on-surface-variant">Pending</native:text>
                    </native:column>
                </native:row>
                <native:row class="w-full gap-2">
                    <native:column class="flex-1 rounded-xl bg-theme-surface-variant p-3 gap-1">
                        <native:text class="text-2xl font-bold text-theme-on-surface">{{ $completedTasks }}</native:text>
                        <native:text class="text-xs text-theme-on-surface-variant">Completed</native:text>
                    </native:column>
                    <native:column class="flex-1 rounded-xl bg-theme-surface-variant p-3 gap-1">
                        <native:text class="text-2xl font-bold text-theme-destructive">{{ $overdueTasks }}</native:text>
                        <native:text class="text-xs text-theme-on-surface-variant">Overdue</native:text>
                    </native:column>
                </native:row>
                <native:row class="w-full items-center justify-between">
                    <native:text class="text-sm text-theme-on-surface-variant">Completion</native:text>
                    <native:text class="text-sm font-semibold text-theme-on-surface">{{ $progress }}%</native:text>
                </native:row>
                <native:progress-bar value="{{ $progress / 100 }}" />
            </native:column>

            <native:outlined-text-input label="Search tasks" placeholder="Title, details or category" native:model.debounce.300ms="search" />

            <native:row class="w-full gap-2">
                <native:column class="flex-1">
                    <native:select label="Show" :options="['All', 'Pending', 'Completed', 'Overdue', 'Today', 'Upcoming']" native:model="statusFilter" />
                </native:column>
                <native:column class="flex-1">
                    <native:select label="Sort by" :options="['Due date', 'Priority', 'Newest', 'Oldest', 'A-Z']" native:model="sort" />
                </native:column>
            </native:row>
            <native:row class="w-full gap-2">
                <native:column class="flex-1">
                    <native:select label="Category" :options="$categoryOptions" native:model="categoryFilter" />
                </native:column>
                <native:column class="flex-1">
                    <native:select label="Priority" :options="['All priorities', 'low', 'medium', 'high']" native:model="priorityFilter" />
                </native:column>
            </native:row>

            @if ($formMessage !== '')
                <native:text class="text-sm text-theme-on-surface-variant">{{ $formMessage }}</native:text>
            @endif

            @if (count($tasks) === 0)
                <native:column class="w-full rounded-2xl bg-theme-surface p-5 gap-2">
                    <native:text class="text-lg font-semibold text-theme-on-surface">{{ $totalTasks === 0 ? 'Start with one small task' : 'No tasks match these filters' }}</native:text>
                    <native:text class="text-sm text-theme-on-surface-variant">{{ $totalTasks === 0 ? 'Add your first task and we will keep it here.' : 'Try another search or filter to see more tasks.' }}</native:text>
                    @if ($totalTasks === 0)
                        <native:button label="Create your first task" variant="primary" @press="openNewTask" />
                    @endif
                </native:column>
            @else
                @foreach ($tasks as $task)
                    <native:column class="w-full rounded-2xl bg-theme-surface p-4 gap-3">
                        <native:row class="w-full items-start justify-between gap-2">
                            <native:column class="flex-1 gap-2">
                                <native:text class="text-base font-semibold text-theme-on-surface">{{ $task['title'] }}</native:text>
                                @if ($task['description'] !== '')
                                    <native:text class="text-sm text-theme-on-surface-variant">{{ $task['description'] }}</native:text>
                                @endif
                            </native:column>
                            <native:badge label="{{ $task['priority_label'] }}" variant="{{ $task['priority'] === 'high' ? 'destructive' : ($task['priority'] === 'medium' ? 'accent' : 'primary') }}" />
                        </native:row>
                        @if ($task['category'] !== '' || $task['due_date'] !== '')
                            <native:text class="text-xs text-theme-on-surface-variant">{{ $task['supporting'] }}</native:text>
                        @endif
                        <native:text class="text-xs font-semibold {{ $task['is_completed'] ? 'text-theme-primary' : 'text-theme-on-surface-variant' }}">{{ $task['is_completed'] ? 'COMPLETED' : 'PENDING' }}</native:text>
                        <native:row class="w-full gap-2">
                            <native:column class="flex-1">
                                <native:button class="w-full" label="{{ $task['is_completed'] ? 'Reopen' : 'Complete' }}" size="sm" variant="primary" @press="toggleTask({{ $task['id'] }})" />
                            </native:column>
                            <native:column class="flex-1">
                                <native:button class="w-full" label="Edit" size="sm" variant="secondary" @press="editTask({{ $task['id'] }})" />
                            </native:column>
                            <native:column class="flex-1">
                                <native:button class="w-full" label="Delete" size="sm" variant="destructive" @press="confirmDelete({{ $task['id'] }})" />
                            </native:column>
                        </native:row>
                    </native:column>
                @endforeach
            @endif
        </native:column>
    </native:scroll-view>

    <native:bottom-sheet :visible="$formOpen" detents="large" @dismiss="closeForm">
        <native:scroll-view class="w-full h-full">
            <native:column class="w-full gap-3 p-5">
                <native:text class="text-xl font-bold text-theme-on-surface">{{ $editingTaskId ? 'Edit Task' : 'New Task' }}</native:text>
                <native:outlined-text-input label="Task title" placeholder="What needs to get done?" max-length="160" native:model="title" />
                <native:outlined-text-input label="Description" placeholder="Add a few useful details" multiline="true" max-length="2000" native:model="description" />
                <native:outlined-text-input label="Category" placeholder="Work, Personal, Study, Shopping, Health, Finance, Other" max-length="80" native:model="category" />
                <native:text class="text-sm font-semibold text-theme-on-surface">Priority</native:text>
                <native:row class="w-full gap-2">
                    <native:column class="flex-1"><native:button class="w-full" label="Low" size="sm" variant="{{ $priority === 'low' ? 'primary' : 'secondary' }}" @press="setLowPriority" /></native:column>
                    <native:column class="flex-1"><native:button class="w-full" label="Medium" size="sm" variant="{{ $priority === 'medium' ? 'primary' : 'secondary' }}" @press="setMediumPriority" /></native:column>
                    <native:column class="flex-1"><native:button class="w-full" label="High" size="sm" variant="{{ $priority === 'high' ? 'primary' : 'secondary' }}" @press="setHighPriority" /></native:column>
                </native:row>
                <native:date-picker label="Due date" placeholder="No due date" mode="date" native:model="dueDate" />
                @if ($dueDate !== '')
                    <native:button label="Clear due date" size="sm" variant="secondary" @press="clearDueDate" />
                @endif
                <native:date-picker label="Reminder" placeholder="No reminder" mode="datetime" native:model="reminderAt" />
                @if ($reminderAt !== '')
                    <native:button label="Clear reminder" size="sm" variant="secondary" @press="clearReminder" />
                @endif
                @if ($formMessage !== '')
                    <native:text class="text-sm text-theme-destructive">{{ $formMessage }}</native:text>
                @endif
                <native:button label="{{ $editingTaskId ? 'Update Task' : 'Save Task' }}" variant="primary" @press="saveTask" />
                <native:button label="Cancel" variant="secondary" @press="closeForm" />
            </native:column>
        </native:scroll-view>
    </native:bottom-sheet>

    <native:bottom-sheet :visible="$pendingDeleteId !== null" detents="small" @dismiss="cancelDelete">
        <native:column class="w-full gap-3 p-5">
            <native:text class="text-lg font-semibold text-theme-on-surface">Delete this task?</native:text>
            <native:text class="text-sm text-theme-on-surface-variant">This task will be removed from this device.</native:text>
            <native:button label="Delete task" variant="destructive" @press="deleteTask" />
            <native:button label="Keep task" variant="secondary" @press="cancelDelete" />
        </native:column>
    </native:bottom-sheet>
</native:column>
