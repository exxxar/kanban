<?php

namespace App\Http\Controllers;

use App\Models\OnlineUser;
use Illuminate\Http\Request;
use Illuminate\Database\UniqueConstraintViolationException;
use Jenssegers\Agent\Facades\Agent;

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

        Agent::setUserAgent($userAgent);

        $deviceType = $this->detectDeviceType();
        $browser = $this->detectBrowser();
        $os = $this->detectOS();

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

        // === ПЫТАЕМСЯ ОБНОВИТЬ ИЛИ СОЗДАТЬ ===
        try {
            OnlineUser::updateOrCreate(
                [
                    'session_id' => $sessionId,
                    'board_uuid' => $boardUuid, // ← теперь ищем по двум полям
                ],
                $attributes
            );
        } catch (UniqueConstraintViolationException $e) {
            // Race condition: другой запрос уже создал запись
            // Просто обновляем существующую
            OnlineUser::where('session_id', $sessionId)
                ->where('board_uuid', $boardUuid)
                ->update($attributes);
        }

        // Чистим старые записи
        OnlineUser::cleanupOld(2);

        return response()->json([
            'success' => true,
            'message' => 'Heartbeat received'
        ]);
    }

    public function getOnline($boardUuid)
    {
        OnlineUser::cleanupOld(2);

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

    private function detectDeviceType()
    {
        if (Agent::isMobile()) return 'mobile';
        if (Agent::isTablet()) return 'tablet';
        if (Agent::isDesktop()) return 'desktop';
        return 'other';
    }

    private function detectBrowser()
    {
        $browser = Agent::browser();

        if (empty($browser) || $browser === 'Other') {
            $userAgent = Agent::getUserAgent();
            if (str_contains($userAgent, 'YaBrowser')) return 'Яндекс';
            if (str_contains($userAgent, 'Edg')) return 'Edge';
            if (str_contains($userAgent, 'OPR') || str_contains($userAgent, 'Opera')) return 'Opera';
            if (str_contains($userAgent, 'Chrome')) return 'Chrome';
            if (str_contains($userAgent, 'Firefox')) return 'Firefox';
            if (str_contains($userAgent, 'Safari')) return 'Safari';
            return 'Неизвестно';
        }

        return $browser;
    }

    private function detectOS()
    {
        $platform = Agent::platform();

        if (empty($platform) || $platform === 'Other') {
            $userAgent = Agent::getUserAgent();
            if (str_contains($userAgent, 'Windows')) return 'Windows';
            if (str_contains($userAgent, 'Mac')) return 'macOS';
            if (str_contains($userAgent, 'Linux')) return 'Linux';
            if (str_contains($userAgent, 'Android')) return 'Android';
            if (str_contains($userAgent, 'iOS') || str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) return 'iOS';
            return 'Неизвестно';
        }

        return $platform;
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
