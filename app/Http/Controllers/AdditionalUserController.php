<?php

namespace App\Http\Controllers;

use App\Models\AdditionalUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdditionalUserController extends Controller
{
    protected $request;
    private $repository;

    public function __construct(Request $request, AdditionalUser $content)
    {

        $this->request = $request;
        $this->repository = $content;

    }

    public function index()
    {
        // Obtém dados
        $items = $this->repository->orderBy('quantity')->orderBy('id')->get();

        // Retorna a página
        return view('pages.additional_users.index')->with(['items' => $items]);
    }

    public function create()
    {
        // Retorna a página
        return view('pages.additional_users.create');
    }

    public function store(Request $request)
    {
        // Obtém e valida dados
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'string'],
            'status' => ['nullable', 'boolean'],
        ]);

        // Insere no banco
        $created = $this->repository->create([
            'quantity' => (int) $data['quantity'],
            'price' => toDecimal($data['price']),
            'status' => isset($data['status']) ? (bool) $data['status'] : true,
            'created_by' => Auth::id(),
        ]);

        // Retorna a página
        return redirect()->route('additional.users.index')->with('message', 'Usuário adicional <b>#' . $created->id . '</b> adicionado com sucesso.');
    }

    public function edit($id)
    {
        // Obtém dados
        $item = $this->repository->find($id);

        // Verifica se existe
        if (!$item) return redirect()->back();

        // Retorna a página
        return view('pages.additional_users.edit')->with(['item' => $item]);
    }

    public function update(Request $request, $id)
    {
        // Verifica se existe
        $item = $this->repository->find($id);
        if (!$item) return redirect()->back();

        // Obtém e valida dados
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'string'],
            'status' => ['nullable', 'boolean'],
        ]);

        // Atualiza dados
        $item->update([
            'quantity' => (int) $data['quantity'],
            'price' => toDecimal($data['price']),
            'status' => isset($data['status']) ? (bool) $data['status'] : false,
            'updated_by' => Auth::id(),
        ]);

        // Retorna a página
        return redirect()->route('additional.users.index')->with('message', 'Usuário adicional atualizado com sucesso.');
    }

    public function destroy($id)
    {
        // Obtém dados
        $item = $this->repository->find($id);
        if (!$item) return redirect()->back();

        // Alterna status
        $nextStatus = !$item->status;
        $item->update([
            'status' => $nextStatus,
            'filed_by' => $nextStatus ? null : Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        // Retorna a página
        return redirect()->back()->with('message', 'Status atualizado com sucesso.');
    }
}
