@extends('layouts.app')

@section('title', 'Editar - Módulos')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Editar Setor
</p>
<form action="{{ route('sectors.update', $sectors->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-body">
                @include('pages.sectors._form')
        </div>
    </div>
    <div class="d-flex justify-content-between mt-4">
        @if ($sectors->status == 1)
            <a href="{{ route('sectors.destroy', $sectors->id) }}" class="btn btn-lg btn-danger me-2">
                Desativar Setor
            </a>
            @else
            <a href="{{ route('sectors.destroy', $sectors->id) }}" class="btn btn-lg btn-success me-2">
                Ativar Setor
            </a>
        @endif
            <div>
                <a href="{{ route('sectors.index') }}" class="btn btn-light text-muted me-2">
                    Voltar
                </a>
                <button type="submit" class="btn btn-primary btn-active-danger">
                    Atualizar
                </button>
            </div>
    </div>
</form>
@endsection
