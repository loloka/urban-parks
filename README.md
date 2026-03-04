<div align="center">

# 🌳 Urban Parks

Международная радиолюбительская программа для работы из городских парков

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square)](https://laravel.com) [![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square)](https://php.net) [![Tailwind](https://img.shields.io/badge/Tailwind-4.x-38B2AC?style=flat-square)](https://tailwindcss.com)

</div>

## О проекте

Urban Parks — платформа для радиолюбителей, активирующих городские парки (вдохновлена POTA и WWFF).

**Возможности:** Интерактивная карта · 18+ парков России · Логирование активаций · Фильтры и поиск · Админ-панель · Статистика

## Технологии

- **Админка:** Filament 3
- **Backend:** Laravel 12.x, PHP 8.4
- **Frontend:** Tailwind CSS 3.x, Alpine.js
- **Карты:** Leaflet.js + MarkerCluster OpenStreetMap
- **База данных:** MySQL 8.0
- **Деплой:** Vite, npm

## Установка

### Требования:

- PHP >= 8.2
- Composer
- Node.js >= 18
- MySQL >= 8.0

```bash
git clone https://github.com/loloka/urban-parks.git
cd urban-parks
composer install && npm install
cp .env.example .env
php artisan key:generate
mysql -u root -e "CREATE DATABASE urban_parks;"
php artisan migrate --seed
php artisan make:filament-user
php artisan serve & npm run dev

Сайт: http://localhost:8000 · Админка: http://localhost:8000/admin

Структура

urban-parks/
├── app/Filament/          # Админ-панель (Resources, Widgets)
├── app/Http/Controllers/  # ParkController, ApiController
├── app/Models/            # Park, Activation
├── database/migrations/   # Миграции БД
├── database/seeders/      # Тестовые данные (18 парков)
├── resources/views/       # Blade шаблоны
└── routes/                # web.php, api.php

API
GET /api/parks (параметры: city, region, search)
GET /api/cities
GET /api/regions


Парки
Новосибирск (6): UP-0001 Центральный · UP-0002 Заельцовский · UP-0003 Березовая роща · UP-0004 Сосновый бор · UP-0005 Городское начало · UP-0006 Нарымский сквер

Москва (4): UP-0007 Горького · UP-0008 Сокольники · UP-0009 Коломенское · UP-0010 Победы

СПб (4): UP-0011 Летний сад · UP-0012 Александровский · UP-0013 Таврический · UP-0017 Парк 300-летия

Другие (4): UP-0014 Белоусова (Тула) · UP-0015 Маяковского (Екб) · UP-0016 Горького (Казань) · UP-0018 Ботанический (Влд)

Roadmap
v1.0 (текущая): Карта · Парки · Админка · API

v1.1 (в работе): Авторизация · Публичная форма · Личный кабинет · Дипломы · ADIF экспорт

v2.0 (план): Telegram бот · QRZ интеграция · Другие страны · Мобильное приложение · Ham2K API


Добавлено 04.03
- 📍 Просматривать каталог парков на интерактивной карте
- 📡 Добавлять и модерировать активации
- 🏆 Отслеживать рейтинги активаторов
- 📥 Экспортировать данные в ADIF
- 🎖️ Получать дипломы за достижения

---

## 🚀 Версия 1.1 (04.03.2026)

### ✅ Реализованные функции:

#### **Основные возможности:**
- ✅ Интерактивная карта парков (Leaflet + кластеризация)
- ✅ Каталог парков с фильтрами (город, регион, поиск)
- ✅ Детальные страницы парков с информацией
- ✅ Добавление активаций через форму
- ✅ Админ-панель для модерации активаций
- ✅ Статистика на главной странице

#### **Новое в v1.1:**
- 🆕 **QRZ.com интеграция** — позывные кликабельны
- 🆕 **Экспорт в ADIF** — скачивание логов парка
- 🆕 **Топ активаторов** — рейтинг с медалями 🥇🥈🥉
- 🆕 **Последние активации** — лента на главной
- 🆕 **Улучшенный дизайн** — современный UI/UX




📝 Roadmap
🔜 Планируется в v1.2:
 Страница всех активаций — таблица с фильтрами и поиском
 Личный кабинет активатора — мои активации, статистика
 Страница дипломов — условия получения и список владельцев
 Email уведомления — модератору и активаторам
 RSS лента — подписка на новые активации
🎯 Будущие фичи (v1.3+):
 Импорт ADIF из Ham2K — автозаполнение через файл
 Telegram бот — поиск парков, уведомления
 Тепловая карта активности — визуализация популярности парков
 PDF сертификаты — генерация дипломов с QR-кодом
 Автоподсчёт дипломов — отслеживание прогресса
 Экспорт в CSV/KML/GPX — для Excel/Google Earth/навигаторов
 API для сторонних приложений — интеграция с Ham2K, Log4OM


 ===================================

Лицензия
MIT License

Авторы
R9OGL & UA9OTW @loloka

Благодарности: POTA · WWFF · OpenStreetMap · Laragon · Laravel

Контакты
📧 info@urbanparks.ru · 📱 @urbanparks · 🌐 vk.com/urbanparks

73! Приятных QSO из парков! 📡🌳

Made with ❤️ by ham radio operators

```
