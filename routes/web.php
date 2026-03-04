<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParkController;
use App\Http\Controllers\ActivationController;


// Главная страница
Route::get('/', [ParkController::class, 'index'])->name('home');

// Страница парка
Route::get('/park/{park}', [ParkController::class, 'show'])->name('park.show');

// Экспорт ADIF (добавили эту строку)
Route::get('/park/{park}/adif', [ParkController::class, 'exportAdif'])->name('park.adif');

// Активации
Route::get('/activations/add', [ActivationController::class, 'create'])
    ->name('activations.create');

Route::post('/activations', [ActivationController::class, 'store'])
    ->name('activations.store');

// API endpoints
Route::prefix('api')->group(function () {
    Route::get('/parks', [ParkController::class, 'getParks'])->name('api.parks');
    Route::get('/cities', [ParkController::class, 'getCities'])->name('api.cities');
    Route::get('/regions', [ParkController::class, 'getRegions'])->name('api.regions');
});
