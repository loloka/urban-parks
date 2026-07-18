<?php

namespace App\Http\Controllers;

use App\Models\Activation;
use App\Models\ActivationProof;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Отдача файлов с private-диска только авторизованным (модераторам).
 * Прямых публичных URL у пруфов и логов нет — защита персональных данных
 * и предотвращение хотлинка чужих фото.
 */
class ProofController extends Controller
{
    /**
     * Показать пруф (скриншот/фото) — используется в админке
     */
    public function show(ActivationProof $proof): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($proof->path), 404);

        return Storage::disk('local')->response($proof->path, $proof->original_name);
    }

    /**
     * Скачать исходный ADIF-лог активации
     */
    public function adif(Activation $activation): StreamedResponse
    {
        abort_unless(
            $activation->adif_path && Storage::disk('local')->exists($activation->adif_path),
            404
        );

        return Storage::disk('local')->download(
            $activation->adif_path,
            "activation-{$activation->id}.adi"
        );
    }
}
