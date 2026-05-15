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
                            <p class="text-muted mb-4">Escolha o sistema e o que deseja executar:</p>
                            <div class="mb-4">
                                <label class="form-label fw-semibold" for="update_system_scope">Qual sistema atualizar?</label>
                                <select class="form-select form-select-solid" name="update_scope" id="update_system_scope" required>
                                    <option value="">Selecione</option>
                                    <option value="all">Todos</option>
                                    <option value="individual">Individual</option>
                                    <option value="shared">Compartilhado</option>
                                </select>
                            </div>
                            <div class="mb-4 d-none" id="update_system_tenant_group">
                                <label class="form-label fw-semibold" for="update_system_tenant_id">Qual sistema individual?</label>
                                <select class="form-select form-select-solid" name="tenant_id" data-placeholder="Selecione" id="update_system_tenant_id">
                                    <option value=""></option>
                                    @foreach ($updateSystemDedicatedTenants ?? [] as $tenant)
                                        <option value="{{ $tenant->id }}">{{ $tenant->name }}{{ $tenant->email ? ' - ' . $tenant->email : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="d-none" id="update_system_actions_group">
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

            /**
             * Exibe modal de atualização em massa e injeta a rota de execução.
             */
            $(document).on('click', '.js-open-update-systems-modal', function (event) {
                event.preventDefault();

                var actionUrl = $(this).data('update-url') || "{{ route('systems.update.all.systems') }}";
                var form = $('#form_update_systems_actions');
                form.attr('action', actionUrl);
                form[0].reset();
                $('#update_system_scope').trigger('change');

                var modalElement = document.getElementById('modal_update_systems_actions');
                var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                var selectElements = $('#update_system_scope, #update_system_tenant_id');

                if ($.fn.select2) {
                    selectElements.each(function () {
                        var selectElement = $(this);

                        if (!selectElement.hasClass('select2-hidden-accessible')) {
                            selectElement.select2({
                                dropdownParent: $('#modal_update_systems_actions'),
                                width: '100%',
                            });
                        }
                    });
                }

                modal.show();
            });

            /**
             * Alterna os campos do modal conforme o escopo escolhido.
             */
            $(document).on('change', '#update_system_scope', function () {
                var selectedScope = $(this).val();
                var tenantGroup = $('#update_system_tenant_group');
                var tenantSelect = $('#update_system_tenant_id');
                var actionsGroup = $('#update_system_actions_group');

                if (selectedScope === 'individual') {
                    tenantGroup.removeClass('d-none');
                    actionsGroup.removeClass('d-none');
                    tenantSelect.prop('required', true);
                    return;
                }

                tenantGroup.addClass('d-none');
                tenantSelect.prop('required', false);
                tenantSelect.val('');

                if ($.fn.select2) {
                    tenantSelect.trigger('change.select2');
                }

                if (selectedScope) {
                    actionsGroup.removeClass('d-none');
                    return;
                }

                actionsGroup.addClass('d-none');
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
                var selectedScope = $('#update_system_scope').val();
                var selectedTenant = $('#update_system_tenant_id').val();

                /**
                 * Sem ação selecionada não faz chamada de rede.
                 */
                if (!hasAnyAction) {
                    toastr.warning('Selecione ao menos uma ação para continuar.');
                    return;
                }

                /**
                 * Sem escopo selecionado o backend não sabe qual grupo atualizar.
                 */
                if (!selectedScope) {
                    toastr.warning('Selecione qual sistema atualizar.');
                    return;
                }

                /**
                 * Atualização individual exige um tenant dedicado explícito.
                 */
                if (selectedScope === 'individual' && !selectedTenant) {
                    toastr.warning('Selecione o sistema individual.');
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
                        toastr.error(xhr.responseJSON.message);
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
