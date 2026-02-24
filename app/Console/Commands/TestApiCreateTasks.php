<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TaskApiTester;

class TestApiCreateTasks extends Command
{
    protected $signature = 'test:api-create
                            {--type=all : Тип карточек (all, base, user, order, text, finance)}
                            {--count=1 : Количество карточек}';

    protected $description = 'Отправляет HTTP‑запросы к API и создаёт тестовые карточки разных типов';

    public function handle(TaskApiTester $tester)
    {
        $type = $this->option('type');
        $count = (int) $this->option('count');

        $this->info("Создание {$count} карточек типа: {$type}");

        for ($i = 1; $i <= $count; $i++) {

            $result = match ($type) {
                'base' => $tester->createBase(),
                'user' => $tester->createUser(),
                'order' => $tester->createOrder(),
                'text' => $tester->createText(),
                'finance' => $tester->createFinance(),
                'all' => $tester->createAll(),
                default => $this->error("Неизвестный тип: {$type}")
            };

            $this->line("✔ Создано: " . json_encode($result));
        }

        $this->info("Готово");
        return Command::SUCCESS;
    }
}
