<?php

namespace App\Http\Controllers;

use App\Models\Park;
use App\Models\Activation;
use Illuminate\Http\Request;

class ActivationController extends Controller
{
    /**
     * Показать форму добавления активации
     */
    public function create()
    {
        $parks = Park::orderBy('reference')->get();
        return view('activations.create', compact('parks'));
    }

    /**
     * Сохранить активацию
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'park_id' => 'required|exists:parks,id',
            'callsign' => 'required|string|max:20|regex:/^[A-Z0-9\/]+$/i',
            'activation_date' => 'required|date|before_or_equal:today',
            'qso_count' => 'required|integer|min:1|max:9999',
            'notes' => 'nullable|string|max:1000',
        ], [
            'park_id.required' => 'Выберите парк',
            'park_id.exists' => 'Выбранный парк не найден',
            'callsign.required' => 'Введите позывной',
            'callsign.regex' => 'Позывной должен содержать только A-Z, 0-9 и /',
            'activation_date.required' => 'Укажите дату активации',
            'activation_date.before_or_equal' => 'Дата не может быть в будущем',
            'qso_count.required' => 'Укажите количество QSO',
            'qso_count.min' => 'Минимум 1 QSO',
        ]);

        // Преобразуем позывной в верхний регистр
        $validated['callsign'] = strtoupper($validated['callsign']);

        // Статус по умолчанию - на модерации
        $validated['status'] = 'pending';

        Activation::create($validated);

        return redirect()->route('activations.create')
            ->with('success', '✅ Активация отправлена на модерацию! Спасибо, ' . $validated['callsign'] . '!');
    }
}
