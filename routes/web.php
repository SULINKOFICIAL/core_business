<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GitController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


// Paínel de administração
Route::middleware(['auth'])->group(function () {

    Route::get('/', [ClientController::class, 'index'])->name('index');

    Route::prefix('clientes')->group(function () {
        Route::name('clients.')->group(function () {
            Route::get('/', [ClientController::class, 'index'])->name('index');
            Route::get('/adicionar', [ClientController::class, 'create'])->name('create');
            Route::post('/adicionar', [ClientController::class, 'store'])->name('store');
            Route::get('/visualizar/{id}', [ClientController::class, 'show'])->name('show');
            Route::get('/editar/{id}', [ClientController::class, 'edit'])->name('edit');
            Route::put('/editar/{id}', [ClientController::class, 'update'])->name('update');
            Route::get('/desabilitar/{id}', [ClientController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('comandos')->group(function () {
        Route::name('commands.')->group(function () {
            Route::get('/git-pull', [GitController::class, 'pull'])->name('pull');
        });
    });

});

require __DIR__.'/auth.php';
