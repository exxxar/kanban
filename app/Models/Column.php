<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Column extends Model
{
    protected $fillable = ['title', 'position', 'thread','can_remove', 'board_id', 'config'];

    protected $casts = [
        "can_remove"=>"boolean",
        "config"=>"array"
    ];

    protected $with = ["tasks"];

    public function board():BelongsTo
    {
        return $this->belongsTo(Board::class);
    }


    public function getNotificationsAttribute()
    {
        $config = $this->config ?? [];

        // Если настроек нет — вернуть дефолт
        return $config['notifications'] ?? [
            'enabled' => false,
            'email' => [
                'enabled' => false,
                'to' => [""]
            ],
            'webhook' => [
                'enabled' => false,
                'urls' => [""]
            ],
            'events' => [
                'card_created' => true,
                'card_updated' => false,
                'card_moved' => true,
                'new_message' => true
            ]
        ];
    }

    // --- MUTATOR: сохранить настройки уведомлений ---
    public function setNotificationsAttribute($value)
    {
        $config = $this->config ?? [];
        $config['notifications'] = $value;
        $this->attributes['config'] = json_encode($config);
    }

    public function tasks():HasMany
    {
        return $this->hasMany(Task::class,'column_id','id')->orderBy('position');
    }
}
