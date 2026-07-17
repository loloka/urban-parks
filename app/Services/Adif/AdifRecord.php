<?php

namespace App\Services\Adif;

/**
 * Одна распарсенная запись (QSO) из ADIF-файла.
 * Все значения уже нормализованы: даты в Y-m-d, время в H:i:s,
 * позывные/диапазоны/моды в верхнем регистре.
 */
final class AdifRecord
{
    public function __construct(
        public readonly string $call,
        public readonly string $qsoDate,
        public readonly string $timeOn,
        public readonly string $band,
        public readonly string $mode,
        public readonly ?string $submode = null,
        public readonly ?float $freq = null,
        public readonly ?string $rstSent = null,
        public readonly ?string $rstRcvd = null,
        public readonly ?string $stationCallsign = null,
        public readonly ?string $mySig = null,
        public readonly ?string $mySigInfo = null,
        public readonly ?string $sig = null,
        public readonly ?string $sigInfo = null,
    ) {}

    /**
     * Данные для массовой вставки в таблицу qsos.
     */
    public function toDatabaseRow(): array
    {
        return [
            'callsign' => $this->call,
            'station_callsign' => $this->stationCallsign,
            'qso_date' => $this->qsoDate,
            'time_on' => $this->timeOn,
            'band' => $this->band,
            'mode' => $this->mode,
            'submode' => $this->submode,
            'freq' => $this->freq,
            'rst_sent' => $this->rstSent,
            'rst_rcvd' => $this->rstRcvd,
            'sig_info' => $this->sigInfo,
        ];
    }
}
