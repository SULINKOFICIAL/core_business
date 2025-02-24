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
 * Api para comunicação entre os sistemas core com a central
 */
Route::prefix('central')->group(function () {
    Route::get('/get-database', [ApisController::class, 'getDatabase']);
    Route::post('/error', [ApisController::class, 'notifyErrors']);
    Route::post('/tickets', [ApisController::class, 'tickets']);
});