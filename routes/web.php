<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParkController;
use App\Http\Controllers\ActivationController;
use App\Http\Controllers\ProofController;


// Главная страница
Route::get('/', [ParkController::class, 'index'])->name('home');

// Список всех парков
Route::get('/parks', [ParkController::class, 'list'])->name('parks.index');

// Страница парка
Route::get('/park/{park}', [ParkController::class, 'show'])->name('park.show');

// Активации: загрузка ADIF-лога + пруфов
Route::get('/activations/add', [ActivationController::class, 'create'])
    ->name('activations.create');

Route::post('/activations', [ActivationController::class, 'store'])
    ->middleware('throttle:10,60') // максимум 10 загрузок в час с одного IP
    ->name('activations.store');

// Публичная страница активации (фото, сводка лога, скачивание ADIF)
Route::get('/activations/{activation}', [ActivationController::class, 'show'])
    ->name('activations.show');

// Публичное фото активации — отдаём только type=photo; QTHnow-скриншот сюда не попадает
Route::get('/activations/{activation}/photo/{proof}', [ActivationController::class, 'photo'])
    ->name('activations.photo');

// Публичное скачивание ADIF активации — генерируется из сохранённых QSO
Route::get('/activations/{activation}/adif', [ActivationController::class, 'downloadAdif'])
    ->name('activations.public_adif');

// Файлы модерации (private-диск) — только для авторизованных (админка)
Route::middleware('auth')->group(function () {
    Route::get('/moderation/proofs/{proof}', [ProofController::class, 'show'])
        ->name('proofs.show');
    Route::get('/moderation/activations/{activation}/adif', [ProofController::class, 'adif'])
        ->name('activations.adif');
});

// API endpoints
Route::prefix('api')->group(function () {
    Route::get('/parks', [ParkController::class, 'getParks'])->name('api.parks');
    Route::get('/cities', [ParkController::class, 'getCities'])->name('api.cities');
    Route::get('/regions', [ParkController::class, 'getRegions'])->name('api.regions');
});
