<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NewsCategory;
use Illuminate\Support\Facades\Auth;

class NewsCategoryController extends Controller
{
    
    protected $request;
    private $repository;

    public function __construct(Request $request, NewsCategory $content)
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
        $news = NewsCategory::all();

        // Retorna a página
        return view('pages.news.categories.index')->with([
            'news' => $news,
        ]);
    }

    public function create()
    {   
        // Retorna a página
        return view('pages.news.categories.create');

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
                ->route('news.categories.index')
                ->with('message', 'Categoria <b>'. $created->title . '</b> adicionada com sucesso.');

    }

    public function edit($id)
    {
        // Obtém dados
        $modules = $this->repository->find($id);

        // Verifica se existe
        if(!$modules) return redirect()->back();

        // Retorna a página
        return view('pages.news.categories.edit')->with([
            'news' => $modules,
        ]);

    }
    
    public function update(Request $request, $id)
    {

        // Verifica se existe
        if(!$modules = $this->repository->find($id)) return redirect()->back();

        // Obtém dados
        $data = $request->all();

        // Autor
        $data['updated_by'] = Auth::id();
        
        // Atualiza dados
        $modules->update($data);

        // Retorna a página
        return redirect()
            ->route('news.categories.index')
            ->with('message', 'Categoria <b>'. $modules->title .'</b> atualizada com sucesso.');
        
    }

    public function destroy($id)
    {

        // Obtém dados
        $modules = $this->repository->find($id);

        // Atualiza status
        if($modules->status == 1){
            $this->repository->where('id', $id)->update(['status' => false, 'filed_by' => Auth::id()]);
            $message = 'desabilitado';
        } else {
            $this->repository->where('id', $id)->update(['status' => true]);
            $message = 'habilitado';
        }

        // Retorna a página
        return redirect()
            ->route('news.categories.index')
            ->with('message', 'Categoria <b>'. $modules->title . '</b> '. $message .' com sucesso.');

    }
}
