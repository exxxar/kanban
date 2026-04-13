<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Column extends Model
{
    protected $fillable = ['title', 'position', 'thread','can_remove', 'board_id'];

    protected $casts = [
        "can_remove"=>"boolean"
    ];

    protected $with = ["tasks"];

    public function board():BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function tasks():HasMany
    {
        return $this->hasMany(Task::class,'column_id','id')->orderBy('position');
    }
}
