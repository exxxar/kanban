<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ColumnSheetExport implements FromView, WithStyles, WithColumnWidths, WithDefaultStyles
{
    protected $column;

    public function __construct($column)
    {
        $this->column = $column;
    }

    public function view(): View
    {
        return view('exports.column', [
            'column' => $this->column,
            'title'=>$this->column->title ?? 'Колонка',
            'tasks'  => $this->column->tasks
        ]);
    }

    // Чёрные границы + перенос текста
    public function styles(Worksheet $sheet)
    {
        // Определяем диапазон (например A1:J200)
        $highestRow = $sheet->getHighestRow();
        $range = "A1:J{$highestRow}";

        // Чёрные границы
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Перенос текста
        $sheet->getStyle($range)->getAlignment()->setWrapText(true);

        // Вертикальное выравнивание
        $sheet->getStyle($range)->getAlignment()->setVertical('top');

        return [];
    }

    // Ширина колонок
    public function columnWidths(): array
    {
        return [
            'A' => 6,   // ID
            'B' => 25,  // Название
            'C' => 40,  // Описание
            'D' => 12,  // Приоритет
            'E' => 10,  // Тип
            'F' => 20,  // Labels
            'G' => 35,  // Subtasks
            'H' => 45,  // Data
            'I' => 15,  // Due date
            'J' => 20,  // Viewed
        ];
    }

    // Высота строк по умолчанию
    public function defaultStyles(Worksheet|\PhpOffice\PhpSpreadsheet\Style\Style $sheet)
    {
        $sheet->getDefaultRowDimension()->setRowHeight(20);

        return [];
    }
}
