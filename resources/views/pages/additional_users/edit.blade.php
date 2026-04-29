@extends('layouts.app')

@section('title', 'Editar - Usuário adicional')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Editar Usuário Adicional
</p>
<form action="{{ route('additional.users.update', $item->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="card mb-6">
        <div class="card-body">
            @include('pages.additional_users._form')
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('additional.users.destroy', $item->id) }}" class="btn btn-lg btn-danger me-2">Desabilitar</a>
            <div>
                <a href="{{ route('additional.users.index') }}" class="btn btn-light text-muted me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </div>
    </div>
</form>
@endsection
