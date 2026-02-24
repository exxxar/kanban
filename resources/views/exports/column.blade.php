<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{$title}}</title>
</head>
<body>
<table>
    <thead>
    <tr>
        <th colspan="10" style="font-size: 16px; font-weight: bold;">
            {{ $column->title }}
        </th>
    </tr>

    <tr>
        <th>ID</th>
        <th>Название</th>
        <th>Описание</th>
        <th>Приоритет</th>
        <th>Тип</th>
        <th>Labels</th>
        <th>Подзадачи</th>
        <th>Данные</th>
        <th>Дата выполнения</th>
        <th>Просмотрено</th>
    </tr>
    </thead>

    <tbody>
    @foreach ($tasks as $task)
        <tr>
            <td>{{ $task->id }}</td>
            <td>{{ $task->title }}</td>
            <td>{{ $task->description }}</td>
            <td>{{ $task->priority }}</td>
            <td>{{ $task->type }}</td>

            <td>
                @if($task->labels)
                    {{ implode(', ', $task->labels) }}<br/>
                @endif
            </td>

            <td>
                @if($task->subtasks)
                    @foreach($task->subtasks as $sub)
                        • {{ $sub['text'] }} ({{ $sub['done'] ? '✓' : '—' }})<br/>
                    @endforeach
                @endif
            </td>

            <td>
                {{-- ДИНАМИЧЕСКИЙ ВЫВОД DATA --}}
                @switch($task->type)

                    {{-- USER --}}
                    @case(1)
                        Телефон: {{ $task->data['phone'] ?? '' }}<br/>
                        Email: {{ $task->data['email'] ?? '' }}<br/>
                        Город: {{ $task->data['city'] ?? '' }}<br/>
                        Компания: {{ $task->data['company'] ?? '' }}<br/>
                        Должность: {{ $task->data['position'] ?? '' }}<br/>
                        Заметки: {{ $task->data['notes'] ?? '' }}
                        @break

                        {{-- ORDER --}}
                    @case(2)
                        Товар: {{ $task->data['product'] ?? '' }}<br/>
                        Кол-во: {{ $task->data['quantity'] ?? '' }}<br/>
                        Цена: {{ $task->data['price'] ?? '' }}<br/>
                        Скидка: {{ $task->data['discount'] ?? '' }}<br/>
                        Адрес: {{ $task->data['address'] ?? '' }}<br/>
                        Комментарий: {{ $task->data['comment'] ?? '' }}<br/>
                        @if(isset($task->data['parts']))
                            Состав:
                            @foreach($task->data['parts'] as $p)
                                - {{ $p['name'] }} ({{ $p['amount'] }})
                            @endforeach
                        @endif
                        @break

                        {{-- TEXT --}}
                    @case(3)
                        Вопрос: {{ $task->data['question'] ?? '' }}<br/>
                        Ответ: {{ $task->data['answer'] ?? '' }}
                        @break

                        {{-- FINANCE --}}
                    @case(4)
                        Сумма: {{ $task->data['amount'] ?? '' }} {{ $task->data['currency'] ?? '' }}<br/>
                        Тип: {{ $task->data['operation'] ?? '' }}<br/>
                        Баланс после: {{ $task->data['balanceAfter'] ?? '' }}<br/>
                        Комментарий: {{ $task->data['comment'] ?? '' }}
                        @break

                        {{-- DEVELOPMENT --}}
                    @case(5)
                        Менеджер: {{ $task->data['manager'] ?? '' }}<br/>
                        Дедлайн: {{ $task->data['deadline'] ?? '' }}
                        Технологии:
                        @if(isset($task->data['tech']))
                            @foreach($task->data['tech'] as $tech)
                                - {{ $tech }}
                            @endforeach
                        @endif
                        @break

                        {{-- DEFAULT --}}
                    @default
                        {{ json_encode($task->data, JSON_UNESCAPED_UNICODE) }}
                @endswitch
            </td>

            <td>{{ $task->due_date }}</td>
            <td>{{ $task->last_viewed_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
