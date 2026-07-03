<?php

namespace App\Http\Controllers;

use App\Models\OnlineUser;
use Illuminate\Http\Request;
use Illuminate\Database\UniqueConstraintViolationException;

class OnlineController extends Controller
{
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

        $deviceType = $this->detectDeviceType($userAgent);
        $browser = $this->detectBrowser($userAgent);
        $os = $this->detectOS($userAgent);

        $attributes = [
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'device_type' => $deviceType,
            'browser' => $browser,
            'os' => $os,
            'screen_resolution' => $request->screen_resolution,
            'canvas_hash' => $request->canvas_hash,
            'last_seen_at' => now(),
        ];

        try {
            OnlineUser::updateOrCreate(
                [
                    'session_id' => $sessionId,
                    'board_uuid' => $boardUuid,
                ],
                $attributes
            );
        } catch (UniqueConstraintViolationException $e) {
            OnlineUser::where('session_id', $sessionId)
                ->where('board_uuid', $boardUuid)
                ->update($attributes);
        }

        // === ОЧИСТКА ТОЛЬКО В HEARTBEAT И С МАЛОЙ ВЕРОЯТНОСТЬЮ ===
        // 1 из 10 запросов (примерно раз в 5 минут при 30-секундном интервале)
        if (rand(1, 10) === 1) {
            try {
                OnlineUser::cleanupOld(2);
            } catch (\Exception $e) {
                // Игнорируем ошибки deadlock при очистке
                \Log::warning('Cleanup failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Heartbeat received'
        ]);
    }

    public function getOnline($boardUuid)
    {
        // === УБРАЛИ cleanupOld ОТСЮДА ===
        // Только чтение, без удаления — нет deadlock

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
                    'last_seen_timestamp' => $user->last_seen_at->timestamp,
                ];
            })
        ]);
    }

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

    private function detectBrowser($userAgent)
    {
        if (str_contains($userAgent, 'YaBrowser')) return 'Яндекс';
        if (str_contains($userAgent, 'Edg')) return 'Edge';
        if (str_contains($userAgent, 'OPR') || str_contains($userAgent, 'Opera')) return 'Opera';
        if (str_contains($userAgent, 'Chrome')) return 'Chrome';
        if (str_contains($userAgent, 'Firefox')) return 'Firefox';
        if (str_contains($userAgent, 'Safari')) return 'Safari';
        return 'Другой';
    }

    private function detectOS($userAgent)
    {
        if (str_contains($userAgent, 'Windows')) return 'Windows';
        if (str_contains($userAgent, 'Mac')) return 'macOS';
        if (str_contains($userAgent, 'Linux')) return 'Linux';
        if (str_contains($userAgent, 'Android')) return 'Android';
        if (str_contains($userAgent, 'iOS') || str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) return 'iOS';
        return 'Другая';
    }

    private function maskIp($ip)
    {
        if (str_contains($ip, ':')) {
            return substr($ip, 0, 8) . ':***';
        }

        $parts = explode('.', $ip);
        if (count($parts) === 4) {
            return $parts[0] . '.' . $parts[1] . '.*.*';
        }

        return substr($ip, 0, 7) . '***';
    }
}
