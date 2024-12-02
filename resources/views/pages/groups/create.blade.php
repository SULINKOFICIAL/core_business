@extends('layouts.app')

@section('title', 'Adicionar - Grupos')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Adicionar Grupo
</p>
<form action="{{ route('groups.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="card">
        <div class="card-body">
            @include('pages.groups._form')
        </div>
    </div>
    <div class="d-flex justify-content-end mt-4">
        <a href="{{ route('groups.index') }}" class="btn btn-light text-muted me-2">
            Voltar
        </a>
        <button type="submit" class="btn btn-primary btn-active-danger">
            Cadastrar
        </button>
    </div>
</form>
@endsection