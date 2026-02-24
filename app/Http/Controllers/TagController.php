<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Tag;
use Illuminate\Http\Request;
class TagController extends Controller
{
    public function index($uuid)
    {
        $board = Board::where('uuid', $uuid)->firstOrFail();
        return $board->tags()->get();
    }

    public function store(Request $request, $uuid)
    {
        $board = Board::where('uuid', $uuid)->firstOrFail();

        return $board->tags()->create([
            'name' => $request->name,
            'color' => $request->color ?? '#999999'
        ]);
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();
        return response()->json(['status' => 'ok']);
    }
}
