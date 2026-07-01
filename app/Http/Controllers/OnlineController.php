<?php

namespace App\Http\Controllers;

use App\Models\OnlineUser;
use Illuminate\Http\Request;
use Jensseitors\Agent\Facades\Agent;

class OnlineController extends Controller
{
    // Heartbeat — обновление статуса онлайн
    public function heartbeat(Request $request)
    {
        $request->validate([
            'board_uuid' => 'required|string|exists:boards,uuid',
            'session_id' => 'required|string|max:64',
            'screen_resolution' => 'nullable|string|max:20',
            'canvas_hash' => 'nullable|string|max:64',
        ]);

        $boardUuid = $request->board_uuid;
        $sessionId = $request->session_id;
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();

        // Определяем устройство, браузер, ОС
        $deviceType = $this->detectDeviceType($userAgent);
        $browser = $this->detectBrowser($userAgent);
        $os = $this->detectOS($userAgent);

        // Обновляем или создаём запись
        OnlineUser::updateOrCreate(
            [
                'session_id' => $sessionId,
                'board_uuid' => $boardUuid
            ],
            [
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'device_type' => $deviceType,
                'browser' => $browser,
                'os' => $os,
                'screen_resolution' => $request->screen_resolution,
                'canvas_hash' => $request->canvas_hash,
                'last_seen_at' => now()
            ]
        );

        // Очищаем старые записи
        OnlineUser::cleanupOld(2);

        return response()->json([
            'success' => true,
            'message' => 'Heartbeat received'
        ]);
    }

    // Получение списка онлайн пользователей
    public function getOnline($boardUuid)
    {
        // Очищаем старые записи
        OnlineUser::cleanupOld(2);

        // Получаем онлайн пользователей
        $onlineUsers = OnlineUser::getOnlineForBoard($boardUuid, 2);

        return response()->json([
            'count' => $onlineUsers->count(),
            'users' => $onlineUsers->map(function ($user) {
                return [
                    'session_id' => $user->session_id,
                    'ip_address' => $this->maskIp($user->ip_address),
                    'device_type' => $user->device_type,
                    'browser' => $user->browser,
                    'os' => $user->os,
                    'screen_resolution' => $user->screen_resolution,
                    'last_seen_at' => $user->last_seen_at->diffForHumans(),
                    'last_seen_timestamp' => $user->last_seen_at->timestamp
                ];
            })
        ]);
    }

    // Определение типа устройства
    private function detectDeviceType($userAgent)
    {
        if (str_contains($userAgent, 'Mobile') || str_contains($userAgent, 'Android')) {
            return 'mobile';
        }
        if (str_contains($userAgent, 'Tablet') || str_contains($userAgent, 'iPad')) {
            return 'tablet';
        }
        return 'desktop';
    }

    // Определение браузера
    private function detectBrowser($userAgent)
    {
        if (str_contains($userAgent, 'Chrome') && !str_contains($userAgent, 'Edg')) {
            return 'Chrome';
        }
        if (str_contains($userAgent, 'Firefox')) {
            return 'Firefox';
        }
        if (str_contains($userAgent, 'Safari') && !str_contains($userAgent, 'Chrome')) {
            return 'Safari';
        }
        if (str_contains($userAgent, 'Edg')) {
            return 'Edge';
        }
        if (str_contains($userAgent, 'Opera') || str_contains($userAgent, 'OPR')) {
            return 'Opera';
        }
        return 'Other';
    }

    // Определение ОС
    private function detectOS($userAgent)
    {
        if (str_contains($userAgent, 'Windows')) {
            return 'Windows';
        }
        if (str_contains($userAgent, 'Mac')) {
            return 'macOS';
        }
        if (str_contains($userAgent, 'Linux')) {
            return 'Linux';
        }
        if (str_contains($userAgent, 'Android')) {
            return 'Android';
        }
        if (str_contains($userAgent, 'iOS') || str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            return 'iOS';
        }
        return 'Other';
    }

    // Маскирование IP для безопасности (показываем только часть)
    private function maskIp($ip)
    {
        $parts = explode('.', $ip);
        if (count($parts) === 4) {
            return $parts[0] . '.' . $parts[1] . '.*.*';
        }
        return substr($ip, 0, 7) . '***';
    }
}
