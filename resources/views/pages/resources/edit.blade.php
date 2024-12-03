@extends('layouts.app')

@section('title', 'Editar - Recurso')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Editar
</p>
<form action="{{ route('resources.update', $resources->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-body">
                @include('pages.resources._form')
        </div>
    </div>
    <div class="d-flex justify-content-between mt-4">
        @if ($resources->status == 1)
            <a href="{{ route('resources.destroy', $resources->id) }}" class="btn btn-lg btn-danger me-2">
                Desativar
            </a>
            @else
            <a href="{{ route('resources.destroy', $resources->id) }}" class="btn btn-lg btn-success me-2">
                Ativar
            </a>
        @endif
            <div>
                <a href="{{ route('resources.index') }}" class="btn btn-light text-muted me-2">
                    Voltar
                </a>
                <button type="submit" class="btn btn-primary btn-active-danger">
                    Atualizar
                </button>
            </div>
    </div>
</form>
@endsection
