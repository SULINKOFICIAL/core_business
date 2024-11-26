@extends('layouts.app')

@section('title', 'Painel')

@section('content')
@if ($contents->count())
    <div class="row">
        @foreach ($contents as $client)
        <div class="col-3">
            <div class="card">
                <div class="card-body">
                    <img src="{{ asset('assets/media/logos/logo-sulink.svg') }}" class="w-100 px-10 my-4" alt="">
                    <div class="my-2">
                        <div class="d-flex align-items-center justify-content-center">
                            <p class="fw-bolder text-gray-700 m-0 fs-3 text-center">{{ $client->name }}</p>
                            <span class="badge badge-light-success ms-2">Ativo</span>
                        </div>
                        <p class="text-gray-600 m-0 text-center">
                            {{ $client->domain }}
                        </p>
                    </div>
                    <div class="d-flex">
                        <a href="{{ route('clients.show', $client->id) }}" class="btn btn-sm btn-light-primary w-100 mt-2 ">
                            Acessar Cliente
                        </a>
                        <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-sm btn-light-danger btn-icon ms-2 mt-2 text-gray-600 w-45px">
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
<div class="d-flex justify-content-end mt-4">
    <a href="{{ route('clients.create') }}" class="btn btn-sm btn-primary btn-active-success">
        Adicionar Cliente
    </a>
</div>
@endsection
