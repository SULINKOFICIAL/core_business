@extends('layouts.app')

@section('title', 'Grupo de Recursos')

@section('content')
    <p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
        Grupos de Recursos
    </p>
    <div class="row">
        @foreach ($groups as $group)
            <div class="col-3 d-flex">
                <div class="card w-100 mb-6"> <!-- Card ocupa toda a largura da coluna -->
                    <div class="card-body d-flex flex-column pb-0">
                        <!-- Conteúdo principal -->
                        <div class="flex-grow-1">
                            <p class="fw-bolder text-gray-700 m-0 fs-3 text-center">{{ $group->name }}</p>
                            @foreach ($group->resources as $resource)
                            <p class="text-gray-700 m-0 fs-7 text-center">{{ $resource->name }}</p>
                            @endforeach
                        </div>
    
                        <!-- Botões fixados na parte inferior -->
                        <div class="mt-3">
                            <div class="d-flex">
                                <a href="{{ route('groups.edit', $group->id)}}" class="btn btn-sm btn-light-primary w-100">
                                    Acessar Grupo
                                </a>
                            </div>
                            @if ($group->status == 0)
                                <span class="btn btn-outline btn-outline-dashed btn-outline-danger px-4 py-1 disabled my-3 w-100">Desativado</span>
                            @else
                                <span class="btn btn-outline btn-outline-dashed btn-outline-success px-4 py-1 disabled my-3 w-100">Ativado</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer p-2">
                        <p class="text-gray-600 text-center m-0 fs-8">
                            <span class="fw-bold">Criado: </span>{{ $group->created_at->format('d/m/Y') }} às {{ $group->created_at->format('H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
          
<div class="d-flex mt-4">
    <a href="{{ route('groups.create') }}" class="btn btn-sm btn-primary btn-active-success">
        Criar Grupo
    </a>
</div>
@endsection