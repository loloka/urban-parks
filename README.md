<div align="center">

# 🌳 Urban Parks (UPTA)

Международная радиолюбительская дипломная программа для работы из городских парков

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square)](https://laravel.com) [![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square)](https://php.net) [![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=flat-square)](https://docs.docker.com/compose/) [![Tailwind](https://img.shields.io/badge/Tailwind-4.x-38B2AC?style=flat-square)](https://tailwindcss.com)

</div>

## О проекте

Urban Parks — платформа для радиолюбителей, активирующих городские парки (вдохновлена POTA и WWFF). Активаторы выезжают в парки и проводят QSO, охотники связываются с ними и зарабатывают дипломы.

**Возможности:** интерактивная карта · каталог парков · активации с модерацией · ADIF-экспорт · рейтинг активаторов · мультиязычность RU/EN · админ-панель

Архитектура ADIF-загрузки, модерации и автозачёта: см. [ARCHITECTURE.md](ARCHITECTURE.md).

## Технологии

- **Backend:** Laravel 12.x, PHP 8.4
- **Админка:** Filament 3
- **Frontend:** Tailwind CSS 4, Alpine.js, Vite
- **Карты:** Leaflet.js + MarkerCluster (OpenStreetMap)
- **База данных:** MySQL 8.0
- **Окружение:** Docker Compose (nginx, php-fpm, mysql, node, queue worker)

## Быстрый старт (Docker)

Требуется только [Docker Desktop](https://www.docker.com/products/docker-desktop/). PHP, Composer, Node и MySQL на хост ставить не нужно.

```powershell
git clone https://github.com/loloka/urban-parks.git
cd urban-parks
copy .env.example .env

# Поднять контейнеры (первый раз соберётся образ PHP, ~2-3 минуты)
docker compose up -d --build

# Зависимости и инициализация
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan make:filament-user
```

Готово:

- Сайт: http://localhost:8080
- Админка: http://localhost:8080/admin
- Vite HMR: порт 5173 (контейнер `node` запускает `npm run dev` сам)
- MySQL снаружи (HeidiSQL/DBeaver): `localhost:3306`, логин/пароль из `.env`

### Полезные команды

```powershell
docker compose exec app php artisan migrate     # миграции
docker compose exec app php artisan test        # тесты
docker compose exec app composer format         # php-cs-fixer
docker compose exec node npm run build          # прод-сборка фронта
docker compose logs -f app                      # логи PHP
docker compose down                             # остановить (данные БД сохраняются)
docker compose down -v                          # остановить и стереть БД
```

Порты можно поменять в `.env`: `APP_PORT`, `FORWARD_DB_PORT`, `VITE_PORT`.

### Контейнеры

| Сервис | Что делает |
|---|---|
| `app` | PHP 8.4 FPM + Composer (образ из `docker/php/Dockerfile`) |
| `nginx` | Веб-сервер, порт 8080 → 80 |
| `mysql` | MySQL 8.0, данные в томе `mysql-data` |
| `queue` | `php artisan queue:work` — фоновые задачи |
| `node` | Vite dev-сервер с HMR (только для разработки) |

## Структура

```
urban-parks/
├── app/Filament/          # Админ-панель (Resources, Widgets)
├── app/Http/Controllers/  # ParkController, ActivationController
├── app/Models/            # Park, Activation, Qso, ActivationProof
├── app/Services/Adif/     # Парсер ADIF-логов (+ тесты)
├── database/migrations/   # Миграции БД
├── database/seeders/      # Тестовые данные
├── docker/                # Dockerfile, конфиги nginx и PHP
├── resources/views/       # Blade-шаблоны
├── routes/web.php         # Роуты + API
└── ARCHITECTURE.md        # Архитектура UPTA (БД, ADIF, модерация, СРР)
```

## API

- `GET /api/parks` — парки (параметры: `city`, `region`, `search`)
- `GET /api/cities` — города со счётчиками
- `GET /api/regions` — регионы со счётчиками
- `GET /park/{park}/adif` — экспорт активаций парка в ADIF

## Формат референсов

`UP-<страна>-<регион>-<номер>`: например `UP-RU-NSK-0001` (Россия, Новосибирская область). Номер выдаётся автоматически при создании парка в админке.

## Roadmap

- **v1.2 (в работе):** загрузка ADIF-логов активаторами · таблица QSO · фото-пруфы · экран модерации · личный кабинет
- **v1.3:** дипломы и PDF-сертификаты · статистика охотников · email-уведомления
- **v2.0:** синхронизация с СРР (автозачёт охотников) · Telegram-бот · другие страны

Подробности — в [ARCHITECTURE.md](ARCHITECTURE.md), §7.

## Лицензия

MIT License

## Авторы

R9OGL & UA9OTW · [@loloka](https://github.com/loloka)

Благодарности: POTA · WWFF · OpenStreetMap · Laravel

📧 info@urbanparks.ru · 🌐 vk.com/urbanparks

**73! Приятных QSO из парков!** 📡🌳
