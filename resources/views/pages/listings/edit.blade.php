@extends('layouts.app')

@section('title', 'Editar - Lista')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Editar Lista
</p>
<form action="{{ route('listings.update', $listings->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-body">
                @include('pages.listings._form')
        </div>
    </div>
    <div class="d-flex justify-content-between mt-4">
        @if ($listings->status == 1)
            <a href="{{ route('listings.destroy', $listings->id) }}" class="btn btn-lg btn-danger me-2">
                Desativar Lista
            </a>
            @else
            <a href="{{ route('listings.destroy', $listings->id) }}" class="btn btn-lg btn-success me-2">
                Ativar Lista
            </a>
        @endif
            <div>
                <a href="{{ route('listings.index') }}" class="btn btn-light text-muted me-2">
                    Voltar
                </a>
                <button type="submit" class="btn btn-primary btn-active-danger">
                    Atualizar
                </button>
            </div>
    </div>
</form>
@endsection
