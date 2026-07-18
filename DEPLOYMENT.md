# Деплой Urban Parks

Проект — обычное Laravel 12 приложение. Docker используется только для локальной разработки; на прод можно ехать двумя путями: **шаред-хостинг без Docker** или **VPS с Docker**.

---

## Требования (любой вариант)

- PHP **8.2+** (рекомендуется 8.3/8.4) с расширениями:
  `pdo_mysql`, `intl` (обязательно для Filament!), `mbstring`, `zip`, `gd`, `bcmath`, `exif`, `curl`, `openssl`
- MySQL 5.7+ / 8.0 (кодировка utf8mb4)
- Composer 2
- Node.js **не нужен на сервере** — фронт собирается локально

Проверить расширения на хостинге: `php -m | grep -iE 'intl|pdo_mysql|gd|zip|bcmath|exif'`

---

## Вариант А: шаред-хостинг (без Docker, «по старинке»)

### 1. Собрать фронт локально

```powershell
docker compose exec node npm run build
```

Появится `public/build/` — эту папку нужно доставить на сервер вместе с кодом
(она в `.gitignore`, поэтому заливается отдельно: FTP/SCP или деплой-скриптом).

### 2. Залить код и поставить зависимости

```bash
git clone https://github.com/loloka/urban-parks.git .   # или FTP
composer install --no-dev --optimize-autoloader
```

### 3. Настроить .env

```ini
APP_NAME="Urban Parks"
APP_ENV=production
APP_DEBUG=false                 # ОБЯЗАТЕЛЬНО false на проде!
APP_URL=https://urbanparks.ru

DB_CONNECTION=mysql
DB_HOST=localhost               # обычно localhost на шареде
DB_DATABASE=...                 # из панели хостинга
DB_USERNAME=...
DB_PASSWORD=...

QUEUE_CONNECTION=sync           # на шареде воркер держать негде (см. ниже)
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true      # куки только по HTTPS
CACHE_STORE=database
LOG_LEVEL=warning

# Почта — Resend по SMTP (нужна для подтверждения email активаторов)
MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.com
MAIL_PORT=587
MAIL_USERNAME=resend
MAIL_PASSWORD=re_ВАШ_RESEND_API_KEY
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@ваш-домен      # домен подтверждён в Resend
MAIL_FROM_NAME="Urban Parks"
```

> **Почта / подтверждение email.** Регистрация активаторов требует подтверждения email —
> без рабочей почты новый пользователь не сможет загружать активации (загрузка под middleware
> `verified`). Локально сойдёт `MAIL_MAILER=log` (письма в `storage/logs`), для боевого —
> Resend по SMTP как выше. `MAIL_FROM_ADDRESS` обязан быть на домене, верифицированном в Resend,
> иначе письма не уйдут. Модераторы/админы помечаются подтверждёнными автоматически
> (миграция `verify_existing_staff_emails`).

### 4. Инициализация

```bash
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=ParkSeeder    # только парки, без тестовых активаций!
php artisan make:filament-user            # админ
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

### 5. Document root → `public/`

В панели хостинга корень сайта должен указывать на `/path/to/urban-parks/public`,
а не на корень проекта. Если хостинг не позволяет — сменить хостинг лучше,
чем городить `.htaccess`-костыли (иначе `.env` и `storage/` торчат в веб).

### 6. Права

```bash
chmod -R ug+w storage bootstrap/cache
```

### 7. Крон (планировщик + очередь)

Одна строка в кроне хостинга:

```
* * * * * cd /path/to/urban-parks && php artisan schedule:run >> /dev/null 2>&1
```

Если очередь понадобится по-настоящему (email-уведомления, фоновый парсинг) —
вместо `QUEUE_CONNECTION=sync` поставить `database` и добавить второй крон:

```
* * * * * cd /path/to/urban-parks && php artisan queue:work --stop-when-empty --max-time=50 >> /dev/null 2>&1
```

### Обновление версии (шаред)

```bash
php artisan down
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

(+ залить свежий `public/build`, если менялся фронт)

---

## Вариант Б: VPS с Docker

Тот же `docker-compose.yml`, но:

1. `.env`: `APP_ENV=production`, `APP_DEBUG=false`, нормальные пароли БД,
   `APP_URL` с доменом.
2. Сервис `node` на проде не нужен — фронт собирается один раз:
   `docker compose run --rm node npm ci && docker compose run --rm node npm run build`,
   после чего контейнер `node` можно не поднимать:
   `docker compose up -d app nginx mysql queue`.
3. Перед nginx-контейнером ставится реверс-прокси с HTTPS (Caddy / Traefik /
   системный nginx + certbot) — наш контейнер слушает только 80 внутри.
4. Порт MySQL наружу не публиковать (убрать `ports` у mysql или закрыть фаерволом).
5. Бэкапы: том `mysql-data` + `storage/app` (там ADIF и фото-пруфы) — в крон
   через `mysqldump` и rsync/restic.

---

## Чеклист безопасности перед запуском

- [ ] `APP_DEBUG=false`, `APP_ENV=production`
- [ ] `.env` не доступен по HTTP (document root = `public/`)
- [ ] Пароль БД не `secret`, root-доступ закрыт снаружи
- [ ] HTTPS включён, HTTP редиректится
- [ ] `SESSION_SECURE_COOKIE=true` (куки только по HTTPS)
- [ ] Почта настроена (Resend), `MAIL_FROM_ADDRESS` на верифицированном домене — иначе подтверждение email не работает
- [ ] `php artisan config:cache` выполнен (env-файл не читается на каждый запрос)
- [ ] Сидеры с тестовыми активациями (`ActivationSeeder`) НЕ запущены на проде
- [ ] Админ-пользователь создан с нормальным паролем (или назначен через админку → Пользователи)
- [ ] Бэкап БД настроен
