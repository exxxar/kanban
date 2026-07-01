<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlineUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'board_uuid',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'os',
        'screen_resolution',
        'canvas_hash',
        'last_seen_at'
    ];

    protected $casts = [
        'last_seen_at' => 'datetime'
    ];

    // Очистка старых записей (старше 2 минут)
    public static function cleanupOld($minutes = 2)
    {
        return self::where('last_seen_at', '<', now()->subMinutes($minutes))->delete();
    }

    // Получение онлайн пользователей для доски
    public static function getOnlineForBoard($boardUuid, $minutes = 2)
    {
        return self::where('board_uuid', $boardUuid)
            ->where('last_seen_at', '>=', now()->subMinutes($minutes))
            ->orderBy('last_seen_at', 'desc')
            ->get();
    }
}
