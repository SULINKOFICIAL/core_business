@extends('layouts.app')

@section('title', 'Editar - Armazenamento adicional')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Editar Armazenamento Adicional
</p>
<form action="{{ route('additional.storages.update', $item->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="card mb-6">
        <div class="card-body">
            @include('pages.additional_storages._form')
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('additional.storages.destroy', $item->id) }}" class="btn btn-lg btn-danger me-2">Desabilitar</a>
            <div>
                <a href="{{ route('additional.storages.index') }}" class="btn btn-light text-muted me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </div>
    </div>
</form>
@endsection
