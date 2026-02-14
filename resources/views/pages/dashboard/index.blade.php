@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row gx-5 gx-xl-10">
  <div class="col-xl-12 mb-5 mb-xl-10">
    <div class="card card-flush h-xl-100">
      <div class="card-header py-7">
        <div class="m-0">
          <div class="d-flex align-items-center mb-2">
            <span class="fs-2hx fw-bold text-gray-800 me-2 lh-1 ls-n2"
              >Relatório de Vendas</span
            >

            <span class="badge badge-light-success fs-base">
              <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>
              2.2%
            </span>
          </div>

          <span class="fs-6 fw-semibold text-gray-500">Vendas ao Longo</span>
        </div>
      </div>

      <div class="card-body d-flex align-items-end p-0">
        <div
          id="kt_charts_widget_29"
          class="h-300px w-100 min-h-auto ps-7 pe-0 mb-5"
          style="min-height: 315px"
        >
          <div
            id="apexcharts3249oe1w"
            class="apexcharts-canvas apexcharts3249oe1w apexcharts-theme-light"
            style="width: 839px; height: 300px"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              version="1.1"
              xmlns:xlink="http://www.w3.org/1999/xlink"
              class="apexcharts-svg apexcharts-zoomable"
              xmlns:data="ApexChartsNS"
              transform="translate(0, 0)"
              width="839"
              height="300"
            >
              <foreignObject x="0" y="0" width="839" height="300">
                <style type="text/css">
                  .apexcharts-flip-y {
                    transform: scaleY(-1) translateY(-100%);
                    transform-origin: top;
                    transform-box: fill-box;
                  }
                  .apexcharts-flip-x {
                    transform: scaleX(-1);
                    transform-origin: center;
                    transform-box: fill-box;
                  }
                  .apexcharts-legend {
                    display: flex;
                    overflow: auto;
                    padding: 0 10px;
                  }
                  .apexcharts-legend.apexcharts-legend-group-horizontal {
                    flex-direction: column;
                  }
                  .apexcharts-legend-group {
                    display: flex;
                  }
                  .apexcharts-legend-group-vertical {
                    flex-direction: column-reverse;
                  }
                  .apexcharts-legend.apx-legend-position-bottom,
                  .apexcharts-legend.apx-legend-position-top {
                    flex-wrap: wrap;
                  }
                  .apexcharts-legend.apx-legend-position-right,
                  .apexcharts-legend.apx-legend-position-left {
                    flex-direction: column;
                    bottom: 0;
                  }
                  .apexcharts-legend.apx-legend-position-bottom.apexcharts-align-left,
                  .apexcharts-legend.apx-legend-position-top.apexcharts-align-left,
                  .apexcharts-legend.apx-legend-position-right,
                  .apexcharts-legend.apx-legend-position-left {
                    justify-content: flex-start;
                    align-items: flex-start;
                  }
                  .apexcharts-legend.apx-legend-position-bottom.apexcharts-align-center,
                  .apexcharts-legend.apx-legend-position-top.apexcharts-align-center {
                    justify-content: center;
                    align-items: center;
                  }
                  .apexcharts-legend.apx-legend-position-bottom.apexcharts-align-right,
                  .apexcharts-legend.apx-legend-position-top.apexcharts-align-right {
                    justify-content: flex-end;
                    align-items: flex-end;
                  }
                  .apexcharts-legend-series {
                    cursor: pointer;
                    line-height: normal;
                    display: flex;
                    align-items: center;
                  }
                  .apexcharts-legend-text {
                    position: relative;
                    font-size: 14px;
                  }
                  .apexcharts-legend-text *,
                  .apexcharts-legend-marker * {
                    pointer-events: none;
                  }
                  .apexcharts-legend-marker {
                    position: relative;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    margin-right: 1px;
                  }

                  .apexcharts-legend-series.apexcharts-no-click {
                    cursor: auto;
                  }
                  .apexcharts-legend .apexcharts-hidden-zero-series,
                  .apexcharts-legend .apexcharts-hidden-null-series {
                    display: none !important;
                  }
                  .apexcharts-inactive-legend {
                    opacity: 0.45;
                  }
                </style>
              </foreignObject>
              <g
                class="apexcharts-datalabels-group"
                transform="translate(0, 0) scale(1)"
              ></g>
              <g
                class="apexcharts-datalabels-group"
                transform="translate(0, 0) scale(1)"
              ></g>
              <rect
                width="0"
                height="0"
                x="0"
                y="0"
                rx="0"
                ry="0"
                opacity="1"
                stroke-width="0"
                stroke="none"
                stroke-dasharray="0"
                fill="#fefefe"
              ></rect>
              <g
                class="apexcharts-yaxis"
                rel="0"
                transform="translate(19.375, 0)"
              >
                <g class="apexcharts-yaxis-texts-g">
                  <text
                    x="20"
                    y="34"
                    text-anchor="end"
                    dominant-baseline="auto"
                    font-size="12px"
                    font-family="inherit"
                    font-weight="400"
                    fill="#99a1b7"
                    class="apexcharts-text apexcharts-yaxis-label"
                    style="font-family: inherit"
                  >
                    <tspan>10</tspan>
                    <title>10</title>
                  </text>
                  <text
                    x="20"
                    y="91.6825"
                    text-anchor="end"
                    dominant-baseline="auto"
                    font-size="12px"
                    font-family="inherit"
                    font-weight="400"
                    fill="#99a1b7"
                    class="apexcharts-text apexcharts-yaxis-label"
                    style="font-family: inherit"
                  >
                    <tspan>7.75</tspan>
                    <title>7.75</title>
                  </text>
                  <text
                    x="20"
                    y="149.365"
                    text-anchor="end"
                    dominant-baseline="auto"
                    font-size="12px"
                    font-family="inherit"
                    font-weight="400"
                    fill="#99a1b7"
                    class="apexcharts-text apexcharts-yaxis-label"
                    style="font-family: inherit"
                  >
                    <tspan>5.5</tspan>
                    <title>5.5</title>
                  </text>
                  <text
                    x="20"
                    y="207.0475"
                    text-anchor="end"
                    dominant-baseline="auto"
                    font-size="12px"
                    font-family="inherit"
                    font-weight="400"
                    fill="#99a1b7"
                    class="apexcharts-text apexcharts-yaxis-label"
                    style="font-family: inherit"
                  >
                    <tspan>3.25</tspan>
                    <title>3.25</title>
                  </text>
                  <text
                    x="20"
                    y="264.73"
                    text-anchor="end"
                    dominant-baseline="auto"
                    font-size="12px"
                    font-family="inherit"
                    font-weight="400"
                    fill="#99a1b7"
                    class="apexcharts-text apexcharts-yaxis-label"
                    style="font-family: inherit"
                  >
                    <tspan>1</tspan>
                    <title>1</title>
                  </text>
                </g>
              </g>
              <g
                class="apexcharts-inner apexcharts-graphical"
                transform="translate(49.375, 30)"
              >
                <defs>
                  <clipPath id="gridRectMask3249oe1w">
                    <rect
                      width="766.421875"
                      height="237.73000000000002"
                      x="-3.5"
                      y="-3.5"
                      rx="0"
                      ry="0"
                      opacity="1"
                      stroke-width="0"
                      stroke="none"
                      stroke-dasharray="0"
                      fill="#fff"
                    ></rect>
                  </clipPath>
                  <clipPath id="gridRectBarMask3249oe1w">
                    <rect
                      width="766.421875"
                      height="237.73000000000002"
                      x="-3.5"
                      y="-3.5"
                      rx="0"
                      ry="0"
                      opacity="1"
                      stroke-width="0"
                      stroke="none"
                      stroke-dasharray="0"
                      fill="#fff"
                    ></rect>
                  </clipPath>
                  <clipPath id="gridRectMarkerMask3249oe1w">
                    <rect
                      width="766.421875"
                      height="230.73000000000002"
                      x="-3.5"
                      y="0"
                      rx="0"
                      ry="0"
                      opacity="1"
                      stroke-width="0"
                      stroke="none"
                      stroke-dasharray="0"
                      fill="#fff"
                    ></rect>
                  </clipPath>
                  <clipPath id="forecastMask3249oe1w"></clipPath>
                  <clipPath id="nonForecastMask3249oe1w"></clipPath>
                  <linearGradient
                    x1="0"
                    y1="0"
                    x2="0"
                    y2="1"
                    id="SvgjsLinearGradient1005"
                  >
                    <stop
                      stop-opacity="0.4"
                      stop-color="rgba(246,192,0,0.4)"
                      offset="0"
                    ></stop>
                    <stop
                      stop-opacity="0"
                      stop-color="rgba(255,255,255,0)"
                      offset="0.8"
                    ></stop>
                    <stop
                      stop-opacity="0"
                      stop-color="rgba(255,255,255,0)"
                      offset="1"
                    ></stop>
                  </linearGradient>
                </defs>
                <g class="apexcharts-grid">
                  <g class="apexcharts-gridlines-horizontal">
                    <line
                      x1="0"
                      y1="57.682500000000005"
                      x2="759.421875"
                      y2="57.682500000000005"
                      stroke="#dbdfe9"
                      stroke-dasharray="4"
                      stroke-linecap="butt"
                      class="apexcharts-gridline"
                    ></line>
                    <line
                      x1="0"
                      y1="115.36500000000001"
                      x2="759.421875"
                      y2="115.36500000000001"
                      stroke="#dbdfe9"
                      stroke-dasharray="4"
                      stroke-linecap="butt"
                      class="apexcharts-gridline"
                    ></line>
                    <line
                      x1="0"
                      y1="173.0475"
                      x2="759.421875"
                      y2="173.0475"
                      stroke="#dbdfe9"
                      stroke-dasharray="4"
                      stroke-linecap="butt"
                      class="apexcharts-gridline"
                    ></line>
                  </g>
                  <g class="apexcharts-gridlines-vertical"></g>
                  <line
                    x1="0"
                    y1="230.73000000000002"
                    x2="759.421875"
                    y2="230.73000000000002"
                    stroke="transparent"
                    stroke-dasharray="0"
                    stroke-linecap="butt"
                  ></line>
                  <line
                    x1="0"
                    y1="1"
                    x2="0"
                    y2="230.73000000000002"
                    stroke="transparent"
                    stroke-dasharray="0"
                    stroke-linecap="butt"
                  ></line>
                </g>
                <g class="apexcharts-grid-borders">
                  <line
                    x1="0"
                    y1="0"
                    x2="759.421875"
                    y2="0"
                    stroke="#dbdfe9"
                    stroke-dasharray="4"
                    stroke-linecap="butt"
                    class="apexcharts-gridline"
                  ></line>
                  <line
                    x1="0"
                    y1="230.73000000000002"
                    x2="759.421875"
                    y2="230.73000000000002"
                    stroke="#dbdfe9"
                    stroke-dasharray="4"
                    stroke-linecap="butt"
                    class="apexcharts-gridline"
                  ></line>
                </g>
                <g class="apexcharts-area-series apexcharts-plot-series">
                  <g
                    class="apexcharts-series"
                    zIndex="0"
                    seriesName="Position"
                    data:longestSeries="true"
                    rel="1"
                    data:realIndex="0"
                  >
                    <path
                      d="M 0 153.82C 18.985546875 153.82 35.25887276785714 64.09166666666664 54.244419642857146 64.09166666666664C 73.22996651785715 64.09166666666664 89.5032924107143 64.09166666666664 108.48883928571429 64.09166666666664C 127.47438616071429 64.09166666666664 143.74771205357143 102.54666666666665 162.73325892857144 102.54666666666665C 181.71880580357146 102.54666666666665 197.9921316964286 102.54666666666665 216.97767857142858 102.54666666666665C 235.96322544642857 102.54666666666665 252.2365513392857 153.82 271.2220982142857 153.82C 290.20764508928573 153.82 306.4809709821429 153.82 325.4665178571429 153.82C 344.4520647321429 153.82 360.725390625 102.54666666666665 379.7109375 102.54666666666665C 398.696484375 102.54666666666665 414.96981026785716 102.54666666666665 433.95535714285717 102.54666666666665C 452.9409040178572 102.54666666666665 469.2142299107143 51.27333333333331 488.19977678571433 51.27333333333331C 507.18532366071435 51.27333333333331 523.4586495535715 51.27333333333331 542.4441964285714 51.27333333333331C 561.4297433035714 51.27333333333331 577.7030691964286 102.54666666666665 596.6886160714286 102.54666666666665C 615.6741629464286 102.54666666666665 631.9474888392857 102.54666666666665 650.9330357142858 102.54666666666665C 669.9185825892857 102.54666666666665 686.1919084821429 76.91 705.1774553571429 76.91C 724.1630022321428 76.91 740.436328125 76.91 759.421875 76.91C 759.421875 76.91 759.421875 76.91 759.421875 230.73000000000002 L 0 230.73000000000002z"
                      fill="url(#SvgjsLinearGradient1005)"
                      fill-opacity="1"
                      stroke="none"
                      stroke-opacity="1"
                      stroke-linecap="butt"
                      stroke-width="0"
                      stroke-dasharray="0"
                      class="apexcharts-area"
                      index="0"
                      clip-path="url(#gridRectMask3249oe1w)"
                      pathTo="M 0 153.82C 18.985546875 153.82 35.25887276785714 64.09166666666664 54.244419642857146 64.09166666666664C 73.22996651785715 64.09166666666664 89.5032924107143 64.09166666666664 108.48883928571429 64.09166666666664C 127.47438616071429 64.09166666666664 143.74771205357143 102.54666666666665 162.73325892857144 102.54666666666665C 181.71880580357146 102.54666666666665 197.9921316964286 102.54666666666665 216.97767857142858 102.54666666666665C 235.96322544642857 102.54666666666665 252.2365513392857 153.82 271.2220982142857 153.82C 290.20764508928573 153.82 306.4809709821429 153.82 325.4665178571429 153.82C 344.4520647321429 153.82 360.725390625 102.54666666666665 379.7109375 102.54666666666665C 398.696484375 102.54666666666665 414.96981026785716 102.54666666666665 433.95535714285717 102.54666666666665C 452.9409040178572 102.54666666666665 469.2142299107143 51.27333333333331 488.19977678571433 51.27333333333331C 507.18532366071435 51.27333333333331 523.4586495535715 51.27333333333331 542.4441964285714 51.27333333333331C 561.4297433035714 51.27333333333331 577.7030691964286 102.54666666666665 596.6886160714286 102.54666666666665C 615.6741629464286 102.54666666666665 631.9474888392857 102.54666666666665 650.9330357142858 102.54666666666665C 669.9185825892857 102.54666666666665 686.1919084821429 76.91 705.1774553571429 76.91C 724.1630022321428 76.91 740.436328125 76.91 759.421875 76.91C 759.421875 76.91 759.421875 76.91 759.421875 230.73000000000002 L 0 230.73000000000002z"
                      pathFrom="M 0 230.73000000000002 L 0 230.73000000000002 L 54.244419642857146 230.73000000000002 L 108.48883928571429 230.73000000000002 L 162.73325892857144 230.73000000000002 L 216.97767857142858 230.73000000000002 L 271.2220982142857 230.73000000000002 L 325.4665178571429 230.73000000000002 L 379.7109375 230.73000000000002 L 433.95535714285717 230.73000000000002 L 488.19977678571433 230.73000000000002 L 542.4441964285714 230.73000000000002 L 596.6886160714286 230.73000000000002 L 650.9330357142858 230.73000000000002 L 705.1774553571429 230.73000000000002 L 759.421875 230.73000000000002z"
                    ></path>
                    <path
                      d="M 0 153.82C 18.985546875 153.82 35.25887276785714 64.09166666666664 54.244419642857146 64.09166666666664C 73.22996651785715 64.09166666666664 89.5032924107143 64.09166666666664 108.48883928571429 64.09166666666664C 127.47438616071429 64.09166666666664 143.74771205357143 102.54666666666665 162.73325892857144 102.54666666666665C 181.71880580357146 102.54666666666665 197.9921316964286 102.54666666666665 216.97767857142858 102.54666666666665C 235.96322544642857 102.54666666666665 252.2365513392857 153.82 271.2220982142857 153.82C 290.20764508928573 153.82 306.4809709821429 153.82 325.4665178571429 153.82C 344.4520647321429 153.82 360.725390625 102.54666666666665 379.7109375 102.54666666666665C 398.696484375 102.54666666666665 414.96981026785716 102.54666666666665 433.95535714285717 102.54666666666665C 452.9409040178572 102.54666666666665 469.2142299107143 51.27333333333331 488.19977678571433 51.27333333333331C 507.18532366071435 51.27333333333331 523.4586495535715 51.27333333333331 542.4441964285714 51.27333333333331C 561.4297433035714 51.27333333333331 577.7030691964286 102.54666666666665 596.6886160714286 102.54666666666665C 615.6741629464286 102.54666666666665 631.9474888392857 102.54666666666665 650.9330357142858 102.54666666666665C 669.9185825892857 102.54666666666665 686.1919084821429 76.91 705.1774553571429 76.91C 724.1630022321428 76.91 740.436328125 76.91 759.421875 76.91"
                      fill="none"
                      fill-opacity="1"
                      stroke="#f6c000"
                      stroke-opacity="1"
                      stroke-linecap="butt"
                      stroke-width="3"
                      stroke-dasharray="0"
                      class="apexcharts-area"
                      index="0"
                      clip-path="url(#gridRectMask3249oe1w)"
                      pathTo="M 0 153.82C 18.985546875 153.82 35.25887276785714 64.09166666666664 54.244419642857146 64.09166666666664C 73.22996651785715 64.09166666666664 89.5032924107143 64.09166666666664 108.48883928571429 64.09166666666664C 127.47438616071429 64.09166666666664 143.74771205357143 102.54666666666665 162.73325892857144 102.54666666666665C 181.71880580357146 102.54666666666665 197.9921316964286 102.54666666666665 216.97767857142858 102.54666666666665C 235.96322544642857 102.54666666666665 252.2365513392857 153.82 271.2220982142857 153.82C 290.20764508928573 153.82 306.4809709821429 153.82 325.4665178571429 153.82C 344.4520647321429 153.82 360.725390625 102.54666666666665 379.7109375 102.54666666666665C 398.696484375 102.54666666666665 414.96981026785716 102.54666666666665 433.95535714285717 102.54666666666665C 452.9409040178572 102.54666666666665 469.2142299107143 51.27333333333331 488.19977678571433 51.27333333333331C 507.18532366071435 51.27333333333331 523.4586495535715 51.27333333333331 542.4441964285714 51.27333333333331C 561.4297433035714 51.27333333333331 577.7030691964286 102.54666666666665 596.6886160714286 102.54666666666665C 615.6741629464286 102.54666666666665 631.9474888392857 102.54666666666665 650.9330357142858 102.54666666666665C 669.9185825892857 102.54666666666665 686.1919084821429 76.91 705.1774553571429 76.91C 724.1630022321428 76.91 740.436328125 76.91 759.421875 76.91"
                      pathFrom="M 0 230.73000000000002 L 0 230.73000000000002 L 54.244419642857146 230.73000000000002 L 108.48883928571429 230.73000000000002 L 162.73325892857144 230.73000000000002 L 216.97767857142858 230.73000000000002 L 271.2220982142857 230.73000000000002 L 325.4665178571429 230.73000000000002 L 379.7109375 230.73000000000002 L 433.95535714285717 230.73000000000002 L 488.19977678571433 230.73000000000002 L 542.4441964285714 230.73000000000002 L 596.6886160714286 230.73000000000002 L 650.9330357142858 230.73000000000002 L 705.1774553571429 230.73000000000002 L 759.421875 230.73000000000002"
                      fill-rule="evenodd"
                    ></path>
                    <g
                      class="apexcharts-series-markers-wrap apexcharts-hidden-element-shown"
                      data:realIndex="0"
                    >
                      <g class="apexcharts-series-markers">
                        <path
                          d="M 0, 0 
           m -0, 0 
           a 0,0 0 1,0 0,0 
           a 0,0 0 1,0 -0,0"
                          fill="#f6c000"
                          fill-opacity="1"
                          stroke="#f6c000"
                          stroke-opacity="0.9"
                          stroke-linecap="butt"
                          stroke-width="3"
                          stroke-dasharray="0"
                          cx="0"
                          cy="0"
                          shape="circle"
                          class="apexcharts-marker w9fqbdixz no-pointer-events"
                          default-marker-size="0"
                        ></path>
                      </g>
                    </g>
                  </g>
                  <g class="apexcharts-datalabels" data:realIndex="0"></g>
                </g>
                <line
                  x1="0"
                  y1="0"
                  x2="0"
                  y2="230.73000000000002"
                  stroke="#f6c000"
                  stroke-dasharray="3"
                  stroke-linecap="butt"
                  class="apexcharts-xcrosshairs"
                  x="0"
                  y="0"
                  width="1"
                  height="230.73000000000002"
                  fill="#b1b9c4"
                  filter="none"
                  fill-opacity="0.9"
                  stroke-width="1"
                ></line>
                <line
                  x1="0"
                  y1="0"
                  x2="759.421875"
                  y2="0"
                  stroke="#b6b6b6"
                  stroke-dasharray="0"
                  stroke-width="1"
                  stroke-linecap="butt"
                  class="apexcharts-ycrosshairs"
                ></line>
                <line
                  x1="0"
                  y1="0"
                  x2="759.421875"
                  y2="0"
                  stroke="#b6b6b6"
                  stroke-dasharray="0"
                  stroke-width="0"
                  stroke-linecap="butt"
                  class="apexcharts-ycrosshairs-hidden"
                ></line>
                <g class="apexcharts-xaxis" transform="translate(20, 0)">
                  <g
                    class="apexcharts-xaxis-texts-g"
                    transform="translate(0, -4)"
                  >
                    <text
                      x="0"
                      y="258.73"
                      text-anchor="middle"
                      dominant-baseline="auto"
                      font-size="12px"
                      font-family="inherit"
                      font-weight="400"
                      fill="#99a1b7"
                      class="apexcharts-text apexcharts-xaxis-label"
                      style="font-family: inherit"
                    >
                      <tspan>Apr 02</tspan>
                      <title>Apr 02</title>
                    </text>
                    <text
                      x="54.24441964285715"
                      y="258.73"
                      text-anchor="middle"
                      dominant-baseline="auto"
                      font-size="12px"
                      font-family="inherit"
                      font-weight="400"
                      fill="#99a1b7"
                      class="apexcharts-text apexcharts-xaxis-label"
                      style="font-family: inherit"
                    >
                      <tspan></tspan>
                      <title></title>
                    </text>
                    <text
                      x="108.48883928571429"
                      y="258.73"
                      text-anchor="middle"
                      dominant-baseline="auto"
                      font-size="12px"
                      font-family="inherit"
                      font-weight="400"
                      fill="#99a1b7"
                      class="apexcharts-text apexcharts-xaxis-label"
                      style="font-family: inherit"
                    >
                      <tspan></tspan>
                      <title></title>
                    </text>
                    <text
                      x="162.73325892857142"
                      y="258.73"
                      text-anchor="middle"
                      dominant-baseline="auto"
                      font-size="12px"
                      font-family="inherit"
                      font-weight="400"
                      fill="#99a1b7"
                      class="apexcharts-text apexcharts-xaxis-label"
                      style="font-family: inherit"
                    >
                      <tspan>Apr 05</tspan>
                      <title>Apr 05</title>
                    </text>
                    <text
                      x="216.97767857142856"
                      y="258.73"
                      text-anchor="middle"
                      dominant-baseline="auto"
                      font-size="12px"
                      font-family="inherit"
                      font-weight="400"
                      fill="#99a1b7"
                      class="apexcharts-text apexcharts-xaxis-label"
                      style="font-family: inherit"
                    >
                      <tspan></tspan>
                      <title></title>
                    </text>
                    <text
                      x="271.2220982142857"
                      y="258.73"
                      text-anchor="middle"
                      dominant-baseline="auto"
                      font-size="12px"
                      font-family="inherit"
                      font-weight="400"
                      fill="#99a1b7"
                      class="apexcharts-text apexcharts-xaxis-label"
                      style="font-family: inherit"
                    >
                      <tspan></tspan>
                      <title></title>
                    </text>
                    <text
                      x="325.4665178571429"
                      y="258.73"
                      text-anchor="middle"
                      dominant-baseline="auto"
                      font-size="12px"
                      font-family="inherit"
                      font-weight="400"
                      fill="#99a1b7"
                      class="apexcharts-text apexcharts-xaxis-label"
                      style="font-family: inherit"
                    >
                      <tspan>Apr 10</tspan>
                      <title>Apr 10</title>
                    </text>
                    <text
                      x="379.71093750000006"
                      y="258.73"
                      text-anchor="middle"
                      dominant-baseline="auto"
                      font-size="12px"
                      font-family="inherit"
                      font-weight="400"
                      fill="#99a1b7"
                      class="apexcharts-text apexcharts-xaxis-label"
                      style="font-family: inherit"
                    >
                      <tspan></tspan>
                      <title></title>
                    </text>
                    <text
                      x="433.9553571428572"
                      y="258.73"
                      text-anchor="middle"
                      dominant-baseline="auto"
                      font-size="12px"
                      font-family="inherit"
                      font-weight="400"
                      fill="#99a1b7"
                      class="apexcharts-text apexcharts-xaxis-label"
                      style="font-family: inherit"
                    >
                      <tspan></tspan>
                      <title></title>
                    </text>
                    <text
                      x="488.19977678571433"
                      y="258.73"
                      text-anchor="middle"
                      dominant-baseline="auto"
                      font-size="12px"
                      font-family="inherit"
                      font-weight="400"
                      fill="#99a1b7"
                      class="apexcharts-text apexcharts-xaxis-label"
                      style="font-family: inherit"
                    >
                      <tspan>Apr 17</tspan>
                      <title>Apr 17</title>
                    </text>
                    <text
                      x="542.4441964285714"
                      y="258.73"
                      text-anchor="middle"
                      dominant-baseline="auto"
                      font-size="12px"
                      font-family="inherit"
                      font-weight="400"
                      fill="#99a1b7"
                      class="apexcharts-text apexcharts-xaxis-label"
                      style="font-family: inherit"
                    >
                      <tspan></tspan>
                      <title></title>
                    </text>
                    <text
                      x="596.6886160714286"
                      y="258.73"
                      text-anchor="middle"
                      dominant-baseline="auto"
                      font-size="12px"
                      font-family="inherit"
                      font-weight="400"
                      fill="#99a1b7"
                      class="apexcharts-text apexcharts-xaxis-label"
                      style="font-family: inherit"
                    >
                      <tspan></tspan>
                      <title></title>
                    </text>
                    <text
                      x="650.9330357142857"
                      y="258.73"
                      text-anchor="middle"
                      dominant-baseline="auto"
                      font-size="12px"
                      font-family="inherit"
                      font-weight="400"
                      fill="#99a1b7"
                      class="apexcharts-text apexcharts-xaxis-label"
                      style="font-family: inherit"
                    >
                      <tspan>Apr 20</tspan>
                      <title>Apr 20</title>
                    </text>
                    <text
                      x="705.1774553571428"
                      y="258.73"
                      text-anchor="middle"
                      dominant-baseline="auto"
                      font-size="12px"
                      font-family="inherit"
                      font-weight="400"
                      fill="#99a1b7"
                      class="apexcharts-text apexcharts-xaxis-label"
                      style="font-family: inherit"
                    >
                      <tspan></tspan>
                      <title></title>
                    </text>
                    <text
                      x="759.4218749999999"
                      y="258.73"
                      text-anchor="middle"
                      dominant-baseline="auto"
                      font-size="12px"
                      font-family="inherit"
                      font-weight="400"
                      fill="#99a1b7"
                      class="apexcharts-text apexcharts-xaxis-label"
                      style="font-family: inherit"
                    >
                      <tspan></tspan>
                      <title></title>
                    </text>
                  </g>
                </g>
                <g class="apexcharts-yaxis-annotations"></g>
                <g class="apexcharts-xaxis-annotations"></g>
                <g class="apexcharts-point-annotations"></g>
              </g>
              <rect
                width="0"
                height="0"
                x="0"
                y="0"
                rx="0"
                ry="0"
                opacity="1"
                stroke-width="0"
                stroke="none"
                stroke-dasharray="0"
                fill="#fefefe"
                class="apexcharts-zoom-rect"
              ></rect>
              <rect
                width="0"
                height="0"
                x="0"
                y="0"
                rx="0"
                ry="0"
                opacity="1"
                stroke-width="0"
                stroke="none"
                stroke-dasharray="0"
                fill="#fefefe"
                class="apexcharts-selection-rect"
              ></rect>
            </svg>
            <div class="apexcharts-legend" style="max-height: 150px"></div>
            <div class="apexcharts-tooltip apexcharts-theme-light">
              <div
                class="apexcharts-tooltip-title"
                style="font-family: inherit; font-size: 12px"
              ></div>
              <div
                class="apexcharts-tooltip-series-group apexcharts-tooltip-series-group-0"
                style="order: 1"
              >
                <span
                  class="apexcharts-tooltip-marker"
                  shape="circle"
                  style="color: rgb(246, 192, 0)"
                ></span>
                <div
                  class="apexcharts-tooltip-text"
                  style="font-family: inherit; font-size: 12px"
                >
                  <div class="apexcharts-tooltip-y-group">
                    <span class="apexcharts-tooltip-text-y-label"></span
                    ><span class="apexcharts-tooltip-text-y-value"></span>
                  </div>
                  <div class="apexcharts-tooltip-goals-group">
                    <span class="apexcharts-tooltip-text-goals-label"></span
                    ><span class="apexcharts-tooltip-text-goals-value"></span>
                  </div>
                  <div class="apexcharts-tooltip-z-group">
                    <span class="apexcharts-tooltip-text-z-label"></span
                    ><span class="apexcharts-tooltip-text-z-value"></span>
                  </div>
                </div>
              </div>
            </div>
            <div
              class="apexcharts-xaxistooltip apexcharts-xaxistooltip-bottom apexcharts-theme-light"
            >
              <div
                class="apexcharts-xaxistooltip-text"
                style="font-family: inherit; font-size: 12px"
              ></div>
            </div>
            <div
              class="apexcharts-yaxistooltip apexcharts-yaxistooltip-0 apexcharts-yaxistooltip-left apexcharts-theme-light"
            >
              <div class="apexcharts-yaxistooltip-text"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xxl-4 mb-5 mb-xl-10">
    <div class="card card-flush h-xl-100">
      <div class="card-header py-7">
        <div class="m-0">
          <div class="d-flex align-items-center mb-2">
            <span class="fs-2hx fw-bold text-gray-800 me-2 lh-1 ls-n2"
              >35,568</span
            >

            <span class="badge badge-light-danger fs-base">
              <i class="ki-outline ki-arrow-up fs-5 text-danger ms-n1"></i>
              8.02%
            </span>
          </div>

          <span class="fs-6 fw-semibold text-gray-500"
            >Sistemas por Região</span
          >
        </div>
      </div>

      <div class="card-body pt-0 pb-1">
        <div
          id="kt_charts_widget_27"
          class="min-h-auto"
          style="min-height: 365px"
        >
          Grafico aqui
        </div>
      </div>
    </div>
  </div>

  <div class="col-xxl-4 mb-5 mb-xl-10">
    <div class="card card-flush h-xl-100">
      <div class="card-header py-7">
        <div class="m-0">
          <div class="d-flex align-items-center mb-2">
            <span class="fs-2hx fw-bold text-gray-800 me-2 lh-1 ls-n2"
              >5,037</span
            >

            <span class="badge badge-light-success fs-base">
              <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>
              2.2%
            </span>
          </div>

          <span class="fs-6 fw-semibold text-gray-500"
            >Visits by Social Networks</span
          >
        </div>
      </div>

      <div
        class="card-body card-body d-flex justify-content-between flex-column pt-3"
      >
        <div class="d-flex flex-stack">
          <img
            src="/metronic8/demo32/assets/media/svg/brand-logos/dribbble-icon-1.svg"
            class="me-4 w-30px"
            style="border-radius: 4px"
            alt=""
          />

          <div
            class="d-flex align-items-center flex-stack flex-wrap flex-row-fluid d-grid gap-2"
          >
            <div class="me-5">
              <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6"
                >Dribbble</a
              >

              <span
                class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0"
                >Community</span
              >
            </div>

            <div class="d-flex align-items-center">
              <span class="text-gray-800 fw-bold fs-4 me-3">579</span>

              <div class="m-0">
                <span class="badge badge-light-success fs-base">
                  <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>
                  2.6%
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="separator separator-dashed my-3"></div>

        <div class="d-flex flex-stack">
          <img
            src="/metronic8/demo32/assets/media/svg/brand-logos/linkedin-1.svg"
            class="me-4 w-30px"
            style="border-radius: 4px"
            alt=""
          />

          <div
            class="d-flex align-items-center flex-stack flex-wrap flex-row-fluid d-grid gap-2"
          >
            <div class="me-5">
              <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6"
                >Linked In</a
              >

              <span
                class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0"
                >Social Media</span
              >
            </div>

            <div class="d-flex align-items-center">
              <span class="text-gray-800 fw-bold fs-4 me-3">1,088</span>

              <div class="m-0">
                <span class="badge badge-light-danger fs-base">
                  <i
                    class="ki-outline ki-arrow-down fs-5 text-danger ms-n1"
                  ></i>
                  0.4%
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="separator separator-dashed my-3"></div>

        <div class="d-flex flex-stack">
          <img
            src="/metronic8/demo32/assets/media/svg/brand-logos/slack-icon.svg"
            class="me-4 w-30px"
            style="border-radius: 4px"
            alt=""
          />

          <div
            class="d-flex align-items-center flex-stack flex-wrap flex-row-fluid d-grid gap-2"
          >
            <div class="me-5">
              <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6"
                >Slack</a
              >

              <span
                class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0"
                >Messanger</span
              >
            </div>

            <div class="d-flex align-items-center">
              <span class="text-gray-800 fw-bold fs-4 me-3">794</span>

              <div class="m-0">
                <span class="badge badge-light-success fs-base">
                  <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>
                  0.2%
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="separator separator-dashed my-3"></div>

        <div class="d-flex flex-stack">
          <img
            src="/metronic8/demo32/assets/media/svg/brand-logos/youtube-3.svg"
            class="me-4 w-30px"
            style="border-radius: 4px"
            alt=""
          />

          <div
            class="d-flex align-items-center flex-stack flex-wrap flex-row-fluid d-grid gap-2"
          >
            <div class="me-5">
              <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6"
                >YouTube</a
              >

              <span
                class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0"
                >Video Channel</span
              >
            </div>

            <div class="d-flex align-items-center">
              <span class="text-gray-800 fw-bold fs-4 me-3">978</span>

              <div class="m-0">
                <span class="badge badge-light-success fs-base">
                  <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>
                  4.1%
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="separator separator-dashed my-3"></div>

        <div class="d-flex flex-stack">
          <img
            src="/metronic8/demo32/assets/media/svg/brand-logos/instagram-2-1.svg"
            class="me-4 w-30px"
            style="border-radius: 4px"
            alt=""
          />

          <div
            class="d-flex align-items-center flex-stack flex-wrap flex-row-fluid d-grid gap-2"
          >
            <div class="me-5">
              <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6"
                >Instagram</a
              >

              <span
                class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0"
                >Social Network</span
              >
            </div>

            <div class="d-flex align-items-center">
              <span class="text-gray-800 fw-bold fs-4 me-3">1,458</span>

              <div class="m-0">
                <span class="badge badge-light-success fs-base">
                  <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>
                  8.3%
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-4 mb-5 mb-xl-0">
    <div class="card card-flush h-xl-100">
      <div class="card-header pt-7">
        <h3 class="card-title align-items-start flex-column">
          <span class="card-label fw-bold text-gray-800"
            >Módulos mais vendidos</span
          >
          <span class="text-gray-500 mt-1 fw-semibold fs-6">1.23k ativos</span>
        </h3>
      </div>

      <div class="card-body d-flex align-items-end">
        <div class="w-100">
          <div class="d-flex align-items-center">
            <div class="symbol symbol-30px me-5">
              <span class="symbol-label">
                <i class="ki-outline ki-rocket fs-3 text-gray-600"></i>
              </span>
            </div>

            <div
              class="d-flex align-items-center flex-stack flex-wrap d-grid gap-1 flex-row-fluid"
            >
              <div class="me-5">
                <a
                  href="#"
                  class="text-gray-800 fw-bold text-hover-primary fs-6"
                  >Direct Source</a
                >

                <span
                  class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0"
                  >Direct link clicks</span
                >
              </div>

              <div class="d-flex align-items-center">
                <span class="text-gray-800 fw-bold fs-4 me-3">1,067</span>

                <span class="badge badge-light-success fs-base">
                  <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>

                  2.6%
                </span>
              </div>
            </div>
          </div>

          <div class="separator separator-dashed my-3"></div>

          <div class="d-flex align-items-center">
            <div class="symbol symbol-30px me-5">
              <span class="symbol-label">
                <i class="ki-outline ki-tiktok fs-3 text-gray-600"></i>
              </span>
            </div>

            <div
              class="d-flex align-items-center flex-stack flex-wrap d-grid gap-1 flex-row-fluid"
            >
              <div class="me-5">
                <a
                  href="#"
                  class="text-gray-800 fw-bold text-hover-primary fs-6"
                  >Social Networks</a
                >

                <span
                  class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0"
                  >All Social Channels
                </span>
              </div>

              <div class="d-flex align-items-center">
                <span class="text-gray-800 fw-bold fs-4 me-3">24,588</span>

                <span class="badge badge-light-success fs-base">
                  <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>

                  4.1%
                </span>
              </div>
            </div>
          </div>

          <div class="separator separator-dashed my-3"></div>

          <div class="d-flex align-items-center">
            <div class="symbol symbol-30px me-5">
              <span class="symbol-label">
                <i class="ki-outline ki-sms fs-3 text-gray-600"></i>
              </span>
            </div>

            <div
              class="d-flex align-items-center flex-stack flex-wrap d-grid gap-1 flex-row-fluid"
            >
              <div class="me-5">
                <a
                  href="#"
                  class="text-gray-800 fw-bold text-hover-primary fs-6"
                  >Email Newsletter</a
                >

                <span
                  class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0"
                  >Mailchimp Campaigns</span
                >
              </div>

              <div class="d-flex align-items-center">
                <span class="text-gray-800 fw-bold fs-4 me-3">794</span>

                <span class="badge badge-light-success fs-base">
                  <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>

                  0.2%
                </span>
              </div>
            </div>
          </div>

          <div class="separator separator-dashed my-3"></div>

          <div class="d-flex align-items-center">
            <div class="symbol symbol-30px me-5">
              <span class="symbol-label">
                <i class="ki-outline ki-icon fs-3 text-gray-600"></i>
              </span>
            </div>

            <div
              class="d-flex align-items-center flex-stack flex-wrap d-grid gap-1 flex-row-fluid"
            >
              <div class="me-5">
                <a
                  href="#"
                  class="text-gray-800 fw-bold text-hover-primary fs-6"
                  >Referrals</a
                >

                <span
                  class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0"
                  >Impact Radius visits</span
                >
              </div>

              <div class="d-flex align-items-center">
                <span class="text-gray-800 fw-bold fs-4 me-3">6,578</span>

                <span class="badge badge-light-danger fs-base">
                  <i
                    class="ki-outline ki-arrow-down fs-5 text-danger ms-n1"
                  ></i>

                  0.4%
                </span>
              </div>
            </div>
          </div>

          <div class="separator separator-dashed my-3"></div>

          <div class="d-flex align-items-center">
            <div class="symbol symbol-30px me-5">
              <span class="symbol-label">
                <i class="ki-outline ki-abstract-25 fs-3 text-gray-600"></i>
              </span>
            </div>

            <div
              class="d-flex align-items-center flex-stack flex-wrap d-grid gap-1 flex-row-fluid"
            >
              <div class="me-5">
                <a
                  href="#"
                  class="text-gray-800 fw-bold text-hover-primary fs-6"
                  >Other</a
                >

                <span
                  class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0"
                  >Many Sources</span
                >
              </div>

              <div class="d-flex align-items-center">
                <span class="text-gray-800 fw-bold fs-4 me-3">79,458</span>

                <span class="badge badge-light-success fs-base">
                  <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>

                  8.3%
                </span>
              </div>
            </div>
          </div>

          <div class="separator separator-dashed my-3"></div>

          <div class="d-flex align-items-center">
            <div class="symbol symbol-30px me-5">
              <span class="symbol-label">
                <i class="ki-outline ki-abstract-39 fs-3 text-gray-600"></i>
              </span>
            </div>

            <div
              class="d-flex align-items-center flex-stack flex-wrap d-grid gap-1 flex-row-fluid"
            >
              <div class="me-5">
                <a
                  href="#"
                  class="text-gray-800 fw-bold text-hover-primary fs-6"
                  >Rising Networks</a
                >

                <span
                  class="text-gray-500 fw-semibold fs-7 d-block text-start ps-0"
                  >Social Network</span
                >
              </div>

              <div class="d-flex align-items-center">
                <span class="text-gray-800 fw-bold fs-4 me-3">18,047</span>

                <span class="badge badge-light-success fs-base">
                  <i class="ki-outline ki-arrow-up fs-5 text-success ms-n1"></i>

                  1.9%
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-6 mb-5 mb-xl-10">
    <div class="card card-flush h-xl-100">
      <div class="card-header pt-5">
        <h3 class="card-title align-items-start flex-column">
          <span class="card-label fw-bold text-gray-800">Últimas Vendas</span>

          <span class="text-gray-500 pt-1 fw-semibold fs-6"
            >Counted in Millions</span
          >
        </h3>

        <div class="card-toolbar">
          <a href="#" class="btn btn-sm btn-light">PDF Report</a>
        </div>
      </div>

      <div class="card-body py-3">
        <div class="table-responsive">
          <table class="table table-row-dashed align-middle gs-0 gy-4">
            <thead>
              <tr class="fs-7 fw-bold border-0 text-gray-500">
                <th class="min-w-150px" colspan="2">CAMPAIGN</th>
                <th class="min-w-150px text-end pe-0" colspan="2">SESSIONS</th>
                <th class="text-end min-w-150px" colspan="2">
                  CONVERSION RATE
                </th>
              </tr>
            </thead>

            <tbody>
              <tr>
                <td class="" colspan="2">
                  <a
                    href="#"
                    class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6"
                    >Google</a
                  >
                </td>

                <td class="pe-0" colspan="2">
                  <div class="d-flex justify-content-end">
                    <span class="text-gray-800 fw-bold fs-6 me-1">1,256</span>

                    <span
                      class="text-danger min-w-50px d-block text-end fw-bold fs-6"
                      >-935</span
                    >
                  </div>
                </td>

                <td class="" colspan="2">
                  <div class="d-flex justify-content-end">
                    <span class="text-gray-900 fw-bold fs-6 me-3">23.63%</span>

                    <span
                      class="text-danger min-w-60px d-block text-end fw-bold fs-6"
                      >-9.35%</span
                    >
                  </div>
                </td>
              </tr>
              <tr>
                <td class="" colspan="2">
                  <a
                    href="#"
                    class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6"
                    >Facebook</a
                  >
                </td>

                <td class="pe-0" colspan="2">
                  <div class="d-flex justify-content-end">
                    <span class="text-gray-800 fw-bold fs-6 me-1">446</span>

                    <span
                      class="text-danger min-w-50px d-block text-end fw-bold fs-6"
                      >-576</span
                    >
                  </div>
                </td>

                <td class="" colspan="2">
                  <div class="d-flex justify-content-end">
                    <span class="text-gray-900 fw-bold fs-6 me-3">12.45%</span>

                    <span
                      class="text-danger min-w-60px d-block text-end fw-bold fs-6"
                      >-57.02%</span
                    >
                  </div>
                </td>
              </tr>
              <tr>
                <td class="" colspan="2">
                  <a
                    href="#"
                    class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6"
                    >Bol.com</a
                  >
                </td>

                <td class="pe-0" colspan="2">
                  <div class="d-flex justify-content-end">
                    <span class="text-gray-800 fw-bold fs-6 me-1">67</span>

                    <span
                      class="text-success min-w-50px d-block text-end fw-bold fs-6"
                      >+24</span
                    >
                  </div>
                </td>

                <td class="" colspan="2">
                  <div class="d-flex justify-content-end">
                    <span class="text-gray-900 fw-bold fs-6 me-3">73.63%</span>

                    <span
                      class="text-success min-w-60px d-block text-end fw-bold fs-6"
                      >+28.73%</span
                    >
                  </div>
                </td>
              </tr>
              <tr>
                <td class="" colspan="2">
                  <a
                    href="#"
                    class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6"
                    >Dutchnews.nl</a
                  >
                </td>

                <td class="pe-0" colspan="2">
                  <div class="d-flex justify-content-end">
                    <span class="text-gray-800 fw-bold fs-6 me-1">2,136</span>

                    <span
                      class="text-danger min-w-50px d-block text-end fw-bold fs-6"
                      >-1,229</span
                    >
                  </div>
                </td>

                <td class="" colspan="2">
                  <div class="d-flex justify-content-end">
                    <span class="text-gray-900 fw-bold fs-6 me-3">3.67%</span>

                    <span
                      class="text-danger min-w-60px d-block text-end fw-bold fs-6"
                      >-12.29%</span
                    >
                  </div>
                </td>
              </tr>
              <tr>
                <td class="" colspan="2">
                  <a
                    href="#"
                    class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6"
                    >Stackoverflow</a
                  >
                </td>

                <td class="pe-0" colspan="2">
                  <div class="d-flex justify-content-end">
                    <span class="text-gray-800 fw-bold fs-6 me-1">945</span>

                    <span
                      class="text-danger min-w-50px d-block text-end fw-bold fs-6"
                      >-634</span
                    >
                  </div>
                </td>

                <td class="" colspan="2">
                  <div class="d-flex justify-content-end">
                    <span class="text-gray-900 fw-bold fs-6 me-3">25.03%</span>

                    <span
                      class="text-danger min-w-60px d-block text-end fw-bold fs-6"
                      >-9.35%</span
                    >
                  </div>
                </td>
              </tr>
              <tr>
                <td class="" colspan="2">
                  <a
                    href="#"
                    class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6"
                    >Themeforest</a
                  >
                </td>

                <td class="pe-0" colspan="2">
                  <div class="d-flex justify-content-end">
                    <span class="text-gray-800 fw-bold fs-6 me-1">237</span>

                    <span
                      class="text-success min-w-50px d-block text-end fw-bold fs-6"
                      >106</span
                    >
                  </div>
                </td>

                <td class="" colspan="2">
                  <div class="d-flex justify-content-end">
                    <span class="text-gray-900 fw-bold fs-6 me-3">36.52%</span>

                    <span
                      class="text-success min-w-60px d-block text-end fw-bold fs-6"
                      >+3.06%</span
                    >
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-xl-6 mb-5 mb-xl-10">
    <div class="card card-flush h-xl-100">
      <div class="card-header pt-5">
        <h3 class="card-title align-items-start flex-column">
          <span class="card-label fw-bold text-gray-800"
            >Cupons mais usados</span
          >

          <span class="text-gray-500 pt-2 fw-semibold fs-6"
            >Contagem nos últimos 3 meses</span
          >
        </h3>

        <div class="card-toolbar">
          <a href="#" class="btn btn-sm btn-light">PDF Report</a>
        </div>
      </div>

      <div class="card-body d-flex justify-content-between flex-column py-3">
        <div class="m-0"></div>

        <div class="table-responsive mb-n2">
          <table class="table table-row-dashed gs-0 gy-4">
            <thead>
              <tr class="fs-7 fw-bold border-0 text-gray-500">
                <th class="min-w-300px">KEYWORD</th>
                <th class="min-w-100px">CLICKS</th>
              </tr>
            </thead>

            <tbody>
              <tr>
                <td>
                  <a
                    href="#"
                    class="text-gray-600 fw-bold text-hover-primary mb-1 fs-6"
                    >Buy phone online</a
                  >
                </td>
                <td class="d-flex align-items-center border-0">
                  <span class="fw-bold text-gray-800 fs-6 me-3">263</span>

                  <div class="progress rounded-start-0">
                    <div
                      class="progress-bar bg-success m-0"
                      role="progressbar"
                      style="height: 12px; width: 166px"
                      aria-valuenow="166"
                      aria-valuemin="0"
                      aria-valuemax="166px"
                    ></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <a
                    href="#"
                    class="text-gray-600 fw-bold text-hover-primary mb-1 fs-6"
                    >Top 10 Earbuds</a
                  >
                </td>
                <td class="d-flex align-items-center border-0">
                  <span class="fw-bold text-gray-800 fs-6 me-3">238</span>

                  <div class="progress rounded-start-0">
                    <div
                      class="progress-bar bg-success m-0"
                      role="progressbar"
                      style="height: 12px; width: 158px"
                      aria-valuenow="158"
                      aria-valuemin="0"
                      aria-valuemax="158px"
                    ></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <a
                    href="#"
                    class="text-gray-600 fw-bold text-hover-primary mb-1 fs-6"
                    >Cyber Monday</a
                  >
                </td>
                <td class="d-flex align-items-center border-0">
                  <span class="fw-bold text-gray-800 fs-6 me-3">189</span>

                  <div class="progress rounded-start-0">
                    <div
                      class="progress-bar bg-success m-0"
                      role="progressbar"
                      style="height: 12px; width: 129px"
                      aria-valuenow="129"
                      aria-valuemin="0"
                      aria-valuemax="129px"
                    ></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <a
                    href="#"
                    class="text-gray-600 fw-bold text-hover-primary mb-1 fs-6"
                    >OLED TV in Amsterdam</a
                  >
                </td>
                <td class="d-flex align-items-center border-0">
                  <span class="fw-bold text-gray-800 fs-6 me-3">263</span>

                  <div class="progress rounded-start-0">
                    <div
                      class="progress-bar bg-success m-0"
                      role="progressbar"
                      style="height: 12px; width: 112px"
                      aria-valuenow="112"
                      aria-valuemin="0"
                      aria-valuemax="112px"
                    ></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <a
                    href="#"
                    class="text-gray-600 fw-bold text-hover-primary mb-1 fs-6"
                    >Macbook M1</a
                  >
                </td>
                <td class="d-flex align-items-center border-0">
                  <span class="fw-bold text-gray-800 fs-6 me-3">263</span>

                  <div class="progress rounded-start-0">
                    <div
                      class="progress-bar bg-success m-0"
                      role="progressbar"
                      style="height: 12px; width: 107px"
                      aria-valuenow="107"
                      aria-valuemin="0"
                      aria-valuemax="107px"
                    ></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <a
                    href="#"
                    class="text-gray-600 fw-bold text-hover-primary mb-1 fs-6"
                    >Best noise cancelation headsets</a
                  >
                </td>
                <td class="d-flex align-items-center border-0">
                  <span class="fw-bold text-gray-800 fs-6 me-3">263</span>

                  <div class="progress rounded-start-0">
                    <div
                      class="progress-bar bg-success m-0"
                      role="progressbar"
                      style="height: 12px; width: 74px"
                      aria-valuenow="74"
                      aria-valuemin="0"
                      aria-valuemax="74px"
                    ></div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
