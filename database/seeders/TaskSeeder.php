<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $tasks = [

            // 1. Карточка пользователя
            [
                'column_id' => 1,
                'board_id' => 1,
                'title' => 'Иван Петров',
                'description' => 'Постоянный клиент, VIP‑статус',
                'priority' => 'medium',
                'type' => 1, // user
                'due_date' => null,
                'labels' => ['client'],
                'data' => [
                    'phone' => '+7 999 123‑45‑67',
                    'email' => 'ivan.petrov@example.com',
                    'city' => 'Москва',
                    'company' => 'ООО «Альфа»',
                    'position' => 'Менеджер закупок',
                    'notes' => 'Предпочитает общение в Telegram'
                ],
                'subtasks' => [
                    ['id' => 1, 'text' => 'Позвонить клиенту', 'done' => false],
                    ['id' => 2, 'text' => 'Отправить КП', 'done' => true],
                ],
                'position' => 0,
            ],

            // 2. Карточка заказа
            [
                'column_id' => 2,
                'board_id' => 1,
                'title' => 'Заказ #A‑2041',
                'description' => 'Поставка 12 кг кофе',
                'priority' => 'high',
                'type' => 2, // order
                'due_date' => now()->addDays(3),
                'labels' => ['order'],
                'data' => [
                    'product' => 'Кофе арабика 100%',
                    'quantity' => '12 кг',
                    'price' => 8400,
                    'discount' => '10%',
                    'parts' => [
                        ['name' => 'Кофе молотый', 'amount' => '6 кг'],
                        ['name' => 'Кофе в зернах', 'amount' => '6 кг'],
                    ],
                    'address' => 'Москва, ул. Ленина, 12',
                    'comment' => 'Доставка утром'
                ],
                'subtasks' => [
                    ['id' => 1, 'text' => 'Проверить наличие товара', 'done' => true],
                    ['id' => 2, 'text' => 'Подготовить документы', 'done' => false],
                    ['id' => 3, 'text' => 'Согласовать доставку', 'done' => false],
                ],
                'position' => 1,
            ],

            // 3. Текстовый блок
            [
                'column_id' => 1,
                'board_id' => 1,
                'title' => 'Вопрос клиента',
                'description' => 'Уточнение условий доставки',
                'priority' => 'low',
                'type' => 3, // text
                'due_date' => null,
                'labels' => ['text'],
                'data' => [
                    'question' => 'Можно ли доставить заказ в выходные?',
                    'answer' => 'Да, доставка доступна по субботам с 10:00 до 16:00.'
                ],
                'subtasks' => [],
                'position' => 2,
            ],

            // 4. Финансовая операция
            [
                'column_id' => 3,
                'board_id' => 1,
                'title' => 'Поступление средств',
                'description' => 'Оплата заказа #A‑2041',
                'priority' => 'medium',
                'type' => 4, // finance
                'due_date' => null,
                'labels' => ['finance'],
                'data' => [
                    'amount' => 8400,
                    'currency' => '₽',
                    'operation' => 'income',
                    'balanceAfter' => 15200,
                    'comment' => 'Оплата через СБП'
                ],
                'subtasks' => [],
                'position' => 3,
            ],

            // 5. Карточка разработки
            [
                'column_id' => 2,
                'board_id' => 1,
                'title' => 'Разработка мини‑приложения',
                'description' => 'Создание Telegram mini‑app для оптовых заказов',
                'priority' => 'high',
                'type' => 5, // development
                'due_date' => now()->addDays(10),
                'labels' => ['development'],
                'data' => [
                    'tech' => ['Laravel', 'Vue', 'Telegram API'],
                    'manager' => 'Алексей',
                    'deadline' => 'Через 10 дней'
                ],
                'subtasks' => [
                    ['id' => 1, 'text' => 'Сформировать ТЗ', 'done' => true],
                    ['id' => 2, 'text' => 'Сделать прототип', 'done' => false],
                    ['id' => 3, 'text' => 'Реализовать API', 'done' => false],
                    ['id' => 4, 'text' => 'Интеграция с Telegram', 'done' => false],
                ],
                'position' => 4,
            ],
        ];

        foreach ($tasks as $task) {
            Task::create($task);
        }
    }
}
