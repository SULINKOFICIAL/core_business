@extends('layouts.app')

@section('title', 'Cupons')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1">Cupons</h3>
                <span class="text-muted">Gerencie os cupons de desconto da central</span>
            </div>
            <div class="d-flex gap-2">
                <div class="text-end text-muted">
                    Total: {{ $coupons->total() }}
                </div>
                <a href="{{ route('coupons.create') }}" class="btn btn-primary btn-active-success">
                    Novo cupom
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
    $('#datatables-coupons').DataTable({
        serverSide: true,
        processing: true,
        ajax: '{{ route("coupons.process") }}',
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
        language: {
            "search": "Pesquisar:",
            "zeroRecords": "Ops, não encontramos nenhum resultado :(",
            "info": "Mostrando _START_ até _END_ de _TOTAL_ registros",
            "infoEmpty": "Nenhum registro disponível",
            "infoFiltered": "(Filtrando _MAX_ registros)",
            "processing": "Carregando dados...",
            "paginate": {
                "previous": "Anterior",
                "next": "Próximo"
            }
        }
    });
</script>
@endsection
