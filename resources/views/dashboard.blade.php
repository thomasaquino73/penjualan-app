@extends('layouts.app')
@section('konten')
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
                        <h4 class="ms-1 mb-0">{{ $totalUsers }}</h4>
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
                        <h4 class="ms-1 mb-0">{{ $totalActive }}</h4>
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
                        <h4 class="ms-1 mb-0">{{ $totalLogin }}</h4>
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
                        <h4 class="ms-1 mb-0">{{ $totalVerified }}</h4>
                    </div>
                    <p class="mb-1">Total Verified Users</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="row">
                <div class="col-xl-12 mb-4 col-md-3 col-6">
                    <div class="card h-100">

                        <div class="card-header pb-0">
                            <h5 class="card-title mb-0">Top Selling Brands</h5>
                            <small class="text-muted">Based on this month’s sales</small>
                        </div>

                        <div class="card-body">

                            <!-- DONUT CHART -->
                            <div id="brandSalesDonut"></div>

                            <!-- BOTTOM INFO -->
                            <div class="mt-3 text-center">
                                <small class="text-muted">
                                    Total brands sold this month
                                </small>
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
                                                        <rect id="SvgjsRect2022" width="222" height="163" x="-2" y="0"
                                                            rx="0" ry="0" opacity="1" stroke-width="0"
                                                            stroke="none" stroke-dasharray="0" fill="#fff"></rect>
                                                    </clipPath>
                                                    <clipPath id="forecastMaskz8gle1gxf"></clipPath>
                                                    <clipPath id="nonForecastMaskz8gle1gxf"></clipPath>
                                                    <clipPath id="gridRectMarkerMaskz8gle1gxf">
                                                        <rect id="SvgjsRect2023" width="222" height="167" x="-2" y="-2"
                                                            rx="0" ry="0" opacity="1" stroke-width="0"
                                                            stroke="none" stroke-dasharray="0" fill="#fff"></rect>
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
            <div class="row">
                <div class="col-md-6 col-xl-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between">
                            <div class="card-title m-0 me-2">
                                <h5 class="m-0 me-2">Sales Transactions</h5>
                                <small class="text-muted">Total {{ $TotalTransactions }} Transactions done in this
                                    Month</small>
                            </div>
                            <div class="dropdown">
                                <button class="btn p-0" type="button" id="transactionID" data-bs-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    <i class="ti ti-dots-vertical ti-sm text-muted"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="transactionID">
                                    <a class="dropdown-item" href="javascript:void(0);">Last 28 Days</a>
                                    <a class="dropdown-item" href="javascript:void(0);">Last Month</a>
                                    <a class="dropdown-item" href="javascript:void(0);">Last Year</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <ul class="p-0 m-0">
                                @foreach ($transaksiTerbanyak as $tranTer)
                                    <li class="d-flex mb-3 pb-1 align-items-center">
                                        <div class="badge bg-label-primary me-3 rounded p-2">
                                            <img src="{{ !empty($tranTer->photo_filename) ? asset($tranTer->photo_filename) : asset('image/no-images.jpg') }}"
                                                alt="{{ $tranTer->nama_barang }}" width="50">
                                        </div>

                                        <div
                                            class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                            <div class="me-2">
                                                <h6 class="mb-0">{{ $tranTer->nama_barang }}</h6>
                                                <small class="text-muted d-block">
                                                    {{ $tranTer->total_transaksi }} kali penjualan
                                                </small>
                                            </div>

                                            <div class="user-progress d-flex align-items-center gap-1">
                                                <span class="badge bg-label-success">
                                                    {{ number_format($tranTer->total_qty, 0) }} {{ $tranTer->unit_name }}
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                                {{-- <li class="d-flex mb-3 pb-1 align-items-center">
                                    <div class="badge bg-label-success rounded me-3 p-2">
                                        <i class="ti ti-browser-check ti-sm"></i>
                                    </div>
                                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Bank Transfer</h6>
                                            <small class="text-muted d-block">Add Money</small>
                                        </div>
                                        <div class="user-progress d-flex align-items-center gap-1">
                                            <h6 class="mb-0 text-success">+$480</h6>
                                        </div>
                                    </div>
                                </li>
                                <li class="d-flex mb-3 pb-1 align-items-center">
                                    <div class="badge bg-label-danger rounded me-3 p-2">
                                        <i class="ti ti-brand-paypal ti-sm"></i>
                                    </div>
                                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Paypal</h6>
                                            <small class="text-muted d-block mb-1">Client Payment</small>
                                        </div>
                                        <div class="user-progress d-flex align-items-center gap-1">
                                            <h6 class="mb-0 text-success">+$268</h6>
                                        </div>
                                    </div>
                                </li>
                                <li class="d-flex mb-3 pb-1 align-items-center">
                                    <div class="badge bg-label-secondary me-3 rounded p-2">
                                        <i class="ti ti-credit-card ti-sm"></i>
                                    </div>
                                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Master Card</h6>
                                            <small class="text-muted d-block mb-1">Ordered iPhone 13</small>
                                        </div>
                                        <div class="user-progress d-flex align-items-center gap-1">
                                            <h6 class="mb-0 text-danger">-$699</h6>
                                        </div>
                                    </div>
                                </li>
                                <li class="d-flex mb-3 pb-1 align-items-center">
                                    <div class="badge bg-label-info me-3 rounded p-2">
                                        <i class="ti ti-currency-dollar ti-sm"></i>
                                    </div>
                                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Bank Transactions</h6>
                                            <small class="text-muted d-block mb-1">Refund</small>
                                        </div>
                                        <div class="user-progress d-flex align-items-center gap-1">
                                            <h6 class="mb-0 text-success">+$98</h6>
                                        </div>
                                    </div>
                                </li>
                                <li class="d-flex mb-3 pb-1 align-items-center">
                                    <div class="badge bg-label-danger me-3 rounded p-2">
                                        <i class="ti ti-brand-paypal ti-sm"></i>
                                    </div>
                                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Paypal</h6>
                                            <small class="text-muted d-block mb-1">Client Payment</small>
                                        </div>
                                        <div class="user-progress d-flex align-items-center gap-1">
                                            <h6 class="mb-0 text-success">+$126</h6>
                                        </div>
                                    </div>
                                </li>
                                <li class="d-flex align-items-center">
                                    <div class="badge bg-label-success me-3 rounded p-2">
                                        <i class="ti ti-browser-check ti-sm"></i>
                                    </div>
                                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Bank Transfer</h6>
                                            <small class="text-muted d-block mb-1">Pay Office Rent</small>
                                        </div>
                                        <div class="user-progress d-flex align-items-center gap-1">
                                            <h6 class="mb-0 text-danger">-$1290</h6>
                                        </div>
                                    </div>
                                </li> --}}
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between">
                            <div class="card-title m-0 me-2">
                                <h5 class="m-0 me-2">Popular Products</h5>
                                <small class="text-muted">Total 10.4k Visitors</small>
                            </div>
                            <div class="dropdown">
                                <button class="btn p-0" type="button" id="popularProduct" data-bs-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    <i class="ti ti-dots-vertical ti-sm text-muted"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="popularProduct">
                                    <a class="dropdown-item" href="javascript:void(0);">Last 28 Days</a>
                                    <a class="dropdown-item" href="javascript:void(0);">Last Month</a>
                                    <a class="dropdown-item" href="javascript:void(0);">Last Year</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <ul class="p-0 m-0">
                                <li class="d-flex mb-4 pb-1">
                                    <div class="me-3">
                                        <img src="../../assets/img/products/iphone.png" alt="User" class="rounded"
                                            width="46">
                                    </div>
                                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Apple iPhone 13</h6>
                                            <small class="text-muted d-block">Item: #FXZ-4567</small>
                                        </div>
                                        <div class="user-progress d-flex align-items-center gap-1">
                                            <p class="mb-0 fw-medium">$999.29</p>
                                        </div>
                                    </div>
                                </li>
                                <li class="d-flex mb-4 pb-1">
                                    <div class="me-3">
                                        <img src="../../assets/img/products/nike-air-jordan.png" alt="User"
                                            class="rounded" width="46">
                                    </div>
                                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Nike Air Jordan</h6>
                                            <small class="text-muted d-block">Item: #FXZ-3456</small>
                                        </div>
                                        <div class="user-progress d-flex align-items-center gap-1">
                                            <p class="mb-0 fw-medium">$72.40</p>
                                        </div>
                                    </div>
                                </li>
                                <li class="d-flex mb-4 pb-1">
                                    <div class="me-3">
                                        <img src="../../assets/img/products/headphones.png" alt="User"
                                            class="rounded" width="46">
                                    </div>
                                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Beats Studio 2</h6>
                                            <small class="text-muted d-block">Item: #FXZ-9485</small>
                                        </div>
                                        <div class="user-progress d-flex align-items-center gap-1">
                                            <p class="mb-0 fw-medium">$99</p>
                                        </div>
                                    </div>
                                </li>
                                <li class="d-flex mb-4 pb-1">
                                    <div class="me-3">
                                        <img src="../../assets/img/products/apple-watch.png" alt="User"
                                            class="rounded" width="46">
                                    </div>
                                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Apple Watch Series 7</h6>
                                            <small class="text-muted d-block">Item: #FXZ-2345</small>
                                        </div>
                                        <div class="user-progress d-flex align-items-center gap-1">
                                            <p class="mb-0 fw-medium">$249.99</p>
                                        </div>
                                    </div>
                                </li>
                                <li class="d-flex mb-4 pb-1">
                                    <div class="me-3">
                                        <img src="../../assets/img/products/amazon-echo.png" alt="User"
                                            class="rounded" width="46">
                                    </div>
                                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Amazon Echo Dot</h6>
                                            <small class="text-muted d-block">Item: #FXZ-8959</small>
                                        </div>
                                        <div class="user-progress d-flex align-items-center gap-1">
                                            <p class="mb-0 fw-medium">$79.40</p>
                                        </div>
                                    </div>
                                </li>
                                <li class="d-flex">
                                    <div class="me-3">
                                        <img src="../../assets/img/products/play-station.png" alt="User"
                                            class="rounded" width="46">
                                    </div>
                                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Play Station Console</h6>
                                            <small class="text-muted d-block">Item: #FXZ-7892</small>
                                        </div>
                                        <div class="user-progress d-flex align-items-center gap-1">
                                            <p class="mb-0 fw-medium">$129.48</p>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between">
                <div class="card-title m-0 me-2">
                    <h5 class="m-0 me-2">Minimum Stock</h5>
                    <small class="text-muted">
                        {{ now()->translatedFormat('F Y') }}
                    </small>
                </div>

                <div class="dropdown">
                    <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                        <i class="ti ti-dots-vertical ti-sm text-muted"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="{{ route('data-barang.index') }}">
                            View All
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">

                @if ($minStock->count() > 0)
                    <ul class="p-0 m-0">
                        @foreach ($minStock as $item)
                            @php
                                $minStockQty = $item->primary_minimum_stock ?? 0;
                                $currentStock = $item->current_stock ?? 0;
                                $percentage = $minStockQty > 0 ? round(($currentStock / $minStockQty) * 100) : 0;
                                $percentage = min(100, max(0, $percentage));
                                $shortage = max(0, $minStockQty - $currentStock);
                            @endphp

                            <li class="d-flex mb-4 pb-1 align-items-center">
                                <div class="avatar avatar-sm me-3">
                                    <img src="{{ !empty($item->photo_filename) ? asset($item->photo_filename) : asset('image/no-images.jpg') }}"
                                        alt="{{ $item->nama_barang }}" class="rounded"
                                        style="width: 100%; height: 100%; object-fit: cover;">
                                </div>

                                <div class="d-flex w-100 align-items-center gap-2">
                                    <div class="d-flex justify-content-between flex-grow-1 flex-wrap">
                                        <div>
                                            <h6 class="mb-0">{{ $item->nama_barang }}</h6>
                                            <small class="text-muted">
                                                {{ $item->id_barang }}
                                            </small>
                                        </div>
                                        <div class="user-progress d-flex align-items-center gap-2">
                                            <span class="badge bg-danger"> <small class=" text-white">
                                                    Stok: <strong>{{ number_format($currentStock, 0) }}</strong> /
                                                    Min: <strong>{{ number_format($minStockQty, 0) }}</strong>
                                                </small></span>
                                        </div>
                                    </div>

                                    <div class="chart-progress" data-color="danger" data-series="{{ $percentage }}">
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-5">

                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="ti ti-check fs-2"></i>
                            </span>
                        </div>

                        <h6 class="mb-1 text-success">
                            Semua stok aman
                        </h6>

                        <small class="text-muted">
                            Tidak ada barang yang berada di bawah minimum stock bulan ini.
                        </small>

                    </div>
                @endif

            </div>
        </div>
    </div>
    <div class="marquee-container">
        <div class="marquee-text" id="content">
            @foreach ($brandName as $bn)
                <span class="brand-item">{{ $bn->detail }} </span>
            @endforeach
            @foreach ($brandName as $bn)
                <span class="brand-item">{{ $bn->detail }} </span>
            @endforeach
        </div>
    </div>
@endsection
@push('style')
    <style>
        .marquee-container {
            width: 100%;
            overflow: hidden;
            background-color: #FFFAF3;
            padding: 20px 0;
            /* Tambahkan ini agar aman */
            position: relative;
        }

        .marquee-text {
            white-space: nowrap;
            display: inline-block;
            /* Durasi bisa diatur: makin besar angkanya, makin lambat gerakannya */
            animation: move-text 30s linear infinite;
        }

        .brand-item {
            font-size: 20px;
            font-weight: bold;
            color: #0A2947;
            text-transform: uppercase;
            /* Menambahkan margin kanan agar setiap brand punya jarak */
            margin-right: 50px;
            /* Menambahkan transparansi 50% */
            opacity: 0.5;

            /* Opsi tambahan: Agar transisi saat hover lebih halus */
            transition: opacity 0.3s ease;
        }

        @keyframes move-text {
            0% {
                transform: translateX(0);
            }

            100% {
                /* Dengan duplikasi konten, -50% akan membuat loop terlihat sempurna */
                transform: translateX(-50%);
            }
        }
    </style>
@endpush
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        var options = {
            chart: {
                type: 'line',
                height: 100,
                sparkline: {
                    enabled: true // 🔥 ini bikin mini chart seperti di gambar kamu
                }
            },
            series: [{
                name: 'Profit',
                data: [10, 40, 20, 60, 40, 80]
            }],
            stroke: {
                width: 2,
                curve: 'smooth'
            },
            colors: ['#00cfe8'],
            tooltip: {
                enabled: true
            }
        };

        var chart = new ApexCharts(document.querySelector("#profitChart"), options);
        chart.render();
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            let labels = @json($brandLabels ?? []);
            let values = @json($brandValues ?? []);

            console.log("LABELS:", labels);
            console.log("VALUES:", values);

            if (!values.length) {
                document.querySelector("#brandSalesDonut").innerHTML =
                    "<div class='text-center text-muted'>No Data</div>";
                return;
            }

            const options = {
                series: values,
                chart: {
                    type: 'donut',
                    height: 100
                },
                labels: labels,

                legend: {
                    position: 'bottom'
                },

                dataLabels: {
                    enabled: true
                },

                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: function(w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    }
                                }
                            }
                        }
                    }
                }
            };

            new ApexCharts(
                document.querySelector("#brandSalesDonut"),
                options
            ).render();
        });
    </script>
@endpush
@push('style')
    <style>
        #profitChart {
            width: 100%;
        }

        .card-body {
            overflow: hidden;
        }
    </style>
@endpush
