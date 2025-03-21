<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientsActionsController;
use App\Http\Controllers\CpanelController;
use App\Http\Controllers\ERedeController;
use App\Http\Controllers\ErrorMiCoreController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TicketController;

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
            Route::get('/editar/{id}', [PackageController::class, 'edit'])->name('edit');
            Route::put('/editar/{id}', [PackageController::class, 'update'])->name('update');
            Route::get('/desabilitar/{id}', [PackageController::class, 'destroy'])->name('destroy');
            Route::post('/atribuir/{id}', [PackageController::class, 'assign'])->name('assign');
            Route::post('/atualizar/{id}', [PackageController::class, 'upgrade'])->name('upgrade');
            Route::post('/novo/{id}', [PackageController::class, 'new'])->name('new');
        });
    });

    Route::prefix('modulos')->group(function () {
        Route::name('modules.')->group(function () {
            Route::get('/', [ModuleController::class, 'index'])->name('index');
            Route::get('/adicionar', [ModuleController::class, 'create'])->name('create');
            Route::post('/adicionar', [ModuleController::class, 'store'])->name('store');
            Route::get('/editar/{id}', [ModuleController::class, 'edit'])->name('edit');
            Route::put('/editar/{id}', [ModuleController::class, 'update'])->name('update');
            Route::get('/desabilitar/{id}', [ModuleController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('grupos')->group(function () {
        Route::name('groups.')->group(function () {
            Route::get('/', [GroupController::class, 'index'])->name('index');
            Route::get('/adicionar', [GroupController::class, 'create'])->name('create');
            Route::post('/adicionar', [GroupController::class, 'store'])->name('store');
            Route::get('/editar/{id}', [GroupController::class, 'edit'])->name('edit');
            Route::put('/editar/{id}', [GroupController::class, 'update'])->name('update');
            Route::get('/desabilitar/{id}', [GroupController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('recursos')->group(function () {
        Route::name('resources.')->group(function () {
            Route::get('/', [ResourceController::class, 'index'])->name('index');
            Route::get('/adicionar', [ResourceController::class, 'create'])->name('create');
            Route::post('/adicionar', [ResourceController::class, 'store'])->name('store');
            Route::get('/editar/{id}', [ResourceController::class, 'edit'])->name('edit');
            Route::put('/editar/{id}', [ResourceController::class, 'update'])->name('update');
            Route::get('/desabilitar/{id}', [ResourceController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('pedidos')->group(function () {
        Route::name('orders.')->group(function () {
            Route::get('/visualizar/{id}', [OrderController::class, 'show'])->name('show');
        });
    });

    Route::prefix('tickets')->group(function () {
        Route::name('tickets.')->group(function () {
            Route::get('/', [TicketController::class, 'index'])->name('index');
            Route::put('/editar/{id}', [TicketController::class, 'update'])->name('update');
        });
    });

    Route::prefix('errors')->group(function () {
        Route::name('errors.')->group(function () {
            Route::get('/', [ErrorMiCoreController::class, 'index'])->name('index');
            Route::get('/visualizar', [ErrorMiCoreController::class, 'show'])->name('show');
        });
    });

    Route::prefix('sistemas')->group(function () {
        Route::name('systems.')->group(function () {
            Route::get('/recurso', [ClientsActionsController::class, 'feature'])->name('feature');
            Route::get('/acessar-recursos', [ClientsActionsController::class, 'getResources'])->name('get.resources');
            Route::get('/atualizar-banco/{id}', [ClientsActionsController::class, 'updateDatabase'])->name('update.database');
        });
    });

    Route::prefix('rede')->group(function () {
        Route::name('rede.')->group(function () {
            Route::get('/testar', [ERedeController::class, 'testar'])->name('test');
            Route::get('/token/{token}', [ERedeController::class, 'verifySolicitation'])->name('verify.token');
            Route::get('/criptografia/{token}', [ERedeController::class, 'cryptogram'])->name('cryptogram');
        });
    });

    Route::prefix('cpanel')->group(function () {
        Route::name('cpanel.')->group(function () {
            Route::get('/gerar', [CpanelController::class, 'make'])->name('make');
            Route::get('/subdominio/{id}', [CpanelController::class, 'clientMakeDomain'])->name('subdomain');
            Route::get('/clonar-banco/{id}', [CpanelController::class, 'clientMakeDatabase'])->name('clone');
            Route::get('/insere-token/{id}', [CpanelController::class, 'clientAddTokenAndUser'])->name('token');
        });
    });

});
    
Route::get('/resposta-rede', [ERedeController::class, 'webhook']);

require __DIR__.'/auth.php';