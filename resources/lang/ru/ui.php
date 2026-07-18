<?php

return [
    // Навигация
    'nav' => [
        'map' => 'Карта',
        'parks' => 'Парки',
        'top' => 'Топ',
        'add_activation' => 'Добавить активацию',
        'diplomas' => 'Дипломы',
        'cabinet' => 'Кабинет',
        'login' => 'Войти',
        'logout' => 'Выйти',
    ],

    // Hero секция
    'hero' => [
        'title' => 'Активируй городские парки!',
        'subtitle' => 'Международная радиолюбительская программа для работы из городских парков',
        'stats' => [
            'parks' => 'Парков',
            'activations' => 'Активаций',
            'cities' => 'Городов',
            'regions' => 'Регионов',
        ],
        'featured_city' => 'Столица программы: :city · парков: :count',
        'latest_activation' => 'Последняя активация:',
    ],

    // Карта
    'map' => [
        'title' => 'Карта парков',
        'filter_city' => 'Все города',
        'filter_region' => 'Все регионы',
        'search_placeholder' => 'Поиск парка...',
        'visible_parks' => 'парков на карте',
        'reset_filters' => 'Сбросить фильтры',
    ],

    // Топ активаторов
    'top' => [
        'title' => 'Топ активаторов',
        'subtitle' => 'Лидеры программы Urban Parks',
        'rank' => '#',
        'callsign' => 'Позывной',
        'parks' => 'Парков',
        'activations' => 'Активаций',
        'qso' => 'QSO',
        'no_activators' => 'Пока нет активаторов',
        'be_first' => 'Будь первым!',
        'view_all' => 'Посмотреть всех активаторов',
    ],

    // Парки
    'parks' => [
        'title' => 'Последние добавленные парки',
        'latest_activator' => 'Последний:',
        'not_activated' => 'Ещё не активирован',
        'activations_count' => 'Активаций',
        'more_details' => 'Подробнее',
        'not_found' => 'Парки не найдены',
        'try_filters' => 'Попробуйте изменить фильтры',
        'loading' => 'Загрузка парков...',
    ],

    // Страница парка
    'park' => [
        'area' => 'Площадь',
        'location' => 'Местоположение',
        'activations' => 'Активации',
        'no_activations' => 'Пока нет активаций',
        'be_first_activator' => 'Станьте первым активатором этого парка!',
        'add_activation' => 'Добавить активацию',
        'export_adif' => 'Скачать ADIF',
        'qso_count' => 'QSO',
        'notes' => 'Заметки',
    ],

    // Features
    'features' => [
        'title' => 'Почему Urban Parks?',
        'accessibility' => [
            'title' => 'Доступность',
            'text' => 'Городские парки доступны круглый год и находятся рядом с домом',
        ],
        'diplomas' => [
            'title' => 'Дипломы',
            'text' => 'Зарабатывай дипломы за активацию парков и связи с активаторами',
        ],
        'community' => [
            'title' => 'Сообщество',
            'text' => 'Встречай единомышленников и участвуй в массовых активациях',
        ],
    ],

    // Footer
    'footer' => [
        'about' => 'О проекте',
        'rules' => 'Правила',
        'api' => 'API',
        'contacts' => 'Контакты',
        'copyright' => 'Сделано с ❤️ радиолюбителями для радиолюбителей',
    ],

    // Общие
    'loading' => 'Загрузка...',
    'error' => 'Ошибка загрузки данных',
    // Страница парка (добавляем недостающие)
    'park' => [
        'area' => 'Площадь',
        'location' => 'Местоположение',
        'activations' => 'Активации',
        'no_activations' => 'Активаций пока нет',
        'be_first_activator' => 'Станьте первым активатором этого парка!',
        'add_activation' => 'Добавить активацию',
        'export_adif' => 'Скачать ADIF',
        'qso_count' => 'QSO',
        'notes' => 'Заметки',
    ],

    // Общие фразы (добавляем)
    'description' => 'Описание',
    'actions' => 'Действия',
    'statistics' => 'Статистика',
    'activation_rules' => 'Правила активации',
    'latitude' => 'Широта',
    'longitude' => 'Долгота',
    'active' => 'Активен',
    'inactive' => 'Неактивен',
    'total_activations' => 'Всего активаций',
    'unique_callsigns' => 'Уникальных позывных',
    'total_qso' => 'Всего QSO',
    'minimum_10_qso' => 'Минимум 10 QSO для активации',
    'work_on_amateur_bands' => 'Работа на любительских диапазонах',
    'respect_park_visitors' => 'Уважение к посетителям парка',
    'portable_equipment' => 'Портативное оборудование',
    'home' => 'Главная',
    'navigation' => 'Навигация',

    // Список всех парков
    'parks_index' => [
        'title' => 'Все парки',
        'subtitle' => 'Парки и сады программы Urban Parks',
        'activations' => 'активаций',
        'more_details' => 'Подробнее',
        'empty' => 'Парки не найдены',
    ],

    // Публичная страница активации
    'activation_page' => [
        'title' => 'Активация',
        'in_park' => 'в парке',
        'photos' => 'Фотографии',
        'no_photos' => 'Фотографии не приложены',
        'log_summary' => 'Сводка лога',
        'log' => 'Журнал QSO',
        'col_time' => 'Время',
        'col_callsign' => 'Позывной',
        'col_band' => 'Диапазон',
        'col_mode' => 'Мода',
        'rst_hint' => 'отправлен / принят',
        'total_qso' => 'Всего QSO',
        'bands' => 'Диапазоны',
        'modes' => 'Виды',
        'time_utc' => 'Время (UTC)',
        'download_adif' => 'Скачать ADIF',
        'no_log' => 'Лог недоступен',
        'notes' => 'Заметки активатора',
        'view' => 'Подробнее',
        'pending_notice' => 'Активация ещё не одобрена — эта страница видна только вам как модератору.',
    ],
];
