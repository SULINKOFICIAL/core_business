<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientPurchaseController extends Controller
{ 
    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
   public function store(Request $request)
   {

        // Obtém dados
        $data = $request->all();

        // Comparar se teve um upgrade ou downgrade de usuários no sistema

        // Comparar se teve um upgrade ou downgrade de pacotes no sistema

        // Verificar como será o calculo da diferença

        dd($data);

   }

}
