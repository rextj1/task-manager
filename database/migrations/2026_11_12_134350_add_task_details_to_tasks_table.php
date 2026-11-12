<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->text('description')->nullable();

            $table->string('priority', 20)
                ->default('medium');

            $table->date('due_date')->nullable();

            $table->string('category', 80)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'priority',
                'due_date',
                'category',
            ]);
        });
    }
};