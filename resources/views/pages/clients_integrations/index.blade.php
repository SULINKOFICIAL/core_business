@extends('layouts.app')

@section('title', 'Integrações de Clientes')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Integrações de Clientes
</p>
<div class="card">
    <div class="card-body">
        <div class="d-flex flex-stack flex-wrap mb-5">
            <div class="d-flex align-items-center position-relative my-1 mb-2 mb-md-0">
                <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6"><span class="path1"></span><span class="path2"></span></i>
                <input type="text" data-kt-docs-table-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Procurar integrações">
            </div>
            <div class="d-flex justify-content-end" data-kt-docs-table-toolbar="base">
                <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                    <i class="ki-duotone ki-filter fs-2"><span class="path1"></span><span class="path2"></span></i> Filtrar
                </button>
                <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                    <form action="" id="clients-integrations-filters">
                        <div class="px-7 py-5"><div class="fs-4 text-gray-900 fw-bold">Filtros</div></div>
                        <div class="separator border-gray-200"></div>
                        <div class="px-7 py-5">
                            <div class="mb-5">
                                <label class="form-label fs-6 fw-semibold mb-2">Provider:</label>
                                <select class="form-select form-select-solid" name="provider_filter">
                                    <option value="all" selected>Todos</option>
                                    <option value="meta_whatsapp">Meta WhatsApp</option>
                                </select>
                            </div>
                            <div class="mb-5">
                                <label class="form-label fs-6 fw-semibold mb-2">Tipo:</label>
                                <select class="form-select form-select-solid" name="type_filter">
                                    <option value="all" selected>Todos</option>
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="instagram">Instagram</option>
                                    <option value="facebook">Facebook</option>
                                    <option value="mercado_livre">Mercado Livre</option>
                                </select>
                            </div>
                            <div class="mb-5">
                                <label class="form-label fs-6 fw-semibold mb-2">Status:</label>
                                <select class="form-select form-select-solid" name="status_filter">
                                    <option value="all" selected>Todos</option>
                                    <option value="active">Ativa</option>
                                    <option value="in_progress">Em progresso</option>
                                    <option value="expired">Expirada</option>
                                    <option value="revoked">Revogada</option>
                                </select>
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

        <table id="datatables-clients-integrations" data-dt-manual="true" class="table table-striped table-row-bordered gy-2 gs-7 align-middle">
            <thead class="rounded">
                <tr class="fw-bold fs-6 text-gray-700 px-7">
                    <th class="text-start">ID</th>
                    <th class="text-start">Cliente</th>
                    <th class="text-start">Provider</th>
                    <th class="text-start">Tipo</th>
                    <th class="text-start">Conta Externa</th>
                    <th class="text-center">Expira Em</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Criado Em</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@section('custom-footer')
@parent
<script>
    $(document).ready(function() {
        const dataTable = $('#datatables-clients-integrations').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: '{{ route("clients.integrations.process") }}',
                data: function (data) {
                    data.searchBy = data.search.value;
                    data.order_by = data.columns[data.order[0].column].data;
                    data.provider_filter = $('[name="provider_filter"]').val();
                    data.type_filter = $('[name="type_filter"]').val();
                    data.status_filter = $('[name="status_filter"]').val();
                },
            },
            order: [[7, 'desc']],
            columns: [
                { data: 'id', name: 'id' },
                { data: 'client', name: 'client' },
                { data: 'provider', name: 'provider' },
                { data: 'type', name: 'type' },
                { data: 'external_account_id', name: 'external_account_id' },
                { data: 'token_expires_at', name: 'token_expires_at', className: 'text-center' },
                { data: 'status_badge', name: 'status', orderable: false, searchable: false, className: 'text-center' },
                { data: 'created_at', name: 'created_at', className: 'text-center' },
            ],
            pagingType: 'simple_numbers',
        });

        $('[data-kt-docs-table-filter="search"]').on('keyup', function () {
            dataTable.search($(this).val()).draw();
        });

        $('#clients-integrations-filters').on('submit', function(e) {
            e.preventDefault();
            dataTable.ajax.reload();
        });

        $('#clients-integrations-filters').on('reset', function() {
            setTimeout(() => dataTable.ajax.reload(), 0);
        });
    });
</script>
@endsection
