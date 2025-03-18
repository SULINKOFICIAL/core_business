<?php

use App\Http\Controllers\ApiPaymentsController;
use App\Http\Controllers\ApisController;
use Illuminate\Support\Facades\Route;

/**
 * API para comunicação com o WebSite micore.com.br
 */
Route::prefix('micore')->group(function () {
    Route::post('/cadastrar-se', [ApisController::class, 'newClient']);
});

/**
 * API que envia informações para os miCores.
 */
Route::prefix('central')->middleware('auth.bearer')->group(function () {
    Route::get('/get-database', [ApisController::class, 'getDatabase']);
    Route::get('/data',         [ApisController::class, 'plan']);
});

/**
 * API que recebe dados dos miCores.
 */
Route::prefix('central')->middleware('auth.bearer')->group(function () {
    Route::post('/payment', [ApisController::class, 'payment']);
    Route::post('/error',   [ApisController::class, 'notifyErrors']);
    Route::post('/tickets', [ApisController::class, 'tickets']);
    Route::post('/cartao',  [ApiPaymentsController::class, 'newCard']);
});