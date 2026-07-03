<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function index(Request $request)
    {
        $boards = Board::with(['columns', 'tags'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'boards' => $boards,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $board = Board::create([
            'uuid' => \Str::uuid(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'config' => [],
        ]);

        return response()->json([
            'success' => true,
            'board' => $board->load(['columns', 'tags']),
        ], 201);
    }

    public function show(string $uuid)
    {
        $board = Board::where('uuid', $uuid)
            ->with(['columns.tasks', 'tags'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'board' => $board,
        ]);
    }

    public function update(Request $request, string $uuid)
    {
        $board = Board::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $board->update($validated);

        return response()->json([
            'success' => true,
            'board' => $board->fresh(),
        ]);
    }

    public function destroy(string $uuid)
    {
        $board = Board::where('uuid', $uuid)->firstOrFail();
        $board->delete();

        return response()->json([
            'success' => true,
            'message' => 'Board deleted',
        ]);
    }

    public function templates()
    {
        $templates = config('board_templates');

        return response()->json([
            'success' => true,
            'templates' => collect($templates)->map(function ($tpl, $key) {
                return [
                    'id' => $key,
                    'title' => $tpl['title'],
                    'icon' => $tpl['icon'],
                ];
            })->values(),
        ]);
    }

    public function applyTemplate(Request $request, string $uuid)
    {
        $request->validate(['template' => 'required|string']);

        $board = Board::where('uuid', $uuid)->firstOrFail();

        // Вызываем существующий метод из HomeController
        app(\App\Http\Controllers\HomeController::class)->applyTemplate($request, $uuid);

        return response()->json([
            'success' => true,
            'board' => $board->fresh()->load(['columns.tasks', 'tags']),
        ]);
    }

    public function updateConfig(Request $request, string $uuid)
    {
        $board = Board::where('uuid', $uuid)->firstOrFail();

        $board->update([
            'config' => array_merge($board->config ?? [], $request->all()),
        ]);

        return response()->json([
            'success' => true,
            'config' => $board->config,
        ]);
    }
}
