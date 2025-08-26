<?php

use App\Http\Controllers\ApisController;
use App\Http\Controllers\ApisDomainsController;
use Illuminate\Support\Facades\Route;

/**
 * API para comunicação com o WebSite micore.com.br
 */
Route::prefix('micore')->group(function () {
    Route::post('/cadastrar-se',      [ApisController::class, 'newClient']);
    Route::post('/encontrar-cliente', [ApisController::class, 'findClient']);
});


Route::prefix('central')->middleware('auth.bearer')->group(function () {

    /** API que envia informações para os miCores */
    Route::get('/meu-plano',      [ApisController::class, 'plan']);
    Route::get('/minhas-compras', [ApisController::class, 'orders']);
    Route::get('/compra/{id}',    [ApisController::class, 'order']);
    Route::get('/cartoes',        [ApisController::class, 'cards']);
    Route::get('/meu-banco',      [ApisController::class, 'getDatabase']);
    Route::get('/pacotes',        [ApisController::class, 'packages']);

    /** API que recebe dados dos miCores */
    Route::post('/pagamento', [ApisController::class, 'payment']);
    Route::post('/tickets',   [ApisController::class, 'tickets']);
    Route::post('/sugestoes', [ApisController::class, 'suggestions']);
    Route::post('/cartao',    [ApisController::class, 'newCard']);
    Route::post('/error',     [ApisController::class, 'notifyErrors']);

    /** API gerencia os domínios */

    Route::prefix('dominios')->group(function () {
        Route::get('/',  [ApisDomainsController::class, 'index']);
        Route::post('/adicionar', [ApisDomainsController::class, 'store']);
        Route::get('/editar/{id}', [ApisDomainsController::class, 'edit']);
        Route::put('/editar/{id}', [ApisDomainsController::class, 'update']);
        Route::delete('/remover/{id}', [ApisDomainsController::class, 'destroy']);
    });

});