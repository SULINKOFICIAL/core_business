<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Resource;
use Illuminate\Http\Request;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Support\Facades\Auth;

class ClientsActionsController extends Controller
{

    protected $request;
    private $repository;

    public function __construct(Request $request, Client $content)
    {
        $this->request = $request;
        $this->repository = $content;
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
        $response = $this->guzzle('put', 'sistema/configurar-permissao', $client, ['name' => $data['name'], 'status' => $data['status']]);

        // Retorna resposta
        return $response;

    }

    // Obtém permissões do usuário
    public function updateDatabase($id){

        // Encontra o cliente
        $client = $this->repository->find($id);

        // Realiza solicitação
        $response = $this->guzzle('POST', 'sistema/atualizar-banco', $client);
        
        // Se salva o status da atualização
        $client->db_last_version = $response['status'];
        $client->save();

        // Retorna a página
        return redirect()
                ->route('clients.index')
                ->with('message', 'Migrate Executado' );

    }

    // Obtém permissões do usuário
    public function getResources(Request $request)
    {

        // Obtém dados do formulário
        $data = $request->all();

        // Encontra o Cliente modelo 1
        $client = $this->repository->find(1);
        
        // Realiza solicitação
        $modules = $this->guzzle('post', 'sistema/permissoes-recursos', $client, $data);

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

        foreach ($modules as $permissions) {
            foreach ($permissions as $permission) {
                
                /**
                 * Busca um registro onde o campo 'name' seja igual a $permission.
                 * 
                 * Se o registro existir, atualiza o campo 'status' para true.
                 * Se o registro não existir, cria um novo com 'name' = $permission 
                 * e 'status' = true.
                 */
                Resource::updateOrCreate(
                    ['name' => $permission],
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

    /**
     * Realiza uma solicitação Guzzle com autenticação Bearer
     *
     * @param string $method Método HTTP (get, post, etc)
     * @param string $url URL para a solicitação
     * @param object $client Objeto cliente contendo informações do cliente
     * @param array|null $data Dados opcionais para incluir na requisição
     * @return array Resposta da API
     */
    public function guzzle($method, $url, $client, $data = null)
    {
        // Instancia o Guzzle
        $guzzle = new guzzle();

        // Inicializa os parâmetros da requisição
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . env('CENTRAL_CORE_TOKEN'),
            ]
        ];

        // Se houver dados, adiciona ao corpo da requisição
        if ($data !== null) {
            $options['json'] = $data;
        }
        
        // Realiza a solicitação
        $response = $guzzle->$method("https://$client->domain/api/$url", $options);

        // Obtém o corpo da resposta
        $response = $response->getBody()->getContents();

        // Decodifica o JSON
        $response = json_decode($response, true);

        // Retorna a resposta
        return $response;
    }

}
