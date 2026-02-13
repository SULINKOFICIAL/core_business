@extends('layouts.app')

@section('title', 'Editar - Usuário')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Editar Usuário
</p>
<form action="{{ route('users.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-body">
            @include('pages.users._form')
        </div>
    </div>
    <div class="d-flex justify-content-between mt-4">
        @if ($user->status == 1)
            <a href="{{ route('users.destroy', $user->id) }}" class="btn btn-lg btn-danger me-2">
                Desativar
            </a>
        @else
            <a href="{{ route('users.destroy', $user->id) }}" class="btn btn-lg btn-success me-2">
                Ativar
            </a>
        @endif
        <div>
            <a href="{{ route('users.index') }}" class="btn btn-light text-muted me-2">
                Voltar
            </a>
            <button type="submit" class="btn btn-primary btn-active-danger">
                Atualizar
            </button>
        </div>
    </div>
</form>
@endsection
