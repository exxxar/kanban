<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Board;
use App\Models\Column;

class ColumnSeeder extends Seeder
{
    public function run(): void
    {
        Board::all()->each(function ($board) {
            Column::create([
                'board_id' => $board->id,
                'title' => 'К выполнению',
                'position' => 0,
                'thread' => 0,
                'can_remove'=>false,
            ]);

            Column::create([
                'board_id' => $board->id,
                'title' => 'В процессе',
                'position' => 1,
                'thread' => 1
            ]);

            Column::create([
                'board_id' => $board->id,
                'title' => 'Готово',
                'position' => 2,
                'thread' => 2
            ]);
        });
    }
}
