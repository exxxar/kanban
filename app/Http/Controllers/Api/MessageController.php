<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CardMessage;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    /**
     * Получить все сообщения задачи
     */
    public function index(int $taskId)
    {
        $task = Task::findOrFail($taskId);

        $messages = $task->messages()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($message) => $this->formatMessage($message));

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    /**
     * Отправить сообщение
     */
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

        // === СОХРАНЕНИЕ ФАЙЛОВ ===
        $attachments = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('messages', 'public');

                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'url' => Storage::disk('public')->url($path),
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ];
            }
        }

        // === СОЗДАНИЕ СООБЩЕНИЯ ===
        $message = $task->messages()->create([
            'sender_type' => $validated['sender_type'],
            'sender_label' => $validated['sender_label'] ?? null,
            'message' => $validated['message'] ?? null,
            'payload' => $validated['payload'] ?? [],
            'attachments' => $attachments,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => $this->formatMessage($message),
        ], 201);
    }

    /**
     * Отметить сообщение как прочитанное
     */
    public function markRead(int $messageId)
    {
        $message = CardMessage::findOrFail($messageId);
        $message->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => $this->formatMessage($message->fresh()),
        ]);
    }

    /**
     * Отметить все сообщения задачи как прочитанные
     */
    public function markAllRead(int $taskId)
    {
        $task = Task::findOrFail($taskId);

        $updated = $task->messages()
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => "Marked {$updated} messages as read",
            'updated_count' => $updated,
        ]);
    }

    /**
     * Удалить сообщение
     */
    public function destroy(int $messageId)
    {
        $message = CardMessage::findOrFail($messageId);

        // Удаляем файлы с диска
        if (!empty($message->attachments)) {
            foreach ($message->attachments as $attachment) {
                if (!empty($attachment['path']) && Storage::disk('public')->exists($attachment['path'])) {
                    Storage::disk('public')->delete($attachment['path']);
                }
            }
        }

        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message deleted',
        ]);
    }

    /**
     * Обновить сообщение
     */
    public function update(Request $request, int $messageId)
    {
        $message = CardMessage::findOrFail($messageId);

        $validated = $request->validate([
            'message' => 'nullable|string',
            'payload' => 'nullable|array',
        ]);

        $message->update($validated);

        return response()->json([
            'success' => true,
            'message' => $this->formatMessage($message->fresh()),
        ]);
    }

    /**
     * Добавить вложения к существующему сообщению
     */
    public function addAttachments(Request $request, int $messageId)
    {
        $message = CardMessage::findOrFail($messageId);

        $validated = $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:10240',
        ]);

        $attachments = $message->attachments ?? [];

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('messages', 'public');

                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'url' => Storage::disk('public')->url($path),
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ];
            }
        }

        $message->update(['attachments' => $attachments]);

        return response()->json([
            'success' => true,
            'message' => $this->formatMessage($message->fresh()),
        ]);
    }

    /**
     * Удалить вложение из сообщения
     */
    public function removeAttachment(int $messageId, int $attachmentIndex)
    {
        $message = CardMessage::findOrFail($messageId);
        $attachments = $message->attachments ?? [];

        if (!isset($attachments[$attachmentIndex])) {
            return response()->json([
                'success' => false,
                'message' => 'Attachment not found',
            ], 404);
        }

        // Удаляем файл с диска
        $attachment = $attachments[$attachmentIndex];
        if (!empty($attachment['path']) && Storage::disk('public')->exists($attachment['path'])) {
            Storage::disk('public')->delete($attachment['path']);
        }

        // Удаляем из массива
        array_splice($attachments, $attachmentIndex, 1);
        $message->update(['attachments' => $attachments]);

        return response()->json([
            'success' => true,
            'message' => $this->formatMessage($message->fresh()),
        ]);
    }

    /**
     * Форматирование сообщения для ответа
     * Добавляет URL к вложениям если его нет
     */
    protected function formatMessage(CardMessage $message): array
    {
        $data = $message->toArray();

        // Добавляем URL к вложениям если его нет
        if (!empty($data['attachments'])) {
            $data['attachments'] = array_map(function ($attachment) {
                if (empty($attachment['url']) && !empty($attachment['path'])) {
                    $attachment['url'] = Storage::disk('public')->url($attachment['path']);
                }
                return $attachment;
            }, $data['attachments']);
        }

        return $data;
    }
}
