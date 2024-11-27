<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class GitController extends Controller
{
    public function pull()
    {
        // Verifica autorização (exemplo simples)
        $token = request('token');
        // if ($token !== 'SEU_TOKEN_SEGURO') {
        //     abort(403, 'Acesso negado');
        // }

        // Executa o comando Artisan
        Artisan::call('app:git-pull');

        // Retorna a saída
        return response()->json([
            'message' => 'Git pull executado com sucesso!',
            'output' => Artisan::output(),
        ]);
    }
}
