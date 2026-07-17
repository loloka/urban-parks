# Архитектура UPTA (Urban Parks Award)

Технический документ по дипломной программе «Городские парки»: схема данных, конвейер загрузки ADIF-логов, модерация активаций и перспективный автозачёт охотников через API СРР.

Статус реализации на 17.07.2026: ✅ — уже в коде, 🔜 — спроектировано, реализуется следующим шагом.

---

## 1. Роли и глоссарий

| Термин | Значение |
|---|---|
| **Активатор** | Радиолюбитель, работающий из парка. Загружает ADIF-лог + пруфы |
| **Охотник (hunter)** | Работает из дома, проводит QSO с активатором |
| **QSO** | Одна радиосвязь: позывной + дата + время + диапазон + мода |
| **Активация** | Один выезд активатора в один парк (≥ N QSO по регламенту, обычно 10) |
| **Референс** | Номер парка: `UP-RU-NSK-0001` (страна, регион, порядковый номер) |
| **MY_SIG / MY_SIG_INFO** | ADIF-теги: программа (`UPTA`) и референс парка активатора |
| **SIG / SIG_INFO** | То же про корреспондента — основа зачёта park-to-park |

> **Решение:** стандартизируем значение `MY_SIG` = `UPTA`. Старый экспорт писал `URBAN_PARK` — парсер принимает оба, но генерируем всегда `UPTA` (короче, уникальнее, в стиле POTA/WWFF).

---

## 2. Структура базы данных

### 2.1. Схема (MySQL 8, InnoDB, utf8mb4)

```
users ──< activations >── parks
              │
              ├──< qsos            (связи из ADIF-лога)
              └──< activation_proofs (фото/скриншоты/GPX)

awards ──< user_awards >── users     (🔜 дипломы)
```

### 2.2. Таблицы

**parks** ✅ (реализована)

```sql
CREATE TABLE parks (
    id            BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    reference     VARCHAR(20) NOT NULL UNIQUE,     -- UP-RU-NSK-0001
    country_code  VARCHAR(5)  NOT NULL DEFAULT 'RU',
    region_code   VARCHAR(10) NULL,                -- NSK, MSK, SPB...
    name          VARCHAR(255) NOT NULL,
    name_en       VARCHAR(255) NULL,
    city          VARCHAR(255) NOT NULL,
    region        VARCHAR(255) NOT NULL,
    latitude      DECIMAL(10,7) NOT NULL,
    longitude     DECIMAL(10,7) NOT NULL,
    description   TEXT NULL,
    description_en TEXT NULL,
    area          VARCHAR(255) NULL,
    status        ENUM('active','pending','inactive') DEFAULT 'active',
    activation_count INT DEFAULT 0,                -- денормализованный счётчик
    created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
    INDEX (city), INDEX (region), INDEX (status)
);
```

**activations** ✅ + 🔜 поля загрузки (миграция `2026_07_17_000002` готова)

```sql
CREATE TABLE activations (
    id            BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    park_id       BIGINT UNSIGNED NOT NULL,        -- FK parks, CASCADE
    user_id       BIGINT UNSIGNED NULL,            -- FK users, SET NULL (владелец лога)
    status        ENUM('pending','approved','rejected') DEFAULT 'pending',
    moderator_note TEXT NULL,
    callsign      VARCHAR(20) NOT NULL,            -- позывной активатора
    activation_date DATE NOT NULL,
    qso_count     INT DEFAULT 0,
    notes         TEXT NULL,
    adif_path     VARCHAR(255) NULL,               -- исходный .adi в private storage
    source        VARCHAR(10) DEFAULT 'manual',    -- manual | adif
    created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,
    INDEX (callsign), INDEX (activation_date)
);
```

**qsos** 🔜 (миграция `2026_07_17_000001` готова) — ядро всей сверки

```sql
CREATE TABLE qsos (
    id               BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    activation_id    BIGINT UNSIGNED NOT NULL,     -- FK activations, CASCADE
    callsign         VARCHAR(20) NOT NULL,         -- позывной ОХОТНИКА
    station_callsign VARCHAR(20) NULL,             -- позывной активатора из лога
    qso_date         DATE NOT NULL,                -- UTC!
    time_on          TIME NOT NULL,                -- UTC!
    band             VARCHAR(10) NOT NULL,         -- 40M, 20M...
    mode             VARCHAR(15) NOT NULL,         -- SSB, CW, FT8...
    submode          VARCHAR(15) NULL,
    freq             DECIMAL(10,4) NULL,           -- МГц
    rst_sent         VARCHAR(8) NULL,
    rst_rcvd         VARCHAR(8) NULL,
    sig_info         VARCHAR(20) NULL,             -- парк корреспондента (P2P)
    created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL,

    -- защита от дублей при повторной загрузке того же лога
    UNIQUE KEY qsos_dedupe_unique (activation_id, callsign, qso_date, time_on, band, mode),

    -- ГЛАВНЫЙ индекс автозачёта: «найди все QSO охотника R0AA за дату X»
    KEY qsos_hunter_match_idx (callsign, qso_date)
);
```

Почему индекс `(callsign, qso_date)` — правильный: запрос сверки всегда имеет вид «позывной = ? AND дата = ?» (равенство + равенство), составной индекс закрывает его целиком. Диапазон/моду фильтруем уже по узкой выборке (у одного охотника за день десятки QSO, не миллионы).

**activation_proofs** 🔜 (миграция `2026_07_17_000003` готова)

```sql
CREATE TABLE activation_proofs (
    id            BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    activation_id BIGINT UNSIGNED NOT NULL,        -- FK, CASCADE
    type          VARCHAR(20) DEFAULT 'photo',     -- photo | screenshot | gpx
    path          VARCHAR(255) NOT NULL,           -- private-диск, НЕ public
    original_name VARCHAR(255) NULL,
    size          INT UNSIGNED NULL,
    created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL
);
```

**users** — расширение 🔜 (следующий шаг, когда появится регистрация активаторов)

```sql
ALTER TABLE users
    ADD callsign VARCHAR(20) NULL UNIQUE,          -- основной позывной
    ADD role ENUM('user','moderator','admin') DEFAULT 'user',
    ADD srr_token_encrypted TEXT NULL;             -- Bearer-токен СРР, шифровать Crypt::encryptString()
```

Важно: у пользователя может быть несколько позывных за карьеру (смена категории, спецпозывные). На перспективу — таблица `user_callsigns (user_id, callsign, valid_from, valid_to)`; зачёт охотнику вести по объединению его позывных.

**awards / user_awards** 🔜 (v1.3+)

```sql
CREATE TABLE awards (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    slug VARCHAR(50) UNIQUE,          -- hunter-10, activator-5...
    title VARCHAR(255), rules JSON    -- {"type":"hunter","parks":10}
);
CREATE TABLE user_awards (
    user_id BIGINT UNSIGNED, award_id BIGINT UNSIGNED,
    granted_at TIMESTAMP, certificate_path VARCHAR(255) NULL,
    PRIMARY KEY (user_id, award_id)
);
```

### 2.3. Ключевые решения

1. **Всё время — UTC.** ADIF по стандарту в UTC; в БД храним как есть, конвертируем только при отображении. Никаких локальных часовых поясов в `qsos`.
2. **QSO охотника не дублируем.** Факт «охотник сработал парк» — это запрос к `qsos`, а не отдельная таблица. Материализуем только когда станет медленно (см. §5.4).
3. **ENUM в migration-файлах заменяем на VARCHAR + константы модели** для новых таблиц: добавление статуса в ENUM в MySQL — это ALTER TABLE с блокировкой. Существующие ENUM (`activations.status`) пока не трогаем.
4. **Файлы (ADIF, фото) — только на private-диске** (`storage/app/private`), отдача через подписанные URL / контроллер с авторизацией. В public — ничего пользовательского.

---

## 3. Конвейер загрузки ADIF ✅ (парсер реализован)

### 3.1. Компоненты

```
UploadForm → StoreActivationLogRequest (валидация)
          → AdifParser (app/Services/Adif/) ✅
          → ActivationImporter (транзакция: activation + qsos + proofs) 🔜
          → очередь: нотификация модератору 🔜
```

`AdifParser` (см. `app/Services/Adif/AdifParser.php`, тесты в `tests/Unit/Services/AdifParserTest.php`):

- лимиты: 15 МБ, 20 000 записей — защита от DoS текстом;
- чтение значений строго по заявленной длине тега (byte-oriented, как в стандарте ADIF);
- обязательные поля `CALL, QSO_DATE, TIME_ON, BAND, MODE`; битые записи не валят весь файл, а копятся в `warnings` (первые 50) + счётчик `skipped`;
- нормализация: даты `YYYYMMDD → Y-m-d` с `checkdate()`, время `HHMM[SS] → H:i:s`, позывные/банды/моды в верхний регистр, валидация по regex и справочнику диапазонов;
- `MY_SIG`/`MY_SIG_INFO`/`SIG`/`SIG_INFO` — извлекаются, референсы парков проверяются по маске `UP[-CC][-REG]-NNNN`;
- автоконвертация Windows-1251 → UTF-8 (русские логгеры типа UR5EQF);
- фреймворк-независимый — переиспользуется в SRR-синке (§5).

### 3.2. Валидация загрузки (Form Request) 🔜

```php
public function rules(): array
{
    return [
        'park_id' => ['required', 'exists:parks,id'],
        'adif'    => ['required', 'file', 'max:15360',           // 15 МБ
                      'mimes:adi,adif,txt'],                     // + проверка содержимого парсером!
        'photos'  => ['required', 'array', 'min:2', 'max:3'],    // 2–3 фото обязательны
        'photos.*'=> ['image', 'mimes:jpeg,png,webp', 'max:10240'],
        'gpx'     => ['nullable', 'file', 'mimes:gpx,xml', 'max:2048'],
    ];
}
```

MIME-типу доверять нельзя — настоящая проверка «это ADIF» происходит в парсере (файл без единого валидного тега отбрасывается с `AdifParseException`). Файлы сохраняем с генерированными именами (`Str::uuid()`), оригинальное имя — только в БД.

### 3.3. Импорт (транзакция) 🔜

```php
DB::transaction(function () use ($user, $park, $result, $paths) {
    $activation = Activation::create([
        'park_id' => $park->id,
        'user_id' => $user->id,
        'callsign' => $callsign,               // из STATION_CALLSIGN или профиля
        'activation_date' => $date,            // мин. дата из лога
        'qso_count' => $result->count(),
        'status' => Activation::STATUS_PENDING,
        'adif_path' => $paths['adif'],
        'source' => Activation::SOURCE_ADIF,
    ]);

    $rows = array_map(
        fn ($r) => $r->toDatabaseRow() + ['activation_id' => $activation->id,
                                          'created_at' => now(), 'updated_at' => now()],
        $result->records
    );
    foreach (array_chunk($rows, 500) as $chunk) {
        Qso::insertOrIgnore($chunk);           // уникальный индекс сам съест дубли
    }
    // + activation_proofs для фото/GPX
});
```

Санитарные проверки перед сохранением: все `MY_SIG_INFO` в логе относятся к одному парку и совпадают с выбранным `park_id` (расхождение — предупреждение активатору); одна активация = один парк = один день (лог с несколькими датами разбиваем или просим разбить).

---

## 4. Модерация: проверка лога за 10 секунд

### 4.1. Принцип

Модератор не должен ничего искать — всё, что нужно для решения, собрано на одном экране в порядке принятия решения: **кто → где → чем доказано → что в логе → кнопки**.

### 4.2. Макет экрана (Filament ViewActivation)

```
┌──────────────────────────────────────────────────────────────┐
│ R9OGL → UP-RU-NSK-0001 «Центральный парк» · 15.07.2026       │
│ 47 QSO · 40M/20M · SSB+CW+FT8 · лог загружен 15.07 18:02     │
│ ⚑ Автопроверки: ✅ парк совпадает ✅ дата одна ✅ ≥10 QSO       │
│                 ⚠ 2 записи отброшены парсером                 │
├───────────────────────────┬──────────────────────────────────┤
│  ФОТО-ПРУФЫ (лайтбокс)    │  МИНИ-КАРТА                      │
│  [фото1] [фото2] [фото3]  │  ● маркер парка                  │
│                           │  ▬ GPX-трек поверх (если есть)   │
├───────────────────────────┴──────────────────────────────────┤
│  ЛОГ (свёрнут, раскрывается): таблица QSO с графиком         │
│  распределения по времени (гистограмма 15-мин интервалов)    │
├──────────────────────────────────────────────────────────────┤
│  [✅ Одобрить]  [❌ Отклонить + причина]  [💬 Запросить фото]  │
└──────────────────────────────────────────────────────────────┘
```

### 4.3. Автопроверки (бейджи, считаются при импорте)

Смысл: 90% решений модератор принимает по бейджам, не открывая лог.

| Проверка | Зелёный | Красный флаг |
|---|---|---|
| Референс | `MY_SIG_INFO` = выбранный парк | расхождение |
| Дата | один день | несколько дат в логе |
| Минимум QSO | ≥ 10 | меньше порога |
| Темп | ≤ 6 QSO/мин пиково | 47 QSO за 3 минуты — накрутка |
| Дубли | нет | тот же лог уже загружали (сравнение множества QSO) |
| Время суток | правдоподобно | вся активация 03:00–04:00 UTC без комментария |
| GPX (если есть) | трек внутри полигона парка | трек в 40 км от парка |
| История | активатор с одобренными логами | первый лог новичка → смотреть внимательнее |

Реализация в Filament: `ViewRecord`-страница с `Infolist` (секции из §4.2), фото через `SpatieMediaLibrary`-подобный лайтбокс или простую `ImageEntry` с модалкой, действия `Approve`/`Reject` как `Action` с обязательным `moderator_note` при отказе. Список логов на модерации сортируем «сначала с красными флагами».

Отклонение — всегда с причиной из справочника (нет фото / фото не из парка / лог битый / дубль / другое) + свободный текст. Причина уходит активатору письмом.

---

## 5. Автозачёт охотников через API СРР (перспектива)

### 5.1. Идея

Охотнику не нужно загружать логи: он один раз привязывает аккаунт СРР (award.srr.ru), дальше жмёт «Синхронизировать» (или крон делает это сам), система забирает его ADIF через `GET /api/v1/qso/export` и сверяет с QSO активаторов в нашей базе. Матч = зачтённый парк.

### 5.2. Хранение токена

- Пользователь вставляет Bearer-токен СРР в личном кабинете.
- Храним только шифрованным: `Crypt::encryptString()` → `users.srr_token_encrypted`.
- Токен никогда не попадает в логи/ответы API. При 401 от СРР — помечаем токен протухшим и просим обновить.

### 5.3. Алгоритм синка

```
1. Забрать ADIF охотника из СРР (Http::withToken()->timeout(30)->get(...)).
   Ограничение: не чаще 1 раза в час на пользователя (rate limit + кэш).
2. Прогнать через тот же AdifParser (лимиты защищают и здесь).
3. Для каждой записи охотника H искать подтверждающее QSO активатора:

   SELECT q.id, a.park_id, q.time_on
   FROM qsos q
   JOIN activations a ON a.id = q.activation_id AND a.status = 'approved'
   WHERE q.callsign  = :hunter_call        -- активатор записал охотника
     AND q.qso_date  = :h_date
     AND q.band      = :h_band
     AND ABS(TIME_TO_SEC(TIMEDIFF(q.time_on, :h_time))) <= 600   -- ±10 минут
     AND (q.station_callsign = :h_worked_call OR q.station_callsign IS NULL)
   LIMIT 1;

   -- :h_worked_call — это поле CALL из лога охотника (кого он сработал)
4. Сверка моды — по группам, не строго: SSB≈USB≈LSB≈PHONE, CW, DIGI (FT8/FT4/RTTY/PSK…).
   Часы у людей врут, моды пишут по-разному — жёсткое равенство даёт ложные отказы.
5. Матч найден → фиксируем зачёт (кэш-таблица hunter_credits, см. 5.4):
   (hunter_call, park_id, qso_id, credited_at) с UNIQUE(hunter_call, park_id, qso_id).
6. Результат пользователю: «Найдено 12 связей, новых парков: 3» + список.
```

Крайние случаи: QSO около полуночи UTC (сравнивать `TIMESTAMP(qso_date, time_on)`, а не дату и время раздельно — иначе связь в 23:58/00:03 не сматчится); позывные с дробью (`R0AA/M` vs `R0AA` — матчим по базовому позывному, дробь игнорируем); дубликаты у обеих сторон (UNIQUE-ключ решает).

### 5.4. Кэш зачёта

Пока охотников мало — живой запрос к `qsos` быстрый (индекс §2.2). При росте вводим материализованную таблицу:

```sql
CREATE TABLE hunter_credits (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    hunter_callsign VARCHAR(20) NOT NULL,
    park_id BIGINT UNSIGNED NOT NULL,
    qso_id  BIGINT UNSIGNED NOT NULL,      -- доказательство
    source  VARCHAR(10) DEFAULT 'srr',     -- srr | manual
    credited_at TIMESTAMP,
    UNIQUE KEY (hunter_callsign, park_id, qso_id),
    KEY (hunter_callsign), KEY (park_id)
);
```

Пополняется при (а) синке охотника и (б) одобрении новой активации (джоб перепроверяет свежие QSO против уже привязанных охотников). Прогресс дипломов считается по `COUNT(DISTINCT park_id)` из этой таблицы — мгновенно.

### 5.5. Отказоустойчивость

- Синк — это queued job (`QUEUE_CONNECTION=database` уже настроен, воркер в docker-compose есть): API СРР может тормозить, пользователь не должен ждать.
- Ретраи с backoff, circuit breaker при серии 5xx от СРР.
- Все внешние вызовы — через `Http::` фасад с таймаутами, никаких `file_get_contents(url)`.

---

## 6. Безопасность — сводка

1. **SQL-инъекции**: только Eloquent/Query Builder с плейсхолдерами. В поиске экранировать `%`/`_` в LIKE-паттернах.
2. **Загрузка файлов**: лимиты размера (nginx `client_max_body_size 64M` + PHP + FormRequest), белый список расширений, содержимое проверяет парсер, имена файлов — UUID, хранение на private-диске, отдача через авторизованный контроллер.
3. **Публичные формы**: rate limiting (`throttle:`), honeypot/captcha на загрузку без авторизации; в перспективе загрузка логов — только для зарегистрированных.
4. **Токены СРР**: шифрование при хранении, маскирование в UI, не логировать.
5. **Массовое присваивание**: `$fillable` уже везде; `status` никогда не принимать из пользовательского запроса (форсить `pending` на сервере — уже сделано в `ActivationController@store`).
6. **XSS**: Blade экранирует по умолчанию — не использовать `{!! !!}` для пользовательских данных (описания парков из админки — ок).

---

## 7. Дорожная карта реализации

1. ✅ Docker-окружение, парсер ADIF, миграции `qsos`/`activation_proofs`, модели.
2. 🔜 Регистрация/авторизация активаторов (Breeze или Filament-панель `user`), привязка `callsign`.
3. 🔜 Форма загрузки лога (FormRequest §3.2 + ActivationImporter §3.3).
4. 🔜 Экран модерации §4 в Filament + автопроверки.
5. 🔜 Публичная статистика охотников (по `qsos`), страница «мои зачтённые парки».
6. 🔜 Дипломы (`awards`), PDF-сертификаты.
7. 🔜 Синк с СРР §5 (после договорённости с СРР о токенах для пользователей).
