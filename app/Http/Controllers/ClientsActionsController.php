<?php

namespace App\Http\Controllers;

use App\Models\Client;
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

class ClientsActionsController extends Controller
{

    protected $request;
    private $repository;
    protected $guzzleService;

    public function __construct(Request $request, Client $content, GuzzleService $guzzleService)
    {
        $this->request = $request;
        $this->repository = $content;
        $this->guzzleService = $guzzleService;
    }

    // Obtém o modulo do usuário
    public function module(Request $request)
    {
        // Obtém dados do formulário
        $data = $request->all();

        // Encontra o cliente
        $client = $this->repository->find($data['client_id']);

        // Converte 'status' para booleano
        $status = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN);

        // Inicia serviço de módulos
        $moduleService = app(ModuleService::class);

        // Realiza solicitação
        $response = $moduleService->configureModules(
            $client,
            [$data['module_id']],
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
        $client = $this->repository->find($data['client_id']);

        // Converte 'status' para booleano (true ou false)
        $data['status']  = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN);

        // Inicia serviço de módulos
        $moduleService = app(ModuleService::class);

        // Realiza solicitação
        $response = $moduleService->configureFeatureForClient($client, [
            [
                'name'   => $data['name'],
                'module' => $data['module'],
                'status' => $data['status']
            ]
        ]);

        // Retorna resposta
        return $response;

    }

    // Atualiza todos os bancos de dados via API
    public function updateAllDatabase()
    {

        // Obtém todos os clientes
        $clientsId = $this->repository->all();
        
        // Sinaliza todos como desatualizados
        $this->repository->update(['db_last_version' => false]);
        
        // Contador de erros
        $errors = 0;

        // Total de clientes
        $totalClients = count($clientsId);

        // Loop para percorrer todos os clientes
        foreach ($clientsId as $client) {
            // Se a atualização retornar false incrementa o contador de erros
            if ($this->updateDatabase($client->id)) {
                $errors++;
            }
        }

        // Define a mensagem final com base no número de erros
        $message = $errors > 0
        ? "$errors de $totalClients cliente(s) apresentaram erro(s) durante a atualização."
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

        // Obtém todos os clientes
        $clients = $this->repository->all();
        
        // Sinaliza todos como desatualizados
        $this->repository->update([
            'db_last_version'  => false, 
            'git_last_version' => false
        ]);

        // Obtém todos os clientes com instalações dedicadas
        $clientsDedicateds = $clients->filter(function($client) {
            return $client->type_installation == 'dedicated';
        });

        // Atualiza o Git de Todos os exclusívos
        foreach ($clientsDedicateds as $client) {
            $this->updateGit($client->id);
        }

        // Busca um cliente compartilhado e atualiza todos
        $sharedClient = $clients->first(function($client) {
            return $client->type_installation == 'shared';
        });

        if ($sharedClient) {
            
            // Verifica se o cliente compartilhado foi atualizado com sucesso.
            $this->updateGit($sharedClient->id);

            // Atualiza o git de todas as hospedagens compartilhadas
            $this->repository->where('type_installation', 'shared')->update(['git_last_version' => true]);

        }
        
        // Loop para percorrer todos os clientes
        foreach ($clients as $client) {
            $this->updateDatabase($client->id);
        }

        // Redireciona com a mensagem final
        return redirect()
            ->back()
            ->with('message', 'Processo de atualização concluído para todos os clientes.');
    }

    // Atualiza o banco de dados do cliente via API
    public function updateDatabase($id){

        // Encontra o cliente
        $client = $this->repository->find($id);

        // Realiza solicitação
        $response = $this->guzzleService->request('POST', 'sistema/atualizar-banco', $client);

        // Verifica a resposta antes de tentar acessar as chaves
        if (!$response['success']) {
            // Registra a mensagem de erro
            $client->db_last_version = false;
            $client->db_error = $response['message'] ?? 'Erro desconhecido';
        } else {
            // Atualiza db_last_version
            $client->db_last_version = true;
            $client->db_error = null;
        }

        // Atualiza no banco de dados
        $client->save();

        // Retorna a página
        return $client->db_last_version;

    }
    
    // Atualiza o Git do cliente via API
    public function updateGit($id){

        // Encontra o cliente
        $client = $this->repository->find($id);

        // Realiza solicitação
        $response = $this->guzzleService->request('POST', 'sistema/atualizar-git', $client);

        // Verifica a resposta antes de tentar acessar as chaves
        if (!$response['success']) {
            $client->git_last_version = false;
            $client->git_error = $response['message'] ?? 'Erro desconhecido';
        } else {
            $client->git_last_version = true;
            $client->git_error = null;
        }

        // Atualiza no banco de dados
        $client->save();

        // Retorna a página
        return $client->git_last_version;

    }

    // Atualiza o banco de dados do cliente via API
    public function updateDatabaseManual($id){

        // Encontra o cliente
        $client = $this->repository->find($id);

        // Realiza solicitação
        $this->updateDatabase($client->id);

        // Retorna a página
        return redirect()
                ->route('clients.index')
                ->with('message', 'Migração executada com sucesso');

    }

    // Atualiza os arquivos do cliente via API
    public function updateGitManual($id){

        // Encontra o cliente
        $client = $this->repository->find($id);

        // Realiza solicitação
        $this->updateGit($client->id);

        // Retorna a página
        return redirect()
                ->route('clients.index')
                ->with('message', 'GIT Pull executado com sucesso');

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
                'target_client_id' => $id,
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
        $syncFailures = [];

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
        foreach ($clients as $client) {
            foreach ($jobs as $jobName) {
                // Marca o início do disparo individual para auditoria.
                $startedAt = now();

                // Define se esse job deve esperar o tenant processar antes de responder.
                $waitForResponse = $this->shouldWaitForResponse($jobName);

                // Ajusta o timeout apenas para jobs síncronos, que retornam sucesso ou erro imediato.
                $response = $this->guzzleService->request('post', 'sistema/processar-tarefa', $client, [
                    'job' => $jobName,
                    'data' => [],
                    'wait_for_response' => $waitForResponse,
                ], [
                    'timeout' => $waitForResponse ? 20 : 5,
                ]);

                // Converte a resposta para um resumo simples da execução.
                $success = (bool) ($response['success'] ?? false);
                $message = $response['message'] ?? ($success ? 'Tarefa aceita para processamento.' : 'Erro desconhecido');

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
                    'client_id' => $client->id,
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

                    // Guarda somente falhas síncronas para consolidar um log no fim do lote.
                    if ($waitForResponse) {
                        $syncFailures[] = [
                            'client_id' => $client->id,
                            'client_name' => $client->name,
                            'job_name' => $jobName,
                            'status_code' => $response['status_code'] ?? null,
                            'message' => $message,
                        ];
                    }
                }
            }
        }

        // Fecha o lote com os totais finais após processar todos os clientes.
        $dispatch->update([
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'finished_at' => now(),
        ]);

        $this->logSyncFailures('manual', $dispatch->id, $syncFailures);

        // Retorna para a tela anterior informando o identificador do lote criado.
        return redirect()
                ->back()
                ->with('message', 'Lote #' . $dispatch->id . ' executado para ' . $clients->count() . ' cliente(s).');
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
            'test_log',
        ];
    }

    /**
     * Define quais jobs precisam aguardar a execução no tenant antes de responder.
     * Esses casos são reservados para tarefas rápidas cujo erro precisa voltar na hora.
     * Hoje o refresh do Mercado Livre entra nessa regra.
     */
    private function shouldWaitForResponse(string $jobName): bool
    {
        return in_array($jobName, [
            'refresh_mercado_livre',
        ], true);
    }

    /**
     * Registra em log as falhas dos jobs síncronos detectadas no lote manual.
     * Isso cria um ponto único para leitura operacional e futura evolução para alertas.
     * O log só acontece quando realmente houver falhas acumuladas.
     */
    private function logSyncFailures(string $source, ?int $dispatchId, array $syncFailures): void
    {
        // Evita poluir o log quando o lote finalizar sem falhas síncronas.
        if (empty($syncFailures)) {
            return;
        }

        // Mantém os detalhes do lote em um único evento para facilitar a busca depois.
        Log::warning('Falhas detectadas em jobs síncronos da central', [
            'source' => $source,
            'dispatch_id' => $dispatchId,
            'failures_count' => count($syncFailures),
            'failures' => $syncFailures,
        ]);
    }


    // Obtém permissões do usuário
    public function getResources(Request $request)
    {

        // Obtém dados do formulário
        $data = $request->all();

        // Encontra o Cliente modelo 1
        $client = $this->repository->find(1);

        // Realiza solicitação
        $categories = $this->guzzleService->request('post', 'sistema/permissoes-recursos', $client, $data);

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

        /**
         * Faz looping pelas categorias
         */
        foreach ($categories as $category) {

            /**
             * Verifica se veio com pacote ou sem
             */
            if($category['name'] != 'Sem Pacote') {

                /**
                 * Cria a categoria no sistema
                 */
                $moduleCategory = ModuleCategory::updateOrCreate([
                    'name' => $category['name'],
                ], [
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
                    'module_category_id' => $categoryId,
                ], [
                    'name' => $module['name'],
                    'description' => $module['description'],
                    'created_by' => Auth::id()
                ]);

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
        $client = $this->repository->find($id);
        
        // Inicia serviço de módulos
        $moduleService = app(ModuleService::class);

        // Realiza solicitação
        $moduleService->configureModules(
            $client,
            $data['modules'],
            true
        );

        // Cria o tempo da assinatura no MiCore
        $moduleService->createSubscriptionCore(
            $client,
            now()->toDateString(),
            now()->addDays(30)->toDateString()
        );

        // Retorna a página
        return redirect()->route('clients.show', $client->id)->with('message', 'Módulos liberados com sucesso!');

    }

    /**
     * Função responsável por liberar apenas o periodo de 30 o sistema para um cliente
     */
    public function addDate($id)
    {   
        // Encontra o cliente
        $client = $this->repository->find($id);
        
        // Inicia serviço de módulos
        $moduleService = app(ModuleService::class);

        // Cria o tempo da assinatura no MiCore
        $moduleService->createSubscriptionCore(
            $client,
            now()->toDateString(),
            now()->addYears(1)->toDateString()
        );

        // Retorna a página
        return redirect()->route('clients.show', $client->id)->with('message', 'Data enviada com sucesso!');

    }

}
