<?php

namespace App\Http\Controllers;

use App\Models\CardMessage;
use App\Models\Column;
use App\Models\Tag;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function updateNotifications(Request $request, Column $column)
    {
        $validated = $request->validate([
            'notifications' => 'required|array'
        ]);

        $column->notifications = $validated['notifications'];
        $column->save();

        return response()->json([
            'ok' => true,
            'column_id' => $column->id,
            'notifications' => $column->notifications,
            'message' => 'Настройки уведомлений успешно сохранены'
        ]);
    }

    public function markViewed(Task $task)
    {
        $task->update([
            'last_viewed_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function addAttachments(Request $request, Task $task)
    {
        $request->validate([
            'files.*' => 'required|file|max:20480', // 20MB
        ]);

        $existing = $task->attachments ?? [];
        $newFiles = [];

        foreach ($request->file('files') as $file) {
            $path = $file->store("tasks/{$task->id}/attachments", 'public');

            $newFiles[] = [
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ];
        }

        $task->attachments = array_merge($existing, $newFiles);
        $task->save();

        return response()->json($task->attachments);
    }


    public function paginated(Request $request, Column $column)
    {
        $tasks = $column->tasks()
            ->with('tags')
            ->withCount('comments')
            ->orderBy('position', 'desc')
            ->paginate(5);

        return response()->json($tasks);
    }

    public function reorder(Request $request, Column $column)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer'
        ]);

        foreach ($request->order as $position => $taskId) {
            Task::where('id', $taskId)
                ->where('column_id', $column->id)
                ->update(['position' => $position]);

        }

        return response()->json(['status' => 'ok']);
    }


    /**
     * Принять заказ в работу
     *
     * POST /api/v1/tasks/{taskId}/accept
     */
    public function accept(Request $request, int $taskId)
    {
        $validated = $request->validate([
            'order_id' => 'nullable|integer',
        ]);

        $task = Task::with(['board', 'tags', 'client'])->findOrFail($taskId);

        try {
            $result = DB::transaction(function () use ($task, $validated) {
                // === 1. МЕНЯЕМ ПРИОРИТЕТ НА ВЫСОКИЙ ===
                $task->update([
                    'priority' => 'high',
                ]);

                // === 2. ДОБАВЛЯЕМ ТЕГ "ЗАКАЗ В РАБОТЕ" ===
                $this->attachInProgressTag($task);

                // === 3. ОТПРАВЛЯЕМ СООБЩЕНИЕ В ЧАТ ===
                $message = $this->sendAcceptMessage($task, $validated['order_id'] ?? null);

                // === 4. ОТПРАВЛЯЕМ ВЕБХУК ===
                $webhookResult = $this->triggerWebhook($task, 'order_accepted');

                return [
                    'success' => true,
                    'task_id' => $task->id,
                    'message_id' => $message->id,
                    'priority' => $task->priority,
                    'tags' => $task->tags->pluck('name'),
                    'webhook' => $webhookResult,
                ];
            });

            return response()->json($result);

        } catch (\Throwable $e) {
            Log::error('[TaskAccept] Ошибка принятия заказа: ' . $e->getMessage(), [
                'task_id' => $taskId,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при принятии заказа: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Добавить тег "Заказ в работе"
     */
    private function attachInProgressTag(Task $task): void
    {
        $tagName = 'Заказ в работе';
        $tagColor = '#f59e0b'; // оранжевый

        // Ищем существующий тег на доске
        $tag = Tag::where('board_id', $task->board_id)
            ->where('name', $tagName)
            ->first();

        // Если нет — создаём
        if (!$tag) {
            $tag = Tag::create([
                'board_id' => $task->board_id,
                'name' => $tagName,
                'color' => $tagColor,
            ]);
        }

        // Привязываем к задаче (если ещё не привязан)
        if (!$task->tags->contains($tag->id)) {
            $task->tags()->attach($tag->id);
        }
    }

    /**
     * Отправить сообщение о принятии заказа
     */
    private function sendAcceptMessage(Task $task, ?int $orderId): \Illuminate\Database\Eloquent\Model
    {
        $orderText = $orderId ? "Заказ #{$orderId}" : "Заказ";
        $clientName = $task->client?->contact_person
            ?? $task->client?->company_name
            ?? 'Клиент';

        $messageText = "✅ **{$orderText} принят в работу!**\n\n" .
            "👤 Клиент: **{$clientName}**\n" .
            "⚡ Приоритет повышен до **высокого**\n" .
            "🏷️ Добавлен тег: **Заказ в работе**\n\n" .
            "Менеджер приступил к обработке. Ожидайте updates!";

        return $task->messages()->create([
            'sender_type' => 'manager',
            'sender_label' => 'CRM System',
            'message' => $messageText,
            'payload' => [
                'type' => 'order_accepted',
                'order_id' => $orderId,
                'action' => 'accept',
                'timestamp' => now()->toIso8601String(),
            ],
            'attachments' => [],
            'is_read' => true,
        ]);
    }

    /**
     * Отправить вебхук
     */
    private function triggerWebhook(Task $task, string $event): array
    {
        $config = $task->board->config ?? [];
        $webhookUrl = $config['webhook_url'] ?? null;

        if (empty($webhookUrl)) {
            return [
                'sent' => false,
                'reason' => 'webhook_not_configured',
            ];
        }

        try {
            $payload = [
                'event' => $event,
                'timestamp' => now()->toIso8601String(),
                'task' => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'priority' => $task->priority,
                    'type' => $task->type,
                    'board_uuid' => $task->board->uuid,
                ],
                'client' => $task->client ? [
                    'id' => $task->client->id,
                    'company_name' => $task->client->company_name,
                    'contact_person' => $task->client->contact_person,
                    'phone' => $task->client->phone,
                    'cost' => $task->client->cost,
                ] : null,
                'tags' => $task->tags->map(fn($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'color' => $tag->color,
                ])->toArray(),
                'meta' => [
                    'action' => 'order_accepted',
                    'accepted_at' => now()->toIso8601String(),
                ],
            ];

            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Webhook-Event' => $event,
                    'X-Task-Id' => $task->id,
                ])
                ->post($webhookUrl, $payload);

            return [
                'sent' => true,
                'status' => $response->status(),
                'url' => $webhookUrl,
            ];

        } catch (\Throwable $e) {
            Log::warning('[TaskAccept] Webhook failed: ' . $e->getMessage());

            return [
                'sent' => false,
                'reason' => 'webhook_error',
                'error' => $e->getMessage(),
            ];
        }
    }
}
