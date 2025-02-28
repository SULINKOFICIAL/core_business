@extends('layouts.app')

@section('title', 'Módulos')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Módulos
</p>
<div class="row">
    @foreach ($modules as $module)
    <div class="col-3 d-flex">
        <div class="card w-100 mb-6">
            <div class="card-header d-flex align-items-center justify-content-between min-h-60px px-6">
                <div class="w-75">
                    <a href="{{ route('modules.edit', $module->id) }}" class="mb-0 fw-bolder @if ($module->status == 0) text-danger @else text-gray-700 @endif text-hover-primary m-0 fs-5 text-uppercase lh-1">{{ Str::limit($module->name, 18) }}</a>
                    <p class="text-gray-500 mb-0 fw-semibold fs-7 lh-1">Grupo de Recursos</p>
                </div>
                <a href="{{ route('modules.edit', $module->id) }}" class="btn btn-sm btn-icon btn-light-primary">
                    <i class="fa-solid fa-gear"></i>
                </a>
            </div>
            <div class="card-body">
                @if ($module->groups->count())
                    @foreach ($module->groups as $key => $group)
                    <p class="text-gray-700 m-0 fs-7"><span class="fw-bolder">{{ $key + 1 }}.</span> {{ $group->name }}</p>
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
        <a href="{{ route('modules.create') }}" class="btn btn-sm btn-primary btn-active-success">
            Criar Módulo
        </a>
    </div>
@endsection