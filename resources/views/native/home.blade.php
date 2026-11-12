<native:column class="w-full h-full">

    <native:scroll-view class="w-full flex-1">

        <native:column class="w-full gap-4 p-4">

            {{-- Header --}}
            <native:column class="w-full gap-1">

                <native:text class="text-2xl font-bold text-theme-on-surface">
                    Task Manager
                </native:text>

                <native:text class="text-sm text-theme-on-surface-variant">
                    Organise your work and stay productive.
                </native:text>

            </native:column>


            {{-- Summary --}}
            <native:column class="w-full rounded-2xl bg-theme-surface p-4 gap-3">

                <native:text class="text-lg font-semibold text-theme-on-surface">
                    Overview
                </native:text>

                <native:row class="w-full gap-2">

                    <native:column class="flex-1 gap-1">
                        <native:text class="text-2xl font-bold text-theme-on-surface">
                            {{ $totalTasks }}
                        </native:text>

                        <native:text class="text-xs text-theme-on-surface-variant">
                            Total
                        </native:text>
                    </native:column>

                    <native:column class="flex-1 gap-1">
                        <native:text class="text-2xl font-bold text-theme-on-surface">
                            {{ $pendingTasks }}
                        </native:text>

                        <native:text class="text-xs text-theme-on-surface-variant">
                            Pending
                        </native:text>
                    </native:column>

                    <native:column class="flex-1 gap-1">
                        <native:text class="text-2xl font-bold text-theme-on-surface">
                            {{ $completedTasks }}
                        </native:text>

                        <native:text class="text-xs text-theme-on-surface-variant">
                            Completed
                        </native:text>
                    </native:column>

                </native:row>

                <native:text class="text-sm text-theme-on-surface-variant">
                    Progress: {{ $progress }}%
                </native:text>

                <native:text class="text-sm text-theme-on-surface-variant">
                    Overdue tasks: {{ $overdueTasks }}
                </native:text>

            </native:column>


            {{-- Create or edit task --}}
            <native:column class="w-full rounded-2xl bg-theme-surface p-4 gap-3">

                <native:text class="text-lg font-semibold text-theme-on-surface">
                    {{ $editingTaskId ? 'Edit Task' : 'New Task' }}
                </native:text>

                <native:outlined-text-input
                    label="Task title"
                    placeholder="Enter task title"
                    native:model="title"
                />

                <native:outlined-text-input
                    label="Description"
                    placeholder="Enter task description"
                    native:model="description"
                />

                <native:outlined-text-input
                    label="Category"
                    placeholder="e.g. Work, Personal, Study"
                    native:model="category"
                />

                <native:date-picker
                    label="Due date"
                    placeholder="Select a due date"
                    mode="date"
                    native:model="dueDate"
                />

                @if ($dueDate !== '')
                    <native:button
                        label="Clear due date"
                        size="sm"
                        variant="secondary"
                        @press="clearDueDate"
                    />
                @endif

                <native:text class="text-sm font-semibold text-theme-on-surface">
                    Priority
                </native:text>

                <native:row class="w-full gap-2">

                    <native:column class="flex-1">
                        <native:button
                            label="Low"
                            variant="{{ $priority === 'low' ? 'primary' : 'secondary' }}"
                            @press="setLowPriority"
                        />
                    </native:column>

                    <native:column class="flex-1">
                        <native:button
                            label="Medium"
                            variant="{{ $priority === 'medium' ? 'primary' : 'secondary' }}"
                            @press="setMediumPriority"
                        />
                    </native:column>

                    <native:column class="flex-1">
                        <native:button
                            label="High"
                            variant="{{ $priority === 'high' ? 'primary' : 'secondary' }}"
                            @press="setHighPriority"
                        />
                    </native:column>

                </native:row>

                <native:button
                    label="{{ $editingTaskId ? 'Update Task' : 'Save Task' }}"
                    variant="primary"
                    @press="saveTask"
                />

                @if ($editingTaskId !== null)
                    <native:button
                        label="Cancel Editing"
                        variant="secondary"
                        @press="cancelEdit"
                    />
                @endif

                @if ($formMessage !== '')
                    <native:text class="text-sm text-theme-on-surface-variant">
                        {{ $formMessage }}
                    </native:text>
                @endif

            </native:column>


            {{-- Search --}}
            <native:column class="w-full gap-2">

                <native:text class="text-lg font-semibold text-theme-on-surface">
                    Your Tasks
                </native:text>

                <native:outlined-text-input
                    label="Search tasks"
                    placeholder="Search by title, description or category"
                    native:model.debounce.300ms="search"
                />

            </native:column>


            {{-- Filters --}}
            <native:row class="w-full gap-2">

                <native:column class="flex-1">
                    <native:button
                        label="All"
                        size="sm"
                        variant="{{ $statusFilter === 'all' ? 'primary' : 'secondary' }}"
                        @press="showAllTasks"
                    />
                </native:column>

                <native:column class="flex-1">
                    <native:button
                        label="Pending"
                        size="sm"
                        variant="{{ $statusFilter === 'pending' ? 'primary' : 'secondary' }}"
                        @press="showPendingTasks"
                    />
                </native:column>

                <native:column class="flex-1">
                    <native:button
                        label="Completed"
                        size="sm"
                        variant="{{ $statusFilter === 'completed' ? 'primary' : 'secondary' }}"
                        @press="showCompletedTasks"
                    />
                </native:column>

            </native:row>


            {{-- Task cards --}}
            <native:column class="w-full gap-3">

                @if (count($tasks) === 0)

                    <native:column class="w-full rounded-2xl bg-theme-surface p-5 gap-2">

                        <native:text class="text-base font-semibold text-theme-on-surface">
                            No tasks found
                        </native:text>

                        <native:text class="text-sm text-theme-on-surface-variant">
                            Create a task or change your search and filters.
                        </native:text>

                    </native:column>

                @else

                    @foreach ($tasks as $task)

                        <native:column
                            class="w-full rounded-2xl bg-theme-surface p-4 gap-3"
                        >

                            <native:column class="w-full gap-1">

                                <native:text class="text-base font-semibold text-theme-on-surface">
                                    {{ $task['title'] }}
                                </native:text>

                                @if ($task['description'] !== '')
                                    <native:text class="text-sm text-theme-on-surface-variant">
                                        {{ $task['description'] }}
                                    </native:text>
                                @endif

                                <native:text class="text-xs text-theme-on-surface-variant">
                                    {{ $task['supporting'] }}
                                </native:text>

                                <native:text class="text-xs font-semibold text-theme-on-surface">
                                    {{ $task['is_completed'] ? 'COMPLETED' : 'PENDING' }}
                                </native:text>

                            </native:column>

                            <native:row class="w-full gap-2">

                                <native:column class="flex-1">
                                    <native:button
                                        label="{{ $task['is_completed'] ? 'Reopen' : 'Complete' }}"
                                        size="sm"
                                        variant="primary"
                                        @press="toggleTask({{ $task['id'] }})"
                                    />
                                </native:column>

                                <native:column class="flex-1">
                                    <native:button
                                        label="Edit"
                                        size="sm"
                                        variant="secondary"
                                        @press="editTask({{ $task['id'] }})"
                                    />
                                </native:column>

                                <native:column class="flex-1">
                                    <native:button
                                        label="Delete"
                                        size="sm"
                                        variant="destructive"
                                        @press="deleteTask({{ $task['id'] }})"
                                    />
                                </native:column>

                            </native:row>

                        </native:column>

                    @endforeach

                @endif

            </native:column>

        </native:column>

    </native:scroll-view>

</native:column>
