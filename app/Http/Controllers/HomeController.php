<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
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
        $request->validate([
            'type' => 'required|integer|in:1,2,3,4,5,6',
            'board_uuid' => 'required|string|exists:boards,uuid',
        ]);

        $type = (int)$request->type;
        $boardUuid = $request->board_uuid;

        // Находим доску и первую колонку
        $board = Board::where('uuid', $boardUuid)->firstOrFail();
        $column = $board->columns()->orderBy('position')->first();

        if (!$column) {
            return response()->json([
                'ok' => false,
                'message' => 'В доске нет колонок'
            ], 400);
        }

        // Получаем кастомные поля из конфига доски
        $customFields = $board->config['custom_fields'] ?? [];
        $taskCustomData = [];
        $clientCustomData = [];

        // Генерируем тестовые данные для кастомных полей
        foreach ($customFields as $section) {
            foreach ($section['fields'] ?? [] as $field) {
                $testValue = $this->generateTestValue($field['type'], $field['name']);

                if ($section['target'] === 'task') {
                    $taskCustomData[$field['name']] = $testValue;
                } elseif ($section['target'] === 'client') {
                    $clientCustomData[$field['name']] = $testValue;
                }
            }
        }

        // Максимальная позиция в колонке
        $maxPosition = Task::where('column_id', $column->id)->max('position') ?? 0;

        return DB::transaction(function () use ($type, $board, $column, $maxPosition, $taskCustomData, $clientCustomData) {

            $task = null;

            switch ($type) {
                // === ОБЫЧНАЯ ЗАДАЧА ===
                case 1:
                    $task = Task::create([
                        'board_id' => $board->id,
                        'column_id' => $column->id,
                        'title' => '🧪 Тестовая задача: ' . fake()->words(3, true),
                        'type' => 1,
                        'priority' => fake()->randomElement(['low', 'medium', 'high']),
                        'description' => "Это автоматически созданная тестовая задача.\n\nСоздана: " . now()->format('d.m.Y H:i'),
                        'due_date' => now()->addDays(rand(1, 14))->format('Y-m-d'),
                        'labels' => ['development', 'bug'],
                        'subtasks' => [
                            ['id' => 1, 'text' => 'Подготовить ТЗ', 'done' => true],
                            ['id' => 2, 'text' => 'Согласовать с заказчиком', 'done' => false],
                            ['id' => 3, 'text' => 'Начать разработку', 'done' => false],
                        ],
                        'custom_data' => $taskCustomData,
                        'position' => $maxPosition + 1,
                    ]);
                    break;

                // === КЛИЕНТ ===
                case 2:
                    $companyName = fake()->company();

                    $task = Task::create([
                        'board_id' => $board->id,
                        'column_id' => $column->id,
                        'title' => $companyName,
                        'type' => 2,
                        'priority' => 'medium',
                        'description' => "Клиент создан через тестовый API.\n\nСоздан: " . now()->format('d.m.Y H:i'),
                        'due_date' => now()->addDays(rand(7, 30))->format('Y-m-d'),
                        'labels' => ['client'],
                        'subtasks' => [
                            ['id' => 1, 'text' => 'Отправить КП', 'done' => false],
                            ['id' => 2, 'text' => 'Заключить договор', 'done' => false],
                        ],
                        'custom_data' => $taskCustomData,
                        'position' => $maxPosition + 1,
                    ]);

                    // Создаём связанного клиента
                    $task->client()->create([
                        'company_name' => $companyName,
                        'contact_person' => fake()->name(),
                        'phone' => '+7' . fake()->numerify('9#########'),
                        'source' => fake()->randomElement([
                            'Мой бизнес', 'Моя сеть', 'Егор', 'Вася',
                            'Сайт', 'Рекомендация', 'Контекст', 'SEO'
                        ]),
                        'address' => fake()->address(),
                        'placement_type' => fake()->randomElement([
                            'Стандарт', 'Премиум', 'VIP', 'Базовый', 'Корпоративный'
                        ]),
                        'cost' => rand(5, 50) * 1000,
                        'partner' => fake()->randomElement([null, 'Иванов И.', 'Петров П.', 'Сидоров С.']),
                        'deal_comment' => fake()->sentence(10),
                        'links' => [
                            ['url' => 'https://' . fake()->domainName(), 'title' => 'Сайт компании'],
                            ['url' => 'https://vk.com/' . fake()->userName(), 'title' => 'ВКонтакте'],
                        ],
                        'custom_data' => $clientCustomData,
                    ]);

                    $task->load('client');
                    break;

                // === ТЕКСТОВАЯ КАРТОЧКА ===
                case 3:
                    $task = Task::create([
                        'board_id' => $board->id,
                        'column_id' => $column->id,
                        'title' => '📝 ' . fake()->sentence(4),
                        'type' => 1,
                        'priority' => 'low',
                        'description' => fake()->paragraphs(3, true),
                        'custom_data' => array_merge($taskCustomData, [
                            'text_content' => fake()->paragraphs(2, true),
                            'author' => fake()->name(),
                            'source' => 'test-api',
                        ]),
                        'position' => $maxPosition + 1,
                    ]);
                    break;

                // === ФИНАНСОВАЯ КАРТОЧКА ===
                case 4:
                    $amount = rand(10, 100) * 1000;
                    $task = Task::create([
                        'board_id' => $board->id,
                        'column_id' => $column->id,
                        'title' => '💰 Поступление: ' . number_format($amount, 0, ',', ' ') . ' ₽',
                        'type' => 1,
                        'priority' => 'high',
                        'description' => "Финансовая операция\n\nСумма: {$amount} ₽\nКатегория: " . fake()->word(),
                        'due_date' => now()->format('Y-m-d'),
                        'labels' => ['finance'],
                        'custom_data' => array_merge($taskCustomData, [
                            'amount' => $amount,
                            'category' => fake()->randomElement([
                                'Реклама', 'Разработка', 'Дизайн', 'Консалтинг', 'Поддержка'
                            ]),
                            'operation' => 'income',
                            'payment_method' => fake()->randomElement(['Наличные', 'Безнал', 'Карта']),
                            'invoice_number' => 'INV-' . rand(10000, 99999),
                        ]),
                        'position' => $maxPosition + 1,
                    ]);
                    break;

                // === КАРТОЧКА РАЗРАБОТКИ ===
                case 5:
                    $task = Task::create([
                        'board_id' => $board->id,
                        'column_id' => $column->id,
                        'title' => '⚙️ ' . fake()->randomElement([
                                'Рефакторинг модуля', 'Исправление бага', 'Новая фича',
                                'Оптимизация запросов', 'Покрытие тестами', 'Обновление зависимостей'
                            ]),
                        'type' => 1,
                        'priority' => fake()->randomElement(['low', 'medium', 'high']),
                        'description' => "Техническая задача\n\nОписание: " . fake()->paragraph(),
                        'due_date' => now()->addDays(rand(3, 21))->format('Y-m-d'),
                        'labels' => ['development'],
                        'subtasks' => [
                            ['id' => 1, 'text' => 'Анализ кода', 'done' => true],
                            ['id' => 2, 'text' => 'Написание кода', 'done' => false],
                            ['id' => 3, 'text' => 'Code review', 'done' => false],
                            ['id' => 4, 'text' => 'Тестирование', 'done' => false],
                            ['id' => 5, 'text' => 'Деплой', 'done' => false],
                        ],
                        'custom_data' => array_merge($taskCustomData, [
                            'branch' => 'feature/' . fake()->slug(2),
                            'story_points' => rand(1, 13),
                            'estimated_hours' => rand(2, 40),
                            'assignee' => fake()->name(),
                        ]),
                        'position' => $maxPosition + 1,
                    ]);
                    break;

                // === ЗАКАЗ ===
                case 6:
                    // Генерируем позиции заказа
                    $products = [
                        ['name' => 'Пицца Маргарита', 'price' => 790],
                        ['name' => 'Пицца Пепперони', 'price' => 890],
                        ['name' => 'Пицца 4 сыра', 'price' => 950],
                        ['name' => 'Салат Цезарь', 'price' => 450],
                        ['name' => 'Салат Греческий', 'price' => 380],
                        ['name' => 'Борщ', 'price' => 320],
                        ['name' => 'Стейк Рибай', 'price' => 1890],
                        ['name' => 'Паста Карбонара', 'price' => 620],
                        ['name' => 'Суши-сет Филадельфия', 'price' => 1290],
                        ['name' => 'Ролл Калифорния', 'price' => 580],
                        ['name' => 'Кофе Латте', 'price' => 220],
                        ['name' => 'Чай зелёный', 'price' => 150],
                        ['name' => 'Лимонад', 'price' => 250],
                        ['name' => 'Десерт Тирамису', 'price' => 380],
                        ['name' => 'Чизкейк Нью-Йорк', 'price' => 420],
                    ];

                    // Выбираем случайное количество позиций (2-5)
                    $itemsCount = rand(2, 5);
                    $selectedProducts = fake()->randomElements($products, $itemsCount);

                    $items = [];
                    $totalSum = 0;

                    foreach ($selectedProducts as $product) {
                        $qty = rand(1, 3);
                        $items[] = [
                            'name' => $product['name'],
                            'qty' => $qty,
                            'price' => $product['price'],
                            'total' => $product['price'] * $qty,
                        ];
                        $totalSum += $product['price'] * $qty;
                    }

                    $customerName = fake()->name();
                    $orderId = rand(10000, 99999);

                    $task = Task::create([
                        'board_id' => $board->id,
                        'column_id' => $column->id,
                        'title' => "🛒 Заказ #{$orderId}",
                        'type' => 1,
                        'priority' => 'high',
                        'description' => "Заказ от {$customerName}\n\nПозиций: {$itemsCount}\nСумма: " . number_format($totalSum, 0, ',', ' ') . " ₽\n\nСоздан: " . now()->format('d.m.Y H:i'),
                        'due_date' => now()->addHours(rand(1, 24))->format('Y-m-d'),
                        'labels' => ['urgent'],
                        'subtasks' => [
                            ['id' => 1, 'text' => 'Принять заказ', 'done' => true],
                            ['id' => 2, 'text' => 'Подтвердить оплату', 'done' => false],
                            ['id' => 3, 'text' => 'Передать в доставку', 'done' => false],
                            ['id' => 4, 'text' => 'Доставить клиенту', 'done' => false],
                        ],
                        'custom_data' => array_merge($taskCustomData, [
                            // Основная структура заказа (как в старом формате)
                            'order_id' => $orderId,
                            'sum' => $totalSum,
                            'customer' => [
                                'name' => $customerName,
                                'phone' => '+7' . fake()->numerify('9#########'),
                                'email' => fake()->safeEmail(),
                                'address' => fake()->address(),
                            ],
                            'items' => $items,
                            'meta' => [
                                'source' => 'test-api',
                                'generated_at' => now()->toISOString(),
                                'payment_status' => fake()->randomElement(['pending', 'paid', 'failed']),
                                'delivery_type' => fake()->randomElement(['courier', 'pickup', 'delivery']),
                                'comment' => fake()->optional()->sentence(),
                            ],
                        ]),
                        'position' => $maxPosition + 1,
                    ]);
                    break;
            }

            // Загружаем связанные данные для ответа
            $task->load(['tags', 'client']);

            return response()->json([
                'ok' => true,
                'type' => $type,
                'type_name' => $this->getTypeName($type),
                'task' => $task,
                'board' => [
                    'uuid' => $board->uuid,
                    'title' => $board->title,
                ],
                'column' => [
                    'id' => $column->id,
                    'title' => $column->title,
                ],
                'custom_data_generated' => [
                    'task' => $taskCustomData,
                    'client' => $clientCustomData,
                ],
                'message' => 'Тестовая карточка успешно создана'
            ]);
        });
    }

// === Вспомогательные методы ===

    private function generateTestValue($type, $name)
    {
        return match ($type) {
            'text' => fake()->words(3, true),
            'number' => rand(10, 1000),
            'date' => now()->addDays(rand(1, 30))->format('Y-m-d'),
            'email' => fake()->safeEmail(),
            'url' => 'https://' . fake()->domainName(),
            'textarea' => fake()->paragraphs(2, true),
            default => null,
        };
    }

    private function getTypeName($type)
    {
        return match ($type) {
            1 => 'Обычная задача',
            2 => 'Клиент',
            3 => 'Текстовая карточка',
            4 => 'Финансовая карточка',
            5 => 'Карточка разработки',
            6 => 'Заказ', // ← НОВОЕ
            default => 'Неизвестный тип',
        };
    }
}
