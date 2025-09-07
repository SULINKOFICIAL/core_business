@extends('layouts.app')

@section('title', 'Detalhes - Notícia')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Detalhes Notícia
</p>
<div class="card w-1000px m-auto">
    <div class="card-header align-items-center">
        <h2 class="m-0">Avisos</h2>
    </div>
    <div class="card-body news-body">
        <h2>{{ $news->title }}</h2>
        <div>
            {!! $news->body !!}
        </div>
    </div>
</div>
<div class="d-flex justify-content-between mt-4 w-1000px m-auto">
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
            <a href="{{ route('news.edit', $news->id) }}" class="btn btn-primary btn-active-danger">
                Editar
            </a>
        </div>
</div>
@endsection
