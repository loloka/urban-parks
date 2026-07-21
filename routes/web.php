<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParkController;
use App\Http\Controllers\ActivationController;
use App\Http\Controllers\ProofController;
use App\Http\Controllers\AccountController;


// Главная страница
Route::get('/', [ParkController::class, 'index'])->name('home');

// Список всех парков
Route::get('/parks', [ParkController::class, 'list'])->name('parks.index');

// Правила программы
Route::view('/rules', 'rules')->name('rules');

// Страница парка
Route::get('/park/{park}', [ParkController::class, 'show'])->name('park.show');

// Активации: загрузка ADIF-лога + пруфов — только авторизованным с подтверждённым email
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/activations/add', [ActivationController::class, 'create'])
        ->name('activations.create');

    Route::post('/activations', [ActivationController::class, 'store'])
        ->middleware('throttle:10,60') // максимум 10 загрузок в час с одного IP
        ->name('activations.store');
});

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

// Авторизация активаторов (кастомная, в стиле сайта)
Route::middleware('guest')->group(function () {
    Route::get('/register', [AccountController::class, 'showRegister'])->name('register');
    Route::post('/register', [AccountController::class, 'register'])->middleware('throttle:10,60');
    Route::get('/login', [AccountController::class, 'showLogin'])->name('login');
    Route::post('/login', [AccountController::class, 'login'])->middleware('throttle:20,10');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AccountController::class, 'logout'])->name('logout');
    Route::get('/cabinet', [AccountController::class, 'cabinet'])->name('cabinet');

    // Подтверждение email
    Route::get('/email/verify', [AccountController::class, 'verifyNotice'])
        ->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AccountController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/email/verification-notification', [AccountController::class, 'resendVerification'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

// API endpoints
Route::prefix('api')->group(function () {
    Route::get('/parks', [ParkController::class, 'getParks'])->name('api.parks');
    Route::get('/cities', [ParkController::class, 'getCities'])->name('api.cities');
    Route::get('/regions', [ParkController::class, 'getRegions'])->name('api.regions');
});
