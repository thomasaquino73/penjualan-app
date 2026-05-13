@extends('layouts.app')
@section('konten')
    <h4><span class="text-muted fw-light">
            @foreach ($breadcrumb as $key => $item)
                @if (!empty($item['url']))
                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                @else
                    {{ $item['label'] }}
                @endif

                @if (!$loop->last)
                    /
                @endif
            @endforeach
        </span>
    </h4>
    <div class="row">
        <div class="col-sm-6 col-lg-3 mb-4">
            <div class="card card-border-shadow-info">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="ti ti-users ti-md"></i>
                            </span>
                        </div>
                        <h4 class="ms-1 mb-0"></h4>
                    </div>
                    <p class="mb-1">Total Users</p>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3 mb-4">
            <div class="card card-border-shadow-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="ti ti-user-star ti-md"></i>
                            </span>
                        </div>
                        <h4 class="ms-1 mb-0"></h4>
                    </div>
                    <p class="mb-1">Total Active Users</p>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3 mb-4">
            <div class="card card-border-shadow-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="ti ti-user-up ti-md"></i>
                            </span>
                        </div>
                        <h4 class="ms-1 mb-0"></h4>
                    </div>
                    <p class="mb-1">Total Logged-in Users</p>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3 mb-4">
            <div class="card card-border-shadow-success">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ti ti-user-check ti-md"></i>
                            </span>
                        </div>
                        <h4 class="ms-1 mb-0"></h4>
                    </div>
                    <p class="mb-1">Total Verified Users</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="row">
                <div class="col-xl-6 mb-4 col-md-3 col-6">
                    <div class="card">
                        <div class="card-header pb-0">
                            <h5 class="card-title mb-0">82.5k</h5>
                            <small class="text-muted">Expenses</small>
                        </div>
                        <div class="card-body" style="position: relative;">
                            <div id="expensesChart" style="min-height: 63.5px;">
                                <div id="apexchartsizz5yf6x"
                                    class="apexcharts-canvas apexchartsizz5yf6x apexcharts-theme-light"
                                    style="width: 197px; height: 63.5px;"><svg id="SvgjsSvg1946" width="197"
                                        height="63.5" xmlns="http://www.w3.org/2000/svg" version="1.1"
                                        xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.dev"
                                        class="apexcharts-svg" xmlns:data="ApexChartsNS" transform="translate(0, 0)"
                                        style="background: transparent;">
                                        <g id="SvgjsG1948" class="apexcharts-inner apexcharts-graphical"
                                            transform="translate(38.5, 0)">
                                            <defs id="SvgjsDefs1947">
                                                <clipPath id="gridRectMaskizz5yf6x">
                                                    <rect id="SvgjsRect1950" width="126" height="117" x="-3" y="-1"
                                                        rx="0" ry="0" opacity="1" stroke-width="0"
                                                        stroke="none" stroke-dasharray="0" fill="#fff"></rect>
                                                </clipPath>
                                                <clipPath id="forecastMaskizz5yf6x"></clipPath>
                                                <clipPath id="nonForecastMaskizz5yf6x"></clipPath>
                                                <clipPath id="gridRectMarkerMaskizz5yf6x">
                                                    <rect id="SvgjsRect1951" width="124" height="119" x="-2" y="-2"
                                                        rx="0" ry="0" opacity="1" stroke-width="0"
                                                        stroke="none" stroke-dasharray="0" fill="#fff"></rect>
                                                </clipPath>
                                            </defs>
                                            <g id="SvgjsG1952" class="apexcharts-radialbar">
                                                <g id="SvgjsG1953">
                                                    <g id="SvgjsG1954" class="apexcharts-tracks">
                                                        <g id="SvgjsG1955"
                                                            class="apexcharts-radialbar-track apexcharts-track"
                                                            rel="1">
                                                            <path id="apexcharts-radialbarTrack-0"
                                                                d="M 20.335365853658537 57.49999999999999 A 39.66463414634146 39.66463414634146 0 0 1 99.66463414634146 57.5"
                                                                fill="none" fill-opacity="1"
                                                                stroke="rgba(219,218,222,0.85)" stroke-opacity="1"
                                                                stroke-linecap="round" stroke-width="3.4298780487804885"
                                                                stroke-dasharray="0" class="apexcharts-radialbar-area"
                                                                data:pathOrig="M 20.335365853658537 57.49999999999999 A 39.66463414634146 39.66463414634146 0 0 1 99.66463414634146 57.5">
                                                            </path>
                                                        </g>
                                                    </g>
                                                    <g id="SvgjsG1957">
                                                        <g id="SvgjsG1961"
                                                            class="apexcharts-series apexcharts-radial-series"
                                                            seriesName="Progress" rel="1" data:realIndex="0">
                                                            <path id="SvgjsPath1962"
                                                                d="M 20.335365853658537 57.49999999999999 A 39.66463414634146 39.66463414634146 0 0 1 90.38487257615215 32.00406462798209"
                                                                fill="none" fill-opacity="0.85"
                                                                stroke="rgba(255,159,67,0.85)" stroke-opacity="1"
                                                                stroke-linecap="round" stroke-width="7.621951219512196"
                                                                stroke-dasharray="0"
                                                                class="apexcharts-radialbar-area apexcharts-radialbar-slice-0"
                                                                data:angle="140" data:value="78" index="0" j="0"
                                                                data:pathOrig="M 20.335365853658537 57.49999999999999 A 39.66463414634146 39.66463414634146 0 0 1 90.38487257615215 32.00406462798209">
                                                            </path>
                                                        </g>
                                                        <circle id="SvgjsCircle1958" r="32.949695121951216" cx="60"
                                                            cy="57.5" class="apexcharts-radialbar-hollow"
                                                            fill="transparent"></circle>
                                                        <g id="SvgjsG1959" class="apexcharts-datalabels-group"
                                                            transform="translate(0, 0) scale(1)" style="opacity: 1;"><text
                                                                id="SvgjsText1960"
                                                                font-family="Helvetica, Arial, sans-serif" x="60" y="52.5"
                                                                text-anchor="middle" dominant-baseline="auto"
                                                                font-size="18px" font-weight="500" fill="#5d596c"
                                                                class="apexcharts-text apexcharts-datalabel-value"
                                                                style="font-family: Helvetica, Arial, sans-serif;">78%</text>
                                                        </g>
                                                    </g>
                                                </g>
                                            </g>
                                            <line id="SvgjsLine1963" x1="0" y1="0" x2="120"
                                                y2="0" stroke="#b6b6b6" stroke-dasharray="0" stroke-width="1"
                                                stroke-linecap="butt" class="apexcharts-ycrosshairs"></line>
                                            <line id="SvgjsLine1964" x1="0" y1="0" x2="120"
                                                y2="0" stroke-dasharray="0" stroke-width="0"
                                                stroke-linecap="butt" class="apexcharts-ycrosshairs-hidden"></line>
                                        </g>
                                        <g id="SvgjsG1949" class="apexcharts-annotations"></g>
                                    </svg>
                                    <div class="apexcharts-legend"></div>
                                </div>
                            </div>
                            <div class="mt-md-2 text-center mt-lg-3 mt-3">
                                <small class="text-muted mt-3">$21k Expenses more than last month</small>
                            </div>
                            <div class="resize-triggers">
                                <div class="expand-trigger">
                                    <div style="width: 246px; height: 149px;"></div>
                                </div>
                                <div class="contract-trigger"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 mb-4 col-md-3 col-6">
                    <div class="card">
                        <div class="card-header pb-0">
                            <h5 class="card-title mb-0">Profit</h5>
                            <small class="text-muted">Last Month</small>
                        </div>
                        <div class="card-body" style="position: relative;">
                            <div id="profitLastMonth" style="min-height: 93px;">
                                <div id="apexchartskuy5wmj8g"
                                    class="apexcharts-canvas apexchartskuy5wmj8g apexcharts-theme-light"
                                    style="width: 197px; height: 93px;"><svg id="SvgjsSvg4399" width="197"
                                        height="93" xmlns="http://www.w3.org/2000/svg" version="1.1"
                                        xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.dev"
                                        class="apexcharts-svg apexcharts-zoomable" xmlns:data="ApexChartsNS"
                                        transform="translate(0, 0)" style="background: transparent;">
                                        <g id="SvgjsG4401" class="apexcharts-inner apexcharts-graphical"
                                            transform="translate(6, 12)">
                                            <defs id="SvgjsDefs4400">
                                                <clipPath id="gridRectMaskkuy5wmj8g">
                                                    <rect id="SvgjsRect4406" width="190" height="78" x="-3" y="-1"
                                                        rx="0" ry="0" opacity="1" stroke-width="0"
                                                        stroke="none" stroke-dasharray="0" fill="#fff"></rect>
                                                </clipPath>
                                                <clipPath id="forecastMaskkuy5wmj8g"></clipPath>
                                                <clipPath id="nonForecastMaskkuy5wmj8g"></clipPath>
                                                <clipPath id="gridRectMarkerMaskkuy5wmj8g">
                                                    <rect id="SvgjsRect4407" width="202" height="94" x="-9" y="-9"
                                                        rx="0" ry="0" opacity="1" stroke-width="0"
                                                        stroke="none" stroke-dasharray="0" fill="#fff"></rect>
                                                </clipPath>
                                            </defs>
                                            <line id="SvgjsLine4405" x1="0" y1="0" x2="0"
                                                y2="76" stroke="#b6b6b6" stroke-dasharray="3"
                                                stroke-linecap="butt" class="apexcharts-xcrosshairs" x="0" y="0"
                                                width="1" height="76" fill="#b1b9c4" filter="none"
                                                fill-opacity="0.9" stroke-width="1"></line>
                                            <g id="SvgjsG4424" class="apexcharts-xaxis" transform="translate(0, 0)">
                                                <g id="SvgjsG4425" class="apexcharts-xaxis-texts-g"
                                                    transform="translate(0, -4)"></g>
                                            </g>
                                            <g id="SvgjsG4434" class="apexcharts-grid">
                                                <g id="SvgjsG4435" class="apexcharts-gridlines-horizontal"></g>
                                                <g id="SvgjsG4436" class="apexcharts-gridlines-vertical">
                                                    <line id="SvgjsLine4437" x1="0" y1="0"
                                                        x2="0" y2="76" stroke="#dbdade"
                                                        stroke-dasharray="6" stroke-linecap="butt"
                                                        class="apexcharts-gridline"></line>
                                                    <line id="SvgjsLine4438" x1="36.8" y1="0"
                                                        x2="36.8" y2="76" stroke="#dbdade"
                                                        stroke-dasharray="6" stroke-linecap="butt"
                                                        class="apexcharts-gridline"></line>
                                                    <line id="SvgjsLine4439" x1="73.6" y1="0"
                                                        x2="73.6" y2="76" stroke="#dbdade"
                                                        stroke-dasharray="6" stroke-linecap="butt"
                                                        class="apexcharts-gridline"></line>
                                                    <line id="SvgjsLine4440" x1="110.39999999999999" y1="0"
                                                        x2="110.39999999999999" y2="76" stroke="#dbdade"
                                                        stroke-dasharray="6" stroke-linecap="butt"
                                                        class="apexcharts-gridline"></line>
                                                    <line id="SvgjsLine4441" x1="147.2" y1="0"
                                                        x2="147.2" y2="76" stroke="#dbdade"
                                                        stroke-dasharray="6" stroke-linecap="butt"
                                                        class="apexcharts-gridline"></line>
                                                    <line id="SvgjsLine4442" x1="184" y1="0"
                                                        x2="184" y2="76" stroke="#dbdade"
                                                        stroke-dasharray="6" stroke-linecap="butt"
                                                        class="apexcharts-gridline"></line>
                                                </g>
                                                <line id="SvgjsLine4444" x1="0" y1="76" x2="184"
                                                    y2="76" stroke="transparent" stroke-dasharray="0"
                                                    stroke-linecap="butt"></line>
                                                <line id="SvgjsLine4443" x1="0" y1="1" x2="0"
                                                    y2="76" stroke="transparent" stroke-dasharray="0"
                                                    stroke-linecap="butt"></line>
                                            </g>
                                            <g id="SvgjsG4408" class="apexcharts-line-series apexcharts-plot-series">
                                                <g id="SvgjsG4409" class="apexcharts-series" seriesName="seriesx1"
                                                    data:longestSeries="true" rel="1" data:realIndex="0">
                                                    <path id="SvgjsPath4423"
                                                        d="M 0 76L 36.800000000000004 44.33333333333333L 73.60000000000001 63.333333333333336L 110.4 25.333333333333336L 147.20000000000002 44.33333333333333L 184 6.333333333333329"
                                                        fill="none" fill-opacity="1" stroke="rgba(0,207,232,0.85)"
                                                        stroke-opacity="1" stroke-linecap="butt" stroke-width="2"
                                                        stroke-dasharray="0" class="apexcharts-line" index="0"
                                                        clip-path="url(#gridRectMaskkuy5wmj8g)"
                                                        pathTo="M 0 76L 36.800000000000004 44.33333333333333L 73.60000000000001 63.333333333333336L 110.4 25.333333333333336L 147.20000000000002 44.33333333333333L 184 6.333333333333329"
                                                        pathFrom="M -1 76L -1 76L 36.800000000000004 76L 73.60000000000001 76L 110.4 76L 147.20000000000002 76L 184 76">
                                                    </path>
                                                    <g id="SvgjsG4410" class="apexcharts-series-markers-wrap"
                                                        data:realIndex="0">
                                                        <g id="SvgjsG4412" class="apexcharts-series-markers"
                                                            clip-path="url(#gridRectMarkerMaskkuy5wmj8g)">
                                                            <circle id="SvgjsCircle4413" r="3.5" cx="0"
                                                                cy="76"
                                                                class="apexcharts-marker no-pointer-events wofv1ikw6g"
                                                                stroke="transparent" fill="#00cfe8" fill-opacity="1"
                                                                stroke-width="3.2" stroke-opacity="0.9" rel="0"
                                                                j="0" index="0" default-marker-size="3.5"></circle>
                                                            <circle id="SvgjsCircle4414" r="3.5" cx="36.800000000000004"
                                                                cy="44.33333333333333"
                                                                class="apexcharts-marker no-pointer-events wvz8lxed"
                                                                stroke="transparent" fill="#00cfe8" fill-opacity="1"
                                                                stroke-width="3.2" stroke-opacity="0.9" rel="1"
                                                                j="1" index="0" default-marker-size="3.5"></circle>
                                                        </g>
                                                        <g id="SvgjsG4415" class="apexcharts-series-markers"
                                                            clip-path="url(#gridRectMarkerMaskkuy5wmj8g)">
                                                            <circle id="SvgjsCircle4416" r="3.5" cx="73.60000000000001"
                                                                cy="63.333333333333336"
                                                                class="apexcharts-marker no-pointer-events wde91mxdo"
                                                                stroke="transparent" fill="#00cfe8" fill-opacity="1"
                                                                stroke-width="3.2" stroke-opacity="0.9" rel="2"
                                                                j="2" index="0" default-marker-size="3.5"></circle>
                                                        </g>
                                                        <g id="SvgjsG4417" class="apexcharts-series-markers"
                                                            clip-path="url(#gridRectMarkerMaskkuy5wmj8g)">
                                                            <circle id="SvgjsCircle4418" r="3.5" cx="110.4"
                                                                cy="25.333333333333336"
                                                                class="apexcharts-marker no-pointer-events w6ctynweu"
                                                                stroke="transparent" fill="#00cfe8" fill-opacity="1"
                                                                stroke-width="3.2" stroke-opacity="0.9" rel="3"
                                                                j="3" index="0" default-marker-size="3.5"></circle>
                                                        </g>
                                                        <g id="SvgjsG4419" class="apexcharts-series-markers"
                                                            clip-path="url(#gridRectMarkerMaskkuy5wmj8g)">
                                                            <circle id="SvgjsCircle4420" r="3.5" cx="147.20000000000002"
                                                                cy="44.33333333333333"
                                                                class="apexcharts-marker no-pointer-events wq9fs0e6"
                                                                stroke="transparent" fill="#00cfe8" fill-opacity="1"
                                                                stroke-width="3.2" stroke-opacity="0.9" rel="4"
                                                                j="4" index="0" default-marker-size="3.5"></circle>
                                                        </g>
                                                        <g id="SvgjsG4421" class="apexcharts-series-markers"
                                                            clip-path="url(#gridRectMarkerMaskkuy5wmj8g)">
                                                            <circle id="SvgjsCircle4422" r="5" cx="184"
                                                                cy="6.333333333333329"
                                                                class="apexcharts-marker no-pointer-events ws9mk3lqm"
                                                                stroke="#00cfe8" fill="#ffffff" fill-opacity="1"
                                                                stroke-width="3.2" stroke-opacity="0.9" rel="5"
                                                                j="5" index="0" default-marker-size="5"></circle>
                                                        </g>
                                                    </g>
                                                </g>
                                                <g id="SvgjsG4411" class="apexcharts-datalabels" data:realIndex="0"></g>
                                            </g>
                                            <line id="SvgjsLine4445" x1="0" y1="0" x2="184"
                                                y2="0" stroke="#b6b6b6" stroke-dasharray="0" stroke-width="1"
                                                stroke-linecap="butt" class="apexcharts-ycrosshairs"></line>
                                            <line id="SvgjsLine4446" x1="0" y1="0" x2="184"
                                                y2="0" stroke-dasharray="0" stroke-width="0"
                                                stroke-linecap="butt" class="apexcharts-ycrosshairs-hidden"></line>
                                            <g id="SvgjsG4447" class="apexcharts-yaxis-annotations"></g>
                                            <g id="SvgjsG4448" class="apexcharts-xaxis-annotations"></g>
                                            <g id="SvgjsG4449" class="apexcharts-point-annotations"></g>
                                            <rect id="SvgjsRect4450" width="0" height="0" x="0" y="0"
                                                rx="0" ry="0" opacity="1" stroke-width="0"
                                                stroke="none" stroke-dasharray="0" fill="#fefefe"
                                                class="apexcharts-zoom-rect"></rect>
                                            <rect id="SvgjsRect4451" width="0" height="0" x="0" y="0"
                                                rx="0" ry="0" opacity="1" stroke-width="0"
                                                stroke="none" stroke-dasharray="0" fill="#fefefe"
                                                class="apexcharts-selection-rect"></rect>
                                        </g>
                                        <rect id="SvgjsRect4404" width="0" height="0" x="0" y="0"
                                            rx="0" ry="0" opacity="1" stroke-width="0"
                                            stroke="none" stroke-dasharray="0" fill="#fefefe"></rect>
                                        <g id="SvgjsG4432" class="apexcharts-yaxis" rel="0"
                                            transform="translate(-8, 0)">
                                            <g id="SvgjsG4433" class="apexcharts-yaxis-texts-g"></g>
                                        </g>
                                        <g id="SvgjsG4402" class="apexcharts-annotations"></g>
                                    </svg>
                                    <div class="apexcharts-legend" style="max-height: 46.5px;"></div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3 gap-3">
                                <h4 class="mb-0">624k</h4>
                                <small class="text-success">+8.24%</small>
                            </div>
                            <div class="resize-triggers">
                                <div class="expand-trigger">
                                    <div style="width: 246px; height: 163px;"></div>
                                </div>
                                <div class="contract-trigger"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12 mb-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between" style="position: relative;">
                                <div class="d-flex flex-column">
                                    <div class="card-title mb-auto">
                                        <h5 class="mb-1 text-nowrap">Generated Leads</h5>
                                        <small>Monthly Report</small>
                                    </div>
                                    <div class="chart-statistics">
                                        <h3 class="card-title mb-1">4,350</h3>
                                        <small class="text-success text-nowrap fw-medium"><i
                                                class="ti ti-chevron-up me-1"></i> 15.8%</small>
                                    </div>
                                </div>
                                <div id="generatedLeadsChart" style="min-height: 184.8px;">
                                    <div id="apexchartsz8gle1gxf"
                                        class="apexcharts-canvas apexchartsz8gle1gxf apexcharts-theme-light"
                                        style="width: 160px; height: 184.8px;"><svg id="SvgjsSvg2018" width="160"
                                            height="184.79999999999998" xmlns="http://www.w3.org/2000/svg" version="1.1"
                                            xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.dev"
                                            class="apexcharts-svg" xmlns:data="ApexChartsNS" transform="translate(0, 0)"
                                            style="background: transparent;">
                                            <g id="SvgjsG2020" class="apexcharts-inner apexcharts-graphical"
                                                transform="translate(-29, 15)">
                                                <defs id="SvgjsDefs2019">
                                                    <clipPath id="gridRectMaskz8gle1gxf">
                                                        <rect id="SvgjsRect2022" width="222" height="163" x="-2"
                                                            y="0" rx="0" ry="0" opacity="1"
                                                            stroke-width="0" stroke="none" stroke-dasharray="0"
                                                            fill="#fff"></rect>
                                                    </clipPath>
                                                    <clipPath id="forecastMaskz8gle1gxf"></clipPath>
                                                    <clipPath id="nonForecastMaskz8gle1gxf"></clipPath>
                                                    <clipPath id="gridRectMarkerMaskz8gle1gxf">
                                                        <rect id="SvgjsRect2023" width="222" height="167" x="-2"
                                                            y="-2" rx="0" ry="0" opacity="1"
                                                            stroke-width="0" stroke="none" stroke-dasharray="0"
                                                            fill="#fff"></rect>
                                                    </clipPath>
                                                </defs>
                                                <g id="SvgjsG2024" class="apexcharts-pie">
                                                    <g id="SvgjsG2025" transform="translate(0, 0) scale(1)">
                                                        <circle id="SvgjsCircle2026" r="52.858536585365854" cx="109"
                                                            cy="81.5" fill="transparent"></circle>
                                                        <g id="SvgjsG2027" class="apexcharts-slices">
                                                            <g id="SvgjsG2028"
                                                                class="apexcharts-series apexcharts-pie-series"
                                                                seriesName="Electronic" rel="1"
                                                                data:realIndex="0">
                                                                <path id="SvgjsPath2029"
                                                                    d="M 109 5.987804878048777 A 75.51219512195122 75.51219512195122 0 0 1 184.48716037620278 79.55571852010134 L 161.84101226334192 80.13900296407094 A 52.858536585365854 52.858536585365854 0 0 0 109 28.641463414634146 L 109 5.987804878048777 z"
                                                                    fill="rgba(40,199,111,1)" fill-opacity="1"
                                                                    stroke-opacity="1" stroke-linecap="butt"
                                                                    stroke-width="0" stroke-dasharray="0"
                                                                    class="apexcharts-pie-area apexcharts-donut-slice-0"
                                                                    index="0" j="0" data:angle="88.52459016393442"
                                                                    data:startAngle="0" data:strokeWidth="0"
                                                                    data:value="45"
                                                                    data:pathOrig="M 109 5.987804878048777 A 75.51219512195122 75.51219512195122 0 0 1 184.48716037620278 79.55571852010134 L 161.84101226334192 80.13900296407094 A 52.858536585365854 52.858536585365854 0 0 0 109 28.641463414634146 L 109 5.987804878048777 z">
                                                                </path>
                                                            </g>
                                                            <g id="SvgjsG2030"
                                                                class="apexcharts-series apexcharts-pie-series"
                                                                seriesName="Sports" rel="2" data:realIndex="1">
                                                                <path id="SvgjsPath2031"
                                                                    d="M 184.48716037620278 79.55571852010134 A 75.51219512195122 75.51219512195122 0 0 1 79.95309393432012 151.2020004027661 L 88.66716575402408 130.29140028193626 A 52.858536585365854 52.858536585365854 0 0 0 161.84101226334192 80.13900296407094 L 184.48716037620278 79.55571852010134 z"
                                                                    fill="#28c76fb3" fill-opacity="1" stroke-opacity="1"
                                                                    stroke-linecap="butt" stroke-width="0"
                                                                    stroke-dasharray="0"
                                                                    class="apexcharts-pie-area apexcharts-donut-slice-1"
                                                                    index="0" j="1" data:angle="114.09836065573771"
                                                                    data:startAngle="88.52459016393442"
                                                                    data:strokeWidth="0" data:value="58"
                                                                    data:pathOrig="M 184.48716037620278 79.55571852010134 A 75.51219512195122 75.51219512195122 0 0 1 79.95309393432012 151.2020004027661 L 88.66716575402408 130.29140028193626 A 52.858536585365854 52.858536585365854 0 0 0 161.84101226334192 80.13900296407094 L 184.48716037620278 79.55571852010134 z">
                                                                </path>
                                                            </g>
                                                            <g id="SvgjsG2032"
                                                                class="apexcharts-series apexcharts-pie-series"
                                                                seriesName="Decor" rel="3" data:realIndex="2">
                                                                <path id="SvgjsPath2033"
                                                                    d="M 79.95309393432012 151.2020004027661 A 75.51219512195122 75.51219512195122 0 0 1 34.29031729978199 92.47975058771277 L 56.703222109847395 89.18582541139894 A 52.858536585365854 52.858536585365854 0 0 0 88.66716575402408 130.29140028193626 L 79.95309393432012 151.2020004027661 z"
                                                                    fill="#28c76f80" fill-opacity="1" stroke-opacity="1"
                                                                    stroke-linecap="butt" stroke-width="0"
                                                                    stroke-dasharray="0"
                                                                    class="apexcharts-pie-area apexcharts-donut-slice-2"
                                                                    index="0" j="2" data:angle="59.016393442622956"
                                                                    data:startAngle="202.62295081967213"
                                                                    data:strokeWidth="0" data:value="30"
                                                                    data:pathOrig="M 79.95309393432012 151.2020004027661 A 75.51219512195122 75.51219512195122 0 0 1 34.29031729978199 92.47975058771277 L 56.703222109847395 89.18582541139894 A 52.858536585365854 52.858536585365854 0 0 0 88.66716575402408 130.29140028193626 L 79.95309393432012 151.2020004027661 z">
                                                                </path>
                                                            </g>
                                                            <g id="SvgjsG2034"
                                                                class="apexcharts-series apexcharts-pie-series"
                                                                seriesName="Fashion" rel="4" data:realIndex="3">
                                                                <path id="SvgjsPath2035"
                                                                    d="M 34.29031729978199 92.47975058771277 A 75.51219512195122 75.51219512195122 0 0 1 108.98682063576403 5.9878060281652665 L 108.99077444503483 28.641464219715694 A 52.858536585365854 52.858536585365854 0 0 0 56.703222109847395 89.18582541139894 L 34.29031729978199 92.47975058771277 z"
                                                                    fill="#28c76f29" fill-opacity="1" stroke-opacity="1"
                                                                    stroke-linecap="butt" stroke-width="0"
                                                                    stroke-dasharray="0"
                                                                    class="apexcharts-pie-area apexcharts-donut-slice-3"
                                                                    index="0" j="3" data:angle="98.36065573770492"
                                                                    data:startAngle="261.6393442622951"
                                                                    data:strokeWidth="0" data:value="50"
                                                                    data:pathOrig="M 34.29031729978199 92.47975058771277 A 75.51219512195122 75.51219512195122 0 0 1 108.98682063576403 5.9878060281652665 L 108.99077444503483 28.641464219715694 A 52.858536585365854 52.858536585365854 0 0 0 56.703222109847395 89.18582541139894 L 34.29031729978199 92.47975058771277 z">
                                                                </path>
                                                            </g>
                                                        </g>
                                                    </g>
                                                    <g id="SvgjsG2036" class="apexcharts-datalabels-group"
                                                        transform="translate(0, 0) scale(1)"><text id="SvgjsText2037"
                                                            font-family="Public Sans" x="109" y="101.5"
                                                            text-anchor="middle" dominant-baseline="auto"
                                                            font-size=".8125rem" font-weight="400" fill="#28c76f"
                                                            class="apexcharts-text apexcharts-datalabel-label"
                                                            style="font-family: &quot;Public Sans&quot;;">Total</text><text
                                                            id="SvgjsText2038" font-family="Public Sans" x="109" y="82.5"
                                                            text-anchor="middle" dominant-baseline="auto"
                                                            font-size="1.375rem" font-weight="500" fill="#5d596c"
                                                            class="apexcharts-text apexcharts-datalabel-value"
                                                            style="font-family: &quot;Public Sans&quot;;">184</text></g>
                                                </g>
                                                <line id="SvgjsLine2039" x1="0" y1="0" x2="218"
                                                    y2="0" stroke="#b6b6b6" stroke-dasharray="0"
                                                    stroke-width="1" stroke-linecap="butt"
                                                    class="apexcharts-ycrosshairs"></line>
                                                <line id="SvgjsLine2040" x1="0" y1="0" x2="218"
                                                    y2="0" stroke-dasharray="0" stroke-width="0"
                                                    stroke-linecap="butt" class="apexcharts-ycrosshairs-hidden"></line>
                                            </g>
                                            <g id="SvgjsG2021" class="apexcharts-annotations"></g>
                                        </svg>
                                        <div class="apexcharts-legend"></div>
                                        <div class="apexcharts-tooltip apexcharts-theme-false">
                                            <div class="apexcharts-tooltip-series-group" style="order: 1;"><span
                                                    class="apexcharts-tooltip-marker"
                                                    style="background-color: rgb(40, 199, 111);"></span>
                                                <div class="apexcharts-tooltip-text"
                                                    style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">
                                                    <div class="apexcharts-tooltip-y-group"><span
                                                            class="apexcharts-tooltip-text-y-label"></span><span
                                                            class="apexcharts-tooltip-text-y-value"></span></div>
                                                    <div class="apexcharts-tooltip-goals-group"><span
                                                            class="apexcharts-tooltip-text-goals-label"></span><span
                                                            class="apexcharts-tooltip-text-goals-value"></span></div>
                                                    <div class="apexcharts-tooltip-z-group"><span
                                                            class="apexcharts-tooltip-text-z-label"></span><span
                                                            class="apexcharts-tooltip-text-z-value"></span></div>
                                                </div>
                                            </div>
                                            <div class="apexcharts-tooltip-series-group" style="order: 2;"><span
                                                    class="apexcharts-tooltip-marker"
                                                    style="background-color: rgba(40, 199, 111, 0.7);"></span>
                                                <div class="apexcharts-tooltip-text"
                                                    style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">
                                                    <div class="apexcharts-tooltip-y-group"><span
                                                            class="apexcharts-tooltip-text-y-label"></span><span
                                                            class="apexcharts-tooltip-text-y-value"></span></div>
                                                    <div class="apexcharts-tooltip-goals-group"><span
                                                            class="apexcharts-tooltip-text-goals-label"></span><span
                                                            class="apexcharts-tooltip-text-goals-value"></span></div>
                                                    <div class="apexcharts-tooltip-z-group"><span
                                                            class="apexcharts-tooltip-text-z-label"></span><span
                                                            class="apexcharts-tooltip-text-z-value"></span></div>
                                                </div>
                                            </div>
                                            <div class="apexcharts-tooltip-series-group" style="order: 3;"><span
                                                    class="apexcharts-tooltip-marker"
                                                    style="background-color: rgba(40, 199, 111, 0.5);"></span>
                                                <div class="apexcharts-tooltip-text"
                                                    style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">
                                                    <div class="apexcharts-tooltip-y-group"><span
                                                            class="apexcharts-tooltip-text-y-label"></span><span
                                                            class="apexcharts-tooltip-text-y-value"></span></div>
                                                    <div class="apexcharts-tooltip-goals-group"><span
                                                            class="apexcharts-tooltip-text-goals-label"></span><span
                                                            class="apexcharts-tooltip-text-goals-value"></span></div>
                                                    <div class="apexcharts-tooltip-z-group"><span
                                                            class="apexcharts-tooltip-text-z-label"></span><span
                                                            class="apexcharts-tooltip-text-z-value"></span></div>
                                                </div>
                                            </div>
                                            <div class="apexcharts-tooltip-series-group" style="order: 4;"><span
                                                    class="apexcharts-tooltip-marker"
                                                    style="background-color: rgba(40, 199, 111, 0.16);"></span>
                                                <div class="apexcharts-tooltip-text"
                                                    style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">
                                                    <div class="apexcharts-tooltip-y-group"><span
                                                            class="apexcharts-tooltip-text-y-label"></span><span
                                                            class="apexcharts-tooltip-text-y-value"></span></div>
                                                    <div class="apexcharts-tooltip-goals-group"><span
                                                            class="apexcharts-tooltip-text-goals-label"></span><span
                                                            class="apexcharts-tooltip-text-goals-value"></span></div>
                                                    <div class="apexcharts-tooltip-z-group"><span
                                                            class="apexcharts-tooltip-text-z-label"></span><span
                                                            class="apexcharts-tooltip-text-z-value"></span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="resize-triggers">
                                    <div class="expand-trigger">
                                        <div style="width: 467px; height: 186px;"></div>
                                    </div>
                                    <div class="contract-trigger"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-8 col-sm-12">
        </div>
    </div>
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <div class="card-title m-0 me-2">
                        <h5 class="m-0 me-2">Minimum Stock</h5>
                        <small class="text-muted">Counter May 2026</small>
                    </div>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="employeeList" data-bs-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <i class="ti ti-dots-vertical ti-sm text-muted"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="employeeList">
                            <a class="dropdown-item" href="javascript:void(0);">Download</a>
                            <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                            <a class="dropdown-item" href="javascript:void(0);">Share</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="p-0 m-0">
                        <li class="d-flex mb-4 pb-1 align-items-center">
                            <img src="../../assets/img/icons/brands/chrome.png" alt="Chrome" height="28"
                                class="me-3 rounded">
                            <div class="d-flex w-100 align-items-center gap-2">
                                <div class="d-flex justify-content-between flex-grow-1 flex-wrap">
                                    <div>
                                        <h6 class="mb-0">Google Chrome</h6>
                                    </div>

                                    <div class="user-progress d-flex align-items-center gap-2">
                                        <h6 class="mb-0">90.4%</h6>
                                    </div>
                                </div>
                                <div class="chart-progress" data-color="secondary" data-series="85"></div>
                            </div>
                        </li>
                        <li class="d-flex mb-4 pb-1 align-items-center">
                            <img src="../../assets/img/icons/brands/safari.png" alt="Safari" height="28"
                                class="me-3 rounded">
                            <div class="d-flex w-100 align-items-center gap-2">
                                <div class="d-flex justify-content-between flex-grow-1 flex-wrap">
                                    <div>
                                        <h6 class="mb-0">Apple Safari</h6>
                                    </div>
                                    <div class="user-progress d-flex align-items-center gap-2">
                                        <h6 class="mb-0">70.6%</h6>
                                    </div>
                                </div>
                                <div class="chart-progress" data-color="success" data-series="70"></div>
                            </div>
                        </li>
                        <li class="d-flex mb-4 pb-1 align-items-center">
                            <img src="../../assets/img/icons/brands/firefox.png" alt="Firefox" height="28"
                                class="me-3 rounded">
                            <div class="d-flex w-100 align-items-center gap-2">
                                <div class="d-flex justify-content-between flex-grow-1 flex-wrap">
                                    <div>
                                        <h6 class="mb-0">Mozilla Firefox</h6>
                                    </div>
                                    <div class="user-progress d-flex align-items-center gap-2">
                                        <h6 class="mb-0">35.5%</h6>
                                    </div>
                                </div>
                                <div class="chart-progress" data-color="primary" data-series="25"></div>
                            </div>
                        </li>
                        <li class="d-flex mb-4 pb-1 align-items-center">
                            <img src="../../assets/img/icons/brands/opera.png" alt="Opera" height="28"
                                class="me-3 rounded">
                            <div class="d-flex w-100 align-items-center gap-2">
                                <div class="d-flex justify-content-between flex-grow-1 flex-wrap">
                                    <div>
                                        <h6 class="mb-0">Opera Mini</h6>
                                    </div>

                                    <div class="user-progress d-flex align-items-center gap-2">
                                        <h6 class="mb-0">80.0%</h6>
                                    </div>
                                </div>
                                <div class="chart-progress" data-color="danger" data-series="75"></div>
                            </div>
                        </li>
                        <li class="d-flex mb-4 pb-1 align-items-center">
                            <img src="../../assets/img/icons/brands/edge.png" alt="Edge" height="28"
                                class="me-3 rounded">
                            <div class="d-flex w-100 align-items-center gap-2">
                                <div class="d-flex justify-content-between flex-grow-1 flex-wrap">
                                    <div>
                                        <h6 class="mb-0">Internet Explorer</h6>
                                    </div>
                                    <div class="user-progress d-flex align-items-center gap-2">
                                        <h6 class="mb-0">62.2%</h6>
                                    </div>
                                </div>
                                <div class="chart-progress" data-color="info" data-series="60"></div>
                            </div>
                        </li>
                        <li class="d-flex align-items-center">
                            <img src="../../assets/img/icons/brands/brave.png" alt="Brave" height="28"
                                class="me-3 rounded">
                            <div class="d-flex w-100 align-items-center gap-2">
                                <div class="d-flex justify-content-between flex-grow-1 flex-wrap">
                                    <div>
                                        <h6 class="mb-0">Brave</h6>
                                    </div>
                                    <div class="user-progress d-flex align-items-center gap-2">
                                        <h6 class="mb-0">46.3%</h6>
                                    </div>
                                </div>
                                <div class="chart-progress" data-color="warning" data-series="45"></div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-xl-8 col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between pb-0">
                    <div class="card-title mb-0">
                        <h5 class="mb-0">Best Sellers</h5>
                        <small class="text-muted">Last 7 Days</small>
                    </div>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="supportTrackerMenu" data-bs-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <i class="ti ti-dots-vertical ti-sm text-muted"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="supportTrackerMenu">
                            <a class="dropdown-item" href="{{ route('data-barang.index') }}">View More</a>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive card-datatable">
                    <table class="table display no-wrap">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Code</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
