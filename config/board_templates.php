<?php

return [

    // === КЛАССИЧЕСКИЕ ===
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
    ],

    // === CRM ВОРОНКИ ПРОДАЖ ===

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
                        ['label' => 'Вероятность', 'name' => 'probability', 'type' => 'number'],
                        ['label' => 'Комментарий', 'name' => 'deal_comment', 'type' => 'textarea'],
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
        'generate' => [
            'tasks_per_column' => [2, 5],
            'priorities' => ['low', 'medium', 'high'],
            'labels' => ['client'],
            'clients' => true,
            'client_sources' => ['Сайт', 'Рекомендация', 'Контекст', 'SEO', 'Instagram', 'Telegram', 'Холодный звонок'],
            'client_services' => ['Стандарт', 'Премиум', 'VIP', 'Корпоративный'],
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
                    ],
                ],
            ],
        ],
        'generate' => [
            'tasks_per_column' => [2, 4],
            'priorities' => ['low', 'medium', 'high'],
            'labels' => ['client'],
            'clients' => true,
            'client_sources' => ['Сайт', 'Звонок', 'Email'],
            'client_services' => ['Базовый', 'Стандарт'],
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
                    'title' => 'B2B данные',
                    'icon' => 'fa-solid fa-building',
                    'color' => '#667eea',
                    'target' => 'client',
                    'fields' => [
                        ['label' => 'ИНН компании', 'name' => 'inn', 'type' => 'text'],
                        ['label' => 'Отрасль', 'name' => 'industry', 'type' => 'text'],
                        ['label' => 'Размер компании', 'name' => 'company_size', 'type' => 'text'],
                        ['label' => 'Бюджет', 'name' => 'budget', 'type' => 'number'],
                        ['label' => 'ЛПР', 'name' => 'decision_maker', 'type' => 'text'],
                        ['label' => 'Заметки', 'name' => 'notes', 'type' => 'textarea'],
                    ],
                ],
            ],
            'custom_categories' => [
                ['id' => 1, 'name' => 'Корпоративный', 'key' => 'corporate', 'icon' => 'fa-solid fa-building'],
                ['id' => 2, 'name' => 'Средний бизнес', 'key' => 'medium_biz', 'icon' => 'fa-solid fa-city'],
                ['id' => 3, 'name' => 'Малый бизнес', 'key' => 'small_biz', 'icon' => 'fa-solid fa-store'],
                ['id' => 4, 'name' => 'Гос. сектор', 'key' => 'government', 'icon' => 'fa-solid fa-landmark'],
            ],
        ],
        'generate' => [
            'tasks_per_column' => [1, 3],
            'priorities' => ['low', 'medium', 'high'],
            'labels' => ['client'],
            'clients' => true,
            'client_sources' => ['LinkedIn', 'Конференция', 'Партнёр', 'Тендер', 'Рекомендация'],
            'client_services' => ['Enterprise', 'Business', 'Starter'],
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
                        ['label' => 'Адрес', 'name' => 'address', 'type' => 'text'],
                    ],
                ],
            ],
            'custom_categories' => [
                ['id' => 1, 'name' => 'Квартира', 'key' => 'apartment', 'icon' => 'fa-solid fa-building'],
                ['id' => 2, 'name' => 'Дом', 'key' => 'house', 'icon' => 'fa-solid fa-house'],
                ['id' => 3, 'name' => 'Коммерция', 'key' => 'commercial', 'icon' => 'fa-solid fa-store'],
                ['id' => 4, 'name' => 'Участок', 'key' => 'land', 'icon' => 'fa-solid fa-tree'],
            ],
        ],
        'generate' => [
            'tasks_per_column' => [1, 3],
            'priorities' => ['low', 'medium', 'high'],
            'labels' => ['client'],
            'clients' => true,
            'client_sources' => ['Авито', 'Циан', 'Сайт', 'Рекомендация', 'Соцсети'],
            'client_services' => ['Покупка', 'Продажа', 'Аренда'],
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
                        ['label' => 'Даты', 'name' => 'dates', 'type' => 'text'],
                        ['label' => 'Кол-во туристов', 'name' => 'tourists_count', 'type' => 'number'],
                        ['label' => 'Стоимость тура', 'name' => 'tour_cost', 'type' => 'number'],
                        ['label' => 'Отель', 'name' => 'hotel', 'type' => 'text'],
                    ],
                ],
            ],
        ],
        'generate' => [
            'tasks_per_column' => [1, 3],
            'priorities' => ['low', 'medium', 'high'],
            'labels' => ['client'],
            'clients' => true,
            'client_sources' => ['Сайт', 'Instagram', 'Рекомендация', 'Выставка'],
            'client_services' => ['Пляжный', 'Экскурсионный', 'Горнолыжный', 'Круиз'],
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
                    ],
                ],
            ],
            'custom_categories' => [
                ['id' => 1, 'name' => 'Программирование', 'key' => 'programming', 'icon' => 'fa-solid fa-code'],
                ['id' => 2, 'name' => 'Дизайн', 'key' => 'design', 'icon' => 'fa-solid fa-palette'],
                ['id' => 3, 'name' => 'Маркетинг', 'key' => 'marketing', 'icon' => 'fa-solid fa-bullhorn'],
                ['id' => 4, 'name' => 'Языки', 'key' => 'languages', 'icon' => 'fa-solid fa-language'],
            ],
        ],
        'generate' => [
            'tasks_per_column' => [2, 4],
            'priorities' => ['low', 'medium', 'high'],
            'labels' => ['client'],
            'clients' => true,
            'client_sources' => ['Сайт', 'Реклама', 'YouTube', 'Рекомендация'],
            'client_services' => ['Онлайн', 'Офлайн', 'Индивидуально', 'Группа'],
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
                        ['label' => 'Гонорар', 'name' => 'fee', 'type' => 'number'],
                        ['label' => 'Описание', 'name' => 'case_description', 'type' => 'textarea'],
                    ],
                ],
            ],
            'custom_categories' => [
                ['id' => 1, 'name' => 'Гражданское', 'key' => 'civil', 'icon' => 'fa-solid fa-user'],
                ['id' => 2, 'name' => 'Уголовное', 'key' => 'criminal', 'icon' => 'fa-solid fa-scale-balanced'],
                ['id' => 3, 'name' => 'Арбитраж', 'key' => 'arbitration', 'icon' => 'fa-solid fa-building-columns'],
                ['id' => 4, 'name' => 'Семейное', 'key' => 'family', 'icon' => 'fa-solid fa-people-roof'],
            ],
        ],
        'generate' => [
            'tasks_per_column' => [1, 3],
            'priorities' => ['low', 'medium', 'high'],
            'labels' => ['client'],
            'clients' => true,
            'client_sources' => ['Сайт', 'Рекомендация', 'Партнёр'],
            'client_services' => ['Консультация', 'Представительство', 'Абонентское'],
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
                        ['label' => 'Срок', 'name' => 'deadline', 'type' => 'date'],
                        ['label' => 'Команда', 'name' => 'team', 'type' => 'text'],
                    ],
                ],
            ],
        ],
        'generate' => [
            'tasks_per_column' => [1, 3],
            'priorities' => ['low', 'medium', 'high'],
            'labels' => ['client'],
            'clients' => true,
            'client_sources' => ['LinkedIn', 'Конференция', 'Партнёр', 'Сайт'],
            'client_services' => ['Стратегия', 'Оптимизация', 'Внедрение', 'Аудит'],
        ],
    ],

    // === ОТРАСЛЕВЫЕ ===

    'food' => [
        'title' => 'Общепит',
        'icon'  => 'fa-utensils',
        'columns' => [
            'Отзывы', 'Начисления баллов', 'Вопросы', 'Конкурсы',
            'Заказы', 'Вывод средств', 'Доставка', 'Ответы', 'Обратная связь',
        ],
    ],

    'food_test' => [
        'title' => 'Тестовый общепит',
        'icon'  => 'fa-utensils',
        'columns' => [
            'Отзывы', 'Начисления баллов', 'Вопросы', 'Конкурсы',
            'Заказы', 'Вывод средств', 'Доставка', 'Ответы', 'Обратная связь',
        ],
        'generate' => [
            'tasks_per_column' => [3, 8],
            'priorities' => ['low', 'medium', 'high'],
            'labels' => ['VIP', 'Срочно', 'Проблема', 'Повтор', 'Лояльный клиент'],
            'attachments' => [
                ['name' => 'test_image_1.jpg', 'path' => 'test_image_1.jpg', 'size' => 0, 'mime' => 'image/jpg'],
                ['name' => 'test_image_2.jpg', 'path' => 'test_image_2.jpg', 'mime' => 'image/jpg', 'size' => 0],
            ],
            'client_ratio' => 20,           // ← 20% клиентов
            'client_sources' => ['Сайт', 'Приложение', 'Телефон'],
            'client_services' => ['Доставка', 'Самовывоз', 'Зал'],
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

    'autoservice_test' => [
        'title' => 'Тестовый автосервис',
        'icon'  => 'fa-car',
        'columns' => ['Диагностика', 'Ожидание запчастей', 'В работе', 'Готово', 'Выдано клиенту'],
        'generate' => [
            'tasks_per_column' => [2, 5],
            'priorities' => ['low', 'medium', 'high'],
            'labels' => ['Срочно', 'Гарантия', 'Повторное', 'VIP'],
            'client_ratio' => 30,
            'columns' => [
                'Диагностика' => [
                    'titles' => ['Авто #{n}', 'ТО #{n}', 'Ремонт #{n}'],
                    'descriptions' => ['Стук в подвеске', 'Плановое ТО 50000 км', 'Не заводится', 'Замена тормозных колодок'],
                    'subtasks' => ['Осмотреть автомобиль', 'Составить дефектовку', 'Согласовать с клиентом'],
                ],
                'В работе' => [
                    'titles' => ['Ремонт #{n}'],
                    'descriptions' => ['Замена масла и фильтров', 'Ремонт двигателя', 'Покраска кузова'],
                    'subtasks' => ['Разобрать узел', 'Заменить детали', 'Собрать и проверить'],
                ],
            ],
        ],
    ],

    'beauty' => [
        'title' => 'Бьюти',
        'icon'  => 'fa-spa',
        'columns' => ['Запросы', 'Консультация', 'Запись', 'В процессе', 'Завершено'],
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
                        ['label' => 'Сумма', 'name' => 'order_sum', 'type' => 'number'],
                        ['label' => 'Трекинг', 'name' => 'tracking', 'type' => 'text'],
                    ],
                ],
            ],
        ],
        'generate' => [
            'tasks_per_column' => [2, 5],
            'priorities' => ['low', 'medium', 'high'],
            'labels' => ['client'],
            'clients' => true,
            'client_sources' => ['Сайт', 'Маркетплейс', 'Instagram'],
            'client_services' => ['Стандарт', 'Экспресс'],
        ],
    ],

    'hr_recruitment' => [
        'title' => 'Подбор персонала',
        'icon'  => 'fa-user-plus',
        'columns' => [
            'Резюме', 'Скрининг', 'Телефонное интервью',
            'Техническое интервью', 'Финальное интервью', 'Оффер', 'Вышел на работу',
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
                        ['label' => 'Навыки', 'name' => 'skills', 'type' => 'textarea'],
                    ],
                ],
            ],
        ],
        'generate' => [
            'tasks_per_column' => [2, 4],
            'priorities' => ['low', 'medium', 'high'],
            'labels' => ['client'],
            'clients' => true,
            'client_sources' => ['HeadHunter', 'LinkedIn', 'Рекомендация', 'Отклик'],
            'client_services' => ['Junior', 'Middle', 'Senior', 'Lead'],
        ],
    ],

    'marketing' => [
        'title' => 'Маркетинг',
        'icon'  => 'fa-bullhorn',
        'columns' => ['Идеи', 'Планирование', 'В работе', 'На согласовании', 'Запущено', 'Анализ результатов'],
    ],

    'support' => [
        'title' => 'Техподдержка',
        'icon'  => 'fa-headset',
        'columns' => ['Новые тикеты', 'В работе', 'Ожидание клиента', 'Ожидание решения', 'Решено', 'Закрыто'],
    ],

];
