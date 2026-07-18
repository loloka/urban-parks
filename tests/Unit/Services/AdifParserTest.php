<?php

namespace Tests\Unit\Services;

use App\Services\Adif\AdifParseException;
use App\Services\Adif\AdifParser;
use PHPUnit\Framework\TestCase;

class AdifParserTest extends TestCase
{
    private AdifParser $parser;

    protected function setUp(): void
    {
        $this->parser = new AdifParser();
    }

    private function fixture(): string
    {
        return file_get_contents(__DIR__ . '/../../fixtures/sample.adi');
    }

    public function test_parses_valid_records_and_skips_invalid(): void
    {
        $result = $this->parser->parse($this->fixture());

        // 6 записей в файле: 4 валидных, 1 битый позывной, 1 битая дата
        $this->assertSame(4, $result->count());
        $this->assertSame(2, $result->skipped);
        $this->assertNotEmpty($result->warnings);
    }

    public function test_normalizes_fields(): void
    {
        $result = $this->parser->parse($this->fixture());
        $first = $result->records[0];

        $this->assertSame('UA9OV', $first->call);
        $this->assertSame('2026-07-15', $first->qsoDate);
        $this->assertSame('08:30:00', $first->timeOn);
        $this->assertSame('40M', $first->band);
        $this->assertSame('SSB', $first->mode);
        $this->assertSame(7.093, $first->freq);
        $this->assertSame('R9OGL', $first->stationCallsign);
        $this->assertSame('UPTA', $first->mySig);
        $this->assertSame('UP-RU-NSK-0001', $first->mySigInfo);
    }

    public function test_parses_six_digit_time(): void
    {
        $result = $this->parser->parse($this->fixture());

        $this->assertSame('08:35:12', $result->records[1]->timeOn);
    }

    public function test_park_to_park_sig_info(): void
    {
        $result = $this->parser->parse($this->fixture());
        $ft8 = $result->records[2];

        $this->assertSame('FT8', $ft8->mode);
        $this->assertSame('MFSK', $ft8->submode);
        $this->assertSame('UP-RU-MSK-0002', $ft8->sigInfo);
    }

    public function test_last_record_without_eor_is_parsed(): void
    {
        $result = $this->parser->parse($this->fixture());
        // end() требует ссылку — с readonly-свойством напрямую нельзя
        $records = $result->records;
        $last = end($records);

        $this->assertSame('UA9OTW/P', $last->call);
    }

    public function test_collects_park_references(): void
    {
        $result = $this->parser->parse($this->fixture());

        $this->assertSame(['UP-RU-NSK-0001'], $result->parkReferences());
    }

    public function test_filter_by_program(): void
    {
        $result = $this->parser->parse($this->fixture());

        // strict: только записи с MY_SIG=UPTA
        $this->assertCount(3, $result->filterByProgram('UPTA', strict: true));
        // non-strict: записи без MY_SIG тоже проходят
        $this->assertCount(4, $result->filterByProgram('UPTA'));
    }

    public function test_rejects_non_adif_content(): void
    {
        $this->expectException(AdifParseException::class);

        $this->parser->parse('просто текст, никакого ADIF здесь нет');
    }

    public function test_rejects_oversized_content(): void
    {
        $this->expectException(AdifParseException::class);

        $this->parser->parse(str_repeat('x', AdifParser::MAX_FILE_SIZE + 1));
    }

    public function test_converts_windows_1251(): void
    {
        $adif = "<EOH><CALL:4>R0AA <QSO_DATE:8>20260101 <TIME_ON:4>1200 "
            . "<BAND:3>20M <MODE:3>SSB <EOR>";
        // Симулируем cp1251-файл: добавим кириллический комментарий в 1251
        $cp1251 = iconv('UTF-8', 'Windows-1251', "Лог активации\n") . $adif;

        $result = $this->parser->parse($cp1251);

        $this->assertSame(1, $result->count());
        $this->assertSame('R0AA', $result->records[0]->call);
    }

    public function test_database_row_shape(): void
    {
        $result = $this->parser->parse($this->fixture());
        $row = $result->records[0]->toDatabaseRow();

        $this->assertSame('UA9OV', $row['callsign']);
        $this->assertSame('2026-07-15', $row['qso_date']);
        $this->assertArrayHasKey('band', $row);
        $this->assertArrayHasKey('sig_info', $row);
    }
}
