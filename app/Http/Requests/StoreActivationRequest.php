<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreActivationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null; // только авторизованные активаторы
    }

    public function rules(): array
    {
        return [
            'park_id' => ['required', 'exists:parks,id'],
            'callsign' => ['required', 'string', 'max:20', 'regex:/^[A-Z0-9\/]+$/i'],

            // ADIF-лог — обязателен (mime-типу не доверяем, содержимое проверит парсер)
            'adif' => ['required', 'file', 'max:15360'],

            // Скриншот QTHnow — единственный обязательный пруф
            'screenshot' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:10240'],

            // Фото с активации — по желанию, до 5 штук (галерея)
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpeg,png,webp', 'max:10240'],

            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'park_id.required' => 'Выберите парк',
            'park_id.exists' => 'Выбранный парк не найден',
            'callsign.required' => 'Введите позывной',
            'callsign.regex' => 'Позывной должен содержать только A-Z, 0-9 и /',
            'adif.required' => 'Прикрепите ADIF-лог (.adi)',
            'adif.max' => 'Лог слишком большой (максимум 15 МБ)',
            'screenshot.required' => 'Прикрепите скриншот из QTHnow — это обязательный пруф',
            'screenshot.image' => 'Скриншот должен быть изображением (JPEG/PNG/WebP)',
            'screenshot.max' => 'Скриншот слишком большой (максимум 10 МБ)',
            'photos.max' => 'Не больше 5 фотографий',
            'photos.*.image' => 'Фото должны быть изображениями (JPEG/PNG/WebP)',
            'photos.*.max' => 'Каждое фото — максимум 10 МБ',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('callsign')) {
            $this->merge(['callsign' => strtoupper(trim((string) $this->input('callsign')))]);
        }
    }
}
