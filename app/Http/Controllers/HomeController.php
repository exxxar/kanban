<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Task;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('new')) {
            $request->session()->forget('board_uuid');
        }

        // Если доска уже есть — отправляем туда
        if ($request->session()->has('board_uuid')) {
            $uuid = $request->session()->get('board_uuid');
            $board = Board::query()
                ->where("uuid", $uuid)
                ->first();

            if (!is_null($board))
                return redirect('/board/' . $uuid);
        }

        // Создаём пустую доску
        $board = Board::create([
            'uuid' => Str::uuid(),
            'title' => 'Новая доска',
            'description' => 'Выберите шаблон'
        ]);

        // Сохраняем UUID в сессию
        $request->session()->put('board_uuid', $board->uuid);

        return redirect('/board/' . $board->uuid);
    }

    public function chooseTemplate()
    {
        $templates = config('board_templates');

        return response()->json(
            collect($templates)->map(function ($tpl, $key) {
                return [
                    'id' => $key,
                    'title' => $tpl['title'],
                    'icon' => $tpl['icon'],
                ];
            })->values()
        );
    }


    public function applyTemplate(Request $request, $uuid)
    {
        $request->validate([
            'template' => 'required|string'
        ]);

        $board = Board::where('uuid', $uuid)->firstOrFail();
        $templates = config('board_templates');

        if (!isset($templates[$request->template])) {
            return response()->json(['error' => 'Template not found'], 404);
        }

        $tpl = $templates[$request->template];

        $createdColumns = [];


        foreach ($tpl['columns'] as $index => $title) {
            $column = $board->columns()->create([
                'title' => $title,
                'position' => $index,
                'thread' => $index,
            ]);

            $createdColumns[$title] = $column;
        }

        if (!empty($tpl['generate'])) {

            $gen = $tpl['generate'];

            [$min, $max] = $gen['tasks_per_column'] ?? [2, 5];

            foreach ($createdColumns as $columnTitle => $column) {

                $config = $gen['columns'][$columnTitle] ?? [];
                $count = rand($min, $max);

                for ($i = 0; $i < $count; $i++) {

                    $n = rand(1000, 9999);

                    // title
                    $titleTpl = collect($config['titles'] ?? ['Задача #{n}'])->random();
                    $title = str_replace('{n}', $n, $titleTpl);

                    // description
                    $description = collect($config['descriptions'] ?? [null])->random();

                    // labels
                    $labels = collect($gen['labels'] ?? [])
                        ->random(rand(0, 2))
                        ->values()
                        ->toArray();

                    // subtasks
                    $subtasks = collect($config['subtasks'] ?? [])
                        ->map(fn($s) => [
                            'text' => $s,
                            'done' => (bool)rand(0, 1),
                        ])
                        ->toArray();

                    // attachments
                    $attachments = rand(0, 1)
                        ? ($gen['attachments'] ?? [])
                        : [];

                    // data (кастомные поля)
                    $data = [];
                    if (!empty($config['data'])) {
                        foreach ($config['data'] as $key => $values) {
                            $data[$key] = collect($values)->random();
                        }
                    }

                    $task = $column->tasks()->create([
                        'title' => $title,
                        'description' => $description,
                        'priority' => collect($gen['priorities'] ?? ['low'])->random(),
                        'labels' => $labels,
                        'subtasks' => $subtasks,
                        'attachments' => $attachments,
                        'data' => $data,
                        'position' => $i,
                        'board_id' => $board->id,
                        'due_date' => now()->addDays(rand(0, 5)),
                        'last_viewed_at' => now()->subMinutes(rand(0, 500)),
                    ]);

                    // сообщения
                    if (!empty($config['messages'])) {

                        $messagesCount = rand(1, 4);

                        for ($m = 0; $m < $messagesCount; $m++) {

                            $isClient = rand(0, 1);

                            $task->messages()->create([
                                'sender_type' => $isClient ? 'external' : 'manager',
                                'sender_label' => $isClient
                                    ? 'Клиент ' . rand(1000, 9999)
                                    : 'Менеджер',
                                'message' => $this->fakeMessage($columnTitle),
                                'is_read' => (bool)rand(0, 1),
                            ]);
                        }
                    }
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

    private function fakeMessage($column)
    {
        $map = [
            'Отзывы' => [
                'Почему нет соуса?',
                'Все было вкусно, спасибо!',
                'Очень долго ждал заказ',
            ],
            'Доставка' => [
                'Курьер уже рядом?',
                'Я жду заказ',
            ],
            'Обратная связь' => [
                'Хочу оформить возврат',
                'Ошибка в заказе',
            ],
        ];

        return collect($map[$column] ?? ['Ок'])->random();
    }

    public function testCards(Request $request)
    {
        $type = (int)$request->type;

        $payload = match ($type) {

            // USER CARD
            1 => [
                'name' => 'Тестовый пользователь',
                'phone' => '+79990000000',
                'email' => 'test@example.com',
                'meta' => [
                    'source' => 'test-api',
                    'generated_at' => now()->toISOString()
                ]
            ],

            // ORDER CARD
            2 => [
                'order_id' => rand(1000, 9999),
                'sum' => rand(500, 5000),
                'customer' => [
                    'name' => 'Иван Петров',
                    'phone' => '+79991234567'
                ],
                'items' => [
                    ['name' => 'Пицца Маргарита', 'qty' => 1, 'price' => 790],
                    ['name' => 'Салат Цезарь', 'qty' => 1, 'price' => 450]
                ],
                'meta' => [
                    'source' => 'test-api',
                    'generated_at' => now()->toISOString()
                ]
            ],

            // TEXT CARD
            3 => [
                'text' => 'Тестовое текстовое сообщение',
                'author' => 'Система',
                'meta' => [
                    'generated_at' => now()->toISOString()
                ]
            ],

            // FINANCE CARD
            4 => [
                'amount' => rand(100, 1000),
                'category' => 'Тестовая категория',
                'operation' => 'income',
                'meta' => [
                    'generated_at' => now()->toISOString()
                ]
            ],

            // DEVELOPMENT CARD
            5 => [
                'task' => 'Тестовая разработческая задача',
                'priority' => 'high',
                'status' => 'open',
                'meta' => [
                    'generated_at' => now()->toISOString()
                ]
            ],

            default => [
                'info' => 'Неизвестный тип карточки'
            ]
        };

        Task::query()
            ->create($payload);

        return response()->json([
            'ok' => true,
            'type' => $type,
            'payload' => $payload,
            'message' => 'Тестовая карточка успешно сгенерирована'
        ]);

    }
}
