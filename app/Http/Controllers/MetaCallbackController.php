<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MetaCallbackController extends Controller
{

    /**
     * Callback para receber autorização OAuth,
     * redireciona para a URL de origem com parametros,
     * recebidos da meta
     */
    public function callback(Request $request)
    {

        // Obtém dados
        $data = $request->all();

        // Decodifica o state
        $data['decoded'] = json_decode(base64_decode($request->get('state')), true);

        // Loga dados
        Log::info(json_encode($data));

        // Redireciona para aplicação
        return redirect()->away('http://' . $data['decoded']['origin'] . '/callbacks/meta?code=' . $data['code']);

    }
}
