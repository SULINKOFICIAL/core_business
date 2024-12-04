<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use GuzzleHttp\Client as Guzzle;

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
                'Authorization' => 'Bearer ' . $client->token,
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
