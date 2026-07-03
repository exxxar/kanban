<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(int $taskId)
    {
        $task = Task::findOrFail($taskId);

        $messages = $task->messages()
            ->with('attachments')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    public function store(Request $request, int $taskId)
    {
        $task = Task::findOrFail($taskId);

        $validated = $request->validate([
            'message' => 'nullable|string',
            'payload' => 'nullable|array',
            'sender_type' => 'required|string',
            'sender_label' => 'nullable|string',
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240',
        ]);

        $message = $task->messages()->create([
            'sender_type' => $validated['sender_type'],
            'sender_label' => $validated['sender_label'] ?? null,
            'message' => $validated['message'] ?? null,
            'payload' => $validated['payload'] ?? [],
            'is_read' => false,
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('messages', 'public');
                $message->attachments()->create([
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => $message->load('attachments'),
        ], 201);
    }

    public function markRead(int $messageId)
    {
        $message = Message::findOrFail($messageId);
        $message->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => $message->fresh(),
        ]);
    }

    public function markAllRead(int $taskId)
    {
        $task = Task::findOrFail($taskId);
        $task->messages()->where('is_read', false)->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All messages marked as read',
        ]);
    }
}
