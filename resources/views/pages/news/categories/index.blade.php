@extends('layouts.app')

@section('title', 'Categorias de Notícias')

@section('content')
<div class="card">
    <div class="card-body">
        <table id="datatables-news-categories" data-dt-manual="true" class="table table-striped table-row-bordered gy-2 gs-7 align-middle">
            <thead class="rounded">
                <tr class="fw-bold fs-6 text-gray-700 px-7">
                    <th class="text-start" style="width: 40%">Nome</th>
                    <th class="text-start">Preview</th>
                    <th class="text-start">Status</th>
                    <th class="text-start">Criado Em</th>
                    <th class="text-start" style="width: 10%">Ações</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<div class="d-flex justify-content-between mt-4">
    <a href="{{ route('news.categories.create') }}" class="btn btn-sm btn-primary btn-active-success">
        Adicionar Categoria
    </a>
</div>
@endsection

@section('custom-footer')
<script>
    $('#datatables-news-categories').DataTable({
        serverSide: true,
        processing: true,
        ajax: '{{ route("news.categories.process") }}',
        order: [[3, 'desc']],
        columns: [
            { data: 'name', name: 'name' },
            { data: 'preview', orderable: false, searchable: false },
            { data: 'status_label', name: 'status', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' },
        ],
        pagingType: 'simple_numbers',
    });
</script>
@endsection
