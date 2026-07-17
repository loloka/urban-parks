<?php

namespace App\Services\Adif;

/**
 * Парсер ADIF-файлов (.adi) — ADIF 3.x.
 *
 * Формат: <ИМЯ_ПОЛЯ:ДЛИНА[:ТИП]>значение ... <EOR>
 * Заголовок файла заканчивается тегом <EOH>.
 *
 * Парсер сознательно не зависит от Laravel — его легко тестировать
 * и переиспользовать (например, в консольной команде синка с СРР).
 *
 * Безопасность:
 *  - лимит на размер файла и число записей (защита от zip-бомб текстом);
 *  - значения читаются строго по заявленной длине (byte-oriented, как велит стандарт);
 *  - все данные нормализуются и валидируются до попадания в БД;
 *  - вставка в БД — только через Eloquent/Query Builder (prepared statements).
 */
final class AdifParser
{
    /** Максимальный размер файла: 15 МБ (~50 000 QSO с запасом) */
    public const MAX_FILE_SIZE = 15 * 1024 * 1024;

    /** Максимум записей в одном файле */
    public const MAX_RECORDS = 20000;

    /** Допустимые радиолюбительские диапазоны (ADIF Band enumeration, основные) */
    private const VALID_BANDS = [
        '2190M', '630M', '160M', '80M', '60M', '40M', '30M', '20M', '17M',
        '15M', '12M', '10M', '8M', '6M', '5M', '4M', '2M', '1.25M',
        '70CM', '33CM', '23CM', '13CM', '9CM', '6CM', '3CM',
    ];

    /**
     * Разобрать файл с диска.
     *
     * @throws AdifParseException если файл не читается, слишком большой или пустой
     */
    public function parseFile(string $path): AdifParseResult
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new AdifParseException('Файл не найден или недоступен для чтения.');
        }

        if (filesize($path) > self::MAX_FILE_SIZE) {
            throw new AdifParseException('Файл слишком большой (максимум 15 МБ).');
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new AdifParseException('Не удалось прочитать файл.');
        }

        return $this->parse($content);
    }

    /**
     * Разобрать содержимое ADIF.
     *
     * @throws AdifParseException если в файле нет ни одного ADIF-тега
     */
    public function parse(string $content): AdifParseResult
    {
        if (strlen($content) > self::MAX_FILE_SIZE) {
            throw new AdifParseException('Файл слишком большой (максимум 15 МБ).');
        }

        $content = $this->normalizeEncoding($content);
        $body = $this->stripHeader($content);

        if (!str_contains($body, '<')) {
            throw new AdifParseException('Файл не похож на ADIF: не найдено ни одного тега.');
        }

        $records = [];
        $warnings = [];
        $skipped = 0;
        $recordNumber = 0;

        foreach ($this->splitRecords($body) as $rawFields) {
            $recordNumber++;

            if ($recordNumber > self::MAX_RECORDS) {
                $warnings[] = 'Достигнут лимит в ' . self::MAX_RECORDS . ' записей — остаток файла пропущен.';
                break;
            }

            try {
                $records[] = $this->buildRecord($rawFields);
            } catch (AdifParseException $e) {
                $skipped++;
                // Не копим тысячи одинаковых предупреждений
                if (count($warnings) < 50) {
                    $warnings[] = "Запись #{$recordNumber} пропущена: {$e->getMessage()}";
                }
            }
        }

        return new AdifParseResult($records, $warnings, $skipped);
    }

    /**
     * Разбить тело файла на записи (наборы полей), разделённые <EOR>.
     *
     * @return iterable<array<string, string>>
     */
    private function splitRecords(string $body): iterable
    {
        $offset = 0;
        $length = strlen($body);
        $fields = [];

        while ($offset < $length) {
            $open = strpos($body, '<', $offset);

            if ($open === false) {
                break;
            }

            $close = strpos($body, '>', $open);

            if ($close === false) {
                break;
            }

            $tag = substr($body, $open + 1, $close - $open - 1);
            $offset = $close + 1;

            // Конец записи
            if (strcasecmp($tag, 'EOR') === 0) {
                if ($fields !== []) {
                    yield $fields;
                    $fields = [];
                }
                continue;
            }

            // Поле вида ИМЯ:ДЛИНА или ИМЯ:ДЛИНА:ТИП
            $parts = explode(':', $tag);

            if (count($parts) < 2 || !ctype_digit($parts[1])) {
                continue; // служебный или битый тег — пропускаем
            }

            $name = strtoupper($parts[0]);
            $valueLength = (int) $parts[1];

            // Читаем значение строго по заявленной длине (в байтах)
            $value = substr($body, $offset, $valueLength);
            $offset += $valueLength;

            $fields[$name] = trim($value);
        }

        // Хвост без <EOR> — некоторые логгеры забывают закрыть последнюю запись
        if ($fields !== []) {
            yield $fields;
        }
    }

    /**
     * Собрать и провалидировать одну запись.
     *
     * @param array<string, string> $f сырые поля записи
     *
     * @throws AdifParseException при отсутствии/невалидности обязательных полей
     */
    private function buildRecord(array $f): AdifRecord
    {
        foreach (['CALL', 'QSO_DATE', 'TIME_ON', 'BAND', 'MODE'] as $required) {
            if (!isset($f[$required]) || $f[$required] === '') {
                throw new AdifParseException("отсутствует обязательное поле {$required}");
            }
        }

        $call = $this->normalizeCallsign($f['CALL']);
        $qsoDate = $this->normalizeDate($f['QSO_DATE']);
        $timeOn = $this->normalizeTime($f['TIME_ON']);
        $band = strtoupper($f['BAND']);
        $mode = strtoupper($f['MODE']);

        if (!in_array($band, self::VALID_BANDS, true)) {
            throw new AdifParseException("неизвестный диапазон «{$band}»");
        }

        if (!preg_match('/^[A-Z0-9\-]{1,15}$/', $mode)) {
            throw new AdifParseException("невалидная мода «{$mode}»");
        }

        $freq = null;
        if (isset($f['FREQ']) && is_numeric($f['FREQ'])) {
            $freq = round((float) $f['FREQ'], 4);
        }

        return new AdifRecord(
            call: $call,
            qsoDate: $qsoDate,
            timeOn: $timeOn,
            band: $band,
            mode: $mode,
            submode: isset($f['SUBMODE']) ? strtoupper(substr($f['SUBMODE'], 0, 15)) : null,
            freq: $freq,
            rstSent: isset($f['RST_SENT']) ? substr($f['RST_SENT'], 0, 8) : null,
            rstRcvd: isset($f['RST_RCVD']) ? substr($f['RST_RCVD'], 0, 8) : null,
            stationCallsign: $this->optionalCallsign($f, 'STATION_CALLSIGN')
                ?? $this->optionalCallsign($f, 'OPERATOR'),
            mySig: isset($f['MY_SIG']) ? strtoupper(trim($f['MY_SIG'])) : null,
            mySigInfo: $this->normalizeParkReference($f['MY_SIG_INFO'] ?? null),
            sig: isset($f['SIG']) ? strtoupper(trim($f['SIG'])) : null,
            sigInfo: $this->normalizeParkReference($f['SIG_INFO'] ?? null),
        );
    }

    /**
     * Позывной: A-Z, 0-9, дробь. Например R9OGL, UA9OTW/P, R0AA/M.
     */
    private function normalizeCallsign(string $raw): string
    {
        $call = strtoupper(trim($raw));

        if (!preg_match('/^[A-Z0-9]{3,12}(\/[A-Z0-9]{1,4}){0,2}$/', $call)) {
            throw new AdifParseException("невалидный позывной «{$call}»");
        }

        return $call;
    }

    private function optionalCallsign(array $f, string $key): ?string
    {
        if (!isset($f[$key]) || $f[$key] === '') {
            return null;
        }

        try {
            return $this->normalizeCallsign($f[$key]);
        } catch (AdifParseException) {
            return null; // необязательное поле — молча игнорируем мусор
        }
    }

    /**
     * QSO_DATE: YYYYMMDD → Y-m-d с проверкой календарной корректности.
     */
    private function normalizeDate(string $raw): string
    {
        if (!preg_match('/^(\d{4})(\d{2})(\d{2})$/', trim($raw), $m)) {
            throw new AdifParseException("невалидная дата «{$raw}» (ожидается YYYYMMDD)");
        }

        [, $y, $mo, $d] = $m;

        if (!checkdate((int) $mo, (int) $d, (int) $y) || (int) $y < 1945) {
            throw new AdifParseException("несуществующая дата «{$raw}»");
        }

        return "{$y}-{$mo}-{$d}";
    }

    /**
     * TIME_ON: HHMM или HHMMSS → H:i:s.
     */
    private function normalizeTime(string $raw): string
    {
        $time = trim($raw);

        if (!preg_match('/^(\d{2})(\d{2})(\d{2})?$/', $time, $m)) {
            throw new AdifParseException("невалидное время «{$raw}» (ожидается HHMM или HHMMSS)");
        }

        $h = (int) $m[1];
        $i = (int) $m[2];
        $s = (int) ($m[3] ?? 0);

        if ($h > 23 || $i > 59 || $s > 59) {
            throw new AdifParseException("невалидное время «{$raw}»");
        }

        return sprintf('%02d:%02d:%02d', $h, $i, $s);
    }

    /**
     * Референс парка: UP-0001 или UP-RU-NSK-0001. Мусор превращаем в null.
     */
    private function normalizeParkReference(?string $raw): ?string
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $ref = strtoupper(trim($raw));

        return preg_match('/^UP(-[A-Z]{2})?(-[A-Z]{2,4})?-\d{1,5}$/', $ref) ? $ref : null;
    }

    /**
     * Русскоязычные логгеры нередко сохраняют .adi в Windows-1251.
     * Приводим всё к UTF-8, чтобы кириллица в NAME/QTH не ломала БД.
     */
    private function normalizeEncoding(string $content): string
    {
        if (mb_check_encoding($content, 'UTF-8')) {
            return $content;
        }

        $converted = @iconv('Windows-1251', 'UTF-8//IGNORE', $content);

        return $converted !== false ? $converted : mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
    }

    /**
     * Отрезать заголовок файла (всё до <EOH> включительно).
     */
    private function stripHeader(string $content): string
    {
        if (preg_match('/<EOH>/i', $content, $m, PREG_OFFSET_CAPTURE)) {
            return substr($content, $m[0][1] + strlen($m[0][0]));
        }

        return $content;
    }
}
