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
        <div class="col-sm-12 col-xl-5 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <div class="card-title m-0 me-2">
                        <h5 class="m-0 me-2">Sales Transactions</h5>
                        <small class="text-muted">Total {{ $TotalTransactions }} Transactions done in this
                            Month</small>
                    </div>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="salesID" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                            <i class="ti ti-dots-vertical ti-sm text-muted"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="salesID">
                            @can('sales_order-browse')
                                <a class="dropdown-item " href="{{ route('sales-order.index') }}">
                                    View All
                                </a>
                            @endcan

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

                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="me-2">
                                        <h6 class="mb-0">{{ $tranTer->nama_barang }}</h6>
                                        <small class="text-muted d-block">
                                            {{-- {{ $tranTer->total_transaksi }} kali penjualan --}}
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

                    </ul>
                </div>
            </div>

        </div>
        <div class="col-sm-12 col-xl-7 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <div class="card-title m-0 me-2">
                        <h5 class="m-0 me-2">Sales Statistics</h5>
                        <small class="text-muted" id="salesInfo">
                            Total Sales {{ $currentYear }} :
                            <strong class="text-primary">
                                {{ format_uang(convert_currency($totalSalesThisYear, 1)) }}
                            </strong>
                        </small>
                    </div>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="transactionID" data-bs-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <i class="ti ti-dots-vertical ti-sm text-muted"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="transactionID">

                            <a class="dropdown-item changeYear" href="#" data-year="{{ $currentYear }}">
                                This Year ({{ $currentYear }})
                            </a>

                            <a class="dropdown-item changeYear" href="#" data-year="{{ $lastYear }}">
                                Last Year ({{ $lastYear }})
                            </a>

                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="salesStatisticsChart" height="120"></canvas>
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

                                        <div class="chart-progress" data-color="danger"
                                            data-series="{{ $percentage }}">
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
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <div class="card-title m-0 me-2">
                        <h5 class="m-0 me-2">Most Buyers</h5>
                        <small class="text-muted">
                            {{ now()->translatedFormat('F Y') }}
                        </small>
                    </div>

                    <div class="dropdown">
                        <button class="btn p-0" type="button" data-bs-toggle="dropdown">
                            <i class="ti ti-dots-vertical ti-sm text-muted"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="{{ route('sales-order.index') }}">
                                View All
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">

                    @forelse($mostBuyers as $buyer)
                        <div class="d-flex justify-content-between align-items-center mb-3">

                            <div>
                                <h6 class="mb-0">
                                    {{ $buyer->customer_name }}
                                </h6>

                                <small class="text-muted">
                                    {{ $buyer->total_invoice }} Orders
                                </small>
                            </div>

                            <div class="text-end">
                                <span class="fw-semibold">
                                    Rp {{ number_format($buyer->total_amount, 0, ',', '.') }}
                                </span>
                            </div>

                        </div>

                    @empty

                        <p class="text-muted text-center">
                            Belum ada transaksi
                        </p>
                    @endforelse

                </div>
            </div>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const salesStatistics = @json($salesStatistics);

            new Chart(document.getElementById('salesStatisticsChart'), {
                type: 'bar',
                data: {
                    labels: salesStatistics.labels,
                    datasets: [{
                        label: 'Total Penjualan',
                        data: salesStatistics.values,
                        backgroundColor: '#696cff',
                        borderRadius: 6,
                        maxBarThickness: 35
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Rp ' + Number(context.raw).toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + Number(value).toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });

        });
    </script>
    <script>
        let salesChart;

        const salesStatistics = @json($salesStatistics);

        salesChart = new Chart(
            document.getElementById('salesStatisticsChart'), {
                type: 'bar',
                data: {
                    labels: salesStatistics.labels,
                    datasets: [{
                        data: salesStatistics.values,
                        backgroundColor: '#696cff',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            }
        );

        $(document).on('click', '.changeYear', function(e) {

            e.preventDefault();

            let year = $(this).data('year');

            $.ajax({

                url: "{{ route('dashboard.sales-statistics') }}",

                type: "GET",

                data: {
                    year: year
                },

                beforeSend: function() {

                    $('.changeYear').addClass('disabled');

                },

                success: function(res) {

                    salesChart.data.labels = res.labels;

                    salesChart.data.datasets[0].data = res.values;

                    salesChart.update();

                    $("#salesInfo").html(
                        'Total Sales ' + res.year +
                        ' : <strong class="text-primary">Rp ' + res.totalSales + '</strong>'
                    );

                },

                complete: function() {

                    $('.changeYear').removeClass('disabled');

                }

            });

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
