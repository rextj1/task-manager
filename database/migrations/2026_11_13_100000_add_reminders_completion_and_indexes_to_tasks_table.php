<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dateTime('reminder_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->index(['is_completed', 'due_date']);
            $table->index(['priority', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['is_completed', 'due_date']);
            $table->dropIndex(['priority', 'due_date']);
            $table->dropColumn(['reminder_at', 'completed_at']);
        });
    }
};
