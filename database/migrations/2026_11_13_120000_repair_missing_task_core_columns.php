<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $missingTitle = ! Schema::hasColumn('tasks', 'title');
        $missingCompletion = ! Schema::hasColumn('tasks', 'is_completed');

        if (! $missingTitle && ! $missingCompletion) {
            return;
        }

        Schema::table('tasks', function (Blueprint $table) use ($missingTitle, $missingCompletion) {
            if ($missingTitle) {
                $table->string('title')->default('Untitled task');
            }
            if ($missingCompletion) {
                $table->boolean('is_completed')->default(false);
            }
        });

        // An earlier drifted schema had this name bound to an expression
        // instead of the missing column. Recreate it after repairing the table.
        if (Schema::hasIndex('tasks', 'tasks_is_completed_due_date_index')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropIndex('tasks_is_completed_due_date_index');
            });
        }

        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['is_completed', 'due_date'], 'tasks_is_completed_due_date_index');
        });
    }

    public function down(): void
    {
        // Keep repaired columns and data: a rollback must not remove task fields.
    }
};
