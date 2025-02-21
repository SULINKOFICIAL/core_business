@extends('layouts.app')

@section('title', 'Painel')

@section('content')
@if ($contents->count())
    <div class="row">
        @foreach ($contents as $client)
        <div class="col-3">
            <div class="card mb-4">
                <div class="card-body text-center">
                    @if ($client->logo)
                    <img src="{{ asset('storage/clientes/' . $client->id . '/logo.png') }}" alt="Logo do Cliente" class="img-fluid h-50px">
                    @else
                    <div class="h-50px d-flex align-items-center justify-content-center">
                        <p class="m-0 fs-2x fw-bolder text-gray-300 text-uppercase">Sem logo</p>
                    </div>
                    @endif
                    <div class="my-5">
                        <div class="d-flex align-items-center justify-content-center">
                            <p class="fw-bolder text-gray-700 m-0 fs-3 text-center">{{ $client->name }}</p>
                            @if ($client->systemStatus() === 'OK')
                                <span class="badge badge-light-success ms-2">Ativo</span>
                            @elseif ($client->systemStatus() === 'Error')
                                <span class="badge badge-light-danger ms-2">Inativo</span>
                            @elseif ($client->systemStatus() === 'Token Empty')
                                <span class="badge badge-light-warning ms-2">Token não configurado</span>
                            @endif
                        </div>
                        <a href="https://{{ $client->domain }}" target="_blank" class="text-gray-600 text-hover-danger m-0 text-center">
                            {{ $client->domain }}
                        </a>
                    </div>
                    <div class="d-flex">
                        <a href="{{ route('clients.show', $client->id) }}" class="btn btn-sm btn-light-primary w-100">
                            Acessar Cliente
                        </a>
                        <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-sm btn-light-danger btn-icon ms-2 text-gray-600 w-45px" title="Editar">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                    </div>
                </div>
                <div class="card-footer p-2">
                    <p class="text-gray-600 text-center m-0 fs-8">
                       <span class="fw-bold">Criado: </span>{{ $client->created_at->format('d/m/Y') }} às {{ $client->created_at->format('H:i') }}
                    </p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@else
<div class="card">
    <div class="card-body">
        <p class="text-center m-0">Sem contas cadastradas</p>
    </div>
</div>
@endif
<div class="d-flex mt-4">
    <a href="{{ route('clients.create') }}" class="btn btn-sm btn-primary btn-active-success">
        Adicionar Cliente
    </a>
</div>
@endsection
