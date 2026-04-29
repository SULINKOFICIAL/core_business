@extends('layouts.app')

@section('title', 'Adicionar - Usuário adicional')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Adicionar Usuário Adicional
</p>
<form action="{{ route('additional.users.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="card mb-6">
        <div class="card-body">
            @include('pages.additional_users._form')
        </div>
        <div class="card-footer d-flex justify-content-end">
            <a href="{{ route('additional.users.index') }}" class="btn btn-light text-muted me-2">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
    </div>
</form>
@endsection
