<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(int $taskId)
    {
        $task = Task::findOrFail($taskId);

        $comments = $task->comments()
            ->with('attachments')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'comments' => $comments,
        ]);
    }

    public function store(Request $request, int $taskId)
    {
        $task = Task::findOrFail($taskId);

        $validated = $request->validate([
            'text' => 'required|string',
            'author' => 'nullable|string|max:255',
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240',
        ]);

        $comment = $task->comments()->create([
            'text' => $validated['text'],
            'author' => $validated['author'] ?? 'API User',
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('comments', 'public');
                $comment->attachments()->create([
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'comment' => $comment->load('attachments'),
        ], 201);
    }

    public function update(Request $request, int $commentId)
    {
        $comment = Comment::findOrFail($commentId);

        $validated = $request->validate([
            'text' => 'required|string',
        ]);

        $comment->update($validated);

        return response()->json([
            'success' => true,
            'comment' => $comment->fresh(),
        ]);
    }

    public function destroy(int $commentId)
    {
        $comment = Comment::findOrFail($commentId);
        $comment->attachments()->delete();
        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted',
        ]);
    }
}
