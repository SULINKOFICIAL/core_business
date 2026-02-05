@extends('layouts.app')

@section('title', 'Editar - Categoria de Módulo')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Editar Categoria de Módulo
</p>
<form action="{{ route('modules.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-body">
            @include('pages.modules.categories._form')
        </div>
    </div>
    <div class="d-flex justify-content-between mt-4">
        @if ($category->status == 1)
            <a href="{{ route('modules.categories.destroy', $category->id) }}" class="btn btn-lg btn-danger me-2">
                Desativar Categoria
            </a>
            @else
            <a href="{{ route('modules.categories.destroy', $category->id) }}" class="btn btn-lg btn-success me-2">
                Ativar Categoria
            </a>
        @endif
            <div>
                <a href="{{ url()->previous() ?? route('modules.categories.index') }}" class="btn btn-light text-muted me-2">
                    Voltar
                </a>
                <button type="submit" class="btn btn-primary btn-active-danger">
                    Atualizar
                </button>
            </div>
    </div>
</form>
@endsection
