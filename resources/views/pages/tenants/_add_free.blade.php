<button id="drawer_add_free" class="btn btn-success btn-active-danger position-fixed" style="right: 50px; bottom: 50px">Liberar 30 Dias</button>
<form action="{{ route('systems.add.free', $client->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div
        class="bg-white"
        data-kt-drawer="true"
        data-kt-drawer-activate="true"
        data-kt-drawer-toggle="#drawer_add_free"
        data-kt-drawer-close="#kt_drawer_example_basic_close"
        data-kt-drawer-width="700px">
        <div class="card w-100 rounded-0">
            <div class="card-header border-bottom-0 p-0" style="height: 355px !important">
                <div class="d-flex justify-content-between align-items-center w-100 border-bottom" style="height: 65px">
                    <div class="card-title ps-8">
                        <div class="d-flex justify-content-center flex-column me-3">
                            <span class="fs-4 fw-bold text-gray-700 me-1 lh-1">
                                Liberar Sistema por 30 dias para: 
                                <span class="text-danger">
                                    {{ $client->name }}
                                </span>
                            </span>
                        </div>
                    </div>
                    <div class="card-toolbar pe-8">
                        <div class="btn btn-sm btn-icon btn-circle btn-active-light-primary" id="kt_drawer_example_basic_close">
                            <i class="fa-solid fa-xmark"></i>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3 bg-light rounded w-100 ms-5 me-8 px-4">
                    <div>
                        <p class="m-0 fw-bold text-gray-700 fs-1">
                            Atribuir todos
                        </p>
                    </div>
                    <div class="ms-8 d-flex gap-2">
                        <span class="btn btn-icon btn-sm btn-success" id="check-all-free">
                            <i class="fa-solid fa-check"></i>
                        </span>
                        <span class="btn btn-icon btn-sm btn-danger" id="uncheck-all-free">
                            <i class="fa-solid fa-xmark"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body hover-scroll-overlay-y px-5 pt-0 pb-5">
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
                                <input class="form-check-input cursor-pointer input-features-free" name="modules[]" type="checkbox" value="{{ $module->id }}" @if(in_array($module->id, $client->modules->pluck('id')->toArray())) checked @endif/>
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="card-footer bg-light py-6">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="">
                        <p class="m-0 fw-bold text-gray-700 fs-4">
                            Modulos Selecionados: <span class="text-success" id="count-modules-free">0</span>
                        </p>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="fs-2x fw-bolder text-gray-700 me-8" id="total-free">
                            R$ 0,00
                        </span>
                        <button type="submit" class="btn btn-success">
                            Liberar 30 Dias
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
        function sumFree() {

            // Valor total dos módulos selecionados
            let modulesTotal = 0;
            $('.input-features-free:checked').each(function() {
                // Pega o valor do módulo correspondente e converte para número
                let valor = $(this).closest('.d-flex').find('.value-module').text().replace('R$', '').replace('.', '').replace(',', '.');
                modulesTotal += parseFloat(valor); // Soma o valor do módulo
            });

            // Calculando o valor total (preço base + módulos adicionais + usuários adicionais)
            let total = modulesTotal;

            // Exibe o total
            $('#total-free').text('R$ ' + total.toFixed(2).replace('.', ','));

        }

        /**
         * Função responsável por atribuir todos os módulos
         */
        function checkAllFree() {
            $('.input-features-free').each(function() {
                $(this).prop('checked', true);
            });
            sumFree();
        }

        /**
         * Função responsável por desmarcar todos os módulos
         */
        function uncheckAllFree() {
            $('.input-features-free').each(function() {
                $(this).prop('checked', false);
            });
            sumFree();
        }

        function countModulesFree() {
            let count = $('.input-features-free:checked').length;
            $('#count-modules-free').text(count);
        }

        // Quando um checkbox for alterado (marcado/desmarcado), recalcular o total
        $('.input-features-free').change(function() {
            sumFree();

            countModulesFree();
        });

        // Realiza cálculo inicial
        sumFree();

        // Quando o botão "Atribuir todos" for clicado
        $('#check-all-free').click(function() {
            checkAllFree();

            countModulesFree();
        });

        // Quando o botão "Desmarcar todos" for clicado
        $('#uncheck-all-free').click(function() {
            uncheckAllFree();

            countModulesFree();
        });

    });
    </script>
@endsection