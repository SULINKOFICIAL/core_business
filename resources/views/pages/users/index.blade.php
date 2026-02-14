@extends('layouts.app')

@section('title', 'Usuários')

@section('content')
    <p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
        Usuários
    </p>
    <div class="card">
        <div class="card-body">
            <table id="datatables-users" data-dt-manual="true" class="table table-striped table-row-bordered gy-2 gs-7 align-middle">
                <thead class="rounded">
                    <tr class="fw-bold fs-6 text-gray-700 px-7">
                        <th class="text-start" width="35%">Nome</th>
                        <th class="text-start" width="30%">Email</th>
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
        <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary btn-active-success">
            Criar Usuário
        </a>
    </div>
@endsection

@section('custom-footer')
<script>
    $('#datatables-users').DataTable({
        serverSide: true,
        processing: true,
        ajax: '{{ route("users.process") }}',
        order: [[0, 'asc']],
        columns: [
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'created_at', name: 'created_at', className: 'text-center' },
            { data: 'status_label', name: 'status', orderable: false, searchable: false, className: 'text-center' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-center' },
        ],
        pagingType: 'simple_numbers',
    });
</script>
@endsection
