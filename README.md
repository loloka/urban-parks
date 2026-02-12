<div align="center">

# 🌳 Urban Parks

### Международная радиолюбительская программа для работы из городских парков

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.x-38B2AC?style=for-the-badge&logo=tailwind-css)](https://tailwindcss.com)
[![Filament](https://img.shields.io/badge/Filament-3.x-FDBA74?style=for-the-badge)](https://filamentphp.com)

[🌐 Демо](#) · [📖 Документация](#документация) · [🐛 Баги](https://github.com/loloka/urban-parks/issues) · [💡 Идеи](https://github.com/loloka/urban-parks/issues)

</div>

---

## 📖 О проекте

**Urban Parks** — веб-платформа для радиолюбителей, активирующих городские парки. Вдохновлён POTA и WWFF, но для городских парков.

### ✨ Возможности

- 🗺️ Интерактивная карта с кластеризацией (Leaflet.js)
- 📍 18+ парков России
- 📡 Логирование активаций
- 🎯 Фильтры по городам/регионам
- 🔍 Быстрый поиск
- 👨‍💼 Админ-панель Filament 3
- 📊 Статистика в реальном времени
- 🌍 Международная поддержка
- 📱 Адаптивный дизайн

---

## 🛠️ Технологии

- Laravel 12.x
- PHP 8.4+
- MySQL 8.0+
- Tailwind CSS 4.x
- Alpine.js 3.x
- Vite 7.x
- Filament 3.2+
- Leaflet.js 1.9+
- OpenStreetMap

---

## 🚀 Установка

```bash
git clone https://github.com/loloka/urban-parks.git
cd urban-parks
composer install
npm install
cp .env.example .env
php artisan key:generate
mysql -u root -e "CREATE DATABASE urban_parks CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --seed
php artisan make:filament-user
php artisan serve
npm run dev
```

Готово!

Сайт: http://localhost:8000
Админка: http://localhost:8000/admin
📚 API
GET /api/parks
Получить все парки (фильтры: city, region, search)

GET /api/cities
Список городов с количеством парков

GET /api/regions
Список регионов с количеством парков

🗺️ Парки
Новосибирск (6) 🏆
UP-0001 Центральный парк | UP-0002 Заельцовский | UP-0003 Березовая роща | UP-0004 Сосновый бор | UP-0005 Городское начало | UP-0006 Нарымский сквер

Москва (4)
UP-0007 Парк Горького | UP-0008 Сокольники | UP-0009 Коломенское | UP-0010 Парк Победы

Санкт-Петербург (4)
UP-0011 Летний сад | UP-0012 Александровский сад | UP-0013 Таврический сад | UP-0017 Парк 300-летия

Другие (4)
UP-0014 ЦПКиО Белоусова (Тула) | UP-0015 Парк Маяковского (Екатеринбург) | UP-0016 Парк Горького (Казань) | UP-0018 Ботанический сад (Владивосток)

🎯 Roadmap
v1.0 (Текущая) ✅

Карта, парки, админка, API
v1.1 (В разработке) 🚧

Авторизация
Публичная форма активаций
Личный кабинет
Дипломы
ADIF экспорт
v2.0 (План) 📅

Telegram бот
QRZ интеграция
Другие страны
Мобильное приложение
Ham2K API
🤝 Участие
🐛 Баг? → Issue
💡 Идея? → Discussions
🌳 Добавь парки → Pull Request
⭐ Поставь звезду!
📄 Лицензия
MIT License. См. LICENSE

👨‍💻 Автор
R9OGL & UA9OTW

Благодарности: POTA, WWFF, OpenStreetMap, Laragon

📞 Контакты
📧 info@urbanparks.ru
📱 Telegram: @urbanparks
🌐 VK: vk.com/urbanparks

73! Приятных QSO из парков! 📡🌳
Made with ❤️ by ham radio operators
