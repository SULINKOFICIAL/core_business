@extends('layouts.app')

@section('title', 'Notícias')

@section('content')
<div class="card">
    <div class="card-body">
        <table id="datatables-news" data-dt-manual="true" class="table table-striped table-row-bordered gy-2 gs-7 align-middle">
            <thead class="rounded">
                <tr class="fw-bold fs-6 text-gray-700 px-7">
                    <th class="text-start" style="width: 30%">Título</th>
                    <th class="text-start">Exibir durante</th>
                    <th class="text-start">Categoria</th>
                    <th class="text-start">Prioridade</th>
                    <th class="text-start">Status</th>
                    <th class="text-start" style="width: 10%">Ações</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<div class="d-flex justify-content-between mt-4">
    <a href="{{ route('news.create') }}" class="btn btn-sm btn-primary btn-active-success">
        Adicionar Notícia
    </a>
</div>
@endsection

@section('custom-footer')
<script>
    $('#datatables-news').DataTable({
        serverSide: true,
        processing: true,
        ajax: '{{ route("news.process") }}',
        order: [[0, 'desc']],
        columns: [
            { data: 'title', name: 'title' },
            { data: 'period', orderable: false, searchable: false },
            { data: 'category_label', orderable: false, searchable: false },
            { data: 'priority_label', name: 'priority', orderable: false, searchable: false },
            { data: 'status_label', name: 'status', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' },
        ],
        pagingType: 'simple_numbers',
    });
</script>
@endsection
