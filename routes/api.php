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


Route::prefix('central')->middleware('auth.bearer')->group(function () {

    /**
     * API que envia informações para os miCores.
     */
    Route::get('/meu-plano',      [ApisController::class, 'plan']);
    Route::get('/minhas-compras', [ApisController::class, 'orders']);
    Route::get('/compra/{id}',    [ApisController::class, 'order']);
    Route::get('/meu-banco',      [ApisController::class, 'getDatabase']);

    /**
     * API que recebe dados dos miCores.
     */
    Route::post('/pagamento', [ApisController::class, 'payment']);
    Route::post('/tickets',   [ApisController::class, 'tickets']);
    Route::post('/cartao',    [ApisController::class, 'newCard']);
    Route::post('/error',     [ApisController::class, 'notifyErrors']);

});