@extends('layouts.app')

@section('title', 'Painel')

@section('content')
<div class="card">
    <div class="card-body">
        <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle datatables">
            <thead class="rounded" style="background: #1c283e">
                <tr class="fw-bold fs-6 text-white px-7">
                    <th class="">Nome do Cliente</th>
                    <th class="text-center px-0">Domínio</th>
                    <th class="text-center px-0">Plano</th>
                    <th class="text-center px-0">Criado Em</th>
                    <th class="text-center px-0">Banco</th>
                    <th class="text-center px-0">Status</th>
                    <th class="text-end pe-12 w-100px"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($contents as $client)
                    <tr>
                        <td>
                            <a href="{{ route('clients.show', $client->id) }}" class="text-gray-700 text-hover-primary fw-bold">
                                {{ $client->name }}
                            </a>
                        </td>
                        <td class="text-center">
                            <a href="https://{{ $client->domains()->first()->domain }}" target="_blank" class="text-gray-600 text-hover-danger m-0 text-center">
                                {{ $client->domains()->first()->domain }} ({{ $client->domains()->count() }})
                            </a>
                        </td>
                        <td class="text-center">
                            @if ($client->package)
                            <span class="badge badge-light-success">{{ $client->package->name }}</span>
                            @else
                            <span class="badge badge-light-primary">Sem pacote</span>
                            @endif
                        </td>
                        <td class="text-center text-gray-600">{{ $client->created_at->format('d/m/Y')}}</td>
                        <td class="text-center">
                            @if ($client->db_last_version == 0)
                                <i class="fa-solid fa-circle-xmark text-danger"></i>
                            @else
                                <i class="fa-solid fa-circle-check text-success"></i>
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($client->status == 0)
                                <span class="badge badge-light-danger">Desabilitado</span>  
                                @else
                                <span class="badge badge-light-success">Habilitado</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-4 align-items-center">
                                <a href="{{ route('clients.show', $client->id) }}" class="btn btn-sm btn-primary btn-active-success fw-bolder text-uppercase py-2">
                                    Visualizar
                                </a>
                                <a href="https://{{ $client->domains[0]->domain }}/acessar/{{ $client->token }}" target="_blank" class="text-gray-700" data-bs-toggle="tooltip" data-bs-placement="top" title="Acessar como sistema">
                                    <i class="fa-solid fa-up-right-from-square"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="d-flex mt-4">
    <a href="{{ route('clients.create') }}" class="btn btn-sm btn-primary btn-active-success">
        Adicionar Cliente
    </a>
</div>
@endsection
