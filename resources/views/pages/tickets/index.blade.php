@extends('layouts.app')

@section('title', 'Setores')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Tickets de Clientes
</p>
<div class="card">
    <div class="card-body">
        <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle datatables">
            <thead class="rounded" style="background: #1c283e">
                <tr class="fw-bold fs-6 text-white px-7">
                    <th class="text-start" width="5%">Cliente</th>
                    <th class="text-start" width="15%">Título</th>
                    <th class="text-start px-0">Descrição</th>
                    <th class="text-center px-0">Criado Em</th>
                    <th class="text-center px-0">Progresso</th>
                    <th class="text-center px-0">Status</th>
                    <th class="text-center px-0">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($contents as $content)
                    <tr>
                        <td class="text-center pe-8">{{ $content->client_id }}</td>
                        <td>{{ $content->title }}</td>
                        <td class="text-start">{{ $content->description }}</td>
                        <td class="text-center">{{ $content->created_at->format('d/m/Y')}}</td>
                        <td class="text-center">
                            @if ($content->progress == 'aberto')
                            <span class="badge badge-light-warning">Aberto</span>
                            @elseif ($content->progress == 'em andamento')
                            <span class="badge badge-light-info">Em Andamento</span>
                            @else
                            <span class="badge badge-light-danger">Fechado</span>
                            @endif
                        </td>
                        <td class="text-center pe-1">
                            @if ($content->status == 0)
                                <span class="badge badge-light-danger">Desabilitado</span>  
                                @else
                                <span class="badge badge-light-success">Habilitado</span>
                            @endif
                        </td>
                        <td>
                            <select name="progress" class="form-select form-select-solid" data-control="select2" data-placeholder="Selecione">
                                <option></option>
                                <option value="aberto">Aberto</option>
                                <option value="em andamento">Em Andamento</option>
                                <option value="fechado">Finalizado</option>
                            </select>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection