<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Board;
use Illuminate\Support\Str;

class BoardSeeder extends Seeder
{
    public function run(): void
    {
        //93665154-dd3c-43ff-aff5-66d2c4fcbebe
        Board::create([
            'uuid' => Str::uuid(),
            'title' => 'Проект: Онлайн‑доставка еды',
            'description' => 'Рабочая доска для разработки сервиса',
            'config' => [
                'color' => '#4a90e2',
                'permissions' => 'public'
            ]
        ]);

        Board::create([
            'uuid' => Str::uuid(),
            'title' => 'Личное планирование',
            'description' => 'Задачи на неделю',
            'config' => [
                'color' => '#7ed321',
                'permissions' => 'private'
            ]
        ]);
    }
}
