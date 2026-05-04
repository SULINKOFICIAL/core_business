@extends('layouts.app')

@section('title', $client->name)

@section('content')
    <div class="card mb-5">
        <div class="card-body py-7">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-6">
                <div class="d-flex align-items-center gap-5">
                    <div class="symbol symbol-90px">
                        @if ($client->logo)
                            <img src="{{ asset('storage/tenants/' . $client->id . '/logo.png') }}" alt="Logo do Cliente" class="object-fit-contain">
                        @else
                            <img src="{{ asset('assets/media/images/logo_dark.webp') }}" alt="Logo do Cliente" class="object-fit-contain">
                        @endif
                    </div>
                    <div>
                        <h1 class="mb-1 fw-bolder text-gray-800 text-uppercase fs-2">{{ $client->name }}</h1>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge badge-light-info"><i class="fa-regular fa-calendar me-1"></i>{{ $periodStart }} até {{ $periodEnd }}</span>
                            <span class="badge badge-light-primary"><i class="fa-solid fa-users me-1"></i>{{ $usersLimit }} usuários</span>
                            <span class="badge badge-light-success"><i class="fa-solid fa-hard-drive me-1"></i>{{ $storageLimitGb }} GB</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('systems.update.database', $client->id) }}" class="btn btn-light-primary btn-sm">
                        <i class="fa-solid fa-database me-1"></i>Atualizar DB
                    </a>
                    <a href="{{ route('tenants.edit', $client->id) }}" class="btn btn-light btn-sm">
                        <i class="fa-solid fa-gear me-1"></i>Configurações
                    </a>
                </div>
            </div>

            @if (!$actualPlan['name'])
                <div class="alert alert-danger mt-5 mb-0">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>Nenhum pacote atribuído.
                </div>
            @endif
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-gray-500 fs-8 fw-semibold text-uppercase">Valor do Pacote</span>
                        <i class="fa-solid fa-money-bill-wave"></i>
                    </div>
                    <div class="fs-2 fw-bolder text-gray-800">R$ {{ number_format($client->current_value, 2, ',', '.') }}</div>
                    <div class="text-gray-500 fs-7">{{ $actualPlan['name'] ?: 'Sem plano' }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-gray-500 fs-8 fw-semibold text-uppercase">Usuário</span>
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="fs-2 fw-bolder text-gray-800">{{ $usersLimit }}</div>
                    <div class="text-gray-500 fs-7">Limite no plano atual</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-gray-500 fs-8 fw-semibold text-uppercase">Armazenamento</span>
                        <i class="fa-solid fa-hard-drive"></i>
                    </div>
                    <div class="fs-2 fw-bolder text-gray-800">{{ $storageLimitGb }} GB</div>
                    <div class="text-gray-500 fs-7">Limite no plano atual</div>
                    <div class="progress h-7px">
                        <div class="progress-bar bg-gray-200 w-75" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-gray-500 fs-8 fw-semibold text-uppercase">Domínios</span>
                        <i class="fa-solid fa-globe"></i>
                    </div>
                    <div class="fs-2 fw-bolder text-gray-800">{{ $client->domains->count() }}</div>
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        @forelse ($client->domains as $domain)
                            <span class="badge badge-light text-gray-600">{{ $domain->domain }}</span>
                        @empty
                            <span class="badge badge-light text-gray-600">Sem domínio</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-5">
        <button class="btn btn-sm btn-light-primary btn-sections active" data-show="resources">
            <i class="fa-solid fa-list-check me-1"></i>Plano Atual
        </button>
        <button class="btn btn-sm btn-light btn-sections" data-show="cards">
            <i class="fa-regular fa-credit-card me-1"></i>Cartões
        </button>
        <button class="btn btn-sm btn-light btn-sections" data-show="signatures">
            <i class="fa-solid fa-file-signature me-1"></i>Assinaturas
        </button>
        <button class="btn btn-sm btn-light btn-sections" data-show="orders">
            <i class="fa-solid fa-clock-rotate-left me-1"></i>Histórico de Compras
        </button>
        <button class="btn btn-sm btn-light btn-sections" data-show="api-data" id="btn-api-data">
            <i class="fa-solid fa-wave-square me-1"></i>Dados em Tempo Real
        </button>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="divs-sections div-resources">
                @include('pages.tenants._resources')
            </div>
            <div class="divs-sections div-cards" style="display: none;">
                @include('pages.tenants._cards')
            </div>
            <div class="divs-sections div-orders" style="display: none;">
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
                            <span>Clique em <b>Dados em Tempo Real</b> para consultar a API desta instalação.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-footer')
<script>
    $(document).ready(function(){
        /**
         * Define se os dados via API já foram carregados uma vez.
         */
        let hasLoadedApiData = false;

        $(document).on('click', '.btn-sections', function(){
            $('.btn-sections').removeClass('btn-light-primary active').addClass('btn-light');
            $(this).removeClass('btn-light').addClass('btn-light-primary active');

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
