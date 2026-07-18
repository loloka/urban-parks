<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActivationRequest;
use App\Models\Park;
use App\Services\ActivationImporter;
use App\Services\Adif\AdifParseException;

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
}
