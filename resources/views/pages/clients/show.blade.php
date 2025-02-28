@extends('layouts.app')

@section('title', $client->name)

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="me-12">
                <div class="h-150px w-150px rounded bg-light d-flex align-items-center justify-content-center p-2">
                @if ($client->logo)
                    <img src="{{ asset('storage/clientes/' . $client->id . '/logo.png') }}" alt="Logo do Cliente" class="img-fluid w-100 object-fit-contain">
                @else
                    <div class="h-50px d-flex align-items-center justify-content-center">
                        <p class="m-0 fs-2x fw-bolder text-gray-300 text-uppercase text-center">Sem logo</p>
                    </div>
                @endif
                </div>
            </div>
            <div>
                <div class="d-flex align-items-center justify-content-between">
                    <p class="fw-bold text-gray-700 fs-2x mb-2 text-uppercase lh-1 d-flex">
                        {{ $client->name }}
                    </p>
                    <a href="{{ route('clients.edit', $client->id) }}" class="text-hover-primary">
                        <i class="fa-solid fa-gear"></i>
                    </a>
                </div>
                <p class="text-gray-600 my-2">
                    Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially  in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
                </p>
                <button class="btn btn-sm btn-primary mt-2" data-show="resources">
                    Histórico de Compras
                </button>
                <button class="btn btn-sm btn-primary mt-2" data-show="resources">
                    Ver Recursos
                </button>
            </div>
        </div>
    </div>
</div>
@if (!$responseApi)
    <div class="alert alert-danger d-flex align-items-center p-5 mb-5">
        <i class="ki-duotone ki-shield-tick fs-2hx text-danger me-4">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
        <div class="d-flex flex-column">
            <h4 class="mb-1 text-danger">Erro na API</h4>
            <span>Aconteceu um erro ao buscar as permissões já habilitadas para esse cliente, verifique se o token esta configurado corretamente e o domínio.</span>
        </div>
    </div>
@endif
<div class="div">
    <div class="card">
        <div class="card-body">
            <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle datatables">
                <thead class="rounded" style="background: #1c283e">
                    <tr class="fw-bold fs-6 text-white px-7">
                        <th>Pacote/Upgrade</th>
                        <th>Descrição</th>
                        <th class="text-center px-0">Inicio</th>
                        <th class="text-center px-0">Fim</th>
                        <th class="text-center px-0">Comprado em</th>
                        <th class="text-center px-0">Valor por usuário</th>
                        <th class="text-center px-0">Valor da Assinatura</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <span class="badge badge-light-danger">Usuários adicionais</span>
                        </td>
                        <td>5</td>
                        <td>
                            15/01/2025
                        </td>
                        <td class="text-center">
                            -
                        </td>
                        <td class="text-center">
                            -
                        </td>
                        <td class="text-center">
                            R$ 23,40
                        </td>
                        <td class="text-center">
                            R$ 187,00
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="badge badge-light-primary">Módulos adicionais</span>
                        </td>
                        <td>Chat</td>
                        <td>
                            15/01/2025
                        </td>
                        <td class="text-center">
                            -
                        </td>
                        <td class="text-center">
                            -
                        </td>
                        <td class="text-center">
                            25,40
                        </td>
                        <td class="text-center">
                            R$ 127,00
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="badge badge-light-success">Pacote Free Trial - Até 5 usuários</span>
                        </td>
                        <td>Módulo Base - Financeiro - Vendas</td>
                        <td>
                            01/01/2025
                        </td>
                        <td class="text-center">
                            29/01/2025
                        </td>
                        <td class="text-center">
                            01/01/2025
                        </td>
                        <td class="text-center">
                            R$ 19,80
                        </td>
                        <td class="text-center">
                            R$ 97,00
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="div-resources" style="display: none;">
    @include('pages.clients._resources')
</div>
@include('pages.clients._upgrade')
@endsection

@section('custom-footer')
<script>
    $(document).ready(function(){
        $(document).on('change', '.input-features', function(){

            // Obtém se esta checado ou não
            var checked = $(this).is(':checked');

            var name = $(this).val();

            // Busca OS
           $.ajax({
                type:'GET',
                url: "{{ route('systems.feature') }}",
                data: {
                    status: checked,
                    client_id: "{{ $client->id }}",
                     name: name,
                },
                success: function(response) {
                    toastr.success('Sucesso');
                },
            });
        });
    });
</script>
@endsection
