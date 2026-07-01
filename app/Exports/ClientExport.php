<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class ClientExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $client;
    protected $task;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->task = $client->task;
    }

    public function collection(): Collection
    {
        $data = collect();

        // Заголовок
        $data->push([
            'Поле',
            'Значение'
        ]);

        // Данные задачи
        $data->push(['ID задачи', $this->task?->id]);
        $data->push(['Название задачи', $this->task?->title]);
        $data->push(['Приоритет', $this->task?->priority]);
        $data->push(['Дата создания', $this->client->created_at?->format('d.m.Y H:i')]);
        $data->push(['', '']);

        // Данные клиента
        $data->push(['=== ДАННЫЕ КЛИЕНТА ===', '']);
        $data->push(['Название компании', $this->client->company_name]);
        $data->push(['Контактное лицо', $this->client->contact_person]);
        $data->push(['Телефон', $this->client->phone]);
        $data->push(['Источник лида', $this->client->source]);
        $data->push(['Адрес', $this->client->address]);
        $data->push(['', '']);

        // Сделка
        $data->push(['=== СДЕЛКА ===', '']);
        $data->push(['Вид размещения', $this->client->placement_type]);
        $data->push(['Стоимость (₽)', $this->client->cost]);
        $data->push(['Партнёр', $this->client->partner]);
        $data->push(['Комментарий', $this->client->deal_comment]);
        $data->push(['Ссылки', is_array($this->client->links) ? implode(', ', $this->client->links) : '']);
        $data->push(['', '']);

        // Подзадачи
        $data->push(['=== ПОДЗАДАЧИ ===', '']);
        if ($this->task && !empty($this->task->subtasks)) {
            foreach ($this->task->subtasks as $sub) {
                $data->push([
                    ($sub['done'] ?? false) ? '✓' : '○',
                        $sub['text'] ?? ''
                ]);
            }
        } else {
            $data->push(['Нет подзадач', '']);
        }

        return $data;
    }

    public function headings(): array
    {
        return ['Поле', 'Значение'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Первая строка - заголовки
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E7F1FF']
                ]
            ],
            // Секции выделяем жирным
            6 => ['font' => ['bold' => true, 'size' => 11]], // ДАННЫЕ КЛИЕНТА
            13 => ['font' => ['bold' => true, 'size' => 11]], // СДЕЛКА
            20 => ['font' => ['bold' => true, 'size' => 11]], // ПОДЗАДАЧИ
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 50,
        ];
    }
}
