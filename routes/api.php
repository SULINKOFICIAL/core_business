<?php

use App\Http\Controllers\ApisAccountController;
use App\Http\Controllers\ApisController;
use App\Http\Controllers\ApisOrdersController;
use App\Http\Controllers\ApisPaymentsController;
use App\Http\Controllers\ApisDomainsController;
use App\Http\Controllers\ApisNewsController;
use App\Http\Controllers\ApisTokensController;
use App\Http\Controllers\MetaApiController;
use App\Http\Controllers\MetaApiOnboardingController;
use Illuminate\Support\Facades\Route;

/**
 * API para comunicação com o WebSite micore.com.br
 */
Route::prefix('micore')->group(function () {
    Route::post('/cadastrar-se',      [ApisController::class, 'newClient']);
    Route::post('/encontrar-cliente', [ApisController::class, 'findClient']);
});

/**
 * API para comunicação com os sistemas miCore
 */
Route::prefix('central')->middleware('auth.bearer')->group(function () {

    /** Retorna as informações do banco de dados */
    Route::get('/meu-banco',      [ApisController::class, 'getDatabase']);
    Route::get('/modulos',        [ApisController::class, 'modules']);

    /** APIS que necessitam de um cliente */
    Route::middleware('attach.client')->group(function () {

        /** API que envia informações para os miCores */
        Route::get('/meu-plano',      [ApisAccountController::class, 'plan']);
        Route::get('/minhas-compras', [ApisAccountController::class, 'orders']);
        Route::get('/cartoes',        [ApisAccountController::class, 'cards']);
        
        /**
         * API que gerencia os pedidos
        */
        Route::prefix('pedidos')->group(function () {
            Route::get('/compra/{id}',       [ApisOrdersController::class, 'details']);
            Route::get('/rascunho',          [ApisOrdersController::class, 'draft']);
            Route::get('/uso',               [ApisOrdersController::class, 'usageOptions']);
            Route::post('/atualizar',        [ApisOrdersController::class, 'update']);
            Route::get('/cancelar',          [ApisController::class, 'orderCancel']);
        });

        /**
         * API que gerencia os pagamentos
        */
        Route::prefix('pagamento')->group(function () {
            Route::post('/pagar', [ApisPaymentsController::class, 'orderPayment']);
        });

        /** API que recebe dados dos miCores */
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
        Route::prefix('meta')->group(function () {
            Route::get('/auth/{type}/{host}', [MetaApiController::class, 'OAuth2']);
            Route::post('/onboarding/start',  [MetaApiOnboardingController::class, 'startOnboarding']);
            Route::get('/token/{id}',         [MetaApiController::class, 'token']);
            Route::post('/inscricao',         [MetaApiController::class, 'subscribed']);
            Route::post('/intercambio',       [MetaApiController::class, 'exchange']);
            Route::delete('/desinscricao',    [MetaApiController::class, 'unsubscribed']);
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

/**
 * Callback técnico do Embedded Signup da Meta.
 * Recebe payload bruto do frontend da central e valida no backend.
 */
Route::prefix('integracoes/meta')->group(function () {
    Route::post('/onboarding/callback', [MetaApiOnboardingController::class, 'embeddedOnboardingCallback']);
});
