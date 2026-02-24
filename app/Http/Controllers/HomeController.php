<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Если доска уже есть — отправляем туда
        if ($request->session()->has('board_uuid')) {
            return redirect('/board/' . $request->session()->get('board_uuid'));
        }

        // Создаём новую доску
        $board = Board::create([
            'uuid' => Str::uuid(),
            'title' => 'Моя доска',
            'description' => 'Личная канбан‑доска'
        ]);

        // Список колонок по умолчанию
        $defaultColumns = [
            'Отзывы',
            'Начисления баллов',
            'Вопросы',
            'Конкурсы',
            'Заказы',
            'Вывод средств',
            'Доставка',
            'Ответы',
            'Обратная связь'
        ];

        $board->columns()->create([
            'title' => 'По умолчанию',
            'position' => 0,
            'can_remove'=>false,
            'thread'=>0,
        ]);

        foreach ($defaultColumns as $index => $title) {
            $board->columns()->create([
                'title' => $title,
                'position' => $index+1,
                'thread' => $index+1
            ]);
        }

        // Сохраняем UUID в сессию
        $request->session()->put('board_uuid', $board->uuid);

        return redirect('/board/' . $board->uuid);
    }

}
