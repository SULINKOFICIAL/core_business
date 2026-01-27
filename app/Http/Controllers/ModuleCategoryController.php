<?php

namespace App\Http\Controllers;

use App\Models\ModuleCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModuleCategoryController extends Controller
{
    protected $request;
    private $repository;

    public function __construct(Request $request, ModuleCategory $content)
    {
        $this->request = $request;
        $this->repository = $content;
    }

    public function index()
    {
        $categories = ModuleCategory::all();

        return view('pages.modules.categories.index')->with([
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        return view('pages.modules.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $data['created_by'] = Auth::id();

        $created = $this->repository->create($data);

        return redirect()
            ->route('modules.categories.index')
            ->with('message', 'Categoria <b>'. $created->name .'</b> adicionada com sucesso.');
    }

    public function edit($id)
    {
        $category = $this->repository->find($id);
        if(!$category) return redirect()->back();

        return view('pages.modules.categories.edit')->with([
            'category' => $category,
        ]);
    }

    public function update(Request $request, $id)
    {
        if(!$category = $this->repository->find($id)) return redirect()->back();

        $data = $request->all();
        $data['updated_by'] = Auth::id();

        $category->update($data);

        return redirect()
            ->route('modules.categories.index')
            ->with('message', 'Categoria <b>'. $category->name .'</b> atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $category = $this->repository->find($id);

        if($category->status == 1){
            $this->repository->where('id', $id)->update(['status' => false, 'filed_by' => Auth::id()]);
            $message = 'desabilitada';
        } else {
            $this->repository->where('id', $id)->update(['status' => true]);
            $message = 'habilitada';
        }

        return redirect()
            ->route('modules.categories.index')
            ->with('message', 'Categoria <b>'. $category->name . '</b> '. $message .' com sucesso.');
    }
}
