<?php

namespace App\Http\Controllers;

use App\Events\BoardUpdated;
use App\Models\Task;
use App\Models\Column;
use App\Models\Board;
use Exxxar\Kanban\DTO\MessageDto;
use Illuminate\Http\Request;
use App\Enums\CardTypeEnum;
use Illuminate\Support\Facades\Mail;
use App\Mail\TaskCreatedMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ApiController extends Controller
{
    public function create(Request $request)
    {
        $validated = $request->validate([
         //   'board_uuid' => 'required|string',
            'thread' => 'required|integer',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high',
            'type' => 'required|integer|min:0|max:4',
            'due_date' => 'nullable|date',
            'labels' => 'nullable|array',
            'data' => 'nullable|array',
            'subtasks' => 'nullable|array',
        ]);


        // Используем доску, которую уже нашел Middleware ApiAuth
        $board = $request->board;

        if (!$board) {
             Log::error('API Error: Board not found in request context.');
             return response()->json(['success' => false, 'message' => 'Board context missing'], 500);
        }


        $column = Column::query()
            ->where('board_id', $board->id)
            ->where('thread', $validated['thread'])
            ->first();

        if (!$column) {
            $column = Column::query()
                ->where('board_id', $board->id)
                ->where('thread', 0)
                ->firstOrFail();

            if (is_null($column)){
                $column = Column::query()
                    ->create([
                        "board_id"=>$board->id,
                        'title'=>"По умолчанию",
                        'position'=>0,
                        'thread'=>0,
                        'can_remove'=>false
                    ]);
            }
        }



        $type = CardTypeEnum::from($validated['type']);

        $defaults = $this->defaultsByType($type);

        $payload = array_merge($defaults, $validated);

        $payload['board_id'] = $board->id;
        $payload['column_id'] = $column->id;

        Log::info('api task create'.print_r($payload, true));

        Task::where('column_id', $column->id)
            ->increment('position');

        $task = $board->tasks()->create($payload);

        $mailTo = $board->config["email_for_notification"] ?? null;
        $canSendEmailNotification = $board->config["need_email_notification"] ?? false;

        if (!is_null($mailTo) && $canSendEmailNotification) {
            try {
                Mail::to($mailTo)->send(new TaskCreatedMail($task));
            } catch (\Exception $e) {
                Log::warning('Could not send API task creation email: ' . $e->getMessage());
            }
        }

        event(new BoardUpdated($board));

        return response()->json([
            'success' => true,
            'task' => $task
        ]);
    }

    public function getTasks(Request $request)
    {
        $board = $request->board;

        if (!$board) {
            Log::error('API Error: Board not found in request context.');
            return response()->json(['success' => false, 'message' => 'Board context missing'], 500);
        }

        $tasks = Task::where('board_id', $board->id)->get();

        return response()->json([
            'success' => true,
            'tasks' => $tasks
        ]);
    }

    public function getTask(Request $request, $taskId)
    {
        $board = $request->board;

        if (!$board) {
            Log::error('API Error: Board not found in request context.');
            return response()->json(['success' => false, 'message' => 'Board context missing'], 500);
        }

        $task = Task::where('board_id', $board->id)
            ->where('id', $taskId)
            ->first();

        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        return response()->json([
            'success' => true,
            'task' => $task
        ]);
    }

    public function comments(Request $request, $taskId)
    {
        $board = $request->board;

        if (!$board) {
            Log::error('API Error: Board not found in request context.');
            return response()->json(['success' => false, 'message' => 'Board context missing'], 500);
        }

        $task = Task::where('board_id', $board->id)
            ->where('id', $taskId)
            ->first();

        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        return response()->json([
            'success' => true,
            'comments' => $task->comments
        ]);
    }

    public function addComment(Request $request, $taskId)
    {
        $validated = $request->validate([
            'text' => 'required|string',
        ]);

        $board = $request->board;

        if (!$board) {
            Log::error('API Error: Board not found in request context.');
            return response()->json(['success' => false, 'message' => 'Board context missing'], 500);
        }

        $task = Task::where('board_id', $board->id)
            ->where('id', $taskId)
            ->firstOrFail();

        $comment = $task->comments()->create([
            'text' => $validated['text'],
        ]);

        return response()->json([
            'success' => true,
            'comment' => $comment
        ]);
    }

    public function attachments(Request $request, $taskId)
    {
        $board = $request->board;

        if (!$board) {
            Log::error('API Error: Board not found in request context.');
            return response()->json(['success' => false, 'message' => 'Board context missing'], 500);
        }

        $task = Task::where('board_id', $board->id)
            ->where('id', $taskId)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'attachments' => $task->attachments
        ]);
    }

    public function uploadAttachments(Request $request, $taskId)
    {
        $validated = $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:10240',
        ]);

        $board = $request->board;

        if (!$board) {
            Log::error('API Error: Board not found in request context.');
            return response()->json(['success' => false, 'message' => 'Board context missing'], 500);
        }

        $task = Task::where('board_id', $board->id)
            ->where('id', $taskId)
            ->firstOrFail();



        if ($request->hasFile('files')) {
            $attachments = $task->attachments ?? [];
            foreach ($request->file('files') as $file) {
                $path = $file->store('attachments', 'public');
                $attachments[] = [
                    'url' => $path,
                    'title' => $file->getClientOriginalName(),
                ];

            }
            $task->attachments = $attachments ?? [];
            $task->save();
        }

        return response()->json([
            'success' => true,
            'attachments' => $attachments
        ]);
    }

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
            Log::error('API Error: Board not found in request context.');
            return response()->json([
                'success' => false,
                'message' => 'Board context missing'
            ], 500);
        }

        $task = Task::where('board_id', $board->id)
            ->where('id', $taskId)
            ->firstOrFail();

        // 📩 создаём сообщение
        $message = $task->messages()->create([
            'sender_type' => $validated['sender_type'],
            'sender_label' => $validated['sender_label'] ?? null,
            'message' => $validated['message'] ?? null,
            'payload' => $validated['payload'] ?? [],
            'is_read' => false,
        ]);

        // 📎 сохраняем файлы
        if ($request->hasFile('files')) {
            $attachments = $message->attachments ?? [];
            foreach ($request->file('files') as $file) {
                $path = $file->store('attachments', 'public');
                $attachments[] = [
                    'url' => $path,
                    'title' => $file->getClientOriginalName(),
                ];

            }
            $message->attachments = $attachments ?? [];
            $message->save();
        }


        // 🔥 приводим к DTO-совместимому массиву
        $data = $message->toArray();

        $data['attachments'] = $message->attachments->map(function ($a) {
            return [
                'title' => $a->name,
                'url' => Storage::disk('public')->url($a->path),
            ];
        })->toArray();

        // 🚀 возвращаем строго по DTO
        return response()->json([
            'success' => true,
            'message' => MessageDto::fromArray($data)
        ]);
    }

    private function defaultsByType(CardTypeEnum $type): array
    {
        return match ($type) {

            CardTypeEnum::BASE => [
                'priority' => 'low',
                'labels' => [],
                'data' => [],
                'subtasks' => [],
            ],

            CardTypeEnum::USER => [
                'priority' => 'medium',
                'labels' => ['client'],
                'data' => [
                    'phone' => null,
                    'email' => null,
                    'city' => null,
                    'company' => null,
                    'position' => null,
                    'notes' => null,
                ],
                'subtasks' => [],
            ],

            CardTypeEnum::ORDER => [
                'priority' => 'high',
                'labels' => ['order'],
                'data' => [
                    'product' => null,
                    'quantity' => null,
                    'price' => null,
                    'discount' => null,
                    'address' => null,
                    'comment' => null,
                    'parts' => [],
                ],
                'subtasks' => [],
            ],

            CardTypeEnum::TEXT => [
                'priority' => 'low',
                'labels' => ['text'],
                'data' => [
                    'question' => null,
                    'answer' => null,
                ],
                'subtasks' => [],
            ],

            CardTypeEnum::FINANCE => [
                'priority' => 'medium',
                'labels' => ['finance'],
                'data' => [
                    'amount' => null,
                    'currency' => '₽',
                    'operation' => null,
                    'balanceAfter' => null,
                    'comment' => null,
                ],
                'subtasks' => [],
            ],
        };
    }
}
