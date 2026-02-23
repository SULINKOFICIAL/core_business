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
                <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                    <form action="" id="clients-filters">
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
                            <div class="mb-10">
                                <label class="form-label fs-5 fw-semibold mb-3">Status:</label>
                                <div class="d-flex flex-column flex-wrap fw-semibold" data-kt-docs-table-filter="client_status">

                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3 me-5">
                                        <input class="form-check-input" type="radio" name="client_status" value="1" checked>
                                        <span class="form-check-label text-gray-600">
                                            Ativos
                                        </span>
                                    </label>

                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="radio" name="client_status" value="0">
                                        <span class="form-check-label text-gray-600">
                                            Inativos
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="reset" class="btn btn-light btn-active-light-primary me-2" data-kt-menu-dismiss="true" data-kt-docs-table-filter="reset">Reset</button>
                                <button type="submit" class="btn btn-primary" data-kt-menu-dismiss="true" data-kt-docs-table-filter="filter">Aplicar</button>
                            </div>
                        </div>
                    </form>
                </div>
                <a href="{{ route('clients.create') }}" type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-original-title="Coming Soon" data-kt-initialized="1">
                    <i class="ki-duotone ki-plus fs-2"></i> Criar Sistema
                </a>
            </div>
        </div>
        <table id="datatables-clients" class="table table-striped table-row-bordered gy-2 gs-7 align-middle">
            <thead class="rounded">
                <tr class="fw-bold fs-6 text-gray-700 px-7">
                    <th class="">Nome do Cliente</th>
                    <th class="">Tipo de Instalação</th>
                    <th class="">Expira em</th>
                    <th class="text-center px-0">DB</th>
                    <th class="text-center px-0">GIT</th>
                    <th class="text-center px-0">Status</th>
                    <th class="text-end pe-12 w-100px">Ações</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@section('custom-footer')
<script>
    // Seleciona a tabela
    const table = $('#datatables-clients');

    // Configurações da tabela
    const dataTableOptions = {
        serverSide: true,
        processing: true,
        ajax: {
            url: '{{ route("clients.process") }}',
            data: function (data) {
                data.searchBy       = data.search.value;
                data.order_by       = data.columns[data.order[0].column].data;
                data.per_page       = data.length;
                data.payment_type   = $('input[name="payment_type"]:checked').val();
                data.client_status  = $('input[name="client_status"]:checked').val();
            },
        },
        buttons: false,
        searching: true,
        order: [[0, 'desc']],
        pageLength: 25,
        columns: [
            { targets: 0, data: "name" },
            { targets: 1, data: "type" },
            { targets: 2, data: "expires_at" },
            { targets: 3, data: "bank" },
            { targets: 4, data: "git" },
            { targets: 5, data: "status" },
            { targets: 6, data: "actions", orderable: false },
        ],
        language: {
            "search": "Pesquisar:",
            "zeroRecords": "Ops, não encontramos nenhum resultado :(",
            "info": "Mostrando _START_ até _END_ de _TOTAL_ registros",
            "infoEmpty": "Nenhum registro disponível",
            "infoFiltered": "(Filtrando _MAX_ registros)",
            "processing": "Carregando dados...",
            "paginate": {
                "previous": "Anterior",
                "next": "Próximo",
                "first": '<i class="fa-solid fa-angles-left text-gray-300 text-hover-primary cursor-pointer"></i>',
                "last": '<i class="fa-solid fa-angles-right text-gray-300 text-hover-primary cursor-pointer"></i>',
            }
        },
        columnDefs: [
            {   
                targets: 3,
                className: 'text-center',
            },
            {   
                targets: 4,
                className: 'text-center',
            },
            {   
                targets: 5,
                className: 'text-center',
            },
            {   
                targets: 6,
                className: 'text-end',
            },
        ],
        pagingType: 'simple_numbers',
        drawCallback: function () {
            // Inicializa os menus após cada renderização da tabela
            KTMenu.createInstances();
        },
    };

    // Renderiza tabela
    const dataTable = table.DataTable(dataTableOptions);

    // Busca pelo campo no topo
    $('[data-kt-docs-table-filter="search"]').on('keyup', function () {
        dataTable.search($(this).val()).draw();
    });

    // Filtra a tabela
    $('#clients-filters').on('submit', function(e) {
        e.preventDefault();
        dataTable.ajax.reload();
    });

    // Reseta filtros do menu
    $('#clients-filters').on('reset', function() {
        setTimeout(() => dataTable.ajax.reload(), 0);
    });
</script>
@endsection
