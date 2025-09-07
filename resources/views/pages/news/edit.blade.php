@extends('layouts.app')

@section('title', 'Editar - Notícia')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Editar Notícia
</p>
<form action="{{ route('news.update', $news->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-body">
                @include('pages.news._form')
        </div>
    </div>
    <div class="d-flex justify-content-between mt-4">
        @if ($news->status == 1)
            <a href="{{ route('news.destroy', $news->id) }}" class="btn btn-lg btn-danger me-2">
                Desativar Notícia
            </a>
            @else
            <a href="{{ route('news.destroy', $news->id) }}" class="btn btn-lg btn-success me-2">
                Ativar Notícia
            </a>
        @endif
            <div>
                <a href="{{ url()->previous() ?? route('news.index') }}" class="btn btn-light text-muted me-2">
                    Voltar
                </a>
                <button type="submit" class="btn btn-primary btn-active-danger">
                    Atualizar
                </button>
            </div>
    </div>
</form>
@endsection
