<div class="modal fade" tabindex="-1" id="modal_icon">
    <div class="modal-dialog modal-dialog-centered mw-1000px">
        <div class="modal-content bg-gray-300">
            <div class="modal-header py-3 bg-dark">
                <h5 class="modal-title text-white modal_confirmacao-titulo">Selecione um ícone</h5>
                <div class="btn btn-icon bg-dark ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <span class="svg-icon svg-icon-2x fw-bolder">X</span>
                </div>
            </div>
            <div class="modal-body scroll-y h-700px">
                <div class="mb-4">
                    <div class="position-relative">
                        <i class="fa-solid fa-magnifying-glass position-absolute text-gray-600" style="top: 12px; left: 12px;"></i>
                        <input type="text" id="mc-icon-search" class="form-control form-control-solid ps-10" placeholder="Pesquisar ícone...">
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 mb-5">
                    <button type="button" class="btn btn-sm btn-light-primary fw-bold mc-icon-category-filter active" data-category="all">
                        Todos
                    </button>
                    @foreach (icons() as $categoryIndex => $category)
                        <button type="button" class="btn btn-sm btn-light fw-bold mc-icon-category-filter" data-category="{{ Str::slug($category['titulo']) }}">
                            {{ $category['titulo'] }}
                        </button>
                    @endforeach
                </div>
                <div class="d-flex flex-wrap justify-content-start gap-4" id="mc-icon-list">
                     <div class="mc-icons mc-icons-grid-item text-center p-3 rounded-3 shadow-sm cursor-pointer hover-scale text-hover-primary mc-icon-none-option bg-white w-50px h-50px d-flex justify-content-center align-items-center">
                        <i class="fa-solid fa-xmark fs-2x text-danger"></i>
                    </div>
                    @foreach (icons() as $categoryIndex => $category)
                        @foreach ($category['icones'] as $icon => $name)
                            <div class="mc-icons mc-icons-grid-item text-center p-3 rounded-3 shadow-sm cursor-pointer hover-scale text-hover-primary bg-white w-50px h-50px d-flex justify-content-center align-items-center"
                                data-category="{{ Str::slug($category['titulo']) }}"
                                data-search="{{ strtolower($name . ' ' . $category['titulo']) }}"
                                data-icon="{{ $icon }}">
                                <i class="{{ $icon }} fs-2"></i>
                            </div>
                        @endforeach
                    @endforeach
                </div>
                <div class="text-center py-5 text-muted d-none" id="mc-icon-empty">
                    Nenhum ícone encontrado.
                </div>
            </div>
        </div>
    </div>
</div>


@section('custom-footer')
    @parent
    <script>

        /**
         * Variável para armazenar o ícone selecionado
         */
        let selectedIcon;
        let targetIcon;
        let requiredIcon = true;
        let currentIconCategory = "all";

        /**
         * Normaliza texto para comparação ignorando acentos.
         */
        function normalizeIconSearchText(text) {

            const safeText = (text || "").toString().toLowerCase().trim();

            if (typeof safeText.normalize === "function") {

                return safeText.normalize("NFD").replace(/[\u0300-\u036f]/g, "");

            }

            return safeText;

        }

        /**
         * Aplica os filtros de categoria e busca na listagem de ícones.
         */
        function applyIconFilters() {

            const searchTerm = normalizeIconSearchText($("#mc-icon-search").val());
            let visibleCount = 0;

            $(".mc-icons-grid-item").each(function () {

                const iconCategory = ($(this).data("category") || "").toString();
                const iconText = normalizeIconSearchText($(this).data("search") || $(this).data("icon") || "");
                const matchCategory = currentIconCategory === "all" || iconCategory === currentIconCategory;
                const matchSearch = searchTerm === "" || iconText.includes(searchTerm);
                const isVisible = matchCategory && matchSearch;

                $(this).toggleClass("d-none", !isVisible);

                if (isVisible) {

                    visibleCount++;

                }

            });

            $("#mc-icon-empty").toggleClass("d-none", visibleCount > 0);

        }

        /**
         * Captura todos os ícones clicáveis e atualiza a pré-visualização
         */
        $(document).on("click", ".mc-icons", function () {

            const iconClass = $(this).data("icon") ?? "";

            // Bloqueia remoção quando o seletor exigir ícone obrigatório
            if (!iconClass && requiredIcon) {

                return;

            }

            // Atualiza a pré-visualização do ícone
            if (iconClass) {
                $(selectedIcon).html(`<i class="${iconClass} fs-2x"></i>`);
            } else {
                $(selectedIcon).html(`<i class="fa-solid fa-icons text-danger fs-2x"></i>`);
            }

            // Atualiza o alvo do ícone
            $(targetIcon).val(iconClass);

            // Fecha o modal
            $("#modal_icon").modal("hide");

        });

        /**
         * Confirma a seleção do ícone e atualiza o input hidden
         */
        $(document).on("click", ".mc-select-icon", function () {

            // Captura o ícone selecionado
            selectedIcon = $(this);

            // Captura o alvo do ícone
            targetIcon = $(this).data("icon-target") || $(this).data("target");
            requiredIcon = $(this).data("required-icon") !== false && $(this).data("required-icon") !== "false";

            // Exibe a opção de remover ícone apenas quando permitido no seletor atual
            if (requiredIcon) {

                $(".mc-icon-none-option").hide();

            } else {

                $(".mc-icon-none-option").show();

            }

            // Fecha o modal
            $("#modal_icon").modal("show");

        });

        /**
         * Filtra os ícones ao digitar no campo de busca.
         */
        $(document).on("input", "#mc-icon-search", function () {

            applyIconFilters();

        });

        /**
         * Filtra os ícones ao selecionar uma categoria.
         */
        $(document).on("click", ".mc-icon-category-filter", function (e) {

            e.preventDefault();

            currentIconCategory = ($(this).data("category") || "all").toString();
            $(".mc-icon-category-filter").removeClass("active btn-light-primary").addClass("btn-light");
            $(this).addClass("active btn-light-primary").removeClass("btn-light");
            applyIconFilters();

        });

        /**
         * Reinicia filtros ao abrir o modal de ícones.
         */
        $("#modal_icon").on("shown.bs.modal", function () {

            currentIconCategory = "all";
            $("#mc-icon-search").val("");
            $(".mc-icon-category-filter").removeClass("active btn-light-primary").addClass("btn-light");
            $('.mc-icon-category-filter[data-category="all"]').addClass("active btn-light-primary").removeClass("btn-light");
            applyIconFilters();
            $("#mc-icon-search").trigger("focus");

        });

    </script>
@endsection
