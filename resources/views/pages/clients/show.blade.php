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
                        <p class="fw-bold text-gray-700 fs-2x mb-2 text-uppercase lh-1">
                            {{ $client->name }}
                        </p>
                        <a href="{{ route('clients.edit', $client->id) }}" class="text-hover-primary">
                            <i class="fa-solid fa-gear"></i>
                        </a>
                    </div>
                    <p class="fs-6 text-gray-700 fw-bold mb-0">
                        Valor atual do pacote: <span class="text-success fw-bolder">R$ {{ number_format($client->current_value, 2, ',', '.') }}</span>
                    </p>
                    <p class="fs-6 text-gray-700 fw-bold mb-0">
                        Próxima renovação em: <span class="text-primary fw-bolder"> {{ $client->renovation() ?? 0 }}</span> dias
                    </p>
                    <p class="text-gray-600 mb-0">
                        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially  in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-lx-2">
            <div class="card mb-4">
                <div class="card-body p-6">
                    <p class="fw-bolder text-gray-700 fs-3 text-uppercase">Configuração</p>
                    @foreach ($modules as $module)
                    <div class="mb-1 d-flex align-items-center justify-content-between">
                        <p class="text-gray-700 mb-0">{{ Str::limit($module->name, 25) }}</p>
                        @if (in_array($module->id, $client->modules->pluck('id')->toArray()))
                            <i class="fa-solid fa-circle-check text-success"></i>
                        @else
                            <i class="fa-solid fa-circle-check text-danger"></i>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="gap-2">
                <button class="btn btn-sm w-100 mb-4 btn-primary btn-sections" data-show="purschases">
                    Histórico de Compras
                </button>
                <button class="btn btn-sm w-100 mb-4 btn-success btn-sections" data-show="signatures">
                    Assinaturas
                </button>
                <button class="btn btn-sm w-100 mb-4 btn-danger btn-sections" data-show="resources">
                    Ver Recursos
                </button>
                <a href="{{ route('cpanel.migrate', $client->id) }}" class="btn btn-sm w-100 mb-4 btn-info">
                    Atualizar banco
                </a>
            </div>
        </div>
        <div class="col-12 col-lx-10">
            <div class="divs-sections div-resources" style="display: none;">
                @include('pages.clients._resources')
            </div>
            <div class="divs-sections div-purschases">
                @include('pages.clients._purchases')
            </div>
            <div class="divs-sections div-signatures">
                @include('pages.clients._signatures')
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
    @if (!$client->package_id)
    @include('pages.clients._add_package')
    @else
    @include('pages.clients._change_package')
    @endif
    @include('pages.clients._upgrade')
@endsection

@section('custom-footer')
<script>
    $(document).ready(function(){
        
        $(document).on('click', '.btn-sections', function(){

            var section = $(this).data('show');
            
            $('.divs-sections').hide();

            $('.div-' + section).show();

        });
        
        
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
