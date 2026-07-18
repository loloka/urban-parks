<?php

namespace App\Services;

use App\Models\Activation;
use App\Models\ActivationProof;
use App\Models\Park;
use App\Models\Qso;
use App\Services\Adif\AdifParseException;
use App\Services\Adif\AdifParser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Импорт активации из ADIF-лога: парсинг, сохранение файлов на private-диск,
 * транзакционная запись активации + QSO + пруфов.
 *
 * Итог возвращается массивом:
 *   activation — созданная модель,
 *   imported   — сколько QSO записано,
 *   skipped    — сколько записей отбросил парсер,
 *   warnings   — человекочитаемые предупреждения (кросс-чек парка, битые записи...)
 */
class ActivationImporter
{
    private const DISK = 'local'; // storage/app/private

    public function __construct(private readonly AdifParser $parser) {}

    /**
     * @param array $data валидированные поля формы (park_id, callsign, notes)
     * @param UploadedFile $adif лог .adi
     * @param UploadedFile $screenshot скриншот QTHnow (обязательный пруф)
     * @param UploadedFile[] $photos опциональные фото
     *
     * @throws AdifParseException если лог не разобрался или в нём нет валидных QSO
     */
    public function import(array $data, UploadedFile $adif, UploadedFile $screenshot, array $photos = [], ?int $userId = null): array
    {
        $park = Park::findOrFail($data['park_id']);
        $callsign = strtoupper($data['callsign']);

        // 1. Парсим лог ДО сохранения чего-либо
        $result = $this->parser->parseFile($adif->getRealPath());
        $records = $result->filterByProgram('UPTA', strict: false);

        if ($records === []) {
            throw new AdifParseException('В файле нет валидных QSO. Проверьте, что это ADIF-лог с полями CALL, QSO_DATE, TIME_ON, BAND, MODE.');
        }

        $warnings = $result->warnings;

        // 2. Кросс-чек: MY_SIG_INFO в логе против выбранного парка (теги опциональны!)
        $refsInLog = $result->parkReferences();
        if ($refsInLog !== [] && !in_array($park->reference, $refsInLog, true)) {
            $warnings[] = 'В логе указан парк ' . implode(', ', $refsInLog)
                . ', а выбран ' . $park->reference . ' — модератор обратит на это внимание.';
        }

        // 3. Дата активации — самая частая дата в логе; несколько дат = предупреждение
        $dates = array_count_values(array_map(fn ($r) => $r->qsoDate, $records));
        arsort($dates);
        $activationDate = array_key_first($dates);

        if (count($dates) > 1) {
            $warnings[] = 'В логе несколько дат (' . implode(', ', array_keys($dates))
                . ') — правило «одна активация = один день». Взята дата ' . $activationDate . '.';
        }

        // 4. Защита от дублей: та же связка парк+позывной+дата уже загружена
        $duplicate = Activation::where('park_id', $park->id)
            ->where('callsign', $callsign)
            ->whereDate('activation_date', $activationDate)
            ->exists();

        if ($duplicate) {
            throw new AdifParseException(
                "Активация {$callsign} в парке {$park->reference} за {$activationDate} уже загружена."
            );
        }

        // 5. Сохраняем файлы (вне транзакции; при ошибке БД подчистим)
        $dir = 'activations/' . now()->format('Y/m');
        $storedPaths = [];

        $adifPath = $adif->store("{$dir}/adif", self::DISK);
        $storedPaths[] = $adifPath;

        $proofFiles = [
            ['file' => $screenshot, 'type' => ActivationProof::TYPE_SCREENSHOT],
            ...array_map(fn ($p) => ['file' => $p, 'type' => ActivationProof::TYPE_PHOTO], $photos),
        ];

        try {
            return DB::transaction(function () use (
                $park, $callsign, $data, $records, $result,
                $activationDate, $adifPath, $proofFiles, $dir, &$storedPaths, $warnings, $userId
            ) {
                $activation = Activation::create([
                    'park_id' => $park->id,
                    'user_id' => $userId,
                    'callsign' => $callsign,
                    'activation_date' => $activationDate,
                    'qso_count' => count($records),
                    'notes' => $data['notes'] ?? null,
                    'status' => Activation::STATUS_PENDING,
                    'adif_path' => $adifPath,
                    'source' => Activation::SOURCE_ADIF,
                ]);

                // QSO пачками; дубли внутри лога молча гасятся уникальным индексом
                $now = now();
                $rows = array_map(
                    fn ($r) => $r->toDatabaseRow() + [
                        'activation_id' => $activation->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                    $records
                );

                foreach (array_chunk($rows, 500) as $chunk) {
                    Qso::insertOrIgnore($chunk);
                }

                foreach ($proofFiles as $proof) {
                    $path = $proof['file']->store("{$dir}/proofs", self::DISK);
                    $storedPaths[] = $path;

                    ActivationProof::create([
                        'activation_id' => $activation->id,
                        'type' => $proof['type'],
                        'path' => $path,
                        'original_name' => $proof['file']->getClientOriginalName(),
                        'size' => $proof['file']->getSize(),
                    ]);
                }

                return [
                    'activation' => $activation,
                    'imported' => Qso::where('activation_id', $activation->id)->count(),
                    'skipped' => $result->skipped,
                    'warnings' => $warnings,
                ];
            });
        } catch (\Throwable $e) {
            // Транзакция откатилась — файлы-сироты удаляем
            Storage::disk(self::DISK)->delete($storedPaths);

            throw $e;
        }
    }
}
