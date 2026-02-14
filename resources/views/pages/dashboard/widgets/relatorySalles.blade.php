<div class="card h-xl-100">
    <div class="card-header">
    <h3 class="card-title align-items-start flex-column">
        <span class="card-label fw-bold text-gray-800">Sistemas X Vendas</span>
        <span id="salesMonthLabel" class="text-gray-500 mt-1 fw-semibold fs-6">
        Relatório referente a {{ $dailyChartMonthLabel }}
        </span>
    </h3>
    <div class="card-toolbar">
        <div class="d-flex gap-2 align-items-center">
        <a href="#" id="prevMonthButton" class="btn btn-sm btn-icon btn-light">
            <i class="ki-outline ki-arrow-left"></i>
        </a>
        <span id="salesMonthBadge" class="btn btn-sm btn-light">{{ $dailyChartMonthLabel }}</span>
        <a href="#" id="nextMonthButton" class="btn btn-sm btn-icon btn-light">
            <i class="ki-outline ki-arrow-right"></i>
        </a>
        </div>
    </div>
    </div>
    <div class="card-body d-flex align-items-end p-2">
        <div id="graph_salles" class="w-100" style="height: 350px;">
        </div>
    </div>
</div>

@section('custom-footer')
<script>
var element = document.getElementById('graph_salles');
var salesMonthLabel = document.getElementById('salesMonthLabel');
var salesMonthBadge = document.getElementById('salesMonthBadge');
var prevMonthButton = document.getElementById('prevMonthButton');
var nextMonthButton = document.getElementById('nextMonthButton');
var currentMonthValue = '{{ $dailyChartMonthValue }}';

if (!element) {
    console.warn('Elemento #graph_salles não encontrado.');
} else {
    var height = parseInt(KTUtil.css(element, 'height'));
    var labelColor = '#6C757D';
    var borderColor = '#E9ECEF';
    var systemsColor = '#0D6EFD';
    var salesColor = '#198754';

    var options = {
        series: [
            {
                name: 'Sistemas gerados',
                data: @json($dailyChartSystemsSeries)
            },
            {
                name: 'Vendas pagas',
                data: @json($dailyChartSalesSeries)
            }
        ],
        chart: {
            fontFamily: 'inherit',
            type: 'area',
            height: height,
            toolbar: {
                show: false
            }
        },
        plotOptions: {

        },
        legend: {
            show: false
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
            colors: [systemsColor, salesColor]
        },
        xaxis: {
            categories: @json($dailyChartLabels),
            axisBorder: {
                show: false,
            },
            axisTicks: {
                show: false
            },
            labels: {
                style: {
                    colors: labelColor,
                    fontSize: '12px'
                }
            },
            crosshairs: {
                position: 'front',
                stroke: {
                    color: systemsColor,
                    width: 1,
                    dashArray: 3
                }
            },
            tooltip: {
                enabled: true,
                formatter: undefined,
                offsetY: 0,
                style: {
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: labelColor,
                    fontSize: '12px'
                }
            }
        },
        states: {
            normal: {
                filter: {
                    type: 'none',
                    value: 0
                }
            },
            hover: {
                filter: {
                    type: 'none',
                    value: 0
                }
            },
            active: {
                allowMultipleDataPointsSelection: false,
                filter: {
                    type: 'none',
                    value: 0
                }
            }
        },
        tooltip: {
            style: {
                fontSize: '12px'
            },
            y: {
                formatter: function (val) {
                    return val + ' registro(s)'
                }
            }
        },
        colors: [systemsColor, salesColor],
        grid: {
            borderColor: borderColor,
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

    var chart = new ApexCharts(element, options);
    chart.render();

    function formatMonthLabel(monthValue) {
        var parts = monthValue.split('-');
        var year = parseInt(parts[0], 10);
        var month = parseInt(parts[1], 10);
        var monthNames = [
            'janeiro',
            'fevereiro',
            'março',
            'abril',
            'maio',
            'junho',
            'julho',
            'agosto',
            'setembro',
            'outubro',
            'novembro',
            'dezembro'
        ];

        return monthNames[month - 1] + '/' + year;
    }

    function shiftMonth(monthValue, offset) {
        var parts = monthValue.split('-');
        var year = parseInt(parts[0], 10);
        var month = parseInt(parts[1], 10) - 1;
        var date = new Date(year, month + offset, 1);
        var newYear = date.getFullYear();
        var newMonth = String(date.getMonth() + 1).padStart(2, '0');

        return newYear + '-' + newMonth;
    }

    function updateMonthUi(monthLabel, monthValue) {
        salesMonthLabel.textContent = 'Sistemas e vendas em ' + monthLabel;
        salesMonthBadge.textContent = monthLabel;
        currentMonthValue = monthValue;
    }

    function loadMonthData(monthValue) {
        $.ajax({
            url: '{{ route('dashboard.daily.systems') }}',
            method: 'GET',
            dataType: 'json',
            data: { month: monthValue },
            success: function(response) {
                chart.updateOptions({
                    xaxis: {
                        categories: response.labels
                    }
                });
                chart.updateSeries([{
                    name: 'Sistemas gerados',
                    data: response.systemsSeries
                }, {
                    name: 'Vendas pagas',
                    data: response.salesSeries
                }]);

                updateMonthUi(response.monthLabel, response.monthValue);
            },
            error: function() {
                console.warn('Não foi possível atualizar os dados do gráfico.');
            }
        });
    }

    prevMonthButton.addEventListener('click', function(event) {
        event.preventDefault();
        loadMonthData(shiftMonth(currentMonthValue, -1));
    });

    nextMonthButton.addEventListener('click', function(event) {
        event.preventDefault();
        loadMonthData(shiftMonth(currentMonthValue, 1));
    });
}
</script>
@endsection
