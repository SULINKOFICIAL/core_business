@extends('layouts.app')

@section('title', 'Editar - Módulo')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Editar Módulo
</p>
<form action="{{ route('modules.update', $modules->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('pages.modules._form')
    <div class="d-flex justify-content-between mt-4">
        @if ($modules->status == 1)
            <a href="{{ route('modules.destroy', $modules->id) }}" class="btn btn-lg btn-danger me-2">
                Desativar Módulo
            </a>
            @else
            <a href="{{ route('modules.destroy', $modules->id) }}" class="btn btn-lg btn-success me-2">
                Ativar Módulo
            </a>
        @endif
            <div>
                <a href="{{ url()->previous() ?? route('modules.index') }}" class="btn btn-light text-muted me-2">
                    Voltar
                </a>
                <button type="submit" class="btn btn-primary btn-active-danger">
                    Atualizar
                </button>
            </div>
    </div>
</form>
@endsection