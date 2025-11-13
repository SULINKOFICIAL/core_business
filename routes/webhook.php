<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MetaCallbackController;

// Webhook: para receber notificações
Route::prefix('webhooks')->group(function () {
    Route::get('/meta',             [MetaCallbackController::class, 'token']);
    Route::post('/meta',            [MetaCallbackController::class, 'return'])->name('meta');
});