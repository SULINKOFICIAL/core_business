@extends('layouts.app')

@section('title', 'Usuários')

@section('content')
    <p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
        Usuários
    </p>
    <div class="card">
        <div class="card-body">
            <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle datatables">
                <thead class="rounded" style="background: #1c283e">
                    <tr class="fw-bold fs-6 text-white px-7">
                        <th class="text-start" width="35%">Nome</th>
                        <th class="text-start" width="30%">Email</th>
                        <th class="text-center px-0">Criado Em</th>
                        <th class="text-center px-0">Status</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>
                                <a href="{{ route('users.edit', $user->id) }}" class="text-gray-700 text-hover-primary">{{ $user->name }}</a>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td class="text-center">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="text-center">
                                @if ($user->status == 0)
                                    <span class="badge badge-light-danger">Desabilitado</span>
                                @else
                                    <span class="badge badge-light-success">Habilitado</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('users.edit', $user->id) }}" class="text-gray-600 w-45px" title="Editar">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="d-flex mt-4">
        <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary btn-active-success">
            Criar Usuário
        </a>
    </div>
@endsection
