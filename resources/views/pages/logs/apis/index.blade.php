@extends('layouts.app')

@section('title', 'Logs APIs')

@section('custom-head')
    @parent
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-okaidia.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js" integrity="sha512-7Z9J3l1+EYfeaPKcGXu3MS/7T+w19WtKQY/n+xzmw4hZhJ9tyYmcUS+4QqAlzhicE5LAfMQSF3iFTK9bQdTxXg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js" integrity="sha512-SkmBfuA2hqjzEVpmnMt/LINrjop3GKWqsuLSSB3e7iBmYK7JuWw4ldmmxwD9mdm2IRTTi0OxSAfEGvgEi0i2Kw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex flex-stack flex-wrap mb-5">
            <div class="d-flex align-items-center position-relative my-1 mb-2 mb-md-0">
                <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6"><span class="path1"></span><span class="path2"></span></i>
                <input type="text" data-kt-docs-table-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Procurar logs da API">
            </div>
            <div class="d-flex justify-content-end" data-kt-docs-table-toolbar="base">
                <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                    <i class="ki-duotone ki-filter fs-2"><span class="path1"></span><span class="path2"></span></i>        Filtrar
                </button>
                <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                    <form action="" id="logs-apis-filters">
                        <div class="px-7 py-5"><div class="fs-4 text-gray-900 fw-bold">Filtros</div></div>
                        <div class="separator border-gray-200"></div>
                        <div class="px-7 py-5">
                            <div class="mb-10">
                                <label class="form-label fs-5 fw-semibold mb-3">Status:</label>
                                <div class="d-flex flex-column flex-wrap fw-semibold">
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3 me-5">
                                        <input class="form-check-input" type="radio" name="log_status" value="all" checked>
                                        <span class="form-check-label text-gray-600">Todos</span>
                                    </label>
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3 me-5">
                                        <input class="form-check-input" type="radio" name="log_status" value="Processado">
                                        <span class="form-check-label text-gray-600">Processado</span>
                                    </label>
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="radio" name="log_status" value="Erro">
                                        <span class="form-check-label text-gray-600">Erro</span>
                                    </label>
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
        <table id="datatables-logs-apis" data-dt-manual="true" class="table table-striped table-row-bordered gy-2 gs-7 align-middle">
            <thead class="rounded">
                <tr class="fw-bold fs-6 text-gray-700 px-7">
                    <th class="text-start">ID</th>
                    <th class="text-start">API</th>
                    <th class="text-start">Cliente</th>
                    <th class="text-start">JSON</th>
                    <th class="text-center">Reprocessado</th>
                    <th class="text-center">Status</th>
                    <th class="text-start">Despachado Em</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@section('modals')
<div class="modal fade" tabindex="-1" id="modal_json">
    <div class="modal-dialog modal-dialog-centered mw-1000px">
        <div class="modal-content">
            <div class="modal-header py-3 bg-dark border-0">
                <h5 class="modal-title text-white">Visualizando JSON</h5>
                <div class="btn btn-icon bg-dark ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <span class="svg-icon svg-icon-2x fw-bolder">X</span>
                </div>
            </div>
            <div class="modal-body p-0">
                {{-- JSON HERE --}}
                {{-- JSON HERE --}}
                {{-- JSON HERE --}}
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-footer')
@parent
<script>
    /**
     * Abre a fatura desejada baseado em qual foi clicado.
     */
    $(document).on('click', '.open-json', function(e) {

        // Obtém o ID do JSON
        var id = $(this).data('json');

        // AJAX
        $.ajax({
            url: "{{ route('logs.apis.show', '') }}/" + id,
            type: 'GET',
            success: function(response) {

                // Formata o JSON e insere no modal
                var formattedJson = JSON.stringify(JSON.parse(response), null, 4);
                var highlightedJson = `<pre class="m-0 rounded-0 rounded-bottom-2"><code class="language-json">${formattedJson}</code></pre>`;

                $('#modal_json .modal-body').html(highlightedJson);
                $('#modal_json').modal('show');

                // Ativa o Prism.js após o conteúdo ser carregado
                Prism.highlightAll();
            },
            error: function(xhr) {
                $('#modal_json .modal-body').html('<p class="text-danger">Erro ao carregar JSON.</p>');
                $('#modal_json').modal('show');
            }
        });
        
    });

    // Carrega tabelas
    const dataTable = $('#datatables-logs-apis').DataTable({
        serverSide: true,
        processing: true,
        pageLength: 25,
        ajax: {
            url: '{{ route("logs.apis.process") }}',
            data: function (data) {
                data.searchBy = data.search.value;
                data.order_by = data.columns[data.order[0].column].data;
                data.log_status = $('input[name="log_status"]:checked').val();
            },
        },
        order: [[0, 'desc']],
        columns: [
            { data: 'id', name: 'id' },
            { data: 'api', name: 'api' },
            { data: 'client', name: 'client' },
            { data: 'json', name: 'json', orderable: false, searchable: false },
            { data: 'reprocessed', name: 'reprocessed', orderable: false, searchable: false, className: 'text-center' },
            { data: 'status', name: 'status', orderable: false, searchable: false, className: 'text-center' },
            { data: 'dispatched_at', name: 'dispatched_at' },
        ],
        pagingType: 'simple_numbers',
    });

    $('[data-kt-docs-table-filter="search"]').on('keyup', function () {
        dataTable.search($(this).val()).draw();
    });

    $('#logs-apis-filters').on('submit', function(e) {
        e.preventDefault();
        dataTable.ajax.reload();
    });

    $('#logs-apis-filters').on('reset', function() {
        setTimeout(() => dataTable.ajax.reload(), 0);
    });
</script>
@endsection
