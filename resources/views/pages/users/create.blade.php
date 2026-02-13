@extends('layouts.app')

@section('title', 'Adicionar - Usuário')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Adicionar Usuário
</p>
<form action="{{ route('users.store') }}" method="POST">
    @csrf
    <div class="card">
        <div class="card-body">
            @include('pages.users._form')
        </div>
    </div>
    <div class="d-flex justify-content-end mt-4">
        <a href="{{ route('users.index') }}" class="btn btn-light text-muted me-2">
            Voltar
        </a>
        <button type="submit" class="btn btn-primary btn-active-danger">
            Cadastrar
        </button>
    </div>
</form>
@endsection
