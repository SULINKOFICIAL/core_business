@extends('layouts.app')

@section('title', 'Editar - Pacotes')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Editar Pacote
</p>
<form action="{{ route('packages.update', $packages->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-body">
            @include('pages.packages._form')
        </div>
    </div>
    <div class="d-flex justify-content-between mt-4">
        @if ($packages->status == 1)
            <a href="{{ route('packages.destroy', $packages->id) }}" class="btn btn-lg btn-danger me-2">
                Desativar Pacote
            </a>
        @else
            <a href="{{ route('packages.destroy', $packages->id) }}" class="btn btn-lg btn-success me-2">
                Ativar Pacote
            </a>
        @endif  
        <div>
            <a href="{{ route('packages.index') }}" class="btn btn-light text-muted me-2">
                Voltar
            </a>
            <button type="submit" class="btn btn-primary btn-active-danger">
                Atualizar
            </button>
        </div>
    </div>
</form>
@endsection
