<?php

namespace App\Services\Adif;

/**
 * Результат разбора ADIF-файла: валидные записи + предупреждения.
 */
final class AdifParseResult
{
    /**
     * @param AdifRecord[] $records
     * @param string[] $warnings человекочитаемые причины пропуска записей
     * @param int $skipped количество пропущенных (невалидных) записей
     */
    public function __construct(
        public readonly array $records,
        public readonly array $warnings = [],
        public readonly int $skipped = 0,
    ) {}

    public function count(): int
    {
        return count($this->records);
    }

    public function isEmpty(): bool
    {
        return $this->records === [];
    }

    /**
     * Уникальные референсы парков из MY_SIG_INFO (обычно один на лог).
     *
     * @return string[]
     */
    public function parkReferences(): array
    {
        $refs = [];

        foreach ($this->records as $record) {
            if ($record->mySigInfo !== null) {
                $refs[$record->mySigInfo] = true;
            }
        }

        return array_keys($refs);
    }

    /**
     * Только записи, помеченные нужной программой (MY_SIG).
     * Записи без MY_SIG тоже включаются, если $strict = false —
     * многие логгеры не пишут MY_SIG, парк указывается при загрузке.
     */
    public function filterByProgram(string $program = 'UPTA', bool $strict = false): array
    {
        return array_values(array_filter(
            $this->records,
            static function (AdifRecord $record) use ($program, $strict): bool {
                if ($record->mySig === null) {
                    return !$strict;
                }

                return strcasecmp($record->mySig, $program) === 0;
            }
        ));
    }
}
