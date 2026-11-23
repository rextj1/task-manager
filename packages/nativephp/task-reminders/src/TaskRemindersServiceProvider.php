<?php

namespace BloomRiver\TaskReminders;

use Illuminate\Support\ServiceProvider;

class TaskRemindersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TaskReminders::class);
    }
}
