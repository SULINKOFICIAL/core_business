@extends('layouts.app')

@section('title', 'Instalação: ' . $client->name)

@section('content')
    <div class="card mb-4 w-600px m-auto">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="w-100">
                    <div class="d-flex align-items-center justify-content-between mb-6">
                        <p class="fw-bold text-gray-700 fs-2x mb-0 text-uppercase lh-1">
                            {{ $client->name }}
                        </p>
                        <p class="fs-6 text-gray-700 fw-bold mb-2">
                            @foreach ($client->domains as $domain)
                                <a href="https://{{ $domain->domain }}" target="_blank" class="badge badge-light-primary me-2">{{ $domain->domain }}</a>
                            @endforeach
                        </p>
                    </div>
                    <div class="separator separator-dashed border-gray-300 my-6"></div>
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-3">
                                <i class="ki-duotone ki-check-square fs-2x text-success">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <p class="fs-6 text-gray-700 fw-bolder mb-0">
                                    Conta registrada no sistema
                                </p>
                            </div>
                            <span class="badge badge-light-success">Concluído</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-3">
                                <i class="ki-duotone ki-check-square fs-2x">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <p class="fs-6 text-gray-700 fw-bolder mb-0">
                                    Criação de subdomínio
                                </p>
                            </div>
                            <div>
                                @if (!$provisioning->installAtLeast('database'))
                                    <span class="badge badge-light-warning" id="step-database">Pendente</span>
                                @else
                                    <span class="badge badge-light-success">Concluído</span>
                                @endif
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-3">
                                <i class="ki-duotone ki-check-square fs-2x">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <p class="fs-6 text-gray-700 fw-bolder mb-0">
                                    Banco de dados modelo clonado
                                </p>
                            </div>
                            @if (!$provisioning->installAtLeast('user_token'))
                                <span class="badge badge-light-warning" id="step-user_token">Pendente</span>
                            @else
                                <span class="badge badge-light-success">Concluído</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-3">
                                <i class="ki-duotone ki-check-square fs-2x">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <p class="fs-6 text-gray-700 fw-bolder mb-0">
                                    Inserção de usuário e token no banco
                                </p>
                            </div>
                            @if (!$provisioning->installAtLeast('modules'))
                                <span class="badge badge-light-warning" id="step-modules">Pendente</span>
                            @else
                                <span class="badge badge-light-success">Concluído</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-3">
                                <i class="ki-duotone ki-check-square fs-2x">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <p class="fs-6 text-gray-700 fw-bolder mb-0">
                                    Configuração de módulos
                                </p>
                            </div>
                            @if (!$provisioning->installAtLeast('finalizing'))
                                <span class="badge badge-light-warning" id="step-finalizing">Pendente</span>
                            @else
                                <span class="badge badge-light-success">Concluído</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-3">
                                <i class="ki-duotone ki-check-square fs-2x">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <p class="fs-6 text-gray-700 fw-bolder mb-0">
                                    Finalização
                                </p>
                            </div>
                            @if (!$provisioning->installAtLeast('completed'))
                                <span class="badge badge-light-warning" id="step-completed">Pendente</span>
                            @else
                                <span class="badge badge-light-success">Concluído</span>
                            @endif
                        </div>
                    </div>
                    @if (!$provisioning->installAtLeast('completed'))
                        <button class="btn btn-success btn-sm fw-bolder w-100 mt-4 text-uppercase" id="run-install">Rodar instalação</button>
                    @else
                        <a href="{{ route('tenants.show', $client->id) }}" class="btn btn-success btn-sm fw-bolder w-100 mt-4 text-uppercase">Ver Cliente</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-footer')
<script>

    /**
     * Inicia a instalação
     */
    $('#run-install').click(function(){

        // Desabilita o botão
        $(this).text('Processando...').removeClass('btn-success').addClass('btn-primary').prop('disabled', true);

        // Executa a instalação
        processInstall();

    });

    /**
     * Processa a instalação
     */
    function processInstall(){

        // Envia a requisição AJAX
        $.ajax({
            url: "{{ route('tenants.install.make', '') }}/" + {{ $client->id }},
            type: 'GET',
            success: function(response){

                // Atualiza etapa
                let badge = $('#step-' + response.step);
                
                // Remove a classe de sucesso
                badge.removeClass('badge-light-warning')
                        .addClass('badge-light-success')
                        .html('Concluído');

                // Remove a classe de sucesso
                badge.closest('.d-flex').find('.ki-duotone').addClass('text-success');

                // Se a etapa for a última
                if(response.step === 'completed'){
                    // Redireciona para a página de instalação
                    window.location.href = "{{ route('tenants.show', '') }}/" + {{ $client->id }};
                } else {
                    
                    // Executa a instalação
                    setTimeout(function() {
                        processInstall();
                    }, 1000);

                }

            },
            error: function(response){

                // Exibe mensagem de erro
                toastr.error(response.responseJSON.message);

                // Desabilita o botão
                $('#run-install').text('Rodar instalação').removeClass('btn-primary').addClass('btn-success').prop('disabled', false);

            }
        });

    }

</script>
@endsection
