<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $tasks = Task::with(['tags', 'client', 'comments', 'attachments', 'messages'])
            ->withCount('comments')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'tasks' => $tasks->items(),
            'pagination' => [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'total' => $tasks->total(),
            ],
        ]);
    }

    public function indexByBoard(Request $request, string $boardUuid)
    {
        $board = Board::where('uuid', $boardUuid)->firstOrFail();

        $tasks = Task::where('board_id', $board->id)
            ->with(['tags', 'client', 'comments', 'attachments', 'messages'])
            ->withCount('comments')
            ->orderBy('column_id')
            ->orderBy('position')
            ->get();

        return response()->json([
            'success' => true,
            'tasks' => $tasks,
        ]);
    }

    public function indexByColumn(Request $request, int $columnId)
    {
        $tasks = Task::where('column_id', $columnId)
            ->with(['tags', 'client', 'comments', 'attachments', 'messages'])
            ->withCount('comments')
            ->orderBy('position')
            ->get();

        return response()->json([
            'success' => true,
            'tasks' => $tasks,
        ]);
    }

    public function store(Request $request, string $boardUuid)
    {
        $board = Board::where('uuid', $boardUuid)->firstOrFail();

        // Используем существующий метод из ApiController
        $request->merge(['board_uuid' => $boardUuid]);
        return app(\App\Http\Controllers\ApiController::class)->create($request);
    }

    public function show(int $taskId)
    {
        $task = Task::with(['tags', 'client', 'comments.attachments', 'attachments', 'messages.attachments'])
            ->withCount('comments')
            ->findOrFail($taskId);

        return response()->json([
            'success' => true,
            'task' => $task,
        ]);
    }

    public function update(Request $request, int $taskId)
    {
        $task = Task::findOrFail($taskId);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|in:low,medium,high',
            'due_date' => 'nullable|date',
            'labels' => 'sometimes|array',
            'tag_ids' => 'sometimes|array',
            'subtasks' => 'sometimes|array',
            'custom_data' => 'sometimes|array',
        ]);

        $task->update($validated);

        if (isset($validated['tag_ids'])) {
            $task->tags()->sync($validated['tag_ids']);
        }

        return response()->json([
            'success' => true,
            'task' => $task->fresh()->load(['tags', 'client']),
        ]);
    }

    public function destroy(int $taskId)
    {
        $task = Task::findOrFail($taskId);
        $task->comments()->delete();
        $task->messages()->delete();
        $task->client()?->delete();
        $task->tags()->detach();
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted',
        ]);
    }

    public function duplicate(int $taskId)
    {
        $task = Task::with(['tags', 'client'])->findOrFail($taskId);

        $newTask = $task->replicate();
        $newTask->title = $task->title . ' (копия)';
        $newTask->save();

        if ($task->client) {
            $newClient = $task->client->replicate();
            $newClient->task_id = $newTask->id;
            $newClient->save();
        }

        $newTask->tags()->attach($task->tags->pluck('id'));

        return response()->json([
            'success' => true,
            'task' => $newTask->load(['tags', 'client']),
        ], 201);
    }

    public function markViewed(int $taskId)
    {
        $task = Task::findOrFail($taskId);
        $task->update(['last_viewed_at' => now()]);

        return response()->json([
            'success' => true,
            'last_viewed_at' => $task->last_viewed_at,
        ]);
    }

    public function move(Request $request)
    {
        $validated = $request->validate([
            'task_id' => 'required|integer|exists:tasks,id',
            'column_id' => 'required|integer|exists:columns,id',
        ]);

        $task = Task::findOrFail($validated['task_id']);
        $targetColumn = Column::findOrFail($validated['column_id']);

        $maxPosition = $targetColumn->tasks()->max('position') ?? -1;

        $task->update([
            'column_id' => $targetColumn->id,
            'position' => $maxPosition + 1,
        ]);

        return response()->json([
            'success' => true,
            'task' => $task->fresh(),
        ]);
    }

    public function reorder(Request $request, int $columnId)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:tasks,id',
        ]);

        foreach ($validated['order'] as $index => $taskId) {
            Task::where('id', $taskId)
                ->where('column_id', $columnId)
                ->update(['position' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tasks reordered',
        ]);
    }
}
