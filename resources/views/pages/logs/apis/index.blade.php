@extends('layouts.app')

@section('title', 'Logs APIs')

@section('custom-head')
    @parent
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-okaidia.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js" integrity="sha512-7Z9J3l1+EYfeaPKcGXu3MS/7T+w19WtKQY/n+xzmw4hZhJ9tyYmcUS+4QqAlzhicE5LAfMQSF3iFTK9bQdTxXg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js" integrity="sha512-SkmBfuA2hqjzEVpmnMt/LINrjop3GKWqsuLSSB3e7iBmYK7JuWw4ldmmxwD9mdm2IRTTi0OxSAfEGvgEi0i2Kw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
@endsection

@section('content')
<div class="card mb-5">
    <div class="card-header">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold text-gray-800">Despachos da API por Dia</span>
            <span id="dispatchMonthLabel" class="text-gray-500 mt-1 fw-semibold fs-6">
                Relatório referente a {{ $dailyDispatchChartMonthLabel }}
            </span>
        </h3>
        <div class="card-toolbar">
            <div class="d-flex gap-2 align-items-center">
                <a href="#" id="prevDispatchMonthButton" class="btn btn-sm btn-icon btn-light">
                    <i class="ki-outline ki-arrow-left"></i>
                </a>
                <span id="dispatchMonthBadge" class="btn btn-sm btn-light">{{ $dailyDispatchChartMonthLabel }}</span>
                <a href="#" id="nextDispatchMonthButton" class="btn btn-sm btn-icon btn-light">
                    <i class="ki-outline ki-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="card-body p-2">
        <div class="d-flex align-items-end">
            <div id="graph_logs_apis_dispatches" class="w-100" style="height: 300px;"></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex flex-stack flex-wrap mb-5">
            <div class="d-flex align-items-center position-relative my-1 mb-2 mb-md-0">
                <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6"><span class="path1"></span><span class="path2"></span></i>
                <input type="text" data-kt-docs-table-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Procurar logs da API">
            </div>
            <div class="d-flex justify-content-end" data-kt-docs-table-toolbar="base">
                <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                    <i class="ki-duotone ki-filter fs-2"><span class="path1"></span><span class="path2"></span></i>        Filtrar
                </button>
                <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                    <form action="" id="logs-apis-filters">
                        <div class="px-7 py-5"><div class="fs-4 text-gray-900 fw-bold">Filtros</div></div>
                        <div class="separator border-gray-200"></div>
                        <div class="px-7 py-5">
                            <div class="mb-10">
                                <label class="form-label fs-5 fw-semibold mb-3">Status:</label>
                                <div class="d-flex flex-column flex-wrap fw-semibold">
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3 me-5">
                                        <input class="form-check-input" type="radio" name="log_status" value="all" checked>
                                        <span class="form-check-label text-gray-600">Todos</span>
                                    </label>
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3 me-5">
                                        <input class="form-check-input" type="radio" name="log_status" value="Processado">
                                        <span class="form-check-label text-gray-600">Processado</span>
                                    </label>
                                    <label class="form-check form-check-sm form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="radio" name="log_status" value="Erro">
                                        <span class="form-check-label text-gray-600">Erro</span>
                                    </label>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="reset" class="btn btn-light btn-active-light-primary me-2" data-kt-menu-dismiss="true">Reset</button>
                                <button type="submit" class="btn btn-primary" data-kt-menu-dismiss="true">Aplicar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <table id="datatables-logs-apis" data-dt-manual="true" class="table table-striped table-row-bordered gy-2 gs-7 align-middle">
            <thead class="rounded">
                <tr class="fw-bold fs-6 text-gray-700 px-7">
                    <th class="text-start">ID</th>
                    <th class="text-start">API</th>
                    <th class="text-start">Cliente</th>
                    <th class="text-start">JSON</th>
                    <th class="text-center">Reprocessado</th>
                    <th class="text-center">Status</th>
                    <th class="text-start">Despachado Em</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@section('modals')
<div class="modal fade" tabindex="-1" id="modal_json">
    <div class="modal-dialog modal-dialog-centered mw-1000px">
        <div class="modal-content">
            <div class="modal-header py-3 bg-dark border-0">
                <h5 class="modal-title text-white">Visualizando JSON</h5>
                <div class="btn btn-icon bg-dark ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <span class="svg-icon svg-icon-2x fw-bolder">X</span>
                </div>
            </div>
            <div class="modal-body p-0">
                {{-- JSON HERE --}}
                {{-- JSON HERE --}}
                {{-- JSON HERE --}}
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-footer')
@parent
<script>
    const dispatchChartElement = document.getElementById('graph_logs_apis_dispatches');
    const dispatchMonthLabel = document.getElementById('dispatchMonthLabel');
    const dispatchMonthBadge = document.getElementById('dispatchMonthBadge');
    const prevDispatchMonthButton = document.getElementById('prevDispatchMonthButton');
    const nextDispatchMonthButton = document.getElementById('nextDispatchMonthButton');
    let currentDispatchMonthValue = '{{ $dailyDispatchChartMonthValue }}';

    /**
     * Retorna a string do próximo ou anterior mês no formato YYYY-MM.
     */
    function shiftMonth(monthValue, offset) {
        const parts = monthValue.split('-');
        const year = parseInt(parts[0], 10);
        const month = parseInt(parts[1], 10) - 1;
        const date = new Date(year, month + offset, 1);
        const nextYear = date.getFullYear();
        const nextMonth = String(date.getMonth() + 1).padStart(2, '0');

        return `${nextYear}-${nextMonth}`;
    }

    /**
     * Atualiza os textos de referência do mês no cabeçalho do card.
     */
    function updateMonthUi(monthLabel, monthValue) {
        dispatchMonthLabel.textContent = `Relatório referente a ${monthLabel}`;
        dispatchMonthBadge.textContent = monthLabel;
        currentDispatchMonthValue = monthValue;
    }

    /**
     * Monta as configurações do ApexCharts para o gráfico de webhooks.
     */
    function buildDispatchChartOptions(chartHeight) {
        const dispatchLabelColor = '#6C757D';
        const dispatchBorderColor = '#E9ECEF';
        const dispatchSeriesColors = ['#0D6EFD', '#198754', '#FFC107', '#DC3545'];

        return {
            series: @json($dailyDispatchChartSeries),
            chart: {
                fontFamily: 'inherit',
                type: 'area',
                height: chartHeight,
                toolbar: {
                    show: false
                }
            },
            legend: {
                show: true,
                position: 'bottom',
                fontSize: '12px',
                fontWeight: 'bold',
                labels: {
                    colors: dispatchLabelColor
                },
                itemMargin: {
                    horizontal: 20
                }
            },
            dataLabels: {
                enabled: false
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: 'vertical',
                    shadeIntensity: 0.6,
                    inverseColors: false,
                    opacityFrom: 0.85,
                    opacityTo: 0,
                    stops: [0, 100]
                }
            },
            stroke: {
                curve: 'smooth',
                show: true,
                width: 3,
                colors: dispatchSeriesColors
            },
            xaxis: {
                categories: @json($dailyDispatchChartLabels),
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: dispatchLabelColor,
                        fontSize: '12px'
                    }
                },
                crosshairs: {
                    position: 'front',
                    stroke: {
                        color: dispatchSeriesColors[0],
                        width: 1,
                        dashArray: 3
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: dispatchLabelColor,
                        fontSize: '12px'
                    }
                }
            },
            tooltip: {
                shared: true,
                intersect: false,
                style: {
                    fontSize: '12px'
                },
                y: {
                    formatter: function (value) {
                        return `${value} webhook(s)`;
                    }
                }
            },
            colors: dispatchSeriesColors,
            grid: {
                borderColor: dispatchBorderColor,
                strokeDashArray: 4,
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            },
            markers: {
                strokeColor: '#FFFFFF',
                strokeWidth: 3
            }
        };
    }

    /**
     * Busca os dados do mês e atualiza categorias e séries do gráfico.
     */
    function loadMonthData(dispatchChart, monthValue) {
        $.ajax({
            url: '{{ route('logs.apis.relatory.graphic') }}',
            method: 'GET',
            dataType: 'json',
            data: { month: monthValue },
            success: function (response) {
                // Atualiza os dias exibidos no eixo X.
                dispatchChart.updateOptions({
                    xaxis: {
                        categories: response.labels
                    }
                });

                // Atualiza as linhas exibidas com base na resposta do backend.
                dispatchChart.updateSeries(response.series || []);
                updateMonthUi(response.monthLabel, response.monthValue);
            },
            error: function () {
                console.warn('Não foi possível atualizar os dados do gráfico de despachos.');
            }
        });
    }

    /**
     * Vincula os botões para navegar entre meses no gráfico.
     */
    function bindMonthNavigation(dispatchChart) {
        prevDispatchMonthButton.addEventListener('click', function (event) {
            event.preventDefault();
            loadMonthData(dispatchChart, shiftMonth(currentDispatchMonthValue, -1));
        });

        nextDispatchMonthButton.addEventListener('click', function (event) {
            event.preventDefault();
            loadMonthData(dispatchChart, shiftMonth(currentDispatchMonthValue, 1));
        });
    }

    /**
     * Inicializa o gráfico de despachos caso o elemento e a lib existam.
     */
    function initializeDispatchChart() {
        if (!dispatchChartElement) {
            console.warn('Elemento #graph_logs_apis_dispatches não encontrado.');
            return;
        }

        if (typeof ApexCharts === 'undefined') {
            console.warn('ApexCharts não está disponível para renderizar o relatório de despachos.');
            return;
        }

        // Mantém a altura do gráfico coerente com o card.
        const dispatchChartHeight = parseInt(KTUtil.css(dispatchChartElement, 'height'), 10);
        const dispatchChartOptions = buildDispatchChartOptions(dispatchChartHeight);
        const dispatchChart = new ApexCharts(dispatchChartElement, dispatchChartOptions);

        dispatchChart.render();
        bindMonthNavigation(dispatchChart);
    }

    /**
     * Abre o JSON do log em modal com destaque de sintaxe.
     */
    $(document).on('click', '.open-json', function () {
        // Captura o identificador do log clicado.
        const logId = $(this).data('json');

        $.ajax({
            url: `{{ route('logs.apis.show', '') }}/${logId}`,
            type: 'GET',
            success: function (response) {
                // Formata e renderiza o JSON com Prism.js.
                const formattedJson = JSON.stringify(JSON.parse(response), null, 4);
                const highlightedJson = `<pre class="m-0 rounded-0 rounded-bottom-2"><code class="language-json">${formattedJson}</code></pre>`;

                $('#modal_json .modal-body').html(highlightedJson);
                $('#modal_json').modal('show');
                Prism.highlightAll();
            },
            error: function () {
                $('#modal_json .modal-body').html('<p class="text-danger">Erro ao carregar JSON.</p>');
                $('#modal_json').modal('show');
            }
        });
    });

    /**
     * Inicializa o DataTable de logs da API com busca e filtros.
     */
    const dataTable = $('#datatables-logs-apis').DataTable({
        serverSide: true,
        processing: true,
        pageLength: 25,
        ajax: {
            url: '{{ route("logs.apis.process") }}',
            data: function (requestData) {
                requestData.searchBy = requestData.search.value;

                // O backend espera essas chaves nesse formato.
                requestData.order_by = requestData.columns[requestData.order[0].column].data;
                requestData.log_status = $('input[name="log_status"]:checked').val();
            }
        },
        order: [[0, 'desc']],
        columns: [
            { data: 'id', name: 'id' },
            { data: 'api', name: 'api' },
            { data: 'client', name: 'client' },
            { data: 'json', name: 'json', orderable: false, searchable: false },
            { data: 'reprocessed', name: 'reprocessed', orderable: false, searchable: false, className: 'text-center' },
            { data: 'status', name: 'status', orderable: false, searchable: false, className: 'text-center' },
            { data: 'dispatched_at', name: 'dispatched_at' }
        ],
        pagingType: 'simple_numbers'
    });

    /**
     * Aplica busca textual na tabela durante a digitação.
     */
    $('[data-kt-docs-table-filter="search"]').on('keyup', function () {
        dataTable.search($(this).val()).draw();
    });

    /**
     * Recarrega a tabela quando o usuário aplica o filtro de status.
     */
    $('#logs-apis-filters').on('submit', function (event) {
        event.preventDefault();
        dataTable.ajax.reload();
    });

    /**
     * Recarrega a tabela após resetar os filtros do formulário.
     */
    $('#logs-apis-filters').on('reset', function () {
        setTimeout(() => dataTable.ajax.reload(), 0);
    });

    initializeDispatchChart();
</script>
@endsection
