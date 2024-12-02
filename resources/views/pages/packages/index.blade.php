@extends('layouts.app')

@section('title', 'Pacotes')

@section('content')
    <p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
        Pacotes
    </p>
    <div class="row">
        @foreach ($packages as $package)
            <div class="col-3">
                <div class="card h-60 mb-5">
                    <div class="card-body text-center d-flex flex-column justify-content-between py-0">
                        <div class="my-5">
                            <div class="d-flex align-items-center justify-content-center">
                                <p class="fw-bolder text-gray-700 m-0 fs-3 text-center">{{ $package->name }}</p>
                            </div>
                            <p class="text-gray-600 m-0 text-center">
                                {{ $package->value }}
                            </p>
                        </div>
                        <div class="d-flex">
                            <a href="#" class="btn btn-sm btn-light-primary w-100">
                                Acessar Pacote
                            </a>
                            <a href="{{ route('packages.edit', $package->id) }}" class="btn btn-sm btn-light-danger btn-icon ms-2 text-gray-600 w-45px" title="Editar">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                        </div>
                        <div>
                            @if ($package->status == 0)
                                <a class="btn btn-outline btn-outline-dashed btn-outline-danger btn-active-light-danger btn-sm m-3 disabled">Desativado</a>
                                @else
                                <a class="btn btn-outline btn-outline-dashed btn-outline-success btn-active-light-success btn-sm m-3 disabled">Ativado</a>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer p-2">
                        <p class="text-gray-600 text-center m-0 fs-8">
                            <span class="fw-bold">Criado: </span>{{ $package->created_at->format('d/m/Y') }} às {{ $package->created_at->format('H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>    
<div class="d-flex mt-4">
    <a href="{{ route('packages.create') }}" class="btn btn-sm btn-primary btn-active-success">
        Criar Pacote
    </a>
</div>
@endsection