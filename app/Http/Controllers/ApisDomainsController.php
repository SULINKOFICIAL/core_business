<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ApisDomainsController extends Controller
{

    /**
     * Retorna os domínios do cliente
     */
    public function index(Request $request)
    {
        
        // Recebe dados
        $data = $request->all();

         // Obtém dados do cliente
        $client = $data['client'];

        // Obtém plano atual do cliente
        $domains = $client->domains;

        // Retorna domínios
        return response()->json($domains);

    }

    /**
     * Cadastra um domínio
     */
    public function store(Request $request)
    {
         
        // Recebe dados
        $data = $request->all();

         // Obtém dados do cliente
        $client = $data['client'];

        // Obtém plano atual do cliente
        $domains = $client->domains()->create([
            'domain'        => $data['domain'],
            'description'   => $data['description'],
            'auto_generate' => false,
        ]);

        // Retorna domínios
        return response()->json('Domínio criado com sucesso!', 200);
    }

    /**
     * Visualiza um domínio
     */
    public function edit(Request $request, $id)
    {
         
        // Recebe dados
        $data = $request->all();

         // Obtém dados do cliente
        $client = $data['client'];

        // Obtém plano atual do cliente
        $domain = $client->domains()->find($id);

        // Caso não encontre o domínio
        if(!$domain) return response()->json('Domínio não encontrado', 404);

        // Retorna domínios
        return response()->json($domain);
    }

    /**
     * Atualiza um domínio
     */
    public function update(Request $request, $id)
    {
        // Recebe dados
        $data = $request->all();

         // Obtém dados do cliente
        $client = $data['client'];

        // Obtém plano atual do cliente
        $domain = $client->domains()->find($id);

        // Caso não encontre o domínio
        if(!$domain) return response()->json('Domínio não encontrado', 404);

        // Se for um domínio autogerado
        if($domain->auto_generate){
            return response()->json('Domínio autogerado', 400);
        }

        // Atualiza domínio
        $domain->update([
            'domain'        => $data['domain'],
            'description'   => $data['description'],
            'auto_generate' => false,
        ]);

        // Retorna domínios
        return response()->json('Domínio atualizado com sucesso!', 200);
    }

    /**
     * Remove um domínio
     */
    public function destroy(Request $request, $id)
    {
        // Recebe dados
        $data = $request->all();

         // Obtém dados do cliente
        $client = $data['client'];

        // Obtém plano atual do cliente
        $domain = $client->domains()->find($id);

        // Caso não encontre o domínio
        if(!$domain) return response()->json('Domínio não encontrado', 404);

        // Remove domínio
        $domain->update([
            'status' => !$domain->status
        ]);

        // Gera mensagem do domínio
        $message = $domain->status ? 'Domínio ativado com sucesso!' : 'Domínio desativado com sucesso!';

        // Retorna domínios
        return response()->json([
            'message' => $message,
        ], 200);  
    }
}
