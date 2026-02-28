<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use App\Models\Board;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthTokVerify
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $record = ApiToken::where('token', hash('sha256', $token))
            ->where('is_active', true)
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $board = Board::query()
            ->where("id", $record->board_id)
            ->first();

        $request->board = $board;

        return $next($request);
    }
}
