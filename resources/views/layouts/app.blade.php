<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <title>@yield('title') - All.Core</title>
	    @include('layouts.head')
    </head>
	<body id="kt_app_body" data-kt-app-layout="dark-sidebar" data-kt-app-header-fixed="true" class="app-default">
        @include('layouts.config')
		<div class="d-flex flex-column flex-root app-root" >
			<div class="app-page flex-column flex-column-fluid">
				@include('layouts.header')
				<div class="app-wrapper flex-column flex-row-fluid">
					<div class="app-main flex-column flex-row-fluid py-10">
                        <div class="container container-fluid">
						    @yield('content')
                        </div>
                    </div>
				</div>
			</div>
		</div>
	        @yield('modals')
            @include('includes.modals.icons')
	        <div class="modal fade" tabindex="-1" id="modal_update_systems_actions" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="GET" action="{{ route('systems.update.all.systems') }}" id="form_update_systems_actions">
                        <div class="modal-header">
                            <h3 class="modal-title">Atualizar sistemas</h3>
                            <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                                <i class="fa-solid fa-xmark fs-2"></i>
                            </div>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted mb-4">Escolha o que deseja executar nos sistemas ativos:</p>
                            <div class="bg-light rounded-4 mb-4 p-4">
                                <label class="d-flex align-items-start gap-4 cursor-pointer" for="update_action_git">
                                    <span class="form-check form-check-custom form-check-solid mt-1">
                                        <input class="form-check-input" type="checkbox" name="actions[]" value="git" id="update_action_git" checked>
                                    </span>
                                    <span>
                                        <span class="d-block fw-semibold text-gray-900">Puxar atualizações (Git pull)</span>
                                        <span class="d-block text-muted fs-7">Baixa a versão mais recente do código dos sistemas.</span>
                                    </span>
                                </label>
                            </div>
                            <div class="bg-light rounded-4 mb-4 p-4">
                                <label class="d-flex align-items-start gap-4 cursor-pointer" for="update_action_database">
                                    <span class="form-check form-check-custom form-check-solid mt-1">
                                        <input class="form-check-input" type="checkbox" name="actions[]" value="database" id="update_action_database" checked>
                                    </span>
                                    <span>
                                        <span class="d-block fw-semibold text-gray-900">Atualizar bancos</span>
                                        <span class="d-block text-muted fs-7">Executa as migrations pendentes em cada cliente ativo.</span>
                                    </span>
                                </label>
                            </div>
                            <div class="bg-light rounded-4 mb-4 p-4">
                                <label class="d-flex align-items-start gap-4 cursor-pointer" for="update_action_supervisor">
                                    <span class="form-check form-check-custom form-check-solid mt-1">
                                        <input class="form-check-input" type="checkbox" name="actions[]" value="supervisor" id="update_action_supervisor">
                                    </span>
                                    <span>
                                        <span class="d-block fw-semibold text-gray-900">Reiniciar filas</span>
                                        <span class="d-block text-muted fs-7">Reinicia os workers do Supervisor para aplicar mudanças operacionais.</span>
                                    </span>
                                </label>
                            </div>
                            <div class="bg-light rounded-4 mb-4 p-4">
                                <label class="d-flex align-items-start gap-4 cursor-pointer" for="update_action_npm_build">
                                    <span class="form-check form-check-custom form-check-solid mt-1">
                                        <input class="form-check-input" type="checkbox" name="actions[]" value="npm_build" id="update_action_npm_build">
                                    </span>
                                    <span>
                                        <span class="d-block fw-semibold text-gray-900">Buildar Javascript (npm build)</span>
                                        <span class="d-block text-muted fs-7">Compila os assets frontend para publicar a versão atual do painel.</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Executar selecionados</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
		<script>var hostUrl = "assets/";</script>
		<script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
		<script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
        <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
        <script src="{{ asset('assets/js/datatable-input.js') }}"></script>
        <script src="{{ asset('assets/js/custom.bundle.js') }}"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/pt.min.js"></script>
        <script>

            /**
             * Configura Toaster
             */
            toastr.options = {
                "closeButton": false,
                "debug": false,
                "newestOnTop": false,
                "progressBar": false,
                "positionClass": "toastr-bottom-left",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            /**
             * Verifica se existe um alerta a exibir
             */
				var message = @json(session('message') ?: session('error'));
	            var type = @json(session('type'));

	            if (!type && @json(session('error'))) {
	                type = 'error';
	            }

            /**
             * Exibe o alerta
             */
            if(message){
                switch (type) {
	                    case 'success':
	                        toastr.success(message);
	                        break;
	                    case 'error':
	                        toastr.error(message);
	                        break;
	                    case 'warning':
	                        toastr.warning(message);
	                        break;
                    default:
                        toastr.info(message);
                        break;
                }
            }

            /**
             * Confirma ações sensíveis do menu superior antes de navegar.
             */
            $(document).on('click', '.js-menu-confirm', function (event) {
                var confirmMessage = $(this).data('confirm-message') || 'Deseja mesmo continuar?';
                var shouldProceed = window.confirm(confirmMessage);

                if (!shouldProceed) {
                    event.preventDefault();
                }
            });

	            function globalAjaxErrorContent(xhr) {
	                if (xhr.responseText) {
	                    return xhr.responseText;
	                }

	                if (xhr.responseJSON && xhr.responseJSON.message) {
	                    return xhr.responseJSON.message;
	                }

	                return 'Erro desconhecido';
	            }

	            function openGlobalErrorTab(title, content) {
	                var tab = window.open('', '_blank');

	                if (!tab) {
	                    toastr.error('Não foi possível abrir a aba de erro. Verifique o bloqueador de pop-ups.');
	                    return;
	                }

	                tab.document.write('<!doctype html><html lang="pt-BR"><head><title>Erro da Central</title><style>body{font-family:Arial,sans-serif;margin:24px;background:#f5f5f5;color:#1f2937}pre{white-space:pre-wrap;word-break:break-word;background:#fff;border:1px solid #ddd;border-radius:8px;padding:16px}</style></head><body><h1></h1><pre></pre></body></html>');
	                tab.document.close();
	                tab.document.querySelector('h1').textContent = title;
	                tab.document.querySelector('pre').textContent = content;
	            }

            /**
             * Exibe modal de atualização em massa e injeta a rota de execução.
             */
            $(document).on('click', '.js-open-update-systems-modal', function (event) {
                event.preventDefault();

                var actionUrl = $(this).data('update-url') || "{{ route('systems.update.all.systems') }}";
                $('#form_update_systems_actions').attr('action', actionUrl);

                var modalElement = document.getElementById('modal_update_systems_actions');
                var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                modal.show();
            });

            /**
             * Processa submit do modal de atualização em massa:
             * - valida seleção mínima
             * - dispara evento global para telas interessadas no progresso
             * - envia requisição AJAX assíncrona
             */
            $(document).on('submit', '#form_update_systems_actions', function (event) {
                event.preventDefault();

                var form = $(this);
                var hasAnyAction = form.find('input[name=\"actions[]\"]:checked').length > 0;

                /**
                 * Sem ação selecionada não faz chamada de rede.
                 */
                if (!hasAnyAction) {
                    toastr.warning('Selecione ao menos uma ação para continuar.');
                    return;
                }

                /**
                 * Dispara evento imediatamente para a listagem iniciar polling
                 * sem esperar retorno do backend.
                 */
                window.dispatchEvent(new CustomEvent('systems:update-started'));

                var modalElement = document.getElementById('modal_update_systems_actions');
                var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                modal.hide();

                var submitButton = form.find('button[type=\"submit\"]');
                submitButton.prop('disabled', true);

                /**
                 * Chamada AJAX fire-and-forget do ponto de vista da tela:
                 * a UX de recarga já iniciou via evento global.
                 */
                $.ajax({
                    url: form.attr('action'),
                    method: 'GET',
                    data: form.serialize(),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
	                    success: function (response) {
	                        toastr.success(response.message);
	                    },
	                    error: function (xhr) {
	                        openGlobalErrorTab('Erro ao atualizar sistemas', globalAjaxErrorContent(xhr));
	                    },
                    complete: function () {
                        submitButton.prop('disabled', false);
                    }
                });
            });
        </script>
        @yield('custom-footer')
	</body>
</html>
