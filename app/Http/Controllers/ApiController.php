<?php

namespace App\Http\Controllers;

use App\Events\BoardUpdated;
use App\Models\Task;
use App\Models\Client;
use App\Models\Column;
use App\Models\Board;
use App\Models\Tag;
use App\Models\Comment;
use App\Models\Attachment;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\TaskCreatedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    /**
     * Создание задачи или клиента
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'thread' => 'required|integer',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high',
            'type' => 'required|integer|in:1,2,3,4,5,6',
            'due_date' => 'nullable|date',
            'labels' => 'nullable|array',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|exists:tags,id',
            'subtasks' => 'nullable|array',
            'custom_data' => 'nullable|array',

            // Поля клиента (если type=2)
            'client' => 'nullable|array|required_if:type,2',
            'client.company_name' => 'nullable|string|max:255',
            'client.contact_person' => 'nullable|string|max:255',
            'client.phone' => 'nullable|string|max:50',
            'client.source' => 'nullable|string|max:255',
            'client.address' => 'nullable|string',
            'client.placement_type' => 'nullable|string|max:255',
            'client.cost' => 'nullable|numeric|min:0',
            'client.partner' => 'nullable|string|max:255',
            'client.deal_comment' => 'nullable|string',
            'client.links' => 'nullable|array',
            'client.custom_data' => 'nullable|array',
        ]);

        $board = $request->board;

        if (!$board) {
            return response()->json([
                'success' => false,
                'message' => 'Board context missing'
            ], 500);
        }

        // Находим колонку по thread
        $column = Column::where('board_id', $board->id)
            ->where('thread', $validated['thread'])
            ->first();

        if (!$column) {
            $column = Column::where('board_id', $board->id)
                ->where('thread', 0)
                ->first();

            if (!$column) {
                $column = Column::create([
                    'board_id' => $board->id,
                    'title' => 'По умолчанию',
                    'position' => 0,
                    'thread' => 0,
                    'can_remove' => false
                ]);
            }
        }

        return DB::transaction(function () use ($board, $column, $validated) {
            // Сдвигаем позиции существующих задач
            Task::where('column_id', $column->id)
                ->increment('position');

            // Создаём задачу
            $task = Task::create([
                'board_id' => $board->id,
                'column_id' => $column->id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? '',
                'priority' => $validated['priority'] ?? 'low',
                'type' => $validated['type'],
                'due_date' => $validated['due_date'] ?? null,
                'labels' => $validated['labels'] ?? [],
                'subtasks' => $validated['subtasks'] ?? [],
                'custom_data' => $validated['custom_data'] ?? [],
                'position' => 1,
            ]);

            // Привязываем теги
            if (!empty($validated['tag_ids'])) {
                $task->tags()->sync($validated['tag_ids']);
            }

            // Если это клиент (type=2) — создаём связанного клиента
            if ($validated['type'] === 2 && isset($validated['client'])) {
                $task->client()->create([
                    'company_name' => $validated['client']['company_name'] ?? '',
                    'contact_person' => $validated['client']['contact_person'] ?? '',
                    'phone' => $validated['client']['phone'] ?? '',
                    'source' => $validated['client']['source'] ?? '',
                    'address' => $validated['client']['address'] ?? '',
                    'placement_type' => $validated['client']['placement_type'] ?? '',
                    'cost' => $validated['client']['cost'] ?? null,
                    'partner' => $validated['client']['partner'] ?? '',
                    'deal_comment' => $validated['client']['deal_comment'] ?? '',
                    'links' => $validated['client']['links'] ?? [],
                    'custom_data' => $validated['client']['custom_data'] ?? [],
                ]);
            }

            // Загружаем связанные данные
            $task->load(['tags', 'client']);

            // Отправка email уведомления
            $this->sendNotificationEmail($board, $task);

            // Событие обновления доски
            event(new BoardUpdated($board));

            return response()->json([
                'success' => true,
                'task' => $task
            ], 201);
        });
    }

    /**
     * Получение всех задач доски
     */
    public function getTasks(Request $request)
    {
        $board = $request->board;

        if (!$board) {
            return response()->json([
                'success' => false,
                'message' => 'Board context missing'
            ], 500);
        }

        $tasks = Task::where('board_id', $board->id)
            ->with(['tags', 'client', 'comments', 'attachments', 'messages'])
            ->withCount('comments')
            ->orderBy('column_id')
            ->orderBy('position')
            ->get();

        return response()->json([
            'success' => true,
            'tasks' => $tasks,
            'count' => $tasks->count()
        ]);
    }

    /**
     * Получение одной задачи
     */
    public function getTask(Request $request, $taskId)
    {
        $board = $request->board;

        if (!$board) {
            return response()->json([
                'success' => false,
                'message' => 'Board context missing'
            ], 500);
        }

        $task = Task::where('board_id', $board->id)
            ->where('id', $taskId)
            ->with(['tags', 'client', 'comments', 'attachments', 'messages'])
            ->withCount('comments')
            ->first();

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'task' => $task
        ]);
    }

    /**
     * Получение комментариев задачи
     */
    public function comments(Request $request, $taskId)
    {
        $board = $request->board;

        if (!$board) {
            return response()->json([
                'success' => false,
                'message' => 'Board context missing'
            ], 500);
        }

        $task = Task::where('board_id', $board->id)
            ->where('id', $taskId)
            ->first();

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        $comments = $task->comments()
            ->with('attachments')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'comments' => $comments,
            'count' => $comments->count()
        ]);
    }

    /**
     * Добавление комментария
     */
    public function addComment(Request $request, $taskId)
    {
        $validated = $request->validate([
            'text' => 'required|string',
            'author' => 'nullable|string|max:255',
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240',
        ]);

        $board = $request->board;

        if (!$board) {
            return response()->json([
                'success' => false,
                'message' => 'Board context missing'
            ], 500);
        }

        $task = Task::where('board_id', $board->id)
            ->where('id', $taskId)
            ->firstOrFail();

        return DB::transaction(function () use ($task, $validated, $request) {
            // Создаём комментарий
            $comment = $task->comments()->create([
                'text' => $validated['text'],
                'author' => $validated['author'] ?? 'API User',
            ]);

            // Загружаем файлы
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

            $comment->load('attachments');

            return response()->json([
                'success' => true,
                'comment' => $comment
            ], 201);
        });
    }

    /**
     * Получение вложений задачи
     */
    public function attachments(Request $request, $taskId)
    {
        $board = $request->board;

        if (!$board) {
            return response()->json([
                'success' => false,
                'message' => 'Board context missing'
            ], 500);
        }

        $task = Task::where('board_id', $board->id)
            ->where('id', $taskId)
            ->firstOrFail();

        $attachments = $task->attachments()->get()->map(function ($attachment) {
            return [
                'id' => $attachment->id,
                'name' => $attachment->name,
                'path' => $attachment->path,
                'url' => Storage::disk('public')->url($attachment->path),
                'mime' => $attachment->mime,
                'size' => $attachment->size,
                'created_at' => $attachment->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'attachments' => $attachments,
            'count' => $attachments->count()
        ]);
    }

    /**
     * Загрузка вложений к задаче
     */
    public function uploadAttachments(Request $request, $taskId)
    {
        $validated = $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:10240',
        ]);

        $board = $request->board;

        if (!$board) {
            return response()->json([
                'success' => false,
                'message' => 'Board context missing'
            ], 500);
        }

        $task = Task::where('board_id', $board->id)
            ->where('id', $taskId)
            ->firstOrFail();

        $uploadedAttachments = [];

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('attachments', 'public');

                $attachment = $task->attachments()->create([
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);

                $uploadedAttachments[] = [
                    'id' => $attachment->id,
                    'name' => $attachment->name,
                    'path' => $attachment->path,
                    'url' => Storage::disk('public')->url($attachment->path),
                    'mime' => $attachment->mime,
                    'size' => $attachment->size,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'attachments' => $uploadedAttachments,
            'count' => count($uploadedAttachments)
        ], 201);
    }

    /**
     * Отправка сообщения в чат задачи
     */
    public function sendMessage(Request $request, $taskId)
    {
        $validated = $request->validate([
            'message' => 'nullable|string',
            'payload' => 'nullable|array',
            'sender_type' => 'required|string',
            'sender_label' => 'nullable|string',
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240',
        ]);

        $board = $request->board;

        if (!$board) {
            return response()->json([
                'success' => false,
                'message' => 'Board context missing'
            ], 500);
        }

        $task = Task::where('board_id', $board->id)
            ->where('id', $taskId)
            ->firstOrFail();

        return DB::transaction(function () use ($task, $validated, $request) {
            // Создаём сообщение
            $message = $task->messages()->create([
                'sender_type' => $validated['sender_type'],
                'sender_label' => $validated['sender_label'] ?? null,
                'message' => $validated['message'] ?? null,
                'payload' => $validated['payload'] ?? [],
                'is_read' => false,
            ]);

            // Загружаем файлы
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

            $message->load('attachments');

            // Форматируем вложения для ответа
            $attachments = $message->attachments->map(function ($a) {
                return [
                    'id' => $a->id,
                    'title' => $a->name,
                    'url' => Storage::disk('public')->url($a->path),
                    'mime' => $a->mime,
                    'size' => $a->size,
                ];
            })->toArray();

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'task_id' => $message->task_id,
                    'sender_type' => $message->sender_type,
                    'sender_label' => $message->sender_label,
                    'message' => $message->message,
                    'payload' => $message->payload,
                    'is_read' => $message->is_read,
                    'attachments' => $attachments,
                    'created_at' => $message->created_at,
                ]
            ], 201);
        });
    }

    /**
     * Отправка email уведомления
     */
    private function sendNotificationEmail($board, $task)
    {
        $mailTo = $board->config['email_for_notification'] ?? null;
        $canSendEmailNotification = $board->config['need_email_notification'] ?? false;

        if (!is_null($mailTo) && $canSendEmailNotification) {
            try {
                Mail::to($mailTo)->send(new TaskCreatedMail($task));
            } catch (\Exception $e) {
                Log::warning('Could not send API task creation email: ' . $e->getMessage());
            }
        }
    }
}
