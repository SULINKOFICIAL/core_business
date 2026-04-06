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
                                <div class="d-flex flex-column flex-wrap fw-semibold" data-kt-docs-table-filter="status_filter">

                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3 me-5">
                                        <input class="form-check-input" type="radio" name="status_filter" value="1" checked>
                                        <span class="form-check-label text-gray-600">
                                            Ativos
                                        </span>
                                    </label>

                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="radio" name="status_filter" value="0">
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
                <a href="{{ route('tenants.create') }}" type="button" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-original-title="Coming Soon" data-kt-initialized="1">
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
                    <th class="text-center px-0">SP</th>
                    <th class="text-center px-0">Status</th>
                    <th class="text-end pe-12 w-100px">Ações</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@section('modals')
<div class="modal fade" tabindex="-1" id="modal_client_run_task">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="form-client-run-task" method="GET" action="">
                <div class="modal-header">
                    <h3 class="modal-title">Executar Tarefa</h3>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="text-gray-700 mb-5">
                        Escolha qual tarefa deseja executar para o cliente
                        <span class="fw-bold" id="selected-client-name">-</span>.
                    </div>

                    <div class="mb-4">
                        <label for="scheduled-job-select" class="form-label fw-semibold">Tarefa</label>
                        <select
                            name="job"
                            id="scheduled-job-select"
                            class="form-select form-select-solid"
                            data-control="select2"
                            data-dropdown-parent="#modal_client_run_task"
                            data-placeholder="Selecione uma tarefa"
                        >
                            <option value="all">Todas as tarefas</option>
                            <option value="finish_calls_24h">Finalizar chamadas com 24h</option>
                            <option value="finish_order_access">Finalizar acessos de pedidos</option>
                            <option value="update_s3_metrics">Atualizar métricas do S3</option>
                            <option value="archive_finished_tasks">Arquivar tarefas concluídas</option>
                            <option value="refresh_mercado_livre">Atualizar token Mercado Livre</option>
                            <option value="notify_commitments_10m">Notificar compromissos (10 min antes)</option>
                            <option value="test_log">Registrar log de teste</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Executar</button>
                </div>
            </form>
        </div>
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
            url: '{{ route("tenants.process") }}',
            data: function (data) {
                data.searchBy       = data.search.value;
                data.order_by       = data.columns[data.order[0].column].data;
                data.per_page       = data.length;
                data.payment_type   = $('input[name="payment_type"]:checked').val();
                data.status_filter  = $('input[name="status_filter"]:checked').val();
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
            { targets: 5, data: "sp" },
            { targets: 6, data: "status" },
            { targets: 7, data: "actions", orderable: false },
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
                className: 'text-center',
            },
            {   
                targets: 7,
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

    // Inicializa select2 do modal de tarefas.
    $('#scheduled-job-select').select2({
        dropdownParent: $('#modal_client_run_task'),
        width: '100%',
    });

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

    // Abre o modal para escolher qual tarefa executar no cliente.
    $(document).on('click', '.js-client-run-task-modal', function (e) {
        e.preventDefault();

        const trigger = $(this);
        const clientName = trigger.data('client-name') || 'cliente';
        const actionUrl = trigger.attr('href');

        // Atualiza o formulário do modal com o cliente selecionado.
        $('#selected-client-name').text(clientName);
        $('#form-client-run-task').attr('action', actionUrl);
        $('#scheduled-job-select').val('all').trigger('change');

        $('#modal_client_run_task').modal('show');
    });

    // Confirma ações do menu do cliente (exceto "Acessar como sistema")
    $(document).on('click', '.js-client-action-confirm', function (e) {
        const trigger = $(this);
        const clientName = trigger.data('client-name') || 'cliente';
        const actionLabel = trigger.data('action-label') || 'executar esta ação';
        const shouldProceed = window.confirm(`Deseja mesmo ${actionLabel} no cliente ${clientName}?`);

        if (!shouldProceed) {
            e.preventDefault();
        }
    });

    // Ativa/desativa cliente via AJAX na listagem
    $(document).on('click', '.js-client-toggle-status', function (e) {
        e.preventDefault();

        const trigger = $(this);
        const clientName = trigger.data('client-name') || 'cliente';
        const actionLabel = trigger.data('action-label') || 'alterar status';
        const shouldProceed = window.confirm(`Deseja mesmo ${actionLabel} no cliente ${clientName}?`);

        if (!shouldProceed) {
            return;
        }

        const url = trigger.attr('href');

        $.ajax({
            url: url,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            success: function (response) {
                const message = response && response.message ? response.message : 'Status do cliente atualizado com sucesso.';
                toastr.success(message);
                dataTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Não foi possível atualizar o status do cliente.';
                toastr.error(message);
            }
        });
    });

</script>
@endsection
