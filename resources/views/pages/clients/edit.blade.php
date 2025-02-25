@extends('layouts.app')

@section('title', 'Editar - Cliente')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Editar Cliente
</p>
<form action="{{ route('clients.update', $content->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-body">
            @include('pages.clients._form')
        </div>
    </div>
    <div class="card mt-6">
        <div class="card-body">
            <a href="{{ route('cpanel.subdomain', $content->id) }}" class="btn btn-primary btn-active-success me-4">
                Gerar Subdomínio
            </a>
            <a href="{{ route('cpanel.clone', $content->id) }}" class="btn btn-success btn-active-success me-4">
                Clona Banco de Dados
            </a>
            <a href="{{ route('cpanel.token', $content->id) }}" class="btn btn-danger btn-active-success me-4">
                Gerar Token e Usuário
            </a>
        </div>
    </div>
    <div class="d-flex justify-content-end mt-4">
        <a href="{{ route('clients.index') }}" class="btn btn-light text-muted me-2">
            Voltar
        </a>
        <button type="submit" class="btn btn-primary btn-active-danger">
            Atualizar
        </button>
    </div>
</form>
@endsection
