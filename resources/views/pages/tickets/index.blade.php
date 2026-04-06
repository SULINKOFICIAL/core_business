@extends('layouts.app')

@section('title', 'Tickets')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Tickets de Clientes
</p>
<div class="card">
    <div class="card-body">
        <div class="d-flex flex-stack flex-wrap mb-5">
            <div class="d-flex align-items-center position-relative my-1 mb-2 mb-md-0">
                <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6"><span class="path1"></span><span class="path2"></span></i>
                <input type="text" data-kt-docs-table-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Procurar tickets">
            </div>
            <div class="d-flex justify-content-end" data-kt-docs-table-toolbar="base">
                <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                    <i class="ki-duotone ki-filter fs-2"><span class="path1"></span><span class="path2"></span></i>        Filtrar
                </button>
                <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                    <form action="" id="tickets-filters">
                        <div class="px-7 py-5"><div class="fs-4 text-gray-900 fw-bold">Filtros</div></div>
                        <div class="separator border-gray-200"></div>
                        <div class="px-7 py-5">
                            <div class="mb-10">
                                <label class="form-label fs-5 fw-semibold mb-3">Progresso:</label>
                                <div class="d-flex flex-column flex-wrap fw-semibold">
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3 me-5"><input class="form-check-input" type="radio" name="progress_filter" value="all" checked="checked"><span class="form-check-label text-gray-600">Todos</span></label>
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3 me-5"><input class="form-check-input" type="radio" name="progress_filter" value="pendente"><span class="form-check-label text-gray-600">Pendente</span></label>
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3 me-5"><input class="form-check-input" type="radio" name="progress_filter" value="em andamento"><span class="form-check-label text-gray-600">Em Andamento</span></label>
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3"><input class="form-check-input" type="radio" name="progress_filter" value="fechado"><span class="form-check-label text-gray-600">Finalizado</span></label>
                                </div>
                            </div>
                            <div class="mb-10">
                                <label class="form-label fs-5 fw-semibold mb-3">Status:</label>
                                <div class="d-flex flex-column flex-wrap fw-semibold">
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3 me-5"><input class="form-check-input" type="radio" name="status_filter" value="1" checked><span class="form-check-label text-gray-600">Ativos</span></label>
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3"><input class="form-check-input" type="radio" name="status_filter" value="0"><span class="form-check-label text-gray-600">Inativos</span></label>
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

        <table id="datatables-tickets" data-dt-manual="true" class="table table-striped table-row-bordered gy-2 gs-7 align-middle">
            <thead class="rounded">
                <tr class="fw-bold fs-6 text-gray-700 px-7">
                    <th class="text-start" width="5%">Cliente</th>
                    <th class="text-start" width="15%">Título</th>
                    <th class="text-start px-0">Descrição</th>
                    <th class="text-center px-0">Criado Em</th>
                    <th class="text-center px-0">Progresso</th>
                    <th class="text-center px-0">Status</th>
                    <th class="text-center px-0">Abrir</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@section('modals')
    @parent
    <div id="modal-ticket-zone"></div>
@endsection

@section('custom-footer')
@parent
<script>
    $(document).ready(function() {
        const dataTable = $('#datatables-tickets').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: '{{ route("tickets.process") }}',
                data: function (data) {
                    data.searchBy = data.search.value;
                    data.order_by = data.columns[data.order[0].column].data;
                    data.progress_filter = $('input[name="progress_filter"]:checked').val();
                    data.status_filter = $('input[name="status_filter"]:checked').val();
                },
            },
            order: [[3, 'desc']],
            columns: [
                { data: 'tenant_id', name: 'tenant_id', className: 'text-center pe-8' },
                { data: 'title', name: 'title' },
                { data: 'description', name: 'description', className: 'text-start' },
                { data: 'created_at', name: 'created_at', className: 'text-center' },
                { data: 'progress_badge', name: 'progress', orderable: false, searchable: false, className: 'text-center ticket-progress' },
                { data: 'status_label', name: 'status', orderable: false, searchable: false, className: 'text-center pe-1' },
                { data: 'actions', orderable: false, searchable: false },
            ],
            pagingType: 'simple_numbers',
        });

        $('[data-kt-docs-table-filter="search"]').on('keyup', function () {
            dataTable.search($(this).val()).draw();
        });

        $('#tickets-filters').on('submit', function(e) {
            e.preventDefault();
            dataTable.ajax.reload();
        });

        $('#tickets-filters').on('reset', function() {
            setTimeout(() => dataTable.ajax.reload(), 0);
        });

        $(document).on('click', '.ticket-view-trigger', function() {
            var id = $(this).data('id');
            var url = "{{ route('tickets.show', '') }}/" + id;

            $.ajax({
                url: url,
                method: 'GET',
                success: function(response) {
                    $('#modal-ticket-zone').html(response);
                    $('#modal_ticket_show').modal('show');
                },
            });
        });

        $(document).on('submit', '#form-ticket-reply', function(e) {
            e.preventDefault();

            var form = $(this);
            var url = form.attr('action');

            $.ajax({
                url: url,
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    toastr.success(response.message);
                    $('#modal_ticket_show').modal('hide');
                    dataTable.ajax.reload(null, false);
                },
            });
        });

        $(document).on('click', '.ticket-finish-submit', function(e) {
            e.preventDefault();

            var form = $('#form-ticket-finish');
            var url = form.attr('action');

            $.ajax({
                url: url,
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    toastr.success(response.message);
                    $('#modal_ticket_show').modal('hide');
                    dataTable.ajax.reload(null, false);
                },
            });
        });
    });
</script>
@endsection
