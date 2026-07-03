<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Tag;
use App\Models\Task;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(string $boardUuid)
    {
        $board = Board::where('uuid', $boardUuid)->firstOrFail();

        return response()->json([
            'success' => true,
            'tags' => $board->tags,
        ]);
    }

    public function store(Request $request, string $boardUuid)
    {
        $board = Board::where('uuid', $boardUuid)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'sometimes|string|max:7',
        ]);

        $tag = $board->tags()->create([
            'name' => $validated['name'],
            'color' => $validated['color'] ?? '#999999',
        ]);

        return response()->json([
            'success' => true,
            'tag' => $tag,
        ], 201);
    }

    public function update(Request $request, int $tagId)
    {
        $tag = Tag::findOrFail($tagId);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'color' => 'sometimes|string|max:7',
        ]);

        $tag->update($validated);

        return response()->json([
            'success' => true,
            'tag' => $tag->fresh(),
        ]);
    }

    public function destroy(int $tagId)
    {
        $tag = Tag::findOrFail($tagId);
        $tag->tasks()->detach();
        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tag deleted',
        ]);
    }

    public function attachToTask(Request $request, int $taskId)
    {
        $task = Task::findOrFail($taskId);

        $validated = $request->validate([
            'tag_ids' => 'required|array',
            'tag_ids.*' => 'integer|exists:tags,id',
        ]);

        $task->tags()->syncWithoutDetaching($validated['tag_ids']);

        return response()->json([
            'success' => true,
            'tags' => $task->tags,
        ]);
    }

    public function detachFromTask(int $taskId, int $tagId)
    {
        $task = Task::findOrFail($taskId);
        $task->tags()->detach($tagId);

        return response()->json([
            'success' => true,
            'message' => 'Tag detached',
        ]);
    }
}
