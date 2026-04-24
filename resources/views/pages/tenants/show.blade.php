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
                <div class="card-body p-6 pb-4">
                    <p class="fw-bolder text-gray-700 fs-3 text-uppercase">Assinatura</p>
                    <div class="d-flex flex-column align-items-start mb-1">
                        <p class="text-gray-700 fw-bolder mb-1 fs-5">Início</p>
                        <p class="text-gray-600 mb-0 fw-bold fs-7 start-date" data-start-date="{{ isset($allowSubscription['start_date']) ? date('d/m/Y', strtotime($allowSubscription['start_date'])) : '' }}">
                            {{ isset($allowSubscription['start_date']) ? date('d/m/Y', strtotime($allowSubscription['start_date'])) : 'Sem data' }}
                        </p>
                    </div>
                    <div class="d-flex flex-column align-items-start">
                        <p class="text-gray-700 fw-bolder mb-1 fs-5">Fim</p>
                        <div class="d-flex align-items-center gap-1">
                            <p class="text-gray-600 mb-0 fw-bold fs-7 mb-0 text-hover-primary end-date cursor-pointer">
                                {{ isset($allowSubscription['end_date']) ? date('d/m/Y', strtotime($allowSubscription['end_date'])) : 'Sem data' }}
                                <i class="fa-solid fa-pen text-gray-500"></i>
                            </p>
                            <input type="text" class="form-control form-control-sm end-date-input" style="width:150px; display:none;" value="{{ isset($allowSubscription['end_date']) ? date('d/m/Y', strtotime($allowSubscription['end_date'])) : '' }}">
                        </div>
                    </div>
                    <button class="btn btn-sm w-100 h-25px btn-success d-flex align-items-center justify-content-center mt-1" id="update-subscription">
                        Atualizar
                    </button>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-body p-6 px-5 pb-4">
                    <p class="fw-bolder text-gray-700 fs-3 text-uppercase">Armazenamento</p>
                    <div class="d-flex flex-column align-items-start mb-1">
                        <p class="text-gray-700 fw-bolder mb-1 fs-5">Usado</p>
                        <p class="text-gray-600 mb-0 fw-bold fs-7">
                            {{ $totalStorage ?? 0 }} GB
                        </p>
                    </div>
                    <div class="d-flex flex-column align-items-start">
                        <p class="text-gray-700 fw-bolder mb-1 fs-5">Limite</p>
                        <div class="d-flex align-items-center gap-1">
                            <p class="text-gray-600 mb-0 fw-bold fs-7 mb-0 text-hover-primary storage-limit cursor-pointer">
                                {{ $limitStorage ?? 0 }} GB
                                <i class="fa-solid fa-pen text-gray-500"></i>
                            </p>
                            <input type="text" class="form-control form-control-sm storage-limit-input" style="width:150px; display:none;" value="{{ $limitStorage ?? 0 }}">
                        </div>
                    </div>
                    <button class="btn btn-sm w-100 h-25px btn-success d-flex align-items-center justify-content-center mt-1" id="update-storage">
                        Atualizar
                    </button>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-body p-6 pb-4">
                    <p class="fw-bolder text-gray-700 fs-3 text-uppercase">Usuários</p>
                    <div class="d-flex flex-column align-items-start mb-1">
                        <p class="text-gray-700 fw-bolder mb-1 fs-5">Total</p>
                        <p class="text-gray-600 mb-0 fw-bold fs-7">
                            {{ $totalUsers }}
                        </p>
                    </div>
                    <div class="d-flex flex-column align-items-start">
                        <p class="text-gray-700 fw-bolder mb-1 fs-5">Limite</p>
                        <div class="d-flex align-items-center gap-1">
                            <p class="text-gray-600 mb-0 fw-bold fs-7 mb-0 text-hover-primary users-limits cursor-pointer">
                                {{ $limitUsers }}
                                <i class="fa-solid fa-pen text-gray-500"></i>
                            </p>
                            <input type="text" class="form-control form-control-sm users-limits-input" style="width:150px; display:none;" value="{{ $limitUsers }}">
                        </div>
                    </div>
                    <button class="btn btn-sm w-100 h-25px btn-success d-flex align-items-center justify-content-center mt-1" id="update-users-limits">
                        Atualizar
                    </button>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-body p-6">
                    <p class="fw-bolder text-gray-700 fs-3 text-uppercase">Configuração</p>
                    @foreach ($modules as $module)
                    <div class="mb-1 d-flex align-items-center justify-content-between">
                        <p class="text-gray-700 mb-0">{{ Str::limit($module->name, 25) }}</p>
                        @if (($allowModules[$module->name] ?? 0) == 1)
                            <i class="fa-solid fa-circle-check text-success"></i>
                        @else
                            <i class="fa-solid fa-circle-check text-danger"></i>
                        @endif
                    </div>
                    @endforeach
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
                    Ver Recursos
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
        </div>
    </div>
    @if ($apiError)
        <div class="alert alert-danger d-flex align-items-center p-5 mb-5">
            <i class="ki-duotone ki-shield-tick fs-2hx text-danger me-4">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-danger">Erro na API</h4>
                <span>Aconteceu um erro ao buscar as permissões já habilitadas para esse cliente, verifique se o token esta configurado corretamente e o domínio.</span>
                <br>
                {{ $apiGetPermissions['message'] ?? 'Erro desconhecido verifique a URL do cliente.' }}
            </div>
        </div>
    @endif
    @if (!$client->package_id)
    @include('pages.tenants._add_package')
    @else
    @include('pages.tenants._change_package')
    @endif
    @include('pages.tenants._upgrade')
    @include('pages.tenants._add_free')
@endsection

@section('custom-footer')
<script>
    $(document).ready(function(){
        
        $(document).on('click', '.btn-sections', function(){

            var section = $(this).data('show');
            
            $('.divs-sections').hide();

            $('.div-' + section).show();

        });
        
        $(document).on('change', '.input-modules', function(){

            // Obtém se esta checado ou não
            var checked = $(this).is(':checked');

            var moduleId = $(this).val();

            // Busca OS
            $.ajax({
                type:'GET',
                url: "{{ route('systems.module') }}",
                data: {
                    status: checked,
                    tenant_id: "{{ $client->id }}",
                    module_id: moduleId,
                },
                success: function(response) {
                    toastr.success('Sucesso');
                },
            });
        });

        // Obtem o input
        const inputDate = $('.end-date-input');

        // Inicializa o flatpickr
        flatpickr(inputDate[0], {
            dateFormat: "d/m/Y",
            defaultDate: inputDate.val(),
            onClose: function(selectedDates, dateStr) {
                $('.end-date').html(dateStr + ' <i class="fa-solid fa-pen text-gray-500"></i>');
                inputDate.hide();
                $('.end-date').show();
            }
        });

        /**
         * Função responsável por alterar a data final da assinatura
         */
        $(document).on('click', '.end-date', function() {

            // Esconde o botão
            $(this).hide();

            // Mostra o input e da foco
            inputDate.show().focus();

            // Abre o flatpickr
            inputDate[0]._flatpickr.open();
            
        });

        /**
         * Função responsável por alterar o limite de usuários  
         */
        $(document).on('click', '.users-limits', function(e) {

            e.stopPropagation();

            // Esconde o botão
            $(this).hide();

            // Mostra o input e da foco
            $('.users-limits-input').show().focus();

        });

        /**
         * Função responsável por atualizar o limite de usuários  
         */
        $(document).on('click', '.users-limits-input, .storage-limit-input', function(e) {

            e.stopPropagation();

        });

        // Clique fora
        $(document).on('click', function() {
            $('.users-limits-input').hide();
            $('.users-limits').show();

            $('.storage-limit-input').hide();
            $('.storage-limit').show();
        });

        /**
         * Função responsável por alterar o limite de armazenamento
         */
        $(document).on('click', '.storage-limit', function(e) {

            e.stopPropagation();

            // Esconde o botão
            $(this).hide();

            // Mostra o input e da foco
            $('.storage-limit-input').show().focus();

        });

        /**
         * Função responsável por atualizar o limite de armazenamento
         */
        $(document).on('click', '#update-storage', function(e) {

            e.stopPropagation();

            // Pega o valor do input
            let storageLimit = $('.storage-limit-input').val();

            // Faz a requisição
            $.ajax({
                type:'GET',
                url: "{{ route('systems.update.size.storage') }}",
                data: {
                    tenant_id: "{{ $client->id }}",
                    storage_limit: storageLimit,
                },
                success: function(response) {
                    if(response.success){
                        toastr.success(response.message);
                        $('.storage-limit').html(storageLimit + ' GB <i class="fa-solid fa-pen text-gray-500"></i>');
                        $('.storage-limit-input').hide();
                        $('.storage-limit').show();
                    }else{
                        toastr.error(response.message);
                    }
                },
            });

        });

        /**
         * Função responsável por atualizar o limite de usuários
         */
        $(document).on('click', '#update-users-limits', function(e) {

            e.stopPropagation();

            // Pega o valor do input
            let usersLimit = $('.users-limits-input').val();

            // Faz a requisição
            $.ajax({
                type:'GET',
                url: "{{ route('systems.users-limits') }}",
                data: {
                    tenant_id: "{{ $client->id }}",
                    users_limit: usersLimit,
                },
                success: function(response) {
                    if(response.success){
                        toastr.success(response.message);
                        $('.users-limits').html(usersLimit + ' <i class="fa-solid fa-pen text-gray-500"></i>');
                        $('.users-limits-input').hide();
                        $('.users-limits').show();
                    }else{
                        toastr.error(response.message);
                    }
                },
            });
        });

        /**
         * Função responsável por atualizar a data final para o cliente no micore
         */
        $(document).on('click', '#update-subscription', function() {
            
            // Pega a data final
            let endDate = $('.end-date-input').val();

            // Pega a data inicial
            let startDate = $('.start-date').data('start-date');

            // Faz a requisição
            $.ajax({
                type:'GET',
                url: "{{ route('systems.subscription') }}",
                data: {
                    tenant_id: "{{ $client->id }}",
                    start_date: startDate ? startDate : "",
                    end_date: endDate ? endDate : "",
                },
                success: function(response) {
                    if(response.success){
                        toastr.success(response.message);
                    }else{
                        toastr.error(response.message);
                    }
                },
            });
        });
    });
</script>
@endsection
