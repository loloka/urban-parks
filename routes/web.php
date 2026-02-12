<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParkController;

// Главная страница
Route::get('/', [ParkController::class, 'index'])->name('home');

// Страница парка
Route::get('/park/{park}', [ParkController::class, 'show'])->name('park.show');

// API endpoints
Route::prefix('api')->group(function () {
    Route::get('/parks', [ParkController::class, 'getParks'])->name('api.parks');
    Route::get('/cities', [ParkController::class, 'getCities'])->name('api.cities');
    Route::get('/regions', [ParkController::class, 'getRegions'])->name('api.regions');
});
