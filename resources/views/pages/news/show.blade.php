@extends('layouts.app')

@section('title', 'Detalhes - Notícia')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Detalhes Notícia
</p>
<div class="card w-1000px m-auto">
    <div class="card-header align-items-center">
        <h2 class="m-0">Aviso: <b class="text-primary">{{ $news->title }}</b></h2>
    </div>
    <div class="card-body news-body">
        <div>
            {!! $news->body !!}
        </div>
        @if ($news->cta_text)
            <button class="btn btn-primary btn-active-danger mt-4">
                {{ $news->cta_text }}
            </button>
        @endif
    </div>
</div>
<div class="d-flex justify-content-end mt-4 w-1000px m-auto">
    <div>
        <a href="{{ route('news.index') }}" class="btn btn-light text-muted me-2">
            Voltar
        </a>
        <a href="{{ route('news.edit', $news->id) }}" class="btn btn-primary btn-active-danger">
            Editar
        </a>
    </div>
</div>
@endsection
