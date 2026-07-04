<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function show(int $taskId)
    {
        $task = Task::findOrFail($taskId);

        if (!$task->client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'client' => $task->client,
        ]);
    }

    public function update(Request $request, int $taskId)
    {
        $task = Task::findOrFail($taskId);

        if (!$task->client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found',
            ], 404);
        }

        $validated = $request->validate([
            'company_name' => 'sometimes|string|max:255',
            'contact_person' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:50',
            'source' => 'sometimes|string|max:255',
            'address' => 'nullable|string',
            'placement_type' => 'sometimes|string|max:255',
            'cost' => 'sometimes|numeric|min:0',
            'partner' => 'nullable|string|max:255',
            'deal_comment' => 'nullable|string',
            'links' => 'sometimes|array',
            'custom_data' => 'sometimes|array',
        ]);

        $task->client->update($validated);

        return response()->json([
            'success' => true,
            'client' => $task->client->fresh(),
        ]);
    }

    /**
     * 🆕 Поиск клиента по телефону на доске
     *
     * GET /api/v1/boards/{uuid}/clients/search?phone=+79991234567
     */
    public function searchByPhone(Request $request, string $boardUuid)
    {
        $validated = $request->validate([
            'phone' => 'required|string|min:10',
        ]);

        $board = Board::where('uuid', $boardUuid)->firstOrFail();

        // Нормализуем телефон — оставляем только цифры
        $searchPhone = preg_replace('/\D/', '', $validated['phone']);

        if (strlen($searchPhone) < 10) {
            return response()->json([
                'success' => false,
                'message' => 'Телефон слишком короткий',
            ], 422);
        }

        // Берём последние 10 цифр для сравнения
        $phoneTail = substr($searchPhone, -10);

        // Ищем задачи-клиенты (type=2) на этой доске
        $tasks = Task::where('board_id', $board->id)
            ->where('type', 2) // Только клиенты
            ->whereHas('client', function ($query) use ($phoneTail) {
                $query->whereNotNull('phone')
                    ->where('phone', '!=', '');
            })
            ->with('client')
            ->get();

        // Фильтруем по совпадению последних 10 цифр
        $matchedClients = $tasks->filter(function ($task) use ($phoneTail) {
            if (!$task->client || empty($task->client->phone)) {
                return false;
            }

            $clientPhone = preg_replace('/\D/', '', $task->client->phone);

            if (strlen($clientPhone) < 10) {
                return false;
            }

            return substr($clientPhone, -10) === $phoneTail;
        });

        // Форматируем результаты
        $results = $matchedClients->map(function ($task) {
            return [
                'task_id' => $task->id,
                'title' => $task->title,
                'client' => [
                    'id' => $task->client->id,
                    'company_name' => $task->client->company_name,
                    'contact_person' => $task->client->contact_person,
                    'phone' => $task->client->phone,
                    'source' => $task->client->source,
                    'cost' => $task->client->cost,
                    'custom_data' => $task->client->custom_data,
                ],
                'column_id' => $task->column_id,
                'created_at' => $task->created_at,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'query' => $validated['phone'],
            'normalized' => $searchPhone,
            'found' => $results->count(),
            'clients' => $results,
        ]);
    }

    /**
     * 🆕 Поиск клиента по email
     */
    public function searchByEmail(Request $request, string $boardUuid)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $board = Board::where('uuid', $boardUuid)->firstOrFail();

        $tasks = Task::where('board_id', $board->id)
            ->where('type', 2)
            ->whereHas('client', function ($query) use ($validated) {
                $query->whereJsonContains('custom_data->email', $validated['email']);
            })
            ->with('client')
            ->get();

        $results = $tasks->map(function ($task) {
            return [
                'task_id' => $task->id,
                'title' => $task->title,
                'client' => [
                    'id' => $task->client->id,
                    'company_name' => $task->client->company_name,
                    'contact_person' => $task->client->contact_person,
                    'phone' => $task->client->phone,
                    'custom_data' => $task->client->custom_data,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'query' => $validated['email'],
            'found' => $results->count(),
            'clients' => $results,
        ]);
    }

    /**
     * 🆕 Универсальный поиск клиента (телефон, email, имя, компания)
     */
    public function search(Request $request, string $boardUuid)
    {
        $validated = $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $board = Board::where('uuid', $boardUuid)->firstOrFail();
        $query = mb_strtolower($validated['query']);

        $tasks = Task::where('board_id', $board->id)
            ->where('type', 2)
            ->with('client')
            ->get();

        // Фильтруем по всем полям
        $results = $tasks->filter(function ($task) use ($query) {
            // Поиск по названию задачи
            if (str_contains(mb_strtolower($task->title ?? ''), $query)) {
                return true;
            }

            if (!$task->client) {
                return false;
            }

            // Поиск по полям клиента
            $searchableFields = [
                $task->client->company_name,
                $task->client->contact_person,
                $task->client->phone,
                $task->client->source,
                $task->client->address,
                $task->client->partner,
            ];

            foreach ($searchableFields as $field) {
                if ($field && str_contains(mb_strtolower($field), $query)) {
                    return true;
                }
            }

            // Поиск в custom_data
            if (!empty($task->client->custom_data)) {
                foreach ($task->client->custom_data as $value) {
                    if (is_string($value) && str_contains(mb_strtolower($value), $query)) {
                        return true;
                    }
                }
            }

            return false;
        })->map(function ($task) {
            return [
                'task_id' => $task->id,
                'title' => $task->title,
                'client' => [
                    'id' => $task->client->id,
                    'company_name' => $task->client->company_name,
                    'contact_person' => $task->client->contact_person,
                    'phone' => $task->client->phone,
                    'source' => $task->client->source,
                    'cost' => $task->client->cost,
                    'custom_data' => $task->client->custom_data,
                ],
                'column_id' => $task->column_id,
                'created_at' => $task->created_at,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'query' => $validated['query'],
            'found' => $results->count(),
            'clients' => $results,
        ]);
    }
}
