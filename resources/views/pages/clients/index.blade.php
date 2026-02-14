@extends('layouts.app')

@section('title', 'Painel')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-stack flex-wrap mb-5">
                <div class="d-flex align-items-center position-relative my-1 mb-2 mb-md-0">
                    <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6"><span class="path1"></span><span class="path2"></span></i>
                    <input type="text" data-kt-docs-table-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Procurar clientes">
                </div>
                <div class="d-flex justify-content-end" data-kt-docs-table-toolbar="base">
                <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                    <i class="ki-duotone ki-filter fs-2"><span class="path1"></span><span class="path2"></span></i>        Filtrar
                </button>
                <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true" id="kt-toolbar-filter">
                    <div class="px-7 py-5">
                        <div class="fs-4 text-gray-900 fw-bold">Filtros</div>
                    </div>
                    <div class="separator border-gray-200"></div>
                    <div class="px-7 py-5">
                        <div class="mb-10">
                            <label class="form-label fs-5 fw-semibold mb-3">Tipo de Instalação:</label>
                            <div class="d-flex flex-column flex-wrap fw-semibold" data-kt-docs-table-filter="payment_type">
                                <label class="form-check form-check-sm form-check-custom form-check-solid mb-3 me-5">
                                    <input class="form-check-input" type="radio" name="payment_type" value="all" checked="checked">
                                    <span class="form-check-label text-gray-600">
                                        Todos
                                    </span>
                                </label>

                                <label class="form-check form-check-sm form-check-custom form-check-solid mb-3 me-5">
                                    <input class="form-check-input" type="radio" name="payment_type" value="shared">
                                    <span class="form-check-label text-gray-600">
                                        Compartilhada
                                    </span>
                                </label>

                                <label class="form-check form-check-sm form-check-custom form-check-solid mb-3">
                                    <input class="form-check-input" type="radio" name="payment_type" value="dedicated">
                                    <span class="form-check-label text-gray-600">
                                        Dedicada
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="reset" class="btn btn-light btn-active-light-primary me-2" data-kt-menu-dismiss="true" data-kt-docs-table-filter="reset">Reset</button>
                            <button type="submit" class="btn btn-primary" data-kt-menu-dismiss="true" data-kt-docs-table-filter="filter">Aplicar</button>
                        </div>
                    </div>
                </div>
                <a href="{{ route('clients.create') }}" type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-original-title="Coming Soon" data-kt-initialized="1">
                    <i class="ki-duotone ki-plus fs-2"></i> Criar Sistema
                </a>
            </div>

            <div class="d-flex justify-content-end align-items-center d-none" data-kt-docs-table-toolbar="selected">
                <div class="fw-bold me-5">
                    <span class="me-2" data-kt-docs-table-select="selected_count"></span> Selected
                </div>

                <button type="button" class="btn btn-danger" data-kt-docs-table-select="delete_selected">
                    Selection Action
                </button>
            </div>
        </div>
        <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle datatables">
            <thead class="rounded">
                <tr class="fw-bold fs-6 text-gray-700 px-7">
                    <th class="">Nome do Cliente</th>
                    <th class="text-center px-0">Tipo de Instalação</th>
                    <th class="text-center px-0">Expira em</th>
                    <th class="text-center px-0">Banco</th>
                    <th class="text-center px-0">GIT</th>
                    <th class="text-center px-0">Status</th>
                    <th class="text-end pe-12 w-100px">Ações</th>
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
                                
                                <a href="#" class="btn btn-light-primary btn-active-light-primary btn-sm" data-kt-menu-trigger="hover" data-kt-menu-placement="bottom-end" data-kt-menu-flip="top-end">
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
@endsection