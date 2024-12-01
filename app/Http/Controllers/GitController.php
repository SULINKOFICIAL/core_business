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

     // Método para colocar a aplicação em modo de manutenção
     public function maintenanceDown()
     {
         try {
             Artisan::call('down'); // Executa o comando php artisan down
             return response()->json(['message' => 'Aplicação está em modo de manutenção'], 200);
         } catch (\Exception $e) {
             return response()->json(['error' => 'Falha ao ativar o modo de manutenção', 'details' => $e->getMessage()], 500);
         }
     }

     // Método para remover o modo de manutenção
     public function maintenanceUp()
     {
         try {
             Artisan::call('up'); // Executa o comando php artisan up
             return response()->json(['message' => 'Aplicação saiu do modo de manutenção'], 200);
         } catch (\Exception $e) {
             return response()->json(['error' => 'Falha ao desativar o modo de manutenção', 'details' => $e->getMessage()], 500);
         }
     }
}
