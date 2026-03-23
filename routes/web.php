<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientIntegrationController;
use App\Http\Controllers\ClientInstallController;
use App\Http\Controllers\ClientsActionsController;
use App\Http\Controllers\CpanelController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\ERedeController;
use App\Http\Controllers\ErrorMiCoreController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\IntegrationSuggestionController;
use App\Http\Controllers\MetaApiController;
use App\Http\Controllers\MetaApiOnboardingController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ModuleCategoryController;
use App\Http\Controllers\NewsCategoryController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PagarMeController;
use App\Http\Controllers\SubscriptionsController;
use App\Http\Controllers\SystemSettingsController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TaskDispatchHistoryController;
use App\Http\Controllers\TaskDispatchHistoryProcessingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WhatsAppApiController;
use App\Http\Controllers\LogsApiController;
use App\Http\Controllers\ClientProcessingController;
use App\Http\Controllers\ClientIntegrationProcessingController;
use App\Http\Controllers\CouponProcessingController;
use App\Http\Controllers\LogsApiProcessingController;
use App\Http\Controllers\ModuleCategoryProcessingController;
use App\Http\Controllers\NewsCategoryProcessingController;
use App\Http\Controllers\NewsProcessingController;
use App\Http\Controllers\OrderProcessingController;
use App\Http\Controllers\ResourceProcessingController;
use App\Http\Controllers\SuggestionProcessingController;
use App\Http\Controllers\TicketProcessingController;
use App\Http\Controllers\UserProcessingController;

// Paínel de administração
Route::middleware(['auth'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/sistemas-por-dia', [DashboardController::class, 'dailySystemsByMonth'])->name('dashboard.daily.systems');

    /**
     * Rotas para configurações da conta do usuário autenticado.
     */
    Route::prefix('conta')->name('account.')->group(function () {
        /**
         * Rotas para edição dos dados de configuração da conta.
         */
        Route::prefix('configuracoes')->name('settings.')->group(function () {
            Route::get('/', [AccountSettingsController::class, 'edit'])->name('edit');
            Route::put('/', [AccountSettingsController::class, 'update'])->name('update');
        });
    });

    /**
     * Rotas para configurações sistêmicas da central.
     */
    Route::prefix('configuracoes')->name('system.settings.')->group(function () {
        
        /**
         * Rotas para configuração e teste de SMTP.
         */
        Route::prefix('sistema/smtp')->name('mail.')->group(function () {
            Route::get('/', [SystemSettingsController::class, 'editMail'])->name('edit');
            Route::put('/', [SystemSettingsController::class, 'updateMail'])->name('update');
            Route::post('/testar-email', [SystemSettingsController::class, 'sendTest'])->name('test');
            Route::get('/preview-email', [SystemSettingsController::class, 'preview'])->name('preview');
        });

        /**
         * Rotas para configuração e teste de WhatsApp.
         */
        Route::prefix('sistema/whatsapp')->name('whatsapp.')->group(function () {
            Route::get('/', [SystemSettingsController::class, 'editWhatsApp'])->name('edit');
            Route::put('/', [SystemSettingsController::class, 'updateWhatsApp'])->name('update');
            Route::post('/testar', [SystemSettingsController::class, 'sendWhatsAppTest'])->name('test');
        });
    });

    /**
     * Rotas para gerenciamento dos clientes.
     */
    Route::prefix('clientes')->group(function () {
        Route::name('clients.')->group(function () {
            Route::get('/',                 [ClientController::class, 'index'])->name('index');
            Route::get('/processar',        [ClientProcessingController::class, 'process'])->name('process');
            Route::get('/adicionar',        [ClientController::class, 'create'])->name('create');
            Route::post('/adicionar',       [ClientController::class, 'store'])->name('store');
            Route::get('/visualizar/{id}',  [ClientController::class, 'show'])->name('show');
            Route::get('/editar/{id}',      [ClientController::class, 'edit'])->name('edit');
            Route::put('/editar/{id}',      [ClientController::class, 'update'])->name('update');
            Route::get('/desabilitar/{id}', [ClientController::class, 'destroy'])->name('destroy');       
            

            /**
             * Rotas para instalação de clientes.
             */
            Route::name('install.')->group(function () {
                /**
                 * Rotas para etapas da instalação dos clientes.
                 */
                Route::prefix('instalacao')->group(function () {
                    Route::get('/{id}', [ClientInstallController::class, 'index'])->name('index');

                    /**
                     * Rotas para automações da API do cPanel.
                     */
                    Route::prefix('cpanel')->group(function () {
                        Route::get('/gerar/{id}',           [CpanelController::class, 'make'])->name('make');
                        Route::get('/subdominio/{id}',      [CpanelController::class, 'clientMakeDomain'])->name('subdomain');
                        Route::get('/clonar-banco/{id}',    [CpanelController::class, 'clientMakeDatabase'])->name('clone');
                        Route::get('/insere-token/{id}',    [CpanelController::class, 'clientAddTokenAndUser'])->name('token');
                    });
                });
            });

        });
    });

    /**
     * Rotas para gerenciamento das notícias.
     */
    Route::prefix('noticias')->group(function () {
        Route::name('news.')->group(function () {
            Route::get('/',                 [NewsController::class, 'index'])->name('index');
            Route::get('/processar',        [NewsProcessingController::class, 'process'])->name('process');
            Route::get('/adicionar',        [NewsController::class, 'create'])->name('create');
            Route::post('/adicionar',       [NewsController::class, 'store'])->name('store');
            Route::get('/visualizar/{id}',  [NewsController::class, 'show'])->name('show');
            Route::get('/editar/{id}',      [NewsController::class, 'edit'])->name('edit');
            Route::put('/editar/{id}',      [NewsController::class, 'update'])->name('update');
            Route::get('/desabilitar/{id}', [NewsController::class, 'destroy'])->name('destroy');

            /**
             * Rotas para gerenciamento das categorias de notícias.
             */
            Route::prefix('categorias')->group(function () {
                Route::name('categories.')->group(function () {
                    Route::get('/',                 [NewsCategoryController::class, 'index'])->name('index');
                    Route::get('/processar',        [NewsCategoryProcessingController::class, 'process'])->name('process');
                    Route::get('/adicionar',        [NewsCategoryController::class, 'create'])->name('create');
                    Route::post('/adicionar',       [NewsCategoryController::class, 'store'])->name('store');
                    Route::get('/editar/{id}',      [NewsCategoryController::class, 'edit'])->name('edit');
                    Route::put('/editar/{id}',      [NewsCategoryController::class, 'update'])->name('update');
                    Route::get('/desabilitar/{id}', [NewsCategoryController::class, 'destroy'])->name('destroy');
                });
            });

        });
    });

    /**
     * Rotas para gerenciamento dos pacotes.
     */
    Route::prefix('pacotes')->group(function () {
        Route::name('packages.')->group(function () {
            Route::get('/',                 [PackageController::class, 'index'])->name('index');
            Route::get('/adicionar',        [PackageController::class, 'create'])->name('create');
            Route::post('/adicionar',       [PackageController::class, 'store'])->name('store');
            Route::get('/editar/{id}',      [PackageController::class, 'edit'])->name('edit');
            Route::put('/editar/{id}',      [PackageController::class, 'update'])->name('update');
            Route::get('/desabilitar/{id}', [PackageController::class, 'destroy'])->name('destroy');
            Route::post('/atribuir/{id}',   [PackageController::class, 'assign'])->name('assign');
            Route::post('/atualizar/{id}',  [PackageController::class, 'upgrade'])->name('upgrade');
            Route::post('/novo/{id}',       [PackageController::class, 'new'])->name('new');
        });
    });

    /**
     * Rotas para gerenciamento dos módulos.
     */
    Route::prefix('modulos')->group(function () {
        Route::name('modules.')->group(function () {
            Route::get('/',                 [ModuleController::class, 'index'])->name('index');
            Route::get('/adicionar',        [ModuleController::class, 'create'])->name('create');
            Route::post('/adicionar',       [ModuleController::class, 'store'])->name('store');
            Route::get('/editar/{id}',      [ModuleController::class, 'edit'])->name('edit');
            Route::put('/editar/{id}',      [ModuleController::class, 'update'])->name('update');
            Route::get('/desabilitar/{id}', [ModuleController::class, 'destroy'])->name('destroy');

            /**
             * Rotas para gerenciamento das categorias de módulos.
             */
            Route::prefix('categorias')->group(function () {
                Route::name('categories.')->group(function () {
                    Route::get('/',                 [ModuleCategoryController::class, 'index'])->name('index');
                    Route::get('/processar',        [ModuleCategoryProcessingController::class, 'process'])->name('process');
                    Route::get('/adicionar',        [ModuleCategoryController::class, 'create'])->name('create');
                    Route::post('/adicionar',       [ModuleCategoryController::class, 'store'])->name('store');
                    Route::get('/editar/{id}',      [ModuleCategoryController::class, 'edit'])->name('edit');
                    Route::put('/editar/{id}',      [ModuleCategoryController::class, 'update'])->name('update');
                    Route::get('/desabilitar/{id}', [ModuleCategoryController::class, 'destroy'])->name('destroy');
                });
            });
        });
    });

    /**
     * Rotas para gerenciamento dos grupos.
     */
    Route::prefix('grupos')->group(function () {
        Route::name('groups.')->group(function () {
            Route::get('/',                 [GroupController::class, 'index'])->name('index');
            Route::get('/adicionar',        [GroupController::class, 'create'])->name('create');
            Route::post('/adicionar',       [GroupController::class, 'store'])->name('store');
            Route::get('/editar/{id}',      [GroupController::class, 'edit'])->name('edit');
            Route::put('/editar/{id}',      [GroupController::class, 'update'])->name('update');
            Route::get('/desabilitar/{id}', [GroupController::class, 'destroy'])->name('destroy');
        });
    });

    /**
     * Rotas para gerenciamento dos recursos.
     */
    Route::prefix('recursos')->group(function () {
        Route::name('resources.')->group(function () {
            Route::get('/',                 [ResourceController::class, 'index'])->name('index');
            Route::get('/processar',        [ResourceProcessingController::class, 'process'])->name('process');
            Route::get('/adicionar',        [ResourceController::class, 'create'])->name('create');
            Route::post('/adicionar',       [ResourceController::class, 'store'])->name('store');
            Route::get('/editar/{id}',      [ResourceController::class, 'edit'])->name('edit');
            Route::put('/editar/{id}',      [ResourceController::class, 'update'])->name('update');
            Route::get('/desabilitar/{id}', [ResourceController::class, 'destroy'])->name('destroy');
        });
    });

    /**
     * Rotas para gerenciamento dos pedidos.
     */
    Route::prefix('pedidos')->group(function () {
        Route::name('orders.')->group(function () {
            Route::get('/',                 [OrderController::class, 'index'])->name('index');
            Route::get('/processar',        [OrderProcessingController::class, 'process'])->name('process');
            Route::get('/visualizar/{id}',  [OrderController::class, 'show'])->name('show');
            Route::get('/reprocessar-assinatura-pagarme/{id}',  [OrderController::class, 'reprocessSubscription'])->name('reprocess.subscription');
        });
    });

    /**
     * Rotas para gerenciamento dos cupons.
     */
    Route::prefix('cupons')->group(function () {
        Route::name('coupons.')->group(function () {
            Route::get('/', [CouponController::class, 'index'])->name('index');
            Route::get('/processar', [CouponProcessingController::class, 'process'])->name('process');
            Route::get('/adicionar', [CouponController::class, 'create'])->name('create');
            Route::post('/adicionar', [CouponController::class, 'store'])->name('store');
            Route::get('/editar/{id}', [CouponController::class, 'edit'])->name('edit');
            Route::put('/editar/{id}', [CouponController::class, 'update'])->name('update');
            Route::get('/desabilitar/{id}', [CouponController::class, 'destroy'])->name('destroy');
        });
    });

    /**
     * Rotas para gerenciamento dos tickets.
     */
    Route::prefix('tickets')->group(function () {
        Route::name('tickets.')->group(function () {
            Route::get('/',                 [TicketController::class, 'index'])->name('index');
            Route::get('/processar',        [TicketProcessingController::class, 'process'])->name('process');
            Route::get('/visualizar/{id}',  [TicketController::class, 'show'])->name('show');
            Route::post('/responder/{id}',  [TicketController::class, 'reply'])->name('reply');
            Route::post('/finalizar/{id}',  [TicketController::class, 'finish'])->name('finish');
            Route::put('/editar/{id}',      [TicketController::class, 'update'])->name('update');
        });
    });

    /**
     * Rotas para gerenciamento das sugestões.
     */
    Route::prefix('sugestoes')->group(function () {
        Route::name('suggestions.')->group(function () {
            Route::get('/',                 [IntegrationSuggestionController::class, 'index'])->name('index');
            Route::get('/processar',        [SuggestionProcessingController::class, 'process'])->name('process');
            Route::put('/editar/{id}',      [IntegrationSuggestionController::class, 'update'])->name('update');
        });
    });

    /**
     * Rotas para gerenciamento das integrações dos clientes.
     */
    Route::prefix('integracoes-clientes')->group(function () {
        Route::name('clients.integrations.')->group(function () {
            Route::get('/',                 [ClientIntegrationController::class, 'index'])->name('index');
            Route::get('/processar',        [ClientIntegrationProcessingController::class, 'process'])->name('process');
        });
    });

    /**
     * Rotas para visualização dos erros do sistema.
     */
    Route::prefix('errors')->group(function () {
        Route::name('errors.')->group(function () {
            Route::get('/',                 [ErrorMiCoreController::class, 'index'])->name('index');
            Route::get('/aplicacao',        [ErrorMiCoreController::class, 'application'])->name('application');
            Route::get('/visualizar',       [ErrorMiCoreController::class, 'show'])->name('show');
            Route::get('/arquivar-log',     [ErrorMiCoreController::class, 'archiveLaravelLog'])->name('archive.log');
        });
    });

    /**
     * Rotas para visualização dos logs de APIs.
     */
    Route::prefix('logs/apis')->group(function () {
        Route::name('logs.apis.')->group(function () {
            Route::get('/',                 [LogsApiController::class, 'index'])->name('index');
            Route::get('/relatorio',        [LogsApiController::class, 'relatoryGraphic'])->name('relatory.graphic');
            Route::get('/processar',        [LogsApiProcessingController::class, 'process'])->name('process');
            Route::get('/visualizar/{id}',  [LogsApiController::class, 'show'])->name('show');
        });
    });

    /**
     * Rotas para histórico de tarefas disparadas.
     */
    Route::prefix('historico-tarefas')->group(function () {
        Route::name('task.history.')->group(function () {
            Route::get('/', [TaskDispatchHistoryController::class, 'index'])->name('index');
            Route::get('/processar', [TaskDispatchHistoryProcessingController::class, 'process'])->name('process');
            Route::get('/visualizar/{id}', [TaskDispatchHistoryController::class, 'show'])->name('show');
        });
    });

    /**
     * Rotas para gerenciamento dos usuários.
     */
    Route::prefix('usuarios')->group(function () {
        Route::name('users.')->group(function () {
            Route::get('/',                 [UserController::class, 'index'])->name('index');
            Route::get('/processar',        [UserProcessingController::class, 'process'])->name('process');
            Route::get('/adicionar',        [UserController::class, 'create'])->name('create');
            Route::post('/adicionar',       [UserController::class, 'store'])->name('store');
            Route::get('/editar/{id}',      [UserController::class, 'edit'])->name('edit');
            Route::put('/editar/{id}',      [UserController::class, 'update'])->name('update');
            Route::get('/desabilitar/{id}', [UserController::class, 'destroy'])->name('destroy');
        });
    });


    /**
     * Rotas para comandos nos sistemas miCores.
     */
    Route::prefix('assinaturas')->group(function () {
        Route::name('subscriptions.')->group(function () {
            Route::get('/', [SubscriptionsController::class, 'index'])->name('index');
        });
    });

    /**
     * Rotas para pagamentos nos sistemas miCores.
     */
    Route::prefix('pagamentos')->group(function () {
        Route::name('payments.')->group(function () {
            Route::get('/aprovar/{id}', [OrderController::class, 'approve'])->name('approve');
        });
    });

    /**
     * Rotas para obter informações ou alterar miCores.
     */
    Route::prefix('sistemas')->group(function () {
        Route::name('systems.')->group(function () {
            Route::get('/modulo',                       [ClientsActionsController::class, 'module'])->name('module');
            Route::get('/recurso',                      [ClientsActionsController::class, 'feature'])->name('feature');
            Route::get('/acessar-recursos',             [ClientsActionsController::class, 'getResources'])->name('get.resources');
            Route::get('/atualizar-banco/{id}',         [ClientsActionsController::class, 'updateDatabaseManual'])->name('update.database');
            Route::get('/atualizar-git/{id}',           [ClientsActionsController::class, 'updateGitManual'])->name('update.git');
            Route::get('/reiniciar-filas/{id}',         [ClientsActionsController::class, 'updateSupervisorManual'])->name('update.supervisor');
            Route::get('/atualizar-em-massa',           [ClientsActionsController::class, 'updateAllDatabase'])->name('update.all.db');
            Route::get('/ajustar-armazenamento',        [ClientsActionsController::class, 'updateSizeStorage'])->name('update.size.storage');
            Route::get('/atualizar-sistemas',           [ClientsActionsController::class, 'updateAllSystems'])->name('update.all.systems');
            Route::get('/disparar-jobs-agendados',      [ClientsActionsController::class, 'runScheduledNow'])->name('run.scheduled.now');
            Route::get('/disparar-jobs-agendados/{id}', [ClientsActionsController::class, 'runScheduledNow'])->name('run.scheduled.now.client');
            Route::post('/liberar-sistema/{id}',        [ClientsActionsController::class, 'addFree'])->name('add.free');
            Route::get('/liberar-data/{id}',            [ClientsActionsController::class, 'addDate'])->name('add.date');
        });
    });

    /**
     * Rotas relacionadas aos comandos do desenvolvedor.
     */
    Route::prefix('desenvolvedores')->group(function () {
        Route::name('developer.')->group(function () {
            Route::get('/', [DeveloperController::class, 'index'])->name('index');
        });
    });

});

/**
 * Embedded Signup da Meta deve rodar somente no domínio fixo da central.
 */
Route::prefix('integracoes/meta')->name('meta.embedded.')->group(function () {
    Route::get('/onboarding', [MetaApiOnboardingController::class, 'embeddedOnboarding'])->name('onboarding');
}); 

/**
 * Rotas de callback para autorizações OAuth externas.
 */
Route::name('callbacks.')->prefix('callbacks')->group(function () {
    /**
     * Rotas de callback específicas da Meta.
     */
    Route::prefix('meta')->group(function () {
        /**
         * Rotas nomeadas para callbacks dos produtos da Meta.
         */
        Route::name('meta.')->group(function () {

            /**
             * Criamos rotas de callback separadas para WhatsApp e Instagram,
             * mesmo que ambas chamem a mesma função. Isso facilita o processo
             * de validação da Meta, pois cada produto possui um fluxo OAuth
             * distinto e uma URL de redirecionamento específica.
             *
             * Ao manter endpoints independentes, deixamos explícito para o
             * sistema da Meta e para o App Review que tratamos permissões
             * e integrações de cada produto de forma isolada.
             */
            Route::get('/whatsapp',   [MetaApiController::class, 'callback'])->name('whatsapp');
            Route::get('/instagram',  [MetaApiController::class, 'callback'])->name('instagram');

        });
    });
});

/**
 * Rotas de webhooks para recebimento de notificações externas.
 */
Route::prefix('webhooks')->withoutMiddleware(['web'])->group(function () {

    Route::get('/meta',  [MetaApiController::class, 'authWebhooks']);
    Route::post('/meta', [MetaApiController::class, 'return'])->name('meta');
    Route::post('/whatsapp', [WhatsAppApiController::class, 'return'])->name('whatsapp');

    Route::post('/pagarme', [PagarMeController::class, 'return'])->name('pagarme');
});

require __DIR__.'/auth.php';
