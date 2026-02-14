@extends('layouts.app')

@section('title', 'Cupons')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex flex-stack flex-wrap mb-5">
            <div class="d-flex align-items-center position-relative my-1 mb-2 mb-md-0">
                <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6"><span class="path1"></span><span class="path2"></span></i>
                <input type="text" data-kt-docs-table-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Procurar cupons">
            </div>
            <div class="d-flex justify-content-end" data-kt-docs-table-toolbar="base">
                <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                    <i class="ki-duotone ki-filter fs-2"><span class="path1"></span><span class="path2"></span></i>        Filtrar
                </button>
                <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                    <form action="" id="coupons-filters">
                        <div class="px-7 py-5">
                            <div class="fs-4 text-gray-900 fw-bold">Filtros</div>
                        </div>
                        <div class="separator border-gray-200"></div>
                        <div class="px-7 py-5">
                            <div class="mb-10">
                                <label class="form-label fs-5 fw-semibold mb-3">Tipo:</label>
                                <div class="d-flex flex-column flex-wrap fw-semibold">
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3 me-5">
                                        <input class="form-check-input" type="radio" name="coupon_type" value="all" checked="checked">
                                        <span class="form-check-label text-gray-600">Todos</span>
                                    </label>
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3 me-5">
                                        <input class="form-check-input" type="radio" name="coupon_type" value="percent">
                                        <span class="form-check-label text-gray-600">Percentual</span>
                                    </label>
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3 me-5">
                                        <input class="form-check-input" type="radio" name="coupon_type" value="fixed">
                                        <span class="form-check-label text-gray-600">Valor fixo</span>
                                    </label>
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="radio" name="coupon_type" value="trial">
                                        <span class="form-check-label text-gray-600">Trial</span>
                                    </label>
                                </div>
                            </div>
                            <div class="mb-10">
                                <label class="form-label fs-5 fw-semibold mb-3">Status:</label>
                                <div class="d-flex flex-column flex-wrap fw-semibold">
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3 me-5">
                                        <input class="form-check-input" type="radio" name="client_status" value="1" checked>
                                        <span class="form-check-label text-gray-600">Ativos</span>
                                    </label>
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="radio" name="client_status" value="0">
                                        <span class="form-check-label text-gray-600">Inativos</span>
                                    </label>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="reset" class="btn btn-light btn-active-light-primary me-2" data-kt-menu-dismiss="true">Resetar</button>
                                <button type="submit" class="btn btn-primary" data-kt-menu-dismiss="true">Aplicar</button>
                            </div>
                        </div>
                    </form>
                </div>
                <a href="{{ route('coupons.create') }}" type="button" class="btn btn-primary">
                    <i class="ki-duotone ki-plus fs-2"></i> Novo cupom
                </a>
            </div>
        </div>

        <table id="datatables-coupons" data-dt-manual="true" class="table table-striped table-row-bordered gy-2 gs-7 align-middle">
            <thead class="rounded">
                <tr class="fw-bold fs-6 text-gray-700 px-7">
                    <th class="text-start">Código</th>
                    <th class="text-start">Tipo</th>
                    <th class="text-start">Valor</th>
                    <th class="text-start">Validade</th>
                    <th class="text-start">Usos</th>
                    <th class="text-start">Status</th>
                    <th class="text-end"></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@section('custom-footer')
<script>
    const dataTable = $('#datatables-coupons').DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            url: '{{ route("coupons.process") }}',
            data: function (data) {
                data.searchBy = data.search.value;
                data.order_by = data.columns[data.order[0].column].data;
                data.coupon_type = $('input[name="coupon_type"]:checked').val();
                data.client_status = $('input[name="client_status"]:checked').val();
            },
        },
        order: [[0, 'desc']],
        columns: [
            { data: 'code', name: 'code' },
            { data: 'type_label', name: 'type', orderable: false, searchable: false },
            { data: 'amount_label', name: 'amount', orderable: false, searchable: false },
            { data: 'validity', name: 'starts_at', orderable: false, searchable: false },
            { data: 'uses', name: 'redeemed_count', orderable: false, searchable: false },
            { data: 'status_label', name: 'is_active', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' },
        ],
        pagingType: 'simple_numbers',
    });

    $('[data-kt-docs-table-filter="search"]').on('keyup', function () {
        dataTable.search($(this).val()).draw();
    });

    $('#coupons-filters').on('submit', function(e) {
        e.preventDefault();
        dataTable.ajax.reload();
    });

    $('#coupons-filters').on('reset', function() {
        setTimeout(() => dataTable.ajax.reload(), 0);
    });
</script>
@endsection
