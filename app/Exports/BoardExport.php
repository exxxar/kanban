<?php

namespace App\Exports;

use App\Models\Board;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BoardExport implements WithMultipleSheets
{
    protected Board $board;

    public function __construct(Board $board)
    {
        $this->board = $board;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->board->columns as $column) {
            $sheets[] = new ColumnSheetExport($column);
        }

        return $sheets;
    }
}
