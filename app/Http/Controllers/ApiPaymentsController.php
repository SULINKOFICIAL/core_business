<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientCard;
use Illuminate\Http\Request;

class ApiPaymentsController extends Controller
{

    /**
     * Controlador responsável por gerenciar as APIs do sistema.
     *
     * Este controlador gerencia a criação das contas dos clientes na
     * central, e também é responsável por gerenciar a criação das contas
     * no cPanel que esta em um EC2 em nossa aplicação na Amazon.
     * 
     */
    protected $request;
    private $eRede;

    public function __construct(Request $request)
    {

        $this->request = $request;
        $this->eRede = new ERedeController();

    }

    /**
     * Função responsável por registrar cartões na conta dos clientes.
     */
    public function newCard(Request $request) {
        
        // Obtém dados
        $data = $request->all();

        // Obtém cliente
        $client = Client::where('token', $data['token'])->first();
        
        // Se não tiver token do cliente retorna erro
        if(!isset($data['token']) || !$client) {
            return response()->json('Token ou cliente não encontrado.');
        }

        // Define o id do cliente
        $data['client_id'] = $client->id;

        // Limpa os números do cartão
        $data['number'] = str_replace(' ', '', $data['number']);

        // Verifica se o cartão já foi cadastrado
        if(ClientCard::where('number', $data['number'])->exists()){
            return response()->json('Cartão já cadastrado');
        }

        // Verifica se todos os números do cartão foram preenchidos
        if(strlen($data['number']) != 16){
            return response()->json('Cartão inválido');
        }

        // Separa e valida a data de expiração
        $dateExpiration = explode('/', $data['expiration']);
        $data['expiration_month'] = $dateExpiration[0];
        $data['expiration_year'] = $dateExpiration[1];

        // Registra o cartão
        $card = ClientCard::create($data);

        // Realiza Tokenização na eRede
        $response = $this->eRede->tokenization(
            $client->email,
            $card->number,
            $card->expiration_month,
            $card->expiration_year,
            $card->name,
            $data['ccv'],
        );

        // Se tudo foi bem sucedido
        if($response['returnCode'] == '00'){

            // Salva a tokenization 
            $card->tokenization_id = $response['tokenizationId'];
            $card->tokenization_id_at = now();
            $card->save();

            // Retorna reposta bem sucedida
            return response()->json('Cartão cadastrado com sucesso.');
            
        }
        
        // Retorna reposta bem sucedida
        return response()->json($response['returnMessage']);

    }
}
