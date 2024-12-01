<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ClientsActionsController extends Controller
{

    protected $request;
    private $repository;

    public function __construct(Request $request, Client $content)
    {
        $this->request = $request;
        $this->repository = $content;
    }


    public function status($id){

        // Obtém o cliente
        $client = $this->repository->find($id);

        // Verifica o token do cliente
        if(!$client->token){
            return redirect()
                    ->route('clients.index')
                    ->with('message', 'O cliente não possui o Token configurado.');
        }

        // Realiza consulta
        $response = Http::withToken($client->token)->get("https://$client->domain/api/sistema/status");

        // Se conseguiu realizar a solicitação
        if ($response->successful()) {

            // Se retornou sucesso
            if($response->json()){

                // Obtem JSON
                $json = $response->json();

                // Se encontrou a mensagem
                if($json['status'] === 'ok'){
                    return response()->json('OK');
                } else {
                    return response()->json('Error');
                }

            } else {
                return response()->json(['error' => 'Not Found'], 404);
            }

        } else {
            // Tratar erro
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
}
