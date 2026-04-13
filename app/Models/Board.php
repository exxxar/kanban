<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Board extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'title',
        'description',
        'config'
    ];

    protected $casts = [
        'config' => 'array'
    ];

    protected $with = ["columns"];

    public function columns():HasMany
    {
        return $this->hasMany(Column::class)->orderBy('position');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

}
