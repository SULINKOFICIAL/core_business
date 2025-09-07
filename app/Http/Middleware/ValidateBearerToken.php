<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Client;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateBearerToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // Obter o token de autorização do cabeçalho
        $token = $request->bearerToken();

        // Verificar se o token existe e é válido
        if (!$token || $token !== env('CENTRAL_TOKEN')) {
            return response()->json(['error' => 'Token inválido.'], 401);
        }

        // Obtém dados do cliente
        $tokenClient = $request->input('token_micore');

        // Verifica se o token foi enviado
        if (!$tokenClient) {
            return response()->json(['error' => 'Token de cliente não fornecido'], 400);
        }

        // Obtém dados do cliente
        $client = Client::where('token', $tokenClient)->first();

        // Caso não encontre a conta do cliente
        if(!$client) return response()->json('Conta não encontrada', 404);

        // Adiciona o cliente ao request
        $request->merge(['client' => $client]);

        return $next($request);
    }
}
