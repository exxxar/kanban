<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
    protected $fillable = [
        'task_id',
        'author',
        'text',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
