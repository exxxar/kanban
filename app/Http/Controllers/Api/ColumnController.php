<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Column;
use Illuminate\Http\Request;

class ColumnController extends Controller
{
    public function store(Request $request, string $boardUuid)
    {
        $board = Board::where('uuid', $boardUuid)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $maxPosition = $board->columns()->max('position') ?? -1;
        $maxThread = $board->columns()->max('thread') ?? -1;

        $column = $board->columns()->create([
            'title' => $validated['title'],
            'position' => $maxPosition + 1,
            'thread' => $maxThread + 1,
        ]);

        return response()->json([
            'success' => true,
            'column' => $column,
        ], 201);
    }

    public function update(Request $request, int $columnId)
    {
        $column = Column::findOrFail($columnId);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
        ]);

        $column->update($validated);

        return response()->json([
            'success' => true,
            'column' => $column->fresh(),
        ]);
    }

    public function destroy(int $columnId)
    {
        $column = Column::findOrFail($columnId);
        $column->tasks()->delete();
        $column->delete();

        return response()->json([
            'success' => true,
            'message' => 'Column deleted',
        ]);
    }

    public function reorder(Request $request, string $boardUuid)
    {
        $board = Board::where('uuid', $boardUuid)->firstOrFail();

        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:columns,id',
        ]);

        foreach ($validated['order'] as $index => $columnId) {
            Column::where('id', $columnId)
                ->where('board_id', $board->id)
                ->update(['position' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Columns reordered',
        ]);
    }

    public function updateNotifications(Request $request, int $columnId)
    {
        $column = Column::findOrFail($columnId);

        $column->update([
            'notifications' => $request->all(),
        ]);

        return response()->json([
            'success' => true,
            'notifications' => $column->notifications,
        ]);
    }
}
