<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActivationRequest;
use App\Models\Activation;
use App\Models\ActivationProof;
use App\Models\Park;
use App\Services\ActivationImporter;
use App\Services\Adif\AdifParseException;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivationController extends Controller
{
    /**
     * Форма загрузки лога активации
     */
    public function create()
    {
        $parks = Park::active()->orderBy('reference')->get();

        return view('activations.create', compact('parks'));
    }

    /**
     * Принять ADIF-лог + пруфы, отправить на модерацию
     */
    public function store(StoreActivationRequest $request, ActivationImporter $importer)
    {
        try {
            $result = $importer->import(
                $request->validated(),
                $request->file('adif'),
                $request->file('screenshot'),
                $request->file('photos', [])
            );
        } catch (AdifParseException $e) {
            return back()->withInput()->withErrors(['adif' => $e->getMessage()]);
        }

        $message = "✅ Лог принят: {$result['imported']} QSO из парка "
            . $result['activation']->park->reference
            . '. Активация отправлена на модерацию!';

        if ($result['skipped'] > 0) {
            $message .= " ({$result['skipped']} записей пропущено)";
        }

        return redirect()
            ->route('activations.create')
            ->with('success', $message)
            ->with('warnings', $result['warnings']);
    }

    /**
     * Публичная страница активации: инфо, галерея фото, сводка лога, скачивание ADIF.
     * Гостям видны только одобренные активации; модераторам (auth) — любые (для предпросмотра).
     */
    public function show(Activation $activation)
    {
        abort_unless($this->viewable($activation), 404);

        $activation->load(['park', 'proofs']);

        // Только фото (type=photo) публичны; QTHnow-скриншот и GPX сюда не попадают
        $photos = $activation->proofs->where('type', ActivationProof::TYPE_PHOTO);

        // Сводка лога по сохранённым QSO
        $qsos = $activation->qsos()->get();
        $summary = [
            'total'  => $qsos->count(),
            'bands'  => $qsos->groupBy('band')->map->count()->sortDesc(),
            'modes'  => $qsos->groupBy('mode')->map->count()->sortDesc(),
            'time_start' => $qsos->min('time_on'),
            'time_end'   => $qsos->max('time_on'),
            'has_log'    => $qsos->isNotEmpty(),
        ];

        return view('activations.show', compact('activation', 'photos', 'summary'));
    }

    /**
     * Отдать фото активации (стрим с private-диска). Только type=photo.
     */
    public function photo(Activation $activation, ActivationProof $proof): StreamedResponse
    {
        abort_unless($this->viewable($activation), 404);
        abort_unless(
            $proof->activation_id === $activation->id
                && $proof->type === ActivationProof::TYPE_PHOTO
                && Storage::disk('local')->exists($proof->path),
            404
        );

        // inline — чтобы открывалось в галерее, а не скачивалось
        return Storage::disk('local')->response($proof->path, $proof->original_name, [
            'Content-Disposition' => 'inline',
        ]);
    }

    /**
     * Публичное скачивание ADIF активации — генерируется из сохранённых QSO,
     * а не отдаётся исходный загруженный файл (контролируемый набор полей).
     */
    public function downloadAdif(Activation $activation)
    {
        abort_unless($this->viewable($activation), 404);

        $qsos = $activation->qsos()->orderBy('qso_date')->orderBy('time_on')->get();
        abort_if($qsos->isEmpty(), 404);

        $activation->loadMissing('park');
        $ref = $activation->park->reference;

        $field = fn (string $name, ?string $value): string => ($value === null || $value === '')
            ? ''
            : sprintf('<%s:%d>%s ', $name, strlen($value), $value);

        $adif = "ADIF export — Urban Parks (UPTA)\n";
        $adif .= $field('ADIF_VER', '3.1.4');
        $adif .= $field('PROGRAMID', 'UrbanParks');
        $adif .= "<EOH>\n\n";

        foreach ($qsos as $qso) {
            $time = substr(preg_replace('/\D/', '', (string) $qso->time_on) . '000000', 0, 6);

            $adif .= $field('STATION_CALLSIGN', $qso->station_callsign ?: $activation->callsign);
            $adif .= $field('OPERATOR', $activation->callsign);
            $adif .= $field('CALL', $qso->callsign);
            $adif .= $field('QSO_DATE', $qso->qso_date?->format('Ymd'));
            $adif .= $field('TIME_ON', $time);
            $adif .= $field('BAND', $qso->band);
            $adif .= $field('MODE', $qso->mode);
            $adif .= $field('SUBMODE', $qso->submode);
            $adif .= $field('FREQ', $qso->freq ? rtrim(rtrim((string) $qso->freq, '0'), '.') : null);
            $adif .= $field('RST_SENT', $qso->rst_sent);
            $adif .= $field('RST_RCVD', $qso->rst_rcvd);
            $adif .= $field('MY_SIG', 'UPTA');
            $adif .= $field('MY_SIG_INFO', $ref);
            $adif .= $field('SIG_INFO', $qso->sig_info);
            $adif .= "<EOR>\n";
        }

        $filename = 'UPTA-' . $ref . '-' . $activation->callsign
            . '-' . $activation->activation_date->format('Y-m-d') . '.adi';

        return response($adif, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Одобренная активация видна всем; неодобренная — только авторизованным модераторам.
     */
    private function viewable(Activation $activation): bool
    {
        return $activation->status === Activation::STATUS_APPROVED || auth()->check();
    }
}
