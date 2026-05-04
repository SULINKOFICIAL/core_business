<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantRuntimeStatus;
use App\Models\Module;
use App\Models\ModuleCategory;
use App\Models\Resource;
use App\Models\ScheduledTaskDispatch;
use App\Models\ScheduledTaskDispatchItem;
use App\Services\GuzzleService;
use App\Services\ModuleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use RuntimeException;
use App\Jobs\RefreshTokenIntegrationsJob;

class TenantsActionsController extends Controller
{

    protected $request;
    private $repository;
    protected $guzzleService;

    public function __construct(Request $request, Tenant $content, GuzzleService $guzzleService)
    {
        $this->request = $request;
        $this->repository = $content;
        $this->guzzleService = $guzzleService;
    }

    private function runtimeStatusFor(Tenant $tenant): TenantRuntimeStatus
    {
        $runtimeStatus = $tenant->runtimeStatus()->first();

        if (!$runtimeStatus) {
            throw new RuntimeException("Runtime status não encontrado para o cliente {$tenant->id}.");
        }

        return $runtimeStatus;
    }

    // Obtém o modulo do usuário
    public function module(Request $request)
    {
        // Obtém dados do formulário
        $data = $request->all();

        // Encontra o cliente
        $tenant = $this->repository->find($data['tenant_id']);

        // Converte 'status' para booleano
        $status = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN);

        // Inicia serviço de módulos
        $moduleService = app(ModuleService::class);

        // Realiza solicitação
        $response = $moduleService->configureModules(
            $tenant,
            [
                $data['module_id']
            ],
            $status
        );

        // Retorna resposta
        return $response;
    }

    // Obtém permissões do usuário
    public function feature(Request $request){

        // Obtém dados do formulário
        $data = $request->all();

        // Encontra o cliente
        $tenant = $this->repository->find($data['tenant_id']);

        // Converte 'status' para booleano (true ou false)
        $data['status']  = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN);

        // Inicia serviço de módulos
        $moduleService = app(ModuleService::class);

        // Realiza solicitação
        $response = $moduleService->configureFeatureForTenant($tenant, [
            [
                'name'   => $data['name'],
                'module' => $data['module'],
                'status' => $data['status']
            ]
        ]);

        // Retorna resposta
        return $response;

    }

    // Atualiza a assinatura do cliente
    public function subscription(Request $request){

        // Obtém dados do formulário
        $data = $request->all();

        // Encontra o cliente
        $tenant = $this->repository->find($data['tenant_id']);

        // Inicia serviço de módulos
        $moduleService = app(ModuleService::class);

        // Verifica se veio as datas
        if(empty($data['end_date'])){
            return response()->json([
                'success' => false,
                'message' => 'Data final inválida.'
            ]);
        }

        // Obtem a data de início
        $startDate = isset($data['start_date']) && !empty($data['start_date'])
            ? Carbon::createFromFormat('d/m/Y', $data['start_date'])->format('Y-m-d')
            : now()->format('Y-m-d');

        // Obtem a data de fim
        $endDate   = Carbon::createFromFormat('d/m/Y', $data['end_date'])->format('Y-m-d');

        // Cria o tempo da assinatura no MiCore
        $response = $moduleService->createSubscriptionCore(
            $tenant,
            $startDate,
            $endDate
        );

        // Retorna resposta
        return response()->json([
            'success' => $response['success'],
            'message' => $response['success'] == true ? json_decode($response['data'], true)['message'] : 'Erro ao atualizar assinatura'
        ]);

    }

    // Atualiza o limite de usuários do cliente
    public function usersLimits(Request $request){
        
        // Obtém dados do formulário
        $data = $request->all();

        // Encontra o cliente
        $tenant = $this->repository->find($data['tenant_id']);

        // Atualiza o limite de usuários no plano atual
        if ($tenant->plan) {
            $tenant->plan->update([
                'users_limit' => $data['users_limit']
            ]);
        }

        // Inicia serviço de módulos
        $moduleService = app(ModuleService::class);
        
        // Cria o tempo da assinatura no MiCore
        $response = $moduleService->updateUsersLimitsCore(
            $tenant,
            $data['users_limit']
        );

        return response()->json([
            'success' => $response['success'],
            'message' => $response['success'] == true ? json_decode($response['data'], true)['message'] : 'Erro ao atualizar limite de usuários'
        ]);
    }

    // Atualiza o limite de armazenamento do cliente
    public function updateSizeStorage(Request $request){
        
        // Obtém dados do formulário
        $data = $request->all();

        // Encontra o cliente
        $tenant = $this->repository->find($data['tenant_id']);

        // Converte para bytes
        $sizeStorage = $data['storage_limit'] * 1024 * 1024 * 1024;

        // Atualiza limite no plano atual
        if ($tenant->plan) {
            $tenant->plan->update([
                'size_storage' => (int) $sizeStorage,
            ]);
        }

        // Inicia serviço de módulos
        $moduleService = app(ModuleService::class);
        
        // Cria o tempo da assinatura no MiCore
        $response = $moduleService->updateSizeStorageCore(
            $tenant,
            $sizeStorage
        );

        return response()->json([
            'success' => $response['success'],
            'message' => $response['success'] == true ? json_decode($response['data'], true)['message'] : 'Erro ao atualizar limite de armazenamento'
        ]);
    }

    // Atualiza todos os bancos de dados via API
    public function updateAllDatabase()
    {

        // Obtém todos os clientes
        $clientsId = $this->repository->all();
        
        // Sinaliza todos como desatualizados
        TenantRuntimeStatus::query()->update(['db_last_version' => false]);
        
        // Contador de erros
        $errors = 0;

        // Total de clientes
        $totalTenants = count($clientsId);

        // Loop para percorrer todos os clientes
        foreach ($clientsId as $tenant) {
            // Se a atualização retornar false incrementa o contador de erros
            if ($this->updateDatabase($tenant->id)) {
                $errors++;
            }
        }

        // Define a mensagem final com base no número de erros
        $message = $errors > 0
        ? "$errors de $totalTenants cliente(s) apresentaram erro(s) durante a atualização."
        : 'Bancos de dados atualizados com sucesso';

        // Redireciona com a mensagem final
        return redirect()
            ->route('index')
            ->with('message', $message);
    }

    /**
     * Atualiza todos os sistemas (banco de dados e git)
     * de todos os clientes via API
     */
    public function updateAllSystems()
    {
        $allowedActions = ['git', 'database', 'supervisor', 'npm_build'];
        $selectedActions = collect((array) $this->request->get('actions', []))
            ->filter(function ($action) use ($allowedActions) {
                return in_array($action, $allowedActions, true);
            })
            ->values()
            ->all();

        if (empty($selectedActions)) {
            return redirect()
                ->route('tenants.index')
                ->with('type', 'warning')
                ->with('message', 'Selecione ao menos uma ação para atualizar os sistemas.');
        }

        $shouldUpdateGit = in_array('git', $selectedActions, true);
        $shouldUpdateDatabase = in_array('database', $selectedActions, true);
        $shouldRestartSupervisor = in_array('supervisor', $selectedActions, true);
        $shouldBuildJavascript = in_array('npm_build', $selectedActions, true);

        // Obtém todos os clientes
        $clients = $this->repository->all();
        
        // Sinaliza como desatualizado apenas o que foi solicitado nesta execução.
        if ($shouldUpdateDatabase || $shouldUpdateGit || $shouldRestartSupervisor) {
            $updateColumns = [];

            if ($shouldUpdateDatabase) {
                $updateColumns['db_last_version'] = false;
            }

            if ($shouldUpdateGit) {
                $updateColumns['git_last_version'] = false;
            }

            if ($shouldRestartSupervisor) {
                $updateColumns['sp_last_version'] = false;
            }

            if (!empty($updateColumns)) {
                TenantRuntimeStatus::query()->update($updateColumns);
            }
        }

        // Obtém todos os clientes com instalações dedicadas
        $clientsDedicateds = $clients->filter(function($tenant) {
            return $tenant->type_installation == 'dedicated';
        });

        // Atualiza sistemas das instalações dedicadas.
        foreach ($clientsDedicateds as $tenant) {
            if ($shouldUpdateGit) {
                $this->updateGit($tenant->id);
            }

            if ($shouldRestartSupervisor) {
                $this->restartSupervisor($tenant->id);
            }

            if ($shouldBuildJavascript) {
                $this->runNpmBuild($tenant->id);
            }
        }

        // Busca um cliente compartilhado para aplicar operações compartilhadas.
        $sharedTenant = $clients->first(function($tenant) {
            return $tenant->type_installation == 'shared';
        });

        if ($sharedTenant) {
            
            if ($shouldUpdateGit) {
                // Verifica se o cliente compartilhado foi atualizado com sucesso.
                $this->updateGit($sharedTenant->id);

                // Atualiza o git de todas as hospedagens compartilhadas.
                $sharedRuntimeStatus = $this->runtimeStatusFor($sharedTenant)->refresh();
                foreach ($clients->where('type_installation', 'shared') as $tenant) {
                    $this->runtimeStatusFor($tenant)->update([
                        'git_last_version' => $sharedRuntimeStatus->git_last_version,
                        'git_error' => $sharedRuntimeStatus->git_error,
                    ]);
                }
            }

            if ($shouldRestartSupervisor) {
                // Verifica se o restart de filas no cliente compartilhado foi concluído.
                $this->restartSupervisor($sharedTenant->id);

                // Atualiza o status do supervisor em todas as hospedagens compartilhadas.
                $sharedRuntimeStatus = $this->runtimeStatusFor($sharedTenant)->refresh();
                foreach ($clients->where('type_installation', 'shared') as $tenant) {
                    $this->runtimeStatusFor($tenant)->update([
                        'sp_last_version' => $sharedRuntimeStatus->sp_last_version,
                        'sp_error' => $sharedRuntimeStatus->sp_error,
                    ]);
                }
            }

            if ($shouldBuildJavascript) {
                $this->runNpmBuild($sharedTenant->id);
            }

        }
        
        if ($shouldUpdateDatabase) {
            // Loop para percorrer todos os clientes quando banco foi selecionado.
            foreach ($clients as $tenant) {
                $this->updateDatabase($tenant->id);
            }
        }

        $actionLabels = [
            'git' => 'Git pull',
            'database' => 'banco de dados',
            'supervisor' => 'reinício de filas',
            'npm_build' => 'build de Javascript',
        ];

        $selectedActionLabels = collect($selectedActions)
            ->map(function ($action) use ($actionLabels) {
                return $actionLabels[$action] ?? $action;
            })
            ->implode(', ');

        // Redireciona com a mensagem final
        return redirect()
            ->route('tenants.index')
            ->with('message', 'Processo concluído para: ' . $selectedActionLabels . '.');
    }

    // Atualiza o banco de dados do cliente via API
    public function updateDatabase($id){

        // Encontra o cliente
        $tenant = $this->repository->find($id);
        $runtimeStatus = $this->runtimeStatusFor($tenant);

        // Realiza solicitação
        $response = $this->guzzleService->request('POST', 'sistema/atualizar-banco', $tenant);

        // Verifica a resposta antes de tentar acessar as chaves
        if (!$response['success']) {
            // Registra a mensagem de erro
            $runtimeStatus->db_last_version = false;
            $runtimeStatus->db_error = $response['message'] ?? 'Erro desconhecido';
        } else {
            // Atualiza db_last_version
            $runtimeStatus->db_last_version = true;
            $runtimeStatus->db_error = null;
        }

        // Atualiza no banco de dados
        $runtimeStatus->save();

        // Retorna a página
        return $runtimeStatus->db_last_version;

    }
    
    // Atualiza o Git do cliente via API
    public function updateGit($id){

        // Encontra o cliente
        $tenant = $this->repository->find($id);
        $runtimeStatus = $this->runtimeStatusFor($tenant);

        // Realiza solicitação
        $response = $this->guzzleService->request('POST', 'sistema/atualizar-git', $tenant);

        Log::info($response);

        // Verifica a resposta antes de tentar acessar as chaves
        if (!$response['success']) {
            $runtimeStatus->git_last_version = false;
            $runtimeStatus->git_error = $response['message'] ?? 'Erro desconhecido';
        } else {
            $runtimeStatus->git_last_version = true;
            $runtimeStatus->git_error = null;
        }

        // Atualiza no banco de dados
        $runtimeStatus->save();

        // Retorna a página
        return $runtimeStatus->git_last_version;

    }

    // Reinicia as filas do cliente via API
    public function restartSupervisor($id){

        // Encontra o cliente
        $tenant = $this->repository->find($id);
        $runtimeStatus = $this->runtimeStatusFor($tenant);

        // Realiza solicitação
        $response = $this->guzzleService->request('POST', 'sistema/supervisor-restart', $tenant);

        if (!$response['success']) {
            $runtimeStatus->sp_last_version = false;
            $runtimeStatus->sp_error = $response['message'] ?? 'Erro desconhecido';
        } else {
            $responseData = json_decode($response['data'] ?? null, true);
            $apiSuccess = is_array($responseData) ? (bool) ($responseData['success'] ?? true) : true;

            if (!$apiSuccess) {
                $runtimeStatus->sp_last_version = false;
                $runtimeStatus->sp_error = $responseData['error'] ?? $responseData['message'] ?? 'Erro desconhecido';
            } else {
                $runtimeStatus->sp_last_version = true;
                $runtimeStatus->sp_error = null;
            }
        }

        // Atualiza no banco de dados
        $runtimeStatus->save();

        // Retorna a página
        return $runtimeStatus->sp_last_version;

    }

    // Atualiza o banco de dados do cliente via API
    public function updateDatabaseManual($id){

        // Encontra o cliente
        $tenant = $this->repository->find($id);

        // Realiza solicitação
        $this->updateDatabase($tenant->id);

        // Retorna a página
        return redirect()
                ->route('tenants.index')
                ->with('message', 'Migração executada com sucesso');

    }

    // Atualiza os arquivos do cliente via API
    public function updateGitManual($id){

        // Encontra o cliente
        $tenant = $this->repository->find($id);

        // Realiza solicitação
        $this->updateGit($tenant->id);

        // Retorna a página
        return redirect()
                ->route('tenants.index')
                ->with('message', 'GIT Pull executado com sucesso');

    }

    // Reinicia as filas do cliente via API
    public function updateSupervisorManual($id){

        // Encontra o cliente
        $tenant = $this->repository->find($id);

        // Realiza solicitação
        $response = $this->restartSupervisor($tenant->id);

        // Mantém o status sincronizado para todos os clientes compartilhados.
        if ($tenant->type_installation === 'shared') {
            $sharedRuntimeStatus = $this->runtimeStatusFor($tenant)->refresh();
            $sharedTenants = $this->repository->where('type_installation', 'shared')->get();

            foreach ($sharedTenants as $sharedTenant) {
                $this->runtimeStatusFor($sharedTenant)->update([
                    'sp_last_version' => $sharedRuntimeStatus->sp_last_version,
                    'sp_error' => $sharedRuntimeStatus->sp_error,
                ]);
            }
        }

        // Retorna a página
        return redirect()
                ->route('tenants.index')
                ->with('message', $response ? 'Filas reiniciadas com sucesso' : 'Falha ao reiniciar as filas');

    }

    // Executa npm build do cliente via API
    public function runNpmBuild($id)
    {
        // Encontra o cliente
        $tenant = $this->repository->find($id);

        // Realiza solicitação com timeout maior pois build pode demorar.
        $response = $this->guzzleService->request('POST', 'sistema/npm-build', $tenant, null, [
            'connect_timeout' => 10,
            'timeout' => 1200,
        ]);

        if (!$response['success']) {
            Log::warning('Falha ao executar npm build no cliente.', [
                'tenant_id' => $tenant->id,
                'client_name' => $tenant->name,
                'message' => $response['message'] ?? 'Erro desconhecido',
            ]);

            return false;
        }

        $responseData = json_decode($response['data'] ?? null, true);
        $apiSuccess = is_array($responseData) ? (bool) ($responseData['success'] ?? true) : true;

        if (!$apiSuccess) {
            Log::warning('API do tenant retornou erro no npm build.', [
                'tenant_id' => $tenant->id,
                'client_name' => $tenant->name,
                'error' => $responseData['error'] ?? $responseData['message'] ?? 'Erro desconhecido',
            ]);

            return false;
        }

        return true;
    }

    /**
     * Dispara manualmente um ou mais jobs para clientes selecionados na central.
     * Também grava o histórico do lote e separa quais jobs devem aguardar resposta.
     * Isso permite tratar jobs rápidos sem travar o envio dos jobs mais demorados.
     */
    public function runScheduledNow(Request $request, $id = null)
    {
        // Define os jobs que compõem o lote manual.
        $jobs = $this->scheduledJobs();

        // Permite executar um job específico quando informado pela tela.
        $selectedJob = $request->get('job');

        // Restringe a execução ao job solicitado quando ele for válido.
        if (!empty($selectedJob) && $selectedJob !== 'all') {
            if (!in_array($selectedJob, $jobs, true)) {
                return redirect()
                    ->back()
                    ->with('type', 'error')
                    ->with('message', 'A tarefa selecionada é inválida.');
            }

            $jobs = [$selectedJob];
        }

        // Busca um cliente específico ou todos os clientes ativos.
        if($id !== null){
            $clients = $this->repository->where('id', $id)->where('status', true)->get();
        } else {
            $clients = $this->repository->where('status', true)->get();
        }

        // Cria o registro pai para agrupar todas as execuções do clique manual.
        $dispatch = ScheduledTaskDispatch::create([
            'job_name' => 'manual_batch',
            'job_data' => [
                'jobs' => $jobs,
                'target_tenant_id' => $id,
            ],
            'source' => 'manual',
            'dispatched_by' => Auth::id(),
            'total_clients' => $clients->count(),
            'success_count' => 0,
            'failure_count' => 0,
            'started_at' => now(),
        ]);

        // Mantém os totais consolidados do lote para a listagem principal.
        $successCount = 0;
        $failureCount = 0;
        
        // Finaliza o lote vazio quando não existir cliente elegível.
        if ($clients->isEmpty()) {
            $dispatch->update([
                'finished_at' => now(),
            ]);

            return redirect()
                ->back()
                ->with('type', 'warning')
                ->with('message', 'Nenhum cliente ativo encontrado para executar as tarefas.');
        }

        /**
         * Percorre os clientes e executa todos os jobs definidos no lote.
         * Cada combinação cliente + job gera um item filho no histórico.
         */
        foreach ($clients as $tenant) {
            foreach ($jobs as $jobName) {
                // Marca o início do disparo individual para auditoria.
                $startedAt = now();

                // O tenant sempre responde apenas com o aceite do disparo.
                $response = $this->guzzleService->request('post', 'sistema/processar-tarefa', $tenant, [
                    'job' => $jobName,
                    'data' => [],
                ], [
                    'timeout' => 5,
                ]);

                // Converte a resposta para um resumo simples da execução.
                $success = (bool) ($response['success'] ?? false);
                $message = $response['message'] ?? ($success ? 'Tarefa aceita para processamento.' : 'Falha ao aceitar tarefa para processamento.');

                // Tenta reaproveitar a mensagem vinda do próprio cliente quando existir.
                if (!empty($response['data'])) {
                    $decodedResponse = json_decode($response['data'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedResponse) && !empty($decodedResponse['message'])) {
                        $message = $decodedResponse['message'];
                    }
                }

                // Registra o item filho para rastrear esse cliente e job.
                ScheduledTaskDispatchItem::create([
                    'dispatch_id' => $dispatch->id,
                    'tenant_id' => $tenant->id,
                    'job_name' => $jobName,
                    'success' => $success,
                    'response_status_code' => $response['status_code'] ?? null,
                    'response_message' => $message,
                    'response_body' => $response['data'] ?? null,
                    'requested_at' => $startedAt,
                    'finished_at' => now(),
                ]);

                // Atualiza os totais consolidados do lote conforme o resultado.
                if ($success) {
                    $successCount++;
                } else {
                    $failureCount++;
                }
            }
        }

        // Fecha o lote com os totais finais após processar todos os clientes.
        $dispatch->update([
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'finished_at' => now(),
        ]);

        // Retorna para a tela anterior informando o identificador do lote criado.
        return redirect()
                ->back()
                ->with('message', 'Lote #' . $dispatch->id . ' aceito para processamento em ' . $clients->count() . ' cliente(s).');
    }

    /**
     * Função responsável por atualizar os tokens das integrações cadastradas na central.
     */
    public function runIntegrationsNow($id = null)
    {
        if(empty($id)) {
            return redirect()
                ->back()
                ->with('type', 'error')
                ->with('message', 'Identificador da integração não informado.'); 
        }

        // Dispara o job de atualizaçao dos tokens de integracao
        RefreshTokenIntegrationsJob::dispatch($id);

        // Retorna para a tela anterior informando o identificador do lote criado.
        return redirect()
                ->back()
                ->with('message', 'Job de atualizaçao dos tokens de integracao disparado com sucesso.'); 
    }

    /**
     * Retorna os jobs liberados para disparo manual na tela de clientes.
     * A lista fica centralizada aqui para a validação do controller usar a mesma base.
     * Isso evita aceitar na URL um job que não foi previsto pela central.
     */
    private function scheduledJobs()
    {
        // Mantém a mesma lista configurada para o scheduler da central.
        return [
            'finish_calls_24h',
            'finish_order_access',
            'update_s3_metrics',
            'archive_finished_tasks',
            'refresh_mercado_livre',
            'notify_commitments_10m',
            'import_cfop_table',
            'import_ncm_table',
        ];
    }

    // Obtém permissões do usuário
    public function getResources(Request $request)
    {

        // Obtém dados do formulário
        $data = $request->all();

        // Encontra o Tenante modelo 1
        $tenant = $this->repository->where('type_installation', 'shared')->first();

        // Realiza solicitação
        $categories = $this->guzzleService->request('post', 'sistema/permissoes-recursos', $tenant, $data);

        // Decodifica a resposta
        $categories = json_decode($categories['data'], true);

        /**
         * Define o status de todos os registros como 0 antes da verificação.
         * Em seguida, verifica se a permissão recebida das rotas do Core 
         * corresponde a algum registro existente na tabela de Recursos.
         * 
         * Se houver correspondência, o status será atualizado para 1.
         * Se o status permanecer 0, significa que o nome da permissão recebida 
         * não corresponde a nenhum registro existente em Recursos.
         */
        Resource::where('status', true)->update([
            'status' => 0,
        ]);

        ModuleCategory::where('status', true)->update([
            'status' => 0,
        ]);

        Module::where('status', true)->update([
            'status' => 0,
        ]);

        // Armazena os IDs dos módulos retornados na sincronização atual.
        $syncedModuleIds = [];

        /**
         * Faz looping pelas categorias
         */
        foreach ($categories as $category) {

            dd($category);

            /**
             * Verifica se veio com pacote ou sem
             */
            if($category['name'] != 'Sem Pacote') {

                /**
                 * Cria a categoria no sistema
                 */
                $moduleCategory = ModuleCategory::updateOrCreate([
                    'slug' => $category['slug'],
                ], [
                    'name' => $category['name'],
                    'status' => true,
                    'created_by' => Auth::id()
                ]);

                $categoryId = $moduleCategory->id;

            } else {
                $categoryId = null;
            }
            
            // Faz looping entre modulos
            foreach ($category['modules'] as $key => $module) {

                /**
                 * Cria os módulos
                 */
                $modelModule = Module::updateOrCreate([
                    'slug' => $key,
                ], [
                    'name' => $module['name'],
                    'description' => $module['description'],
                    'module_category_id' => $categoryId,
                    'status' => true,
                    'created_by' => Auth::id()
                ]);

                $syncedModuleIds[] = $modelModule->id;

                // Faz looping entre os recursos
                foreach ($module['resources'] as $resource) {

                    /**
                     * Busca um registro onde o campo 'name' seja igual a $permission.
                     * 
                     * Se o registro existir, atualiza o campo 'status' para true.
                     * Se o registro não existir, cria um novo com 'name' = $permission 
                     * e 'status' = true.
                     */
                    Resource::updateOrCreate(
                        [
                            'name' => $resource,
                            'module_id' => $modelModule->id,
                        ],
                        [
                            'status' => true,
                            'created_by' => Auth::id()
                        ]
                    );
                }

            }
        }

        // Desativa módulos não nativos que não vieram no payload atual da sincronização.
        Module::where('is_native', false)
            ->whereNotIn('id', array_unique($syncedModuleIds))
            ->update([
                'status' => false,
            ]);
        
        // Retorna a página
        return redirect()
        ->route('index')
        ->with('message', 'Permissões atualizadas com sucesso! Os recursos foram sincronizados com o sistema.');

    }

    /**
     * Função responsável por liberar gratis por 30 dias um sistema para um cliente
     */
    public function addFree(Request $request, $id)
    {   
        // Obtem dados
        $data = $request->all();

        // Encontra o cliente
        $tenant = $this->repository->find($id);
        
        // Inicia serviço de módulos
        $moduleService = app(ModuleService::class);

        // Realiza solicitação
        $moduleService->configureModules(
            $tenant,
            $data['modules'],
            true
        );

        // Cria o tempo da assinatura no MiCore
        $moduleService->createSubscriptionCore(
            $tenant,
            now()->toDateString(),
            now()->addDays(30)->toDateString()
        );

        // Retorna a página
        return redirect()->route('tenants.show', $tenant->id)->with('message', 'Módulos liberados com sucesso!');

    }

    /**
     * Função responsável por liberar apenas o periodo de 30 o sistema para um cliente
     */
    public function addDate($id)
    {   
        // Encontra o cliente
        $tenant = $this->repository->find($id);
        
        // Inicia serviço de módulos
        $moduleService = app(ModuleService::class);

        // Cria o tempo da assinatura no MiCore
        $moduleService->createSubscriptionCore(
            $tenant,
            now()->toDateString(),
            now()->addYears(1)->toDateString()
        );

        // Retorna a página
        return redirect()->route('tenants.show', $tenant->id)->with('message', 'Data enviada com sucesso!');

    }

}
