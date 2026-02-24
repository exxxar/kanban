<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TaskApiTester
{
    protected string $endpoint;
    protected string $boardUuid;

    public function __construct()
    {
        $this->endpoint = config('app.url') . '/api/task/create';
        $this->boardUuid = config('kanban.test_board_uuid'); // <-- UUID доски из конфига
    }

    private function send(array $payload)
    {

        return  Http::post($this->endpoint, $payload)->json();
    }

    public function createBase(int $thread = 0, array $override = [])
    {
        return $this->send(array_merge([
            'type' => 0,
            'thread' => $thread,
            'board_uuid' => $this->boardUuid,
            'title' => 'Базовая карточка',
        ], $override));
    }

    public function createUser(int $thread = 0, array $override = [])
    {
        return $this->send(array_merge([
            'type' => 1,
            'thread' => $thread,
            'board_uuid' => $this->boardUuid,
            'title' => 'Тестовый клиент',
            'data' => [
                'phone' => '+7 900 000-00-00',
                'email' => 'test@example.com',
            ],
        ], $override));
    }

    public function createOrder(int $thread = 0, array $override = [])
    {
        return $this->send(array_merge([
            'type' => 2,
            'thread' => $thread,
            'board_uuid' => $this->boardUuid,
            'title' => 'Тестовый заказ',
            'data' => [
                'product' => 'Кофе',
                'quantity' => '10 кг',
                'price' => 5000,
            ],
        ], $override));
    }

    public function createText(int $thread = 0, array $override = [])
    {
        return $this->send(array_merge([
            'type' => 3,
            'thread' => $thread,
            'board_uuid' => $this->boardUuid,
            'title' => 'Вопрос клиента',
            'data' => [
                'question' => 'Можно ли доставить завтра?',
                'answer' => 'Да, конечно.',
            ],
        ], $override));
    }

    public function createFinance(int $thread = 0, array $override = [])
    {
        return $this->send(array_merge([
            'type' => 4,
            'thread' => $thread,
            'board_uuid' => $this->boardUuid,
            'title' => 'Поступление средств',
            'data' => [
                'amount' => 1000,
                'currency' => '₽',
                'operation' => 'income',
            ],
        ], $override));
    }

    public function createAll(int $thread = 0)
    {
        return [
            'base' => $this->createBase($thread),
            'user' => $this->createUser($thread),
            'order' => $this->createOrder($thread),
            'text' => $this->createText($thread),
            'finance' => $this->createFinance($thread),
        ];
    }
}
