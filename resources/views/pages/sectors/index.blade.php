@extends('layouts.app')

@section('title', 'Módulos')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Módulos
</p>
<div class="row">
    @foreach ($sectors as $sector)
    <div class="col-3 d-flex">
        <div class="card w-100 mb-6">
            <div class="card-header d-flex align-items-center justify-content-between min-h-50px">
                <p class="fw-bolder @if ($sector->status == 0) text-danger @else text-gray-700 @endif  m-0 fs-3 text-center text-uppercase">{{ $sector->name }}</p>
                <a href="{{ route('sectors.edit', $sector->id) }}" class="btn btn-sm btn-icon btn-light-primary">
                    A
                </a>
            </div>
            <div class="card-body text-center">
                @if ($sector->groups->count())
                    @foreach ($sector->groups as $group)
                    <span class="badge badge-light-primary mb-2">{{ $group->name }}</span>
                    @endforeach
                @else
                    <p class="text-gray-500 text-center fs-7 fw-bold mb-0">
                        Sem Grupos
                    </p>
                @endif
            </div>
            {{-- <div class="card-footer p-2 border-0">
                <p class="text-gray-600 text-center m-0 fs-8">
                    <span class="fw-bold">Criado: </span>{{ $sector->created_at->format('d/m/Y') }} às {{ $sector->created_at->format('H:i') }}
                </p>
            </div> --}}
        </div>
    </div>
    @endforeach
</div>
    <div class="d-flex mt-4">
        <a href="{{ route('sectors.create') }}" class="btn btn-sm btn-primary btn-active-success">
            Criar Módulo
        </a>
    </div>
@endsection