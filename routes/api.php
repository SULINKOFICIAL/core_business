<?php

use App\Http\Controllers\ApisController;
use Illuminate\Support\Facades\Route;

/**
 * Api para comunicação com o WebSite micore.com.br
 */
Route::prefix('micore')->group(function () {
    Route::post('/cadastrar-se', [ApisController::class, 'newClient']);
});

/**
 * Api que envia informações para os miCores.
 */
Route::prefix('central')->middleware('auth.bearer')->group(function () {
    Route::get('/get-database', [ApisController::class, 'getDatabase']);
    Route::get('/data', [ApisController::class, 'plan']);
    Route::post('/payment', [ApisController::class, 'payment']);
    Route::post('/error', [ApisController::class, 'notifyErrors']);
    Route::post('/tickets', [ApisController::class, 'tickets']);
});