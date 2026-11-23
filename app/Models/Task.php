<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'priority',
        'due_date',
        'reminder_at',
        'category',
        'is_completed',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'due_date' => 'date:Y-m-d',
            'reminder_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
