<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
{
   
    protected $request;
    private $repository;

    public function __construct(Request $request, News $content)
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
        $news = News::all();

        // Retorna a página
        return view('pages.news.index')->with([
            'news' => $news,
        ]);
    }

    public function create()
    {   
        // Retorna a página
        return view('pages.news.create');

    }

    public function store(Request $request)
    {
        // Obtém dados
        $data = $request->all();

        // Separa data de início e fim
        $data['date'] = explode(' até ', $request->date);

        // Remove data do array
        $data['start_date'] = $data['date'][0];
        $data['end_date'] = $data['date'][1];

        // Autor
        $data['created_by'] = Auth::id();

        // Insere no banco de dados
        $created = $this->repository->create($data);

        // Retorna a página
        return redirect()
                ->route('news.index')
                ->with('message', 'Notícia <b>'. $created->title . '</b> adicionada com sucesso.');

    }

    public function show($id)
    {
        // Obtém dados
        $modules = $this->repository->find($id);

        // Verifica se existe
        if(!$modules) return redirect()->back();

        // Retorna a página
        return view('pages.news.show')->with([
            'news' => $modules,
        ]);

    }

    public function edit($id)
    {
        // Obtém dados
        $modules = $this->repository->find($id);

        // Verifica se existe
        if(!$modules) return redirect()->back();

        // Retorna a página
        return view('pages.news.edit')->with([
            'news' => $modules,
        ]);

    }
    
    public function update(Request $request, $id)
    {

        // Verifica se existe
        if(!$modules = $this->repository->find($id)) return redirect()->back();

        // Obtém dados
        $data = $request->all();

        // Separa data de início e fim
        $data['date'] = explode(' até ', $request->date);
        
        // Remove data do array
        $data['start_date'] = $data['date'][0];
        $data['end_date'] = $data['date'][1];

        // Autor
        $data['updated_by'] = Auth::id();
        
        // Atualiza dados
        $modules->update($data);

        // Retorna a página
        return redirect()
            ->route('news.index')
            ->with('message', 'Notícia <b>'. $modules->title .'</b> atualizada com sucesso.');
        
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
            ->route('news.index')
            ->with('message', 'Notícia <b>'. $modules->title . '</b> '. $message .' com sucesso.');

    }
}
