@extends('layouts.app')

@section('title', 'Grupo de Recursos')

@section('content')
    <p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
        Grupos de Recursos
    </p>
    <div class="row">
        @foreach ($groups as $group)
        <div class="col-3 d-flex">
            <div class="card w-100 mb-6">
                <div class="card-header d-flex align-items-center justify-content-between min-h-60px px-6">
                    <div class="w-75">
                        <a href="{{ route('groups.edit', $group->id) }}" class="mb-0 fw-bolder @if ($group->status == 0) text-danger @else text-gray-700 @endif text-hover-primary m-0 fs-5 text-uppercase lh-1">{{ Str::limit($group->name, 25) }}</a>
                        <p class="text-gray-500 mb-0 fw-semibold fs-7 lh-1">Recursos</p>
                    </div>
                    <a href="{{ route('groups.edit', $group->id) }}" class="btn btn-sm btn-icon btn-light-primary">
                        <i class="fa-solid fa-gear"></i>
                    </a>
                </div>
                <div class="card-body">
                    @if ($group->resources->count())
                        @foreach ($group->resources as $key => $resource)
                        <p class="text-gray-700 m-0 fs-7"><span class="fw-bolder">{{ $key + 1 }}.</span> {{ $resource->name }}</p>
                        @endforeach
                    @else
                        <p class="text-gray-500 text-center fs-7 fw-bold mb-0">
                            Sem Grupos
                        </p>
                    @endif
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