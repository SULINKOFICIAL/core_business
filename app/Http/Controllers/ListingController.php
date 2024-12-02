<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ListingController extends Controller
{
    protected $request;
    private $repository;

    public function __construct(Request $request, Listing $content)
    {

        $this->request = $request;
        $this->repository = $content;

    }

    public function index()
    {
        // Obtém dados
        $listings = $this->repository->all();
        
        // Retorna a página
        return view('pages.listings.index')->with([
            'listings' => $listings,
        ]);
    }

    public function create()
    {   

        // Retorna a página
        return view('pages.listings.create')->with([
        ]);

    }

    public function store(Request $request)
    {
        // Obtém dados
        $data = $request->all();

        // Autor
        $data['created_by'] = Auth::id();

        // Insere no banco de dados
        $created = $this->repository->create($data);

            // Retorna a página
            return redirect()
                    ->route('listings.index')
                    ->with('message', 'Setor <b>'. $created->name . '</b> adicionado com sucesso.');

    }

    public function edit($id)
    {

            // Obtém dados
            $listings = $this->repository->find($id);

            // Verifica se existe
            if(!$listings) return redirect()->back();
    
            // Retorna a página
            return view('pages.listings.edit')->with([
                'listings' => $listings
            ]);

    }

    public function update(Request $request, $id)
    {

        // Verifica se existe
        if(!$listings = $this->repository->find($id)) return redirect()->back();

        // Armazena o nome antigo
        $oldName = $listings->name;

        // Obtém dados
        $data = $request->all();

        // Autor
        $data['updated_by'] = Auth::id();

        // Atualiza dados
        $listings->update($data);

        // Retorna a página
        return redirect()
        ->route('listings.index')
        ->with('message', 'Setor <b>'. $oldName . '</b> atualizado para <b>'. $listings->name .'</b> com sucesso.');
        
    }

    public function destroy($id)
    {

        // Obtém dados
        $listings = $this->repository->find($id);

        // Atualiza status
        if($listings->status == 1){
            $this->repository->where('id', $id)->update(['status' => false, 'filed_by' => Auth::id()]);
            $message = 'desabilitado';
        } else {
            $this->repository->where('id', $id)->update(['status' => true]);
            $message = 'habilitado';
        }

        // Retorna a página
        return redirect()
            ->route('listings.index')
            ->with('message', 'Setor <b>'. $listings->name . '</b> '. $message .' com sucesso.');

    }
}

