<?php

namespace App\Http\Middleware;

use App\Models\Client;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AttachClientByToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

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
