@extends('layouts.app')

@section('title', 'Painel')

@section('content')
<div class="card">
    <div class="card-body">
        <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle datatables">
            <thead class="rounded" style="background: #1c283e">
                <tr class="fw-bold fs-6 text-white px-7">
                    <th class="">Nome do Cliente</th>
                    <th class="text-center px-0">Tipo de Instalação</th>
                    <th class="text-center px-0">Plano</th>
                    <th class="text-center px-0">Criado Em</th>
                    <th class="text-center px-0">Banco</th>
                    <th class="text-center px-0">GIT</th>
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
                            <br>
                            @if ($client->domains()->count() > 0)
                                <a href="https://{{ $client->domains()->first()->domain }}" target="_blank" class="text-gray-600 text-hover-primary m-0 text-center">
                                    {{ $client->domains()->first()->domain }} 
                                </a>
                                @if ($client->domains()->count() > 1)
                                    <i class="fa-solid fa-circle-plus text-gray-500 fs-9"></i>
                                @endif
                            @else
                                <span class="badge badge-light-danger">Sem domínio</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($client->type_installation == 'shared')
                                <span class="badge badge-success">Compartilhada</span>
                            @elseif ($client->type_installation == 'dedicated')
                                <span class="badge badge-info">Dedicada</span>
                            @else
                                <span class="badge badge-secondary">Desconhecido</span>
                            @endif
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
                                <i class="fa-solid fa-circle-xmark text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $client->db_error }}"></i>
                            @else
                                <i class="fa-solid fa-circle-check text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Banco de dados atualizado"></i>
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($client->git_last_version == 0)
                                <i class="fa-solid fa-circle-xmark text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $client->git_error }}"></i>
                            @else
                                <i class="fa-solid fa-circle-check text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Git atualizado"></i>
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
                                
                                <a href="#" class="btn btn-success btn-active-light-primary btn-sm" data-kt-menu-trigger="hover" data-kt-menu-placement="bottom-end" data-kt-menu-flip="top-end">
                                    <i class="fa-solid fa-ellipsis-vertical p-0"></i>
                                </a>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-200px py-4" data-kt-menu="true">
                                    <div class="menu-item px-3">
                                        <a href="{{ route('systems.update.database', $client->id) }}" class="menu-link px-3">
                                            <i class="fa-solid fa-database me-2"></i>
                                            Atualiza banco de dados
                                        </a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a href="{{ route('systems.update.git', $client->id) }}" class="menu-link px-3">
                                            <i class="fa-solid fa-code me-2"></i>
                                            Atualiza git
                                        </a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a href="{{ route('clients.destroy', $client->id) }}" class="menu-link px-3">
                                            <i class="fa-solid fa-toggle-off me-2"></i>
                                            @if ($client->status == 0)
                                                Ativar
                                            @else
                                                Desativar
                                            @endif
                                        </a>
                                    </div>
                                    @if ($client->domains()->count() > 0)
                                        <div class="menu-item px-3">
                                            <a href="https://{{ $client->domains[0]->domain }}/acessar/{{ $client->token }}" target="_blank" class="menu-link px-3" data-kt-docs-table-filter="delete_row">
                                                <i class="fa-solid fa-globe me-2"></i>
                                                Acessar como sistema
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="d-flex justify-content-between mt-4">
    <a href="{{ route('systems.update.all.systems') }}" class="btn btn-sm btn-success btn-active-danger">
        Atualizar Sistemas
    </a>
    <a href="{{ route('clients.create') }}" class="btn btn-sm btn-primary btn-active-success">
        Adicionar Cliente
    </a>
</div>
@endsection