<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Http\Request;

class ColumnController extends Controller
{
    public function reorder(Request $request, $uuid)
    {
        $board = Board::where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer'
        ]);

        foreach ($request->order as $position => $columnId) {
            \App\Models\Column::where('id', $columnId)
                ->where('board_id', $board->id)
                ->update(['position' => $position]);
        }

        return response()->json(['status' => 'ok']);
    }

}
