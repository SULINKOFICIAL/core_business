@extends('layouts.app')

@section('title', $contents->name)

@section('content')
<p class="text-center fw-bold text-gray-700 fs-3x mb-4 text-uppercase">
    {{ $contents->name }}
</p>
<div class="card">
    <div class="card-body">
        <div>
            Inserir módulos
        </div>
        <div class="d-flex justify-content-center">
            <a href="{{ route('clients.index') }}" class="btn btn-lg btn-light mx-4">
                Voltar
            </a>
            <a href="#" class="btn btn-lg btn-danger mx-4">
                Desativar Site
            </a>
        </div>
    </div>
</div>
@endsection
