@extends('layouts.app')

@section('title', 'Sugestões')

@section('content')
<p class="text-center fw-bold text-gray-700 fs-2 mb-4 text-uppercase">
    Sugestões de Clientes
</p>
<div class="card">
    <div class="card-body">
        <table id="datatables-suggestions" data-dt-manual="true" class="table table-striped table-row-bordered gy-2 gs-7 align-middle">
            <thead class="rounded">
                <tr class="fw-bold fs-6 text-gray-700 px-7">
                    <th class="text-start" width="5%">Cliente</th>
                    <th class="text-start" width="15%">Nome</th>
                    <th class="text-start px-0">Descrição</th>
                    <th class="text-center px-0">Criado Em</th>
                    <th class="text-center px-0">Progresso</th>
                    <th class="text-center px-0">Status</th>
                    <th class="text-center px-0">Ações</th>
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
        const dataTable = $('#datatables-suggestions').DataTable({
            serverSide: true,
            processing: true,
            ajax: '{{ route("suggestions.process") }}',
            order: [[3, 'desc']],
            columns: [
                { data: 'client_id', name: 'client_id', className: 'text-center pe-8' },
                { data: 'name', name: 'name' },
                { data: 'description', name: 'description', className: 'text-start' },
                { data: 'created_at', name: 'created_at', className: 'text-center' },
                { data: 'progress_badge', name: 'progress', orderable: false, searchable: false, className: 'text-center ticket-progress' },
                { data: 'status_label', name: 'status', orderable: false, searchable: false, className: 'text-center pe-1' },
                { data: 'actions', orderable: false, searchable: false },
            ],
            pagingType: 'simple_numbers',
        });

        // Detecta a alteração no select (evento delegado para linhas dinâmicas)
        $(document).on('change', '.js-suggestion-progress', function() {
            var progress = $(this).val();
            var id = $(this).data('id');
            var url = "{{ route('suggestions.update', '') }}/" + id;

            // Envia a requisição AJAX
            $.ajax({
                url: url,
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    progress: progress
                },
                success: function(response) {
                    var progressBadge = '';
                    if (progress == 'aberto') {
                        progressBadge = '<span class="badge badge-light-warning">Aberto</span>';
                    } else if (progress == 'em andamento') {
                        progressBadge = '<span class="badge badge-light-info">Em Andamento</span>';
                    } else if (progress == 'fechado') {
                        progressBadge = '<span class="badge badge-light-danger">Fechado</span>';
                    }

                    $('tr').find('select[data-id="' + id + '"]').closest('tr').find('.ticket-progress').html(progressBadge);

                    toastr.success(response.message);
                },
            });
        });
    });
</script>
@endsection
