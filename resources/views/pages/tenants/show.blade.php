@extends('layouts.app')

@section('title', $client->name)

@section('content')
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="me-12">
                    <div class="h-150px w-150px">
                        @if ($client->logo)
                            <img src="{{ asset('storage/tenants/' . $client->id . '/logo.png') }}" alt="Logo do Cliente" class="img-fluid w-100 object-fit-contain rounded shadow">
                        @else
                            <img src="{{ asset('assets/media/images/logo.png') }}" alt="Logo do Cliente" class="img-fluid w-100 object-fit-contain rounded shadow">
                        @endif
                    </div>
                </div>
                <div class="w-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <p class="fw-bold text-gray-700 fs-2x mb-0 text-uppercase lh-1">
                            {{ $client->name }}
                        </p>
                        <div class="d-flex gap-4">
                            <a href="{{ route('systems.update.database', $client->id) }}" class="btn btn-icon btn-sm btn-light-primary">
                                <i class="fa-solid fa-database" data-bs-toggle="tooltip" title="Atualizar banco de dados"></i>
                            </a>
                            <a href="{{ route('tenants.edit', $client->id) }}" class="btn btn-icon btn-sm btn-light-primary">
                                <i class="fa-solid fa-gear" data-bs-toggle="tooltip" title="Configurações"></i>
                            </a>
                        </div>
                    </div>
                    @if ($client->package)
                    <p class="fs-6 text-gray-700 fw-bold mb-1">
                        Valor atual do pacote: <span class="text-success fw-bolder">R$ {{ number_format($client->current_value, 2, ',', '.') }}</span>
                    </p>
                    <p class="fs-6 text-gray-700 fw-bold mb-2">
                        Domínios: 
                        @foreach ($client->domains as $domain)
                            <span class="badge badge-light-primary me-2">{{ $domain->domain }}</span>
                        @endforeach
                    </p>
                    <div class="d-flex gap-3">
                        <div class="alert {{ $client->renovation() <= 5 ? 'alert-danger' : 'alert-success' }} d-flex align-items-center p-2 border-dashed {{ $client->renovation() <= 5 ? 'border-danger' : 'border-success' }} mb-0">
                            <i class="ki-duotone ki-shield-tick fs-1 {{ $client->renovation() <= 5 ? 'text-danger' : 'text-success' }} me-2">
                                <span class="path1">
                                </span><span class="path2">
                                </span>
                            </i>
                            <h6 class="mb-0 {{ $client->renovation() <= 5 ? 'text-danger' : 'text-success' }} fw-normal me-2">
                                Renovação em: 
                                <span class="fw-bolder">{{ $client->renovation() ?? 0 }} dias</span></span>
                            </h4>
                        </div>
                        <div class="alert alert-primary d-flex align-items-center p-2 border-dashed border-primary mb-0">
                            <i class="ki-duotone ki-shield-tick fs-1 text-primary me-2">
                                <span class="path1">
                                </span><span class="path2">
                                </span>
                            </i>
                            <h6 class="mb-0 text-primary fw-normal me-2">
                                Armazenamento:
                                <span class="fw-bolder">{{ number_format($client->package->size_storage / 1073741824, 2) }} GB</span></span>
                            </h6>
                        </div>
                    </div>
                    @else
                    <p class="fs-6 text-danger fw-bold mb-0 mt-2">
                        Nenhum pacote atribuido
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-xl-2">
            <div class="card mb-4">
                <div class="card-body p-6">
                    <p class="fw-bolder text-gray-700 fs-3 text-uppercase">Configuração</p>
                    <p class="text-gray-700 fw-bolder mb-2">
                        Plano atual do tenant #{{ $currentPlanId ?? 'Sem plano' }}
                    </p>
                    @if (count($enabledModules) > 0)
                        @foreach ($enabledModules as $moduleName)
                            <div class="mb-1 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-check text-success"></i>
                                <p class="text-gray-700 mb-0">{{ $moduleName }}</p>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted mb-0">Nenhum item liberado no momento.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-10">
            <div class="d-flex gap-2">
                <button class="btn btn-sm w-200px mb-2 btn-warning btn-sections" data-show="cards">
                    Cartões
                </button>
                <button class="btn btn-sm w-200px mb-2 btn-success btn-sections" data-show="signatures">
                    Assinaturas
                </button>
                <button class="btn btn-sm w-200px mb-2 btn-primary btn-sections" data-show="orders">
                    Histórico de Compras
                </button>
                <button class="btn btn-sm w-200px mb-2 btn-danger btn-sections" data-show="resources">
                    Ver Plano Atual
                </button>
                <button class="btn btn-sm w-200px mb-2 btn-info btn-sections" data-show="api-data" id="btn-api-data">
                    Ver dados em tempo real
                </button>
            </div>
            <div class="divs-sections div-cards" style="display: none;">
                @include('pages.tenants._cards')
            </div>
            <div class="divs-sections div-resources" style="display: none;">
                @include('pages.tenants._resources')
            </div>
            <div class="divs-sections div-orders">
                @include('pages.tenants._orders')
            </div>
            <div class="divs-sections div-signatures" style="display: none;">
                {{-- @include('pages.tenants._signatures') --}}
            </div>
            <div class="divs-sections div-api-data" style="display: none;">
                <div class="card">
                    <div class="card-body" id="api-data-container">
                        <div class="d-flex align-items-center gap-3 text-muted">
                            <span class="spinner-border spinner-border-sm d-none" id="api-data-loading" role="status" aria-hidden="true"></span>
                            <span>Clique em <b>Ver dados em tempo real</b> para consultar a API desta instalação.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('pages.tenants._add_free')
@endsection

@section('custom-footer')
<script>
    $(document).ready(function(){
        /**
         * Define se os dados via API já foram carregados uma vez.
         */
        let hasLoadedApiData = false;

        $(document).on('click', '.btn-sections', function(){

            var section = $(this).data('show');
            
            $('.divs-sections').hide();

            $('.div-' + section).show();

        });

        /**
         * Carrega os dados em tempo real da instalação via API apenas sob demanda.
         * Após o primeiro carregamento com sucesso, reutiliza o HTML já renderizado.
         */
        $(document).on('click', '#btn-api-data', function() {
            if (hasLoadedApiData) {
                return;
            }

            const $loading = $('#api-data-loading');
            const $container = $('#api-data-container');

            $loading.removeClass('d-none');

            $.ajax({
                type: 'GET',
                url: "{{ route('tenants.api.data', $client->id) }}",
                success: function(response) {
                    $container.html(response.html);
                    hasLoadedApiData = true;
                },
                error: function() {
                    $container.html(
                        '<div class="alert alert-danger mb-0">Não foi possível consultar a API desta instalação agora.</div>'
                    );
                },
                complete: function() {
                    $loading.addClass('d-none');
                }
            });
        });
    });
</script>
@endsection
