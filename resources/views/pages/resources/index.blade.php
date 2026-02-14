@extends('layouts.app')

@section('title', 'Recursos')

@section('content')
    <p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
        Recursos
    </p>
    <div class="card">
        <div class="card-body">
            <table id="datatables-resources" data-dt-manual="true" class="table table-striped table-row-bordered gy-2 gs-7 align-middle">
                <thead class="rounded">
                    <tr class="fw-bold fs-6 text-gray-700 px-7">
                        <th class="text-start" width="60%">Nome</th>
                        <th class="text-center px-0">Slug</th>
                        <th class="text-center px-0">Criado Em</th>
                        <th class="text-center px-0">Status</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>    
<div class="d-flex mt-4">
    <a href="{{ route('resources.create') }}" class="btn btn-sm btn-primary btn-active-success">
        Criar Recurso
    </a>
</div>
@endsection

@section('custom-footer')
<script>
    $('#datatables-resources').DataTable({
        serverSide: true,
        processing: true,
        ajax: '{{ route("resources.process") }}',
        order: [[2, 'desc']],
        columns: [
            { data: 'name', name: 'name' },
            { data: 'slug', name: 'slug', className: 'text-center' },
            { data: 'created_at', name: 'created_at', className: 'text-center' },
            { data: 'status_label', name: 'status', orderable: false, searchable: false, className: 'text-center' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-center' },
        ],
        pagingType: 'simple_numbers',
    });
</script>
@endsection
