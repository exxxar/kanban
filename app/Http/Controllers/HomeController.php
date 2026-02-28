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

        return redirect('/board/' . $board->uuid );
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

        foreach ($tpl['columns'] as $index => $title) {
            $board->columns()->create([
                'title' => $title,
                'position' => $index,
                'thread' => $index,
            ]);
        }

        return response()->json(['status' => 'ok']);
    }


}
