<?php

namespace App\Http\Controllers;

use App\Models\IntegrationSuggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IntegrationSuggestionController extends Controller
{
    protected $request;
    private $repository;

    public function __construct(Request $request, IntegrationSuggestion $content)
    {

        $this->request = $request;
        $this->repository = $content;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        // Obtém dados
        $contents = $this->repository->all();

        // Retorna a página
        return view('pages.suggestion.index')->with([
            'contents' => $contents,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Obtem os dados
        $data = $request->all();
        $data['updated_by'] = Auth::id();

        // Obtem o registro e atualiza
        $content = $this->repository->find($id);
        $content->update($data);

        // Redireciona via json
        return response()->json([
            'message' => 'Sugestão Atualizada com sucesso'
        ]);

    }
}
