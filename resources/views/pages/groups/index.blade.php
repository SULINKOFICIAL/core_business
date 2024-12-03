@extends('layouts.app')

@section('title', 'Grupo de Recursos')

@section('content')
    <p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
        Grupos de Recursos
    </p>
    <div class="row">
        @foreach ($groups as $group)
            <div class="col-3">
                <div class="card h-60 mb-5">
                    <div class="card-body text-center d-flex flex-column justify-content-between py-0">
                        <div class="my-5">
                            <div class="">
                                <p class="fw-bolder text-gray-700 m-0 fs-3 text-center">{{ $group->name }}</p>
                                @foreach ($group->resources as $resource)
                                <div class="">
                                    <p class=" text-gray-700 m-0 fs-7 ">{{ $resource->name }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="d-flex">
                            <a href="#" class="btn btn-sm btn-light-primary w-100">
                                Acessar Grupo
                            </a>
                            <a href="{{ route('groups.edit', $group->id) }}" class="btn btn-sm btn-light-danger btn-icon ms-2 text-gray-600 w-45px" title="Editar">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                        </div>
                        <div>
                            @if ($group->status == 0)
                                <a class="btn btn-outline btn-outline-dashed btn-outline-danger btn-active-light-danger btn-sm m-3 disabled">Desativado</a>
                                @else
                                <a class="btn btn-outline btn-outline-dashed btn-outline-success btn-active-light-success btn-sm m-3 disabled">Ativado</a>
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