<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
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
        $tokenTenant = $request->input('token_micore');

        // Verifica se o token foi enviado
        if (!$tokenTenant) {
            return response()->json(['error' => 'Token de cliente não fornecido'], 400);
        }

        // Obtém dados do cliente
        $tenant = Tenant::where('token', $tokenTenant)->first();

        // Caso não encontre a conta do cliente
        if(!$tenant) return response()->json('Conta não encontrada', 404);

        // Adiciona o tenant ao request
        $request->merge(['tenant' => $tenant]);

        return $next($request);
    }
}
