<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    protected $request;
    private $repository;

    public function __construct(Request $request, User $content)
    {
        $this->request = $request;
        $this->repository = $content;
    }

    public function index()
    {
        $users = $this->repository->orderBy('name')->get();

        return view('pages.users.index')->with([
            'users' => $users,
        ]);
    }

    public function create()
    {
        return view('pages.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $data['created_by'] = Auth::id();
        $data['status'] = true;

        $created = $this->repository->create($data);

        return redirect()
            ->route('users.index')
            ->with('message', 'Usuário <b>' . e($created->name) . '</b> adicionado com sucesso.');
    }

    public function edit($id)
    {
        $user = $this->repository->find($id);

        if (! $user) {
            return redirect()->back();
        }

        return view('pages.users.edit')->with([
            'user' => $user,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = $this->repository->find($id);

        if (! $user) {
            return redirect()->back();
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $data['updated_by'] = Auth::id();

        $user->update($data);

        return redirect()
            ->route('users.index')
            ->with('message', 'Usuário <b>' . e($user->name) . '</b> atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $user = $this->repository->find($id);

        if (! $user) {
            return redirect()->back();
        }

        if ((int) Auth::id() === (int) $user->id) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Você não pode desativar o próprio usuário.');
        }

        if ((bool) $user->status === true) {
            $this->repository->where('id', $id)->update([
                'status' => false,
                'filed_by' => Auth::id(),
            ]);
            $message = 'desabilitado';
        } else {
            $this->repository->where('id', $id)->update([
                'status' => true,
                'filed_by' => null,
            ]);
            $message = 'habilitado';
        }

        return redirect()
            ->route('users.index')
            ->with('message', 'Usuário <b>' . e($user->name) . '</b> ' . $message . ' com sucesso.');
    }
}
