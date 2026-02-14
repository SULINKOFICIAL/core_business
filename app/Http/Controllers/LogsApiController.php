<?php

namespace App\Http\Controllers;

use App\Models\LogsApi;

class LogsApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.logs.apis.index');
    }

    /**
    * Exibe o recurso especificado.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function show($id)
    {
        // Obtém dados
        $content = LogsApi::find($id);
        
        // Retorna página com dados
        return response()->json($content->json);
    }
}
