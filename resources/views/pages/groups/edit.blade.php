@extends('layouts.app')

@section('title', 'Editar - Grupos')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Editar Grupo
</p>
<form action="{{ route('groups.update', $groups->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-body">
                @include('pages.groups._form')
        </div>
    </div>
    <div class="d-flex justify-content-between mt-4">
        @if ($groups->status == 1)
            <a href="{{ route('groups.destroy', $groups->id) }}" class="btn btn-lg btn-danger me-2">
                Desativar Grupo
            </a>
            @else
            <a href="{{ route('groups.destroy', $groups->id) }}" class="btn btn-lg btn-success me-2">
                Ativar Grupo
            </a>
        @endif
            <div>
                <a href="{{ route('groups.index') }}" class="btn btn-light text-muted me-2">
                    Voltar
                </a>
                <button type="submit" class="btn btn-primary btn-active-danger">
                    Atualizar
                </button>
            </div>
    </div>
</form>
@endsection
