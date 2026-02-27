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

        return $next($request);
    }
}
