<button id="drawer_upgrade" class="btn btn-primary btn-active-success position-fixed" style="right: 50px; bottom: 50px">Modificar Plano</button>
<form action="{{ route('packages.upgrade', $client->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div
        class="bg-white"
        data-kt-drawer="true"
        data-kt-drawer-activate="true"
        data-kt-drawer-toggle="#drawer_upgrade"
        data-kt-drawer-close="#kt_drawer_example_basic_close"
        data-kt-drawer-width="700px">
        <div class="card w-100 rounded-0">
            <div class="card-header pe-5">
                <div class="card-title">
                    <div class="d-flex justify-content-center flex-column me-3">
                        <span class="fs-4 fw-bold text-gray-700 me-1 lh-1">Modificar Plano do Cliente: <span class="text-danger">{{ $client->name }}</span></span>
                    </div>
                </div>
                <div class="card-toolbar">
                    <div class="btn btn-sm btn-icon btn-active-light-primary" id="kt_drawer_example_basic_close">
                        <i class="ki-duotone ki-cross fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                </div>
            </div>
            <div class="card-body hover-scroll-overlay-y p-5">
                @foreach ($modules as $key => $module)
                <div class="d-flex justify-content-between align-items-center mb-3 bg-light p-4 rounded">
                    <div>
                        <p class="m-0 fw-bold text-gray-700">
                            {{ $module->name }} - <span class="text-success value-module">R$ {{ number_format($module->value, 2, ',', '.') }}</span>
                        </p>
                        <span class="text-gray-600">
                            @if ($module->description)
                                {{ $module->description }}
                            @else
                                Sem descrição.
                            @endif
                        </span>
                    </div>
                    <div class="ms-8">
                        <label class="form-check form-switch form-check-custom form-check-solid me-6">
                            <input class="form-check-input cursor-pointer input-features-upgrade" name="modules[]" type="checkbox" value="{{ $module->id }}" @if(in_array($module->id, $client->modules->pluck('id')->toArray())) checked @endif/>
                        </label>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="card-footer bg-light py-6">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="d-flex align-items-center fs-5 text-gray-600 mb-0 gap-2">
                            Módulos <span class="fw-bolder text-gray-700" id="base-price">R$ 0,00</span> x <input type="number" class="form-control p-0 form-control-solid w-30px text-center h-30px border shadow" name="users_limit" id="qnt-users" value="{{ $client->users_limit }}" min="3" required> usuários
                        </div>
                        <p class="m-0 text-primary fs-8">A partir de 3 usuários R$ 29,90 por usuário</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="fs-2x fw-bolder text-gray-700 me-12" id="total-upgrade">
                            R$ 0,00
                        </span>
                        <button type="submit" class="btn btn-icon btn-success btn-active-danger">
                            <i class="fa-solid fa-circle-check fs-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@section('custom-footer')
    @parent
    <script>
    $(document).ready(function() {
        /**
         * Função para calcular o valor total dos módulos selecionados.
         */
        function sumUpgrade() {

            // Preço base do plano (sem considerar os módulos adicionais)
            let basePrice = 29.00;

            // Quantidade de usuários
            let users = parseInt($('#qnt-users').val());

            // Se houver mais de 3 usuários, cobrar R$ 29,90 por cada usuário adicional
            let additionalUsersPrice = 0;
            if (users > 3) {
                additionalUsersPrice = (users - 3) * 29.90; // Preço adicional por usuário
            }

            // Valor total dos módulos selecionados
            let modulesTotal = 0;
            $('.input-features-upgrade:checked').each(function() {
                // Pega o valor do módulo correspondente e converte para número
                let valor = $(this).closest('.d-flex').find('.value-module').text().replace('R$', '').replace('.', '').replace(',', '.');
                modulesTotal += parseFloat(valor); // Soma o valor do módulo
            });

            // Calculando o valor total (preço base + módulos adicionais + usuários adicionais)
            let total = basePrice + modulesTotal + additionalUsersPrice;

            // Exibe o total
            $('#total-upgrade').text('R$ ' + total.toFixed(2).replace('.', ','));
            $('#base-price').text('R$ ' + (basePrice + modulesTotal).toFixed(2).replace('.', ','));

        }

        // Quando um checkbox for alterado (marcado/desmarcado), recalcular o total
        $('.input-features-upgrade').change(function() {
            sumUpgrade();
        });

        // Quando o número de usuários for alterado, recalcular o total
        $('#qnt-users').change(function() {
            sumUpgrade();
        });

        // Realiza cálculo inicial
        sumUpgrade();

    });
    </script>
@endsection