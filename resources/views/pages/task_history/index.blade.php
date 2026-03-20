@extends('layouts.app')

@section('title', 'Histórico de Tarefas')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex flex-stack flex-wrap mb-5">
            <div class="d-flex align-items-center position-relative my-1 mb-2 mb-md-0">
                <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6"><span class="path1"></span><span class="path2"></span></i>
                <input type="text" data-kt-docs-table-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Procurar histórico">
            </div>
            <div class="d-flex justify-content-end" data-kt-docs-table-toolbar="base">
                <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                    <i class="ki-duotone ki-filter fs-2"><span class="path1"></span><span class="path2"></span></i> Filtrar
                </button>
                <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                    <form action="" id="task-history-filters">
                        <div class="px-7 py-5"><div class="fs-4 text-gray-900 fw-bold">Filtros</div></div>
                        <div class="separator border-gray-200"></div>
                        <div class="px-7 py-5">
                            <div class="mb-10">
                                <label class="form-label fs-5 fw-semibold mb-3">Origem:</label>
                                <div class="d-flex flex-column flex-wrap fw-semibold">
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3 me-5">
                                        <input class="form-check-input" type="radio" name="source_filter" value="all" checked>
                                        <span class="form-check-label text-gray-600">Todas</span>
                                    </label>
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3 me-5">
                                        <input class="form-check-input" type="radio" name="source_filter" value="scheduler">
                                        <span class="form-check-label text-gray-600">Agendado</span>
                                    </label>
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="radio" name="source_filter" value="manual">
                                        <span class="form-check-label text-gray-600">Manual</span>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-10">
                                <label class="form-label fs-5 fw-semibold mb-3">Resultado:</label>
                                <div class="d-flex flex-column flex-wrap fw-semibold">
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3 me-5">
                                        <input class="form-check-input" type="radio" name="result_filter" value="all" checked>
                                        <span class="form-check-label text-gray-600">Todos</span>
                                    </label>
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3 me-5">
                                        <input class="form-check-input" type="radio" name="result_filter" value="success">
                                        <span class="form-check-label text-gray-600">Sem falhas</span>
                                    </label>
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3 me-5">
                                        <input class="form-check-input" type="radio" name="result_filter" value="mixed">
                                        <span class="form-check-label text-gray-600">Parcial</span>
                                    </label>
                                    <label class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input" type="radio" name="result_filter" value="failure">
                                        <span class="form-check-label text-gray-600">Somente falha</span>
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="reset" class="btn btn-light btn-active-light-primary me-2" data-kt-menu-dismiss="true">Reset</button>
                                <button type="submit" class="btn btn-primary" data-kt-menu-dismiss="true">Aplicar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <table id="datatables-task-history" class="table table-striped table-row-bordered gy-2 gs-7 align-middle">
            <thead class="rounded">
                <tr class="fw-bold fs-6 text-gray-700 px-7">
                    <th>ID</th>
                    <th>Job</th>
                    <th>Origem</th>
                    <th>Data</th>
                    <th class="text-center">Clientes</th>
                    <th class="text-center">Aceitos</th>
                    <th class="text-center">Falhas</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@section('custom-footer')
<script>
    const table = $('#datatables-task-history');

    const dataTable = table.DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            url: '{{ route("task.history.process") }}',
            data: function (data) {
                data.searchBy = data.search.value;
                data.order_by = data.columns[data.order[0].column].data;
                data.per_page = data.length;
                data.source_filter = $('input[name="source_filter"]:checked').val();
                data.result_filter = $('input[name="result_filter"]:checked').val();
            },
        },
        buttons: false,
        searching: true,
        order: [[0, 'desc']],
        pageLength: 25,
        columns: [
            { targets: 0, data: "id" },
            { targets: 1, data: "job_label" },
            { targets: 2, data: "source_badge" },
            { targets: 3, data: "dispatch_date" },
            { targets: 4, data: "total_clients" },
            { targets: 5, data: "success_count" },
            { targets: 6, data: "failure_count" },
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
                targets: [0, 4, 5, 6],
                className: 'text-center',
            },
            {
                targets: 7,
                className: 'text-end',
            },
        ],
        pagingType: 'simple_numbers',
    });

    $('[data-kt-docs-table-filter="search"]').on('keyup', function () {
        dataTable.search($(this).val()).draw();
    });

    $('#task-history-filters').on('submit', function(e) {
        e.preventDefault();
        dataTable.ajax.reload();
    });

    $('#task-history-filters').on('reset', function() {
        setTimeout(() => dataTable.ajax.reload(), 0);
    });
</script>
@endsection
