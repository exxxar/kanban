<?php

return [

    // ============================================================
    // КЛАССИЧЕСКИЕ ШАБЛОНЫ
    // ============================================================

    'classic' => [
        'title' => 'Классический канбан',
        'icon'  => 'fa-columns',
        'columns' => ['Backlog', 'To Do', 'In Progress', 'Review', 'Done'],
    ],

    'ru_classic' => [
        'title' => 'Рабочий',
        'icon'  => 'fa-list-check',
        'columns' => [
            'Пул заявок',
            'К выполнению',
            'В процессе работы',
            'Обработка результатов',
            'Завершено',
            'Отклонено',
        ],
        'config' => [
            'custom_fields' => [
                [
                    'id' => 1,
                    'title' => 'Дополнительная информация',
                    'icon' => 'fa-solid fa-circle-info',
                    'color' => '#6c757d',
                    'target' => 'task',
                    'fields' => [
                        ['label' => 'Ссылка', 'name' => 'link', 'type' => 'url'],
                        ['label' => 'Заметки', 'name' => 'notes', 'type' => 'textarea'],
                    ],
                ],
            ],
        ],
    ],

    // ============================================================
    // CRM: ВОРОНКИ ПРОДАЖ
    // ============================================================

    'crm_sales' => [
        'title' => 'CRM: Воронка продаж',
        'icon'  => 'fa-handshake',
        'columns' => [
            'Новые лиды',
            'Квалификация',
            'Презентация',
            'Переговоры',
            'Закрытие сделки',
            'Оплата',
            'Выполнение',
            'Завершено',
        ],
        'config' => [
            'custom_fields' => [
                [
                    'id' => 1,
                    'title' => 'Данные сделки',
                    'icon' => 'fa-solid fa-handshake',
                    'color' => '#0d6efd',
                    'target' => 'client',
                    'fields' => [
                        ['label' => 'Бюджет сделки', 'name' => 'deal_budget', 'type' => 'number'],
                        ['label' => 'Дата закрытия', 'name' => 'close_date', 'type' => 'date'],
                        ['label' => 'Вероятность (%)', 'name' => 'probability', 'type' => 'number'],
                        ['label' => 'Комментарий менеджера', 'name' => 'manager_comment', 'type' => 'textarea'],
                    ],
                ],
            ],
            'custom_categories' => [
                ['id' => 1, 'name' => 'Горячий лид', 'key' => 'hot_lead', 'icon' => 'fa-solid fa-fire'],
                ['id' => 2, 'name' => 'Тёплый лид', 'key' => 'warm_lead', 'icon' => 'fa-solid fa-temperature-half'],
                ['id' => 3, 'name' => 'Холодный лид', 'key' => 'cold_lead', 'icon' => 'fa-solid fa-snowflake'],
                ['id' => 4, 'name' => 'Повторный клиент', 'key' => 'repeat_client', 'icon' => 'fa-solid fa-rotate'],
            ],
        ],
    ],

    'crm_simple' => [
        'title' => 'CRM: Простая воронка',
        'icon'  => 'fa-chart-line',
        'columns' => ['Заявки', 'В работе', 'КП отправлено', 'Договор', 'Оплачено', 'Выполнено'],
        'config' => [
            'custom_fields' => [
                [
                    'id' => 1,
                    'title' => 'Сделка',
                    'icon' => 'fa-solid fa-file-invoice-dollar',
                    'color' => '#10b981',
                    'target' => 'client',
                    'fields' => [
                        ['label' => 'Сумма договора', 'name' => 'contract_sum', 'type' => 'number'],
                        ['label' => 'Срок оплаты', 'name' => 'payment_date', 'type' => 'date'],
                        ['label' => 'Номер договора', 'name' => 'contract_number', 'type' => 'text'],
                    ],
                ],
            ],
            'custom_categories' => [
                ['id' => 1, 'name' => 'Новый клиент', 'key' => 'new_client', 'icon' => 'fa-solid fa-user-plus'],
                ['id' => 2, 'name' => 'Постоянный клиент', 'key' => 'regular_client', 'icon' => 'fa-solid fa-user-check'],
                ['id' => 3, 'name' => 'VIP клиент', 'key' => 'vip_client', 'icon' => 'fa-solid fa-crown'],
            ],
        ],
    ],

    'crm_b2b' => [
        'title' => 'CRM: B2B продажи',
        'icon'  => 'fa-building',
        'columns' => [
            'Входящие заявки',
            'Первичный контакт',
            'Выявление потребностей',
            'Коммерческое предложение',
            'Согласование условий',
            'Подписание договора',
            'Оплата',
            'Постоплата',
            'Закрыто (успех)',
            'Закрыто (отказ)',
        ],
        'config' => [
            'custom_fields' => [
                [
                    'id' => 1,
                    'title' => 'B2B данные компании',
                    'icon' => 'fa-solid fa-building',
                    'color' => '#667eea',
                    'target' => 'client',
                    'fields' => [
                        ['label' => 'ИНН компании', 'name' => 'inn', 'type' => 'text'],
                        ['label' => 'Отрасль', 'name' => 'industry', 'type' => 'text'],
                        ['label' => 'Размер компании', 'name' => 'company_size', 'type' => 'text'],
                        ['label' => 'Бюджет', 'name' => 'budget', 'type' => 'number'],
                        ['label' => 'ЛПР', 'name' => 'decision_maker', 'type' => 'text'],
                        ['label' => 'Email компании', 'name' => 'company_email', 'type' => 'email'],
                        ['label' => 'Сайт компании', 'name' => 'company_website', 'type' => 'url'],
                        ['label' => 'Заметки', 'name' => 'notes', 'type' => 'textarea'],
                    ],
                ],
            ],
            'custom_categories' => [
                ['id' => 1, 'name' => 'Корпоративный', 'key' => 'corporate', 'icon' => 'fa-solid fa-building'],
                ['id' => 2, 'name' => 'Средний бизнес', 'key' => 'medium_biz', 'icon' => 'fa-solid fa-city'],
                ['id' => 3, 'name' => 'Малый бизнес', 'key' => 'small_biz', 'icon' => 'fa-solid fa-store'],
                ['id' => 4, 'name' => 'Гос. сектор', 'key' => 'government', 'icon' => 'fa-solid fa-landmark'],
                ['id' => 5, 'name' => 'Стартап', 'key' => 'startup', 'icon' => 'fa-solid fa-rocket'],
            ],
        ],
    ],

    'crm_real_estate' => [
        'title' => 'Недвижимость',
        'icon'  => 'fa-house',
        'columns' => [
            'Новые обращения',
            'Показ объектов',
            'Подбор варианта',
            'Торг',
            'Бронирование',
            'Оформление сделки',
            'Сделка закрыта',
        ],
        'config' => [
            'custom_fields' => [
                [
                    'id' => 1,
                    'title' => 'Объект недвижимости',
                    'icon' => 'fa-solid fa-house',
                    'color' => '#f59e0b',
                    'target' => 'client',
                    'fields' => [
                        ['label' => 'Тип объекта', 'name' => 'property_type', 'type' => 'text'],
                        ['label' => 'Район', 'name' => 'district', 'type' => 'text'],
                        ['label' => 'Площадь (м²)', 'name' => 'area', 'type' => 'number'],
                        ['label' => 'Цена', 'name' => 'price', 'type' => 'number'],
                        ['label' => 'Адрес объекта', 'name' => 'property_address', 'type' => 'text'],
                        ['label' => 'Количество комнат', 'name' => 'rooms', 'type' => 'number'],
                        ['label' => 'Этаж', 'name' => 'floor', 'type' => 'number'],
                        ['label' => 'Описание объекта', 'name' => 'property_description', 'type' => 'textarea'],
                    ],
                ],
            ],
            'custom_categories' => [
                ['id' => 1, 'name' => 'Квартира', 'key' => 'apartment', 'icon' => 'fa-solid fa-building'],
                ['id' => 2, 'name' => 'Дом', 'key' => 'house', 'icon' => 'fa-solid fa-house'],
                ['id' => 3, 'name' => 'Коммерция', 'key' => 'commercial', 'icon' => 'fa-solid fa-store'],
                ['id' => 4, 'name' => 'Участок', 'key' => 'land', 'icon' => 'fa-solid fa-tree'],
                ['id' => 5, 'name' => 'Новостройка', 'key' => 'new_building', 'icon' => 'fa-solid fa-hammer'],
                ['id' => 6, 'name' => 'Вторичка', 'key' => 'secondary', 'icon' => 'fa-solid fa-house-chimney'],
            ],
        ],
    ],

    'crm_tourism' => [
        'title' => 'Туризм',
        'icon'  => 'fa-plane',
        'columns' => [
            'Заявки на туры',
            'Подбор тура',
            'Предложение клиенту',
            'Бронирование',
            'Оплата',
            'Документы',
            'Тур состоялся',
            'Постоплата',
        ],
        'config' => [
            'custom_fields' => [
                [
                    'id' => 1,
                    'title' => 'Данные тура',
                    'icon' => 'fa-solid fa-plane-departure',
                    'color' => '#0d6efd',
                    'target' => 'client',
                    'fields' => [
                        ['label' => 'Страна', 'name' => 'country', 'type' => 'text'],
                        ['label' => 'Город/Курорт', 'name' => 'resort', 'type' => 'text'],
                        ['label' => 'Даты тура', 'name' => 'tour_dates', 'type' => 'text'],
                        ['label' => 'Кол-во туристов', 'name' => 'tourists_count', 'type' => 'number'],
                        ['label' => 'Стоимость тура', 'name' => 'tour_cost', 'type' => 'number'],
                        ['label' => 'Отель', 'name' => 'hotel', 'type' => 'text'],
                        ['label' => 'Тип питания', 'name' => 'meal_type', 'type' => 'text'],
                        ['label' => 'Пожелания', 'name' => 'wishes', 'type' => 'textarea'],
                    ],
                ],
            ],
            'custom_categories' => [
                ['id' => 1, 'name' => 'Пляжный', 'key' => 'beach', 'icon' => 'fa-solid fa-umbrella-beach'],
                ['id' => 2, 'name' => 'Экскурсионный', 'key' => 'excursion', 'icon' => 'fa-solid fa-map-location-dot'],
                ['id' => 3, 'name' => 'Горнолыжный', 'key' => 'ski', 'icon' => 'fa-solid fa-person-skiing'],
                ['id' => 4, 'name' => 'Круиз', 'key' => 'cruise', 'icon' => 'fa-solid fa-ship'],
                ['id' => 5, 'name' => 'Экзотика', 'key' => 'exotic', 'icon' => 'fa-solid fa-tree'],
            ],
        ],
    ],

    'crm_education' => [
        'title' => 'Образование',
        'icon'  => 'fa-graduation-cap',
        'columns' => [
            'Новые заявки',
            'Консультация',
            'Пробное занятие',
            'Запись на курс',
            'Оплата',
            'В обучении',
            'Курс завершён',
        ],
        'config' => [
            'custom_fields' => [
                [
                    'id' => 1,
                    'title' => 'Данные студента',
                    'icon' => 'fa-solid fa-graduation-cap',
                    'color' => '#8b5cf6',
                    'target' => 'client',
                    'fields' => [
                        ['label' => 'Курс', 'name' => 'course', 'type' => 'text'],
                        ['label' => 'Уровень', 'name' => 'level', 'type' => 'text'],
                        ['label' => 'Стоимость курса', 'name' => 'course_cost', 'type' => 'number'],
                        ['label' => 'Дата начала', 'name' => 'start_date', 'type' => 'date'],
                        ['label' => 'Формат обучения', 'name' => 'format', 'type' => 'text'],
                        ['label' => 'Цель обучения', 'name' => 'goal', 'type' => 'textarea'],
                    ],
                ],
            ],
            'custom_categories' => [
                ['id' => 1, 'name' => 'Программирование', 'key' => 'programming', 'icon' => 'fa-solid fa-code'],
                ['id' => 2, 'name' => 'Дизайн', 'key' => 'design', 'icon' => 'fa-solid fa-palette'],
                ['id' => 3, 'name' => 'Маркетинг', 'key' => 'marketing', 'icon' => 'fa-solid fa-bullhorn'],
                ['id' => 4, 'name' => 'Языки', 'key' => 'languages', 'icon' => 'fa-solid fa-language'],
                ['id' => 5, 'name' => 'Менеджмент', 'key' => 'management', 'icon' => 'fa-solid fa-briefcase'],
                ['id' => 6, 'name' => 'Аналитика', 'key' => 'analytics', 'icon' => 'fa-solid fa-chart-simple'],
            ],
        ],
    ],

    'crm_legal' => [
        'title' => 'Юридические услуги',
        'icon'  => 'fa-gavel',
        'columns' => [
            'Обращения',
            'Первичная консультация',
            'Анализ дела',
            'Коммерческое предложение',
            'Договор',
            'В работе',
            'Дело закрыто',
        ],
        'config' => [
            'custom_fields' => [
                [
                    'id' => 1,
                    'title' => 'Данные дела',
                    'icon' => 'fa-solid fa-gavel',
                    'color' => '#dc2626',
                    'target' => 'client',
                    'fields' => [
                        ['label' => 'Тип дела', 'name' => 'case_type', 'type' => 'text'],
                        ['label' => 'Сумма иска', 'name' => 'claim_amount', 'type' => 'number'],
                        ['label' => 'Суд', 'name' => 'court', 'type' => 'text'],
                        ['label' => 'Номер дела', 'name' => 'case_number', 'type' => 'text'],
                        ['label' => 'Гонорар', 'name' => 'fee', 'type' => 'number'],
                        ['label' => 'Дата заседания', 'name' => 'hearing_date', 'type' => 'date'],
                        ['label' => 'Описание дела', 'name' => 'case_description', 'type' => 'textarea'],
                    ],
                ],
            ],
            'custom_categories' => [
                ['id' => 1, 'name' => 'Гражданское', 'key' => 'civil', 'icon' => 'fa-solid fa-user'],
                ['id' => 2, 'name' => 'Уголовное', 'key' => 'criminal', 'icon' => 'fa-solid fa-scale-balanced'],
                ['id' => 3, 'name' => 'Арбитраж', 'key' => 'arbitration', 'icon' => 'fa-solid fa-building-columns'],
                ['id' => 4, 'name' => 'Семейное', 'key' => 'family', 'icon' => 'fa-solid fa-people-roof'],
                ['id' => 5, 'name' => 'Трудовое', 'key' => 'labor', 'icon' => 'fa-solid fa-briefcase'],
                ['id' => 6, 'name' => 'Налоговое', 'key' => 'tax', 'icon' => 'fa-solid fa-file-invoice'],
            ],
        ],
    ],

    'crm_consulting' => [
        'title' => 'Консалтинг',
        'icon'  => 'fa-lightbulb',
        'columns' => [
            'Лиды',
            'Квалификация',
            'Диагностика',
            'Предложение',
            'Презентация',
            'Договор',
            'Проект',
            'Сдача проекта',
        ],
        'config' => [
            'custom_fields' => [
                [
                    'id' => 1,
                    'title' => 'Проект',
                    'icon' => 'fa-solid fa-lightbulb',
                    'color' => '#f59e0b',
                    'target' => 'client',
                    'fields' => [
                        ['label' => 'Тип проекта', 'name' => 'project_type', 'type' => 'text'],
                        ['label' => 'Бюджет', 'name' => 'budget', 'type' => 'number'],
                        ['label' => 'Срок проекта', 'name' => 'deadline', 'type' => 'date'],
                        ['label' => 'Команда проекта', 'name' => 'team', 'type' => 'text'],
                        ['label' => 'KPI проекта', 'name' => 'kpi', 'type' => 'textarea'],
                    ],
                ],
            ],
            'custom_categories' => [
                ['id' => 1, 'name' => 'Стратегия', 'key' => 'strategy', 'icon' => 'fa-solid fa-chess'],
                ['id' => 2, 'name' => 'Оптимизация', 'key' => 'optimization', 'icon' => 'fa-solid fa-gears'],
                ['id' => 3, 'name' => 'Внедрение', 'key' => 'implementation', 'icon' => 'fa-solid fa-rocket'],
                ['id' => 4, 'name' => 'Аудит', 'key' => 'audit', 'icon' => 'fa-solid fa-magnifying-glass-chart'],
                ['id' => 5, 'name' => 'Обучение', 'key' => 'training', 'icon' => 'fa-solid fa-chalkboard-user'],
            ],
        ],
    ],

    // ============================================================
    // ОТРАСЛЕВЫЕ РЕШЕНИЯ
    // ============================================================

    'food' => [
        'title' => 'Общепит',
        'icon'  => 'fa-utensils',
        'columns' => [
            'Отзывы',
            'Начисления баллов',
            'Вопросы',
            'Конкурсы',
            'Заказы',
            'Вывод средств',
            'Доставка',
            'Ответы',
            'Обратная связь',
        ],
    ],

    // === ШАБЛОН С ГЕНЕРАЦИЕЙ ДАННЫХ ===
    'food_test' => [
        'title' => 'Тестовый общепит',
        'icon'  => 'fa-utensils',
        'columns' => [
            'Отзывы',
            'Начисления баллов',
            'Вопросы',
            'Конкурсы',
            'Заказы',
            'Вывод средств',
            'Доставка',
            'Ответы',
            'Обратная связь',
        ],
        'config' => [
            'custom_fields' => [
                [
                    'id' => 1,
                    'title' => 'Данные заказа',
                    'icon' => 'fa-solid fa-burger',
                    'color' => '#f59e0b',
                    'target' => 'client',
                    'fields' => [
                        ['label' => 'Номер заказа', 'name' => 'order_number', 'type' => 'text'],
                        ['label' => 'Сумма заказа', 'name' => 'order_sum', 'type' => 'number'],
                        ['label' => 'Адрес доставки', 'name' => 'delivery_address', 'type' => 'text'],
                        ['label' => 'Время доставки', 'name' => 'delivery_time', 'type' => 'text'],
                        ['label' => 'Комментарий к заказу', 'name' => 'order_comment', 'type' => 'textarea'],
                    ],
                ],
            ],
            'custom_categories' => [
                ['id' => 1, 'name' => 'VIP', 'key' => 'vip', 'icon' => 'fa-solid fa-crown'],
                ['id' => 2, 'name' => 'Срочно', 'key' => 'urgent_food', 'icon' => 'fa-solid fa-bolt'],
                ['id' => 3, 'name' => 'Проблема', 'key' => 'problem', 'icon' => 'fa-solid fa-triangle-exclamation'],
                ['id' => 4, 'name' => 'Повтор', 'key' => 'repeat', 'icon' => 'fa-solid fa-rotate'],
                ['id' => 5, 'name' => 'Лояльный клиент', 'key' => 'loyal', 'icon' => 'fa-solid fa-heart'],
            ],
        ],
        'generate' => [
            'tasks_per_column' => [3, 8],
            'priorities' => ['low', 'medium', 'high'],
            'labels' => ['VIP', 'Срочно', 'Проблема', 'Повтор', 'Лояльный клиент'],
            'client_ratio' => 20,
            'client_sources' => ['Сайт', 'Приложение', 'Телефон', 'Агрегатор'],
            'client_services' => ['Доставка', 'Самовывоз', 'Зал', 'Банкет'],
            'attachments' => [
                ['name' => 'test_image_1.jpg', 'path' => 'test_image_1.jpg', 'size' => 0, 'mime' => 'image/jpg'],
                ['name' => 'test_image_2.jpg', 'path' => 'test_image_2.jpg', 'mime' => 'image/jpg', 'size' => 0],
            ],
            'columns' => [
                'Отзывы' => [
                    'titles' => ['Отзыв #{n}'],
                    'descriptions' => ['Не положили соус', 'Очень вкусно!', 'Доставка опоздала'],
                    'subtasks' => ['Проверить заказ', 'Связаться с клиентом', 'Начислить бонусы'],
                    'messages' => true,
                ],
                'Заказы' => [
                    'titles' => ['Заказ #{n}'],
                    'descriptions' => ['Пицца + кола', 'Сет суши', 'Бургер и картошка'],
                    'subtasks' => ['Принять заказ', 'Передать на кухню', 'Подготовить к выдаче'],
                    'data' => [
                        'payment_status' => ['paid', 'pending'],
                        'delivery_type' => ['delivery', 'pickup'],
                    ],
                ],
                'Доставка' => [
                    'titles' => ['Доставка #{n}'],
                    'descriptions' => ['Курьер выехал', 'Передано в доставку'],
                    'subtasks' => ['Назначить курьера', 'Передать заказ', 'Доставить клиенту'],
                    'messages' => true,
                ],
                'Обратная связь' => [
                    'titles' => ['Обращение #{n}'],
                    'descriptions' => ['Хочу вернуть деньги', 'Ошибка в заказе'],
                    'messages' => true,
                ],
            ],
        ],
    ],

    'autoservice' => [
        'title' => 'Автосервис',
        'icon'  => 'fa-car',
        'columns' => ['Диагностика', 'Ожидание запчастей', 'В работе', 'Готово', 'Выдано клиенту'],
    ],

    // === ШАБЛОН С ГЕНЕРАЦИЕЙ ДАННЫХ ===
    'autoservice_test' => [
        'title' => 'Тестовый автосервис',
        'icon'  => 'fa-car',
        'columns' => ['Диагностика', 'Ожидание запчастей', 'В работе', 'Готово', 'Выдано клиенту'],
        'config' => [
            'custom_fields' => [
                [
                    'id' => 1,
                    'title' => 'Данные автомобиля',
                    'icon' => 'fa-solid fa-car',
                    'color' => '#dc2626',
                    'target' => 'client',
                    'fields' => [
                        ['label' => 'VIN', 'name' => 'vin', 'type' => 'text'],
                        ['label' => 'Марка и модель', 'name' => 'car_model', 'type' => 'text'],
                        ['label' => 'Год выпуска', 'name' => 'year', 'type' => 'number'],
                        ['label' => 'Пробег (км)', 'name' => 'mileage', 'type' => 'number'],
                        ['label' => 'Гос. номер', 'name' => 'plate', 'type' => 'text'],
                        ['label' => 'Описание работ', 'name' => 'work_description', 'type' => 'textarea'],
                    ],
                ],
            ],
            'custom_categories' => [
                ['id' => 1, 'name' => 'Срочно', 'key' => 'urgent_auto', 'icon' => 'fa-solid fa-bolt'],
                ['id' => 2, 'name' => 'Гарантия', 'key' => 'warranty', 'icon' => 'fa-solid fa-shield-halved'],
                ['id' => 3, 'name' => 'Повторное', 'key' => 'repeat_auto', 'icon' => 'fa-solid fa-rotate'],
                ['id' => 4, 'name' => 'VIP', 'key' => 'vip_auto', 'icon' => 'fa-solid fa-crown'],
                ['id' => 5, 'name' => 'Кузовной', 'key' => 'bodywork', 'icon' => 'fa-solid fa-spray-can'],
                ['id' => 6, 'name' => 'Двигатель', 'key' => 'engine', 'icon' => 'fa-solid fa-gear'],
            ],
        ],
        'generate' => [
            'tasks_per_column' => [2, 5],
            'priorities' => ['low', 'medium', 'high'],
            'labels' => ['Срочно', 'Гарантия', 'Повторное', 'VIP'],
            'client_ratio' => 40,
            'client_sources' => ['Сайт', 'Телефон', 'Знакомые', 'Авито'],
            'client_services' => ['ТО', 'Ремонт', 'Диагностика', 'Кузовной', 'Шиномонтаж'],
            'columns' => [
                'Диагностика' => [
                    'titles' => ['Авто #{n}', 'ТО #{n}', 'Ремонт #{n}'],
                    'descriptions' => ['Стук в подвеске', 'Плановое ТО 50000 км', 'Не заводится', 'Замена тормозных колодок'],
                    'subtasks' => ['Осмотреть автомобиль', 'Составить дефектовку', 'Согласовать с клиентом'],
                ],
                'Ожидание запчастей' => [
                    'titles' => ['Ожидание #{n}'],
                    'descriptions' => ['Заказаны запчасти', 'Ожидание поставки'],
                    'subtasks' => ['Заказать запчасти', 'Отследить доставку'],
                ],
                'В работе' => [
                    'titles' => ['Ремонт #{n}'],
                    'descriptions' => ['Замена масла и фильтров', 'Ремонт двигателя', 'Покраска кузова'],
                    'subtasks' => ['Разобрать узел', 'Заменить детали', 'Собрать и проверить'],
                ],
                'Готово' => [
                    'titles' => ['Готово #{n}'],
                    'descriptions' => ['Работы завершены', 'Ожидает выдачи'],
                    'subtasks' => ['Проверить качество', 'Подготовить документы'],
                ],
            ],
        ],
    ],

    'beauty' => [
        'title' => 'Бьюти',
        'icon'  => 'fa-spa',
        'columns' => ['Запросы', 'Консультация', 'Запись', 'В процессе', 'Завершено'],
        'config' => [
            'custom_fields' => [
                [
                    'id' => 1,
                    'title' => 'Данные услуги',
                    'icon' => 'fa-solid fa-spa',
                    'color' => '#ec4899',
                    'target' => 'client',
                    'fields' => [
                        ['label' => 'Услуга', 'name' => 'service', 'type' => 'text'],
                        ['label' => 'Длительность (мин)', 'name' => 'duration', 'type' => 'number'],
                        ['label' => 'Стоимость', 'name' => 'service_cost', 'type' => 'number'],
                        ['label' => 'Мастер', 'name' => 'master', 'type' => 'text'],
                        ['label' => 'Пожелания клиента', 'name' => 'client_wishes', 'type' => 'textarea'],
                    ],
                ],
            ],
            'custom_categories' => [
                ['id' => 1, 'name' => 'Стрижка', 'key' => 'haircut', 'icon' => 'fa-solid fa-scissors'],
                ['id' => 2, 'name' => 'Окрашивание', 'key' => 'coloring', 'icon' => 'fa-solid fa-palette'],
                ['id' => 3, 'name' => 'Маникюр', 'key' => 'manicure', 'icon' => 'fa-solid fa-hand-sparkles'],
                ['id' => 4, 'name' => 'Массаж', 'key' => 'massage', 'icon' => 'fa-solid fa-hand-dots'],
                ['id' => 5, 'name' => 'Косметология', 'key' => 'cosmetology', 'icon' => 'fa-solid fa-face-smile'],
            ],
        ],
    ],

    'ecommerce' => [
        'title' => 'Интернет-магазин',
        'icon'  => 'fa-shopping-cart',
        'columns' => ['Новые заказы', 'Обработка', 'Сборка', 'Оплата', 'Упаковка', 'Отправка', 'Доставлено', 'Возвраты'],
        'config' => [
            'custom_fields' => [
                [
                    'id' => 1,
                    'title' => 'Данные заказа',
                    'icon' => 'fa-solid fa-box',
                    'color' => '#f59e0b',
                    'target' => 'client',
                    'fields' => [
                        ['label' => 'Номер заказа', 'name' => 'order_number', 'type' => 'text'],
                        ['label' => 'Сумма заказа', 'name' => 'order_sum', 'type' => 'number'],
                        ['label' => 'Трекинг-номер', 'name' => 'tracking', 'type' => 'text'],
                        ['label' => 'Служба доставки', 'name' => 'delivery_service', 'type' => 'text'],
                        ['label' => 'Адрес доставки', 'name' => 'delivery_address', 'type' => 'text'],
                    ],
                ],
            ],
            'custom_categories' => [
                ['id' => 1, 'name' => 'Экспресс', 'key' => 'express', 'icon' => 'fa-solid fa-bolt'],
                ['id' => 2, 'name' => 'Стандарт', 'key' => 'standard', 'icon' => 'fa-solid fa-box'],
                ['id' => 3, 'name' => 'Возврат', 'key' => 'return', 'icon' => 'fa-solid fa-rotate-left'],
                ['id' => 4, 'name' => 'Проблема', 'key' => 'problem_ecom', 'icon' => 'fa-solid fa-triangle-exclamation'],
            ],
        ],
    ],

    'hr_recruitment' => [
        'title' => 'Подбор персонала',
        'icon'  => 'fa-user-plus',
        'columns' => [
            'Резюме',
            'Скрининг',
            'Телефонное интервью',
            'Техническое интервью',
            'Финальное интервью',
            'Оффер',
            'Вышел на работу',
        ],
        'config' => [
            'custom_fields' => [
                [
                    'id' => 1,
                    'title' => 'Данные кандидата',
                    'icon' => 'fa-solid fa-user-tie',
                    'color' => '#0d6efd',
                    'target' => 'client',
                    'fields' => [
                        ['label' => 'Вакансия', 'name' => 'vacancy', 'type' => 'text'],
                        ['label' => 'Опыт (лет)', 'name' => 'experience', 'type' => 'number'],
                        ['label' => 'Ожидаемая ЗП', 'name' => 'expected_salary', 'type' => 'number'],
                        ['label' => 'Текущая ЗП', 'name' => 'current_salary', 'type' => 'number'],
                        ['label' => 'Город', 'name' => 'city', 'type' => 'text'],
                        ['label' => 'Навыки', 'name' => 'skills', 'type' => 'textarea'],
                        ['label' => 'LinkedIn', 'name' => 'linkedin', 'type' => 'url'],
                    ],
                ],
            ],
            'custom_categories' => [
                ['id' => 1, 'name' => 'Junior', 'key' => 'junior', 'icon' => 'fa-solid fa-seedling'],
                ['id' => 2, 'name' => 'Middle', 'key' => 'middle', 'icon' => 'fa-solid fa-user'],
                ['id' => 3, 'name' => 'Senior', 'key' => 'senior', 'icon' => 'fa-solid fa-user-gear'],
                ['id' => 4, 'name' => 'Lead', 'key' => 'lead', 'icon' => 'fa-solid fa-user-tie'],
                ['id' => 5, 'name' => 'C-level', 'key' => 'c_level', 'icon' => 'fa-solid fa-crown'],
            ],
        ],
    ],

    'marketing' => [
        'title' => 'Маркетинг',
        'icon'  => 'fa-bullhorn',
        'columns' => ['Идеи', 'Планирование', 'В работе', 'На согласовании', 'Запущено', 'Анализ результатов'],
        'config' => [
            'custom_fields' => [
                [
                    'id' => 1,
                    'title' => 'Данные кампании',
                    'icon' => 'fa-solid fa-bullhorn',
                    'color' => '#ec4899',
                    'target' => 'task',
                    'fields' => [
                        ['label' => 'Канал', 'name' => 'channel', 'type' => 'text'],
                        ['label' => 'Бюджет', 'name' => 'budget', 'type' => 'number'],
                        ['label' => 'Ожидаемый охват', 'name' => 'expected_reach', 'type' => 'number'],
                        ['label' => 'Дата запуска', 'name' => 'launch_date', 'type' => 'date'],
                        ['label' => 'KPI', 'name' => 'kpi', 'type' => 'textarea'],
                    ],
                ],
            ],
            'custom_categories' => [
                ['id' => 1, 'name' => 'Контекст', 'key' => 'context_ads', 'icon' => 'fa-solid fa-magnifying-glass-dollar'],
                ['id' => 2, 'name' => 'Таргет', 'key' => 'target_ads', 'icon' => 'fa-solid fa-bullseye'],
                ['id' => 3, 'name' => 'SEO', 'key' => 'seo', 'icon' => 'fa-solid fa-chart-line'],
                ['id' => 4, 'name' => 'SMM', 'key' => 'smm', 'icon' => 'fa-solid fa-share-nodes'],
                ['id' => 5, 'name' => 'Email', 'key' => 'email_marketing', 'icon' => 'fa-solid fa-envelope-open-text'],
                ['id' => 6, 'name' => 'PR', 'key' => 'pr', 'icon' => 'fa-solid fa-newspaper'],
            ],
        ],
    ],

    'support' => [
        'title' => 'Техподдержка',
        'icon'  => 'fa-headset',
        'columns' => ['Новые тикеты', 'В работе', 'Ожидание клиента', 'Ожидание решения', 'Решено', 'Закрыто'],
        'config' => [
            'custom_fields' => [
                [
                    'id' => 1,
                    'title' => 'Данные тикета',
                    'icon' => 'fa-solid fa-headset',
                    'color' => '#0d6efd',
                    'target' => 'task',
                    'fields' => [
                        ['label' => 'Категория', 'name' => 'category', 'type' => 'text'],
                        ['label' => 'Приоритет SLA', 'name' => 'sla_priority', 'type' => 'text'],
                        ['label' => 'Время решения (ч)', 'name' => 'resolution_time', 'type' => 'number'],
                        ['label' => 'Система', 'name' => 'system', 'type' => 'text'],
                        ['label' => 'Описание проблемы', 'name' => 'issue_description', 'type' => 'textarea'],
                    ],
                ],
            ],
            'custom_categories' => [
                ['id' => 1, 'name' => 'Баг', 'key' => 'bug_support', 'icon' => 'fa-solid fa-bug'],
                ['id' => 2, 'name' => 'Вопрос', 'key' => 'question', 'icon' => 'fa-solid fa-circle-question'],
                ['id' => 3, 'name' => 'Запрос функции', 'key' => 'feature_request', 'icon' => 'fa-solid fa-lightbulb'],
                ['id' => 4, 'name' => 'Инцидент', 'key' => 'incident', 'icon' => 'fa-solid fa-triangle-exclamation'],
                ['id' => 5, 'name' => 'Консультация', 'key' => 'consultation', 'icon' => 'fa-solid fa-comments'],
            ],
        ],
    ],

];
