<?php

namespace App\Http\Controllers;

use App\Events\BoardUpdated;
use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\TaskCreatedMail;

class KanbanController extends Controller
{
    public function getBoard($uuid)
    {
        $board = Board::where('uuid', $uuid)
            ->with([
                'columns' => function ($q) {
                    $q->withCount('tasks'); // 👈 вот это ключ
                },
                'columns.tasks' => function ($q) {
                    $q->withCount('comments')
                        ->orderBy('position', 'asc');
                }
            ])
            ->first();

        $board->columns->each(function ($column) {
            $column->setRelation(
                'tasks',
                $column->tasks->take(5)
            );
        });

        return $board;
    }

    public function duplicate(Task $task)
    {
        $newTask = Task::create([
            'board_id' => $task->board_id,
            'column_id' => $task->column_id,
            'title' => $task->title . ' (копия)',
            'description' => $task->description,
            'priority' => $task->priority,
            'due_date' => $task->due_date,
            'labels' => $task->labels,
            'position' => Task::where('column_id', $task->column_id)->count()
        ]);

        // копируем теги
        if ($task->tags()->exists()) {
            $newTask->tags()->sync($task->tags->pluck('id'));
        }


        return $newTask->load('tags');
    }


    public function createBoard(Request $request)
    {
        return Board::create([
            'uuid' => Str::uuid(),
            'title' => $request->title,
            'description' => $request->description,
            'config' => $request->config ?? []
        ]);
    }


    public function index()
    {
        return Column::with('tasks')
            ->orderBy('position')
            ->get();
    }

    public function storeColumn(Request $request, $uuid)
    {
        $board = Board::where('uuid', $uuid)->firstOrFail();

        return $board->columns()->create([
            'title' => $request->title,
            'position' => $board->columns()->count(),
            'thread' => $board->columns()->count(),
            'can_remove' => true
        ]);
    }


    public function updateColumn(Request $request, Column $column)
    {
        $column->update($request->only('title', 'position'));
        return $column;
    }

    public function deleteColumn(Column $column)
    {
        if ($column->can_remove) {
            $column->delete();
            return response()->json(['status' => 'ok']);
        } else
            abort(400, 'Запрещено удаление, 400');
    }

    public function storeTask(Request $request, $uuid)
    {
        Log::info('storeTask called with UUID: ' . $uuid);
        Log::info('Request data: ' . json_encode($request->all()));

        $data = $request->validate([
            'title' => 'required|string',
            'column_id' => 'required|exists:columns,id',
            'type' => 'integer|in:1,2',
            'priority' => 'nullable|string|in:low,medium,high', // ← Сделать nullable
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'custom_data' => 'nullable|array', // ← НОВОЕ
            'client' => 'array|required_if:type,2',
            'client.company_name' => 'nullable|string',
            'client.phone' => 'nullable|string',
            'client.contact_person' => 'nullable|string',
            'client.source' => 'nullable|string',
            'client.address' => 'nullable|string',
            'client.links' => 'nullable|array',
            'client.deal_comment' => 'nullable|string',
            'client.partner' => 'nullable|string',
            'client.cost' => 'nullable|numeric',
            'client.placement_type' => 'nullable|string',
            'client.custom_data' => 'nullable|array', // ← НОВОЕ
        ]);

        $board = Board::where('uuid', $uuid)->firstOrFail();

        // Сдвигаем все задачи вниз
        Task::where('column_id', $request->column_id)
            ->increment('position');

        $maxPosition = Task::where('column_id', $data['column_id'])->max('position') ?? 0;

        $task = $board->tasks()->create([
            'column_id' => $request->column_id,
            'board_id' => $board->id,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $data['priority'] ?? 'low', // ← Дефолтное значение
            'due_date' => $request->due_date,
            'labels' => $request->labels ?? [],
            'subtasks' => $request->subtasks ?? [],
            'custom_data' => $data['custom_data'] ?? [], // ← НОВОЕ
            'position' => 0//Task::where('column_id', $request->column_id)->count()
        ]);

        if (($data['type'] ?? 1) === 2 && isset($data['client'])) {
            $task->client()->create([
                'company_name' => $data['client']['company_name'] ?? '',
                'contact_person' => $data['client']['contact_person'] ?? '',
                'phone' => $data['client']['phone'] ?? '',
                'source' => $data['client']['source'] ?? '',
                'address' => $data['client']['address'] ?? '',
                'placement_type' => $data['client']['placement_type'] ?? '',
                'cost' => $data['client']['cost'] ?? null,
                'partner' => $data['client']['partner'] ?? '',
                'deal_comment' => $data['client']['deal_comment'] ?? '',
                'links' => $data['client']['links'] ?? [],
                'custom_data' => $data['client']['custom_data'] ?? [], // ← НОВОЕ
            ]);
            $task->load('client'); // подгружаем обратно для ответа
        }

        Log::info('web task create' . print_r([
                'column_id' => $request->column_id,
                'title' => $request->title,
                'board_id' => $board->id,
                'description' => $request->description,
                'priority' => $request->priority,
                'due_date' => $request->due_date,
                'labels' => $request->labels ?? [],
                'subtasks' => $request->subtasks ?? [],
                'position' => 0//Task::where('column_id', $request->column_id)->count()
            ], true));

        $task->tags()->sync($request->tag_ids ?? []);

        try {
            Log::info('Mail: Attempting to send task creation email for task: ' . $task->title);
            Mail::to('owner@example.com')->send(new TaskCreatedMail($task));
            Log::info('Mail: Task creation email sent successfully.');
        } catch (\Exception $e) {
            Log::error('Mail Error: ' . $e->getMessage());
        }

        return $task;
    }


    public function updateTask(Request $request, Task $task)
    {
        $isClient = $request->type === 2;

        $rules = [
            'column_id' => 'required|exists:columns,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string|in:low,medium,high',
            'due_date' => 'nullable|date',
            'labels' => 'nullable|array',
            'subtasks' => 'nullable|array',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
            'custom_data' => 'nullable|array', // ← НОВОЕ
        ];

        if ($isClient) {
            $rules['client'] = 'required|array';
            $rules['client.company_name'] = 'nullable|string|max:255';
            $rules['client.contact_person'] = 'nullable|string|max:255';
            $rules['client.phone'] = 'nullable|string|max:50';
            $rules['client.source'] = 'nullable|string|max:255';
            $rules['client.address'] = 'nullable|string';
            $rules['client.placement_type'] = 'nullable|string|max:255';
            $rules['client.cost'] = 'nullable|numeric|min:0';
            $rules['client.partner'] = 'nullable|string|max:255';
            $rules['client.deal_comment'] = 'nullable|string';
            $rules['client.links'] = 'nullable|array';
            $rules['client.custom_data'] = 'nullable|array'; // ← НОВОЕ

        }
        $validated = $request->validate($rules);

        return DB::transaction(function () use ($task, $validated, $isClient) {
            // Обновляем задачу
            $task->update([
                'column_id' => $validated['column_id'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? '',
                'priority' => $validated['priority'] ?? 'low',
                'due_date' => $validated['due_date'] ?? null,
                'labels' => $validated['labels'] ?? [],
                'subtasks' => $validated['subtasks'] ?? [],
                'custom_data' => $validated['custom_data'] ?? [], // ← НОВОЕ
            ]);

            // Синхронизируем теги
            $task->tags()->sync($validated['tag_ids'] ?? []);

            // Если это клиент — обновляем связанные данные
            if ($isClient && isset($validated['client'])) {
                // ⚠️ КЛЮЧЕВОЕ: принудительно перезагружаем клиента из БД перед обновлением
                if (!$task->client) {
                    $task->load('client');
                }

                if ($task->client) {
                    // Перезагружаем клиента из БД, чтобы getDirty() видел реальные изменения
                    $task->client->refresh();

                    // Теперь обновляем — Laravel увидит реальные изменения
                    $task->client->update($validated['client']);

                    // Принудительно обновляем updated_at, даже если Laravel считает, что ничего не изменилось
                    $task->client->touch();
                }
            }

            // Перезагружаем всё с нуля из БД
            return $task->refresh()->load(['tags', 'client', 'messages'])->loadCount('comments');
        });
    }

    public function deleteTask(Task $task)
    {
        $task->delete();
        return response()->json(['status' => 'ok']);
    }

    public function moveTask(Request $request)
    {
        $task = Task::find($request->task_id);

        if (is_null($task))
            throw new \HttpException("Task Not Found", 404);

        $task->update([
            'column_id' => $request->to_column_id ?? $request->column_id,
            'position' => $request->position ?? 0
        ]);

        return $task;
    }


    public function renameColumn(Request $request, Column $column)
    {
        $request->validate([
            'title' => 'required|string|max:255'
        ]);

        $column->update([
            'title' => $request->title
        ]);

        return $column;
    }

}
