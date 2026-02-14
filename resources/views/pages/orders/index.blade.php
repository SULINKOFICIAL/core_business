@extends('layouts.app')

@section('title', 'Pedidos')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <table id="datatables-orders" data-dt-manual="true" class="table table-striped table-row-bordered gy-2 gs-7 align-middle">
            <thead class="rounded">
                <tr class="fw-bold fs-6 text-gray-700 px-7">
                    <th class="text-start">Pedido</th>
                    <th class="text-start">Cliente</th>
                    <th class="text-start">Tipo</th>
                    <th class="text-start">Status</th>
                    <th class="text-start">Itens</th>
                    <th class="text-start">Total</th>
                    <th class="text-start">Criado</th>
                    <th class="text-start">Pago</th>
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
    $('#datatables-orders').DataTable({
        serverSide: true,
        processing: true,
        ajax: '{{ route("orders.process") }}',
        order: [[6, 'desc']],
        columns: [
            { data: 'order_label', name: 'id' },
            { data: 'client_name', orderable: false, searchable: false },
            { data: 'type', name: 'type' },
            { data: 'status_label', name: 'status', orderable: false, searchable: false },
            { data: 'items_count', orderable: false, searchable: false },
            { data: 'total_label', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'paid_at', name: 'paid_at' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' },
        ],
        pagingType: 'simple_numbers',
    });
</script>
@endsection
