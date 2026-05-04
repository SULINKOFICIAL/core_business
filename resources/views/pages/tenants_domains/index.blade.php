@extends('layouts.app')

@section('title', 'Domínios das Instalações')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex flex-stack flex-wrap mb-5">
            <div class="d-flex align-items-center position-relative my-1 mb-2 mb-md-0">
                <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6"><span class="path1"></span><span class="path2"></span></i>
                <input type="text" data-kt-domains-table-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Procurar domínio">
            </div>

            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#modal-add-tenant-domain">
                    <i class="fa-solid fa-plus fs-7 me-1"></i>Adicionar domínio
                </button>
                <a href="{{ route('tenants.index') }}" class="btn btn-light me-2">Instalações</a>
            </div>
        </div>

        <table id="datatables-tenant-domains" class="table table-striped table-row-bordered gy-2 gs-7 align-middle">
            <thead class="rounded">
                <tr class="fw-bold fs-6 text-gray-700 px-7">
                    <th width="120px">ID</th>
                    <th>Domínio</th>
                    <th width="280px">Tenant</th>
                    <th width="140px">Status</th>
                    <th width="180px">Atualizado</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@section('modals')
<div class="modal fade" tabindex="-1" id="modal-add-tenant-domain">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('tenants.domains.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h3 class="modal-title">Adicionar domínio ao cliente</h3>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <label for="tenant_id" class="form-label fw-semibold">Cliente</label>
                        <select name="tenant_id" id="tenant_id" class="form-select form-select-solid" required>
                            <option value="">Selecione</option>
                            @foreach ($tenants as $tenant)
                                <option value="{{ $tenant->id }}" @selected(old('tenant_id') == $tenant->id)>{{ $tenant->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="domain" class="form-label fw-semibold">Domínio</label>
                        <input type="text" name="domain" id="domain" class="form-control form-control-solid" value="{{ old('domain') }}" placeholder="exemplo.seudominio.com.br" required>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold">Descrição</label>
                        <input type="text" name="description" id="description" class="form-control form-control-solid" value="{{ old('description') }}" placeholder="Descrição opcional">
                    </div>

                    <div class="form-check form-switch form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" name="status" id="status" value="1" @checked(old('status', '1') == '1')>
                        <label class="form-check-label fw-semibold" for="status">Domínio ativo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('custom-footer')
<script>
    const table = $('#datatables-tenant-domains');

    const dataTable = table.DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            url: '{{ route("tenants.domains.process") }}',
            data: function (data) {
                data.searchBy = data.search.value;
                data.order_by = data.columns[data.order[0].column].data;
                data.per_page = data.length;
            },
        },
        buttons: false,
        searching: true,
        order: [[0, 'desc']],
        pageLength: 25,
        columns: [
            { data: 'id', name: 'id' },
            { data: 'domain', name: 'domain' },
            { data: 'tenant', name: 'tenant' },
            { data: 'status', name: 'status' },
            { data: 'updated_at', name: 'updated_at' },
        ],
        language: {
            search: "Pesquisar:",
            zeroRecords: "Ops, não encontramos nenhum resultado :(",
            info: "Mostrando _START_ até _END_ de _TOTAL_ registros",
            infoEmpty: "Nenhum registro disponível",
            infoFiltered: "(Filtrando _MAX_ registros)",
            processing: "Carregando dados...",
            paginate: {
                previous: "Anterior",
                next: "Próximo",
                first: '<i class="fa-solid fa-angles-left text-gray-300 text-hover-primary cursor-pointer"></i>',
                last: '<i class="fa-solid fa-angles-right text-gray-300 text-hover-primary cursor-pointer"></i>',
            }
        },
        pagingType: 'simple_numbers',
    });

    $('[data-kt-domains-table-filter="search"]').on('keyup', function () {
        dataTable.search($(this).val()).draw();
    });

    @if ($errors->any())
        $('#modal-add-tenant-domain').modal('show');
    @endif
</script>
@endsection
