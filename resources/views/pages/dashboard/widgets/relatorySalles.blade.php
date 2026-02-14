<div class="card h-xl-100">
    <div class="card-header">
    <h3 class="card-title align-items-start flex-column">
        <span class="card-label fw-bold text-gray-800">Relatório de vendas</span>
        <span class="text-gray-500 mt-1 fw-semibold fs-6">
        Sistemas gerados em {{ $dailyChartMonthLabel }}
        </span>
    </h3>
    <div class="card-toolbar">
        <div class="d-flex gap-2 align-items-center">
        <a href="#" class="btn btn-sm btn-icon btn-light">
            <i class="ki-outline ki-arrow-left"></i>
        </a>
        <a href="#" class="btn btn-sm btn-light">Fevereiro</a>
        <a href="#" class="btn btn-sm btn-icon btn-light">
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

if (!element) {
    console.warn('Elemento #graph_salles não encontrado.');
} else {
    var height = parseInt(KTUtil.css(element, 'height'));
    var labelColor = '#6C757D';
    var borderColor = '#E9ECEF';
    var baseColor = '#0D6EFD';

    var options = {
        series: [{
            name: 'Sistemas gerados',
            data: @json($dailyChartSeries)
        }],
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
            colors: [baseColor]
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
                    color: baseColor,
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
                    return val + ' sistema(s)'
                }
            }
        },
        colors: [baseColor],
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
            strokeColor: baseColor,
            strokeWidth: 3
        }
    };

    var chart = new ApexCharts(element, options);
    chart.render();
}
</script>
@endsection
