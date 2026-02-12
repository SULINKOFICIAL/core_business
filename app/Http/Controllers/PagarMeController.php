<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PagarMeController extends Controller
{
    public function return(Request $request)
    {
        // Obtem dados
        $data = $request->all();

        // Registra tempo
        Log::info("Webhook PagarMe: " . $data);

        // Retorno Sucesso imediato para o PagarMe (202 Accepted)
        return response()->json([
            'status' => 'Accepted',
            'message' => 'Webhook recebido e será processado em background.'
        ], 202);
    }
}
