<?php

use App\Http\Controllers\ApisController;
use App\Http\Controllers\ApisDomainsController;
use App\Http\Controllers\ApisNewsController;
use App\Http\Controllers\ApisTokensController;
use Illuminate\Support\Facades\Route;

/**
 * API para comunicação com o WebSite micore.com.br
 */
Route::prefix('micore')->group(function () {
    Route::post('/cadastrar-se',      [ApisController::class, 'newClient']);
    Route::post('/encontrar-cliente', [ApisController::class, 'findClient']);
});

/**
 * API para comunicação com o WebSite micore.com.br
 */
Route::prefix('central')->middleware('auth.bearer')->group(function () {

    /** Retorna as informações do banco de dados */
    Route::get('/meu-banco',      [ApisController::class, 'getDatabase']);

    /** APIS que necessitam de um cliente */
    Route::middleware('attach.client')->group(function () {

        /** API que envia informações para os miCores */
        Route::get('/meu-plano',      [ApisController::class, 'plan']);
        Route::get('/minhas-compras', [ApisController::class, 'orders']);
        Route::get('/compra/{id}',    [ApisController::class, 'order']);
        Route::get('/cartoes',        [ApisController::class, 'cards']);
        Route::get('/pacotes',        [ApisController::class, 'packages']);

        /** API que recebe dados dos miCores */
        Route::post('/pagamento', [ApisController::class, 'payment']);
        Route::post('/tickets',   [ApisController::class, 'tickets']);
        Route::post('/sugestoes', [ApisController::class, 'suggestions']);
        Route::post('/cartao',    [ApisController::class, 'newCard']);
        Route::post('/error',     [ApisController::class, 'notifyErrors']);

        /** API gerencia os domínios */
        Route::prefix('dominios')->group(function () {
            Route::get('/',                 [ApisDomainsController::class, 'index']);
            Route::post('/adicionar',       [ApisDomainsController::class, 'store']);
            Route::get('/editar/{id}',      [ApisDomainsController::class, 'edit']);
            Route::put('/editar/{id}',      [ApisDomainsController::class, 'update']);
            Route::delete('/remover/{id}',  [ApisDomainsController::class, 'destroy']);
        });

        /** API gerencia os domínios */
        Route::prefix('tokens')->group(function () {
            Route::get('/url/{host}', [ApisTokensController::class, 'url']);
            Route::get('/token/{id}', [ApisTokensController::class, 'token']);
        });

        /** API gerencia as notícias */
        Route::prefix('noticias')->group(function () {
            Route::get('/',                   [ApisNewsController::class, 'index']);
            Route::get('/detalhes/{id}',      [ApisNewsController::class, 'show']);
            Route::get('/nao-lidas/{id}',     [ApisNewsController::class, 'notRead']);
            Route::post('/marcar-lidas/{id}', [ApisNewsController::class, 'markRead']);
        });
    });

});