@extends('layouts.app')

@section('title', 'Setores')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Setores
</p>
<div class="row">
    @foreach ($sectors as $sector)
    <div class="col-3 d-flex">
        <div class="card w-100 mb-6">
            <div class="card-body text-center d-flex flex-column justify-content-between py-0">
                <div class="my-5 flex-grow-1">
                    <div class="d-flex align-items-center justify-content-center">
                        <p class="fw-bolder text-gray-700 m-0 fs-3 text-center">{{ $sector->name }}</p>
                    </div>
                </div>
                <div class="d-flex">
                    <a href="{{ route('sectors.edit', $sector->id) }}" class="btn btn-sm btn-light-primary w-100">
                        Acessar Setor
                    </a>
                </div>
                    @if ($sector->status == 0)
                    <span class="btn btn-outline btn-outline-dashed btn-outline-danger px-4 py-1 disabled my-3 w-100">Desativado</span>
                    @else
                    <span class="btn btn-outline btn-outline-dashed btn-outline-success px-4 py-1 disabled my-3 w-100">Ativado</span>
                    @endif
            </div>
            <div class="card-footer p-2">
                <p class="text-gray-600 text-center m-0 fs-8">
                    <span class="fw-bold">Criado: </span>{{ $sector->created_at->format('d/m/Y') }} às {{ $sector->created_at->format('H:i') }}
                </p>
            </div>
        </div>
    </div>
    @endforeach
</div>
    <div class="d-flex mt-4">
        <a href="{{ route('sectors.create') }}" class="btn btn-sm btn-primary btn-active-success">
            Criar Setor
        </a>
    </div>
@endsection