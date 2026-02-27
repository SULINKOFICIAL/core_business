<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Module;
use App\Models\Resource;
use App\Services\GuzzleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    // Obtém permissões do usuário
    public function feature(Request $request){

        // Obtém dados do formulário
        $data = $request->all();
        
        // Encontra o cliente
        $client = $this->repository->find($data['client_id']);

        // Converte 'status' para booleano (true ou false)
        $data['status']  = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN);

        // Realiza solicitação
        $response = $this->guzzleService->request('put', 'sistema/configurar-permissao', $client, ['name' => $data['name'], 'status' => $data['status']]);

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
            ->route('index')
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

    // Dispara manualmente jobs agendados
    public function runScheduledNow($id = null)
    {
        $jobs = [
            'finish_calls_24h',
            'finish_order_access',
            'update_s3_metrics',
            'archive_finished_tasks',
            'refresh_mercado_livre',
        ];

        // Busca os clientes
        if($id !== null){
            $clients = $this->repository->where('status', true)->get();
        } else {
            $clients = $this->repository->where('id', $id)->get();
        }

        /**
         * Loop para percorrer todos os clientes
         */
        foreach ($clients as $client) {
            foreach ($jobs as $jobName) {
                $this->guzzleService->request('post', 'sistema/processar-tarefa', $client, [
                    'job' => $jobName,
                    'data' => [],
                ]);
            }
        }

        return redirect()
            ->route('index')
            ->with('message', 'Tarefas executadas com sucesso para ' . $clients->count() . ' cliente(s).');
    }


    // Obtém permissões do usuário
    public function getResources(Request $request)
    {

        // Obtém dados do formulário
        $data = $request->all();

        // Encontra o Cliente modelo 1
        $client = $this->repository->find(2);

        // Realiza solicitação
        $modules = $this->guzzleService->request('post', 'sistema/permissoes-recursos', $client, $data);

        $modules = json_decode($modules['data'], true);

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

        foreach ($modules as $key => $module) {

            /**
             * Cria os módulos
             */
            Module::updateOrCreate([
                'slug' => $key
            ], [
                'name' => $module['name'],
                'description' => $module['description'],
                'created_by' => Auth::id()
            ]);

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
                        'name' => $resource
                    ],
                    [
                        'status' => true,
                        'created_by' => Auth::id()
                    ]
                );
            }
        }
        
        // Retorna a página
        return redirect()
        ->route('index')
        ->with('message', 'Permissões atualizadas com sucesso! Os recursos foram sincronizados com o sistema.');

    }

}
