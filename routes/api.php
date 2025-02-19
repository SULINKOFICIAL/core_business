<?php

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Rota para obtenção do banco de dados correspondente ao subdomínio.
 * 
 * Essa rota é utilizada para validar requisições que desejam acessar 
 * informações do banco de dados de um cliente específico no Core. A requisição 
 * deve fornecer um subdomínio válido, e o token de autenticação correto, 
 * para que o sistema localize e retorne os dados necessários para a conexão 
 * ao banco de dados.
 *
 * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
 */
Route::get('/api/get-database', function (Request $request) {
    $subdomain = $request->query('subdomain');
    $token = $request->header('Authorization');

    // Verifica se o token começa com "Bearer "
    if (str_starts_with($token, 'Bearer ')) {
        $token = substr($token, 7); // Remove o "Bearer " do token
    }

    // Valida o token enviado pelo MiCore
    if (!$token || $token !== env('CENTRAL_CORE_TOKEN')) return response()->json(['error' => 'Token inválido.'], 401);

    // Verifica se existe um subdóminio
    if (!$subdomain) return response()->json(['error' => 'Subdomínio não fornecido.'], 400);

    // Busca o banco de dados correspondente ao subdomínio
    $client = Client::where('domain', $subdomain)->first();

    if (!$client) return response()->json(['error' => 'Empresa não encontrada.'], 404);

    return response()->json([
        'database_name' => $client->table,
        'db_user' => $client->table . '_usr',
        'db_password' => $client->password,
    ]);
});
