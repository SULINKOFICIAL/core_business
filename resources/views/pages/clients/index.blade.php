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
                    <th class="text-center px-0">Status</th>
                    <th class="text-end pe-12"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($contents as $client)
                    <tr>
                        <td>{{ $client->name }}</td>
                        <td class="text-center">
                            <a href="https://{{ $client->domain }}" target="_blank" class="text-gray-600 text-hover-danger m-0 text-center">
                                {{ $client->domain }}
                            </a>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-light-success">Free Trial</span>
                        </td>
                        <td class="text-center text-gray-600">{{ $client->created_at->format('d/m/Y')}}</td>
                        <td class="text-center">
                            @if ($client->status == 0)
                                <span class="badge badge-light-danger">Desabilitado</span>  
                                @else
                                <span class="badge badge-light-success">Habilitado</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('clients.show', $client->id) }}" class="btn btn-sm btn-primary btn-active-success fw-bolder text-uppercase py-2">
                                Visualizar
                            </a>
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
