<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientsActionsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GitController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupsResourceController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PackagesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SectorController;
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
    
    Route::prefix('pacotes')->group(function () {
        Route::name('packages.')->group(function () {
            Route::get('/', [PackageController::class, 'index'])->name('index');
            Route::get('/adicionar', [PackageController::class, 'create'])->name('create');
            Route::post('/adicionar', [PackageController::class, 'store'])->name('store');
            Route::get('/visualizar/{id}', [PackageController::class, 'show'])->name('show');
            Route::get('/editar/{id}', [PackageController::class, 'edit'])->name('edit');
            Route::put('/editar/{id}', [PackageController::class, 'update'])->name('update');
            Route::get('/desabilitar/{id}', [PackageController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('setores')->group(function () {
        Route::name('sectors.')->group(function () {
            Route::get('/', [SectorController::class, 'index'])->name('index');
            Route::get('/adicionar', [SectorController::class, 'create'])->name('create');
            Route::post('/adicionar', [SectorController::class, 'store'])->name('store');
            Route::get('/visualizar/{id}', [SectorController::class, 'show'])->name('show');
            Route::get('/editar/{id}', [SectorController::class, 'edit'])->name('edit');
            Route::put('/editar/{id}', [SectorController::class, 'update'])->name('update');
            Route::get('/desabilitar/{id}', [SectorController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('grupos')->group(function () {
        Route::name('groups.')->group(function () {
            Route::get('/', [GroupController::class, 'index'])->name('index');
            Route::get('/adicionar', [GroupController::class, 'create'])->name('create');
            Route::post('/adicionar', [GroupController::class, 'store'])->name('store');
            Route::get('/visualizar/{id}', [GroupController::class, 'show'])->name('show');
            Route::get('/editar/{id}', [GroupController::class, 'edit'])->name('edit');
            Route::put('/editar/{id}', [GroupController::class, 'update'])->name('update');
            Route::get('/desabilitar/{id}', [GroupController::class, 'destroy'])->name('destroy');
        });
    });
    
    Route::prefix('listas')->group(function () {
        Route::name('listings.')->group(function () {
            Route::get('/', [ListingController::class, 'index'])->name('index');
            Route::get('/adicionar', [ListingController::class, 'create'])->name('create');
            Route::post('/adicionar', [ListingController::class, 'store'])->name('store');
            Route::get('/visualizar/{id}', [ListingController::class, 'show'])->name('show');
            Route::get('/editar/{id}', [ListingController::class, 'edit'])->name('edit');
            Route::put('/editar/{id}', [ListingController::class, 'update'])->name('update');
            Route::get('/desabilitar/{id}', [ListingController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('acoes')->group(function () {
        Route::name('actions.')->group(function () {
            Route::get('/status/{id}', [ClientsActionsController::class, 'status'])->name('status');
        });
    });

});

require __DIR__.'/auth.php';
