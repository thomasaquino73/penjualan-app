@extends('layouts.app')

@section('konten')
    <div class="container py-4">
        {{-- Breadcrumb & Title --}}
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">{{ $title }}</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        @foreach ($breadcrumb as $item)
                            @if ($item['url'])
                                <li class="breadcrumb-item">
                                    <a href="{{ $item['url'] }}"
                                        class="text-decoration-none text-muted small">{{ $item['label'] }}</a>
                                </li>
                            @else
                                <li class="breadcrumb-item active fw-medium small" aria-current="page">{{ $item['label'] }}
                                </li>
                            @endif
                        @endforeach
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('data-barang.index') }}"
                    class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="ti ti-chevron-left me-1"></i> Back to List
                </a>
            </div>
        </div>

        <div class="row">
            {{-- Product Image Section --}}
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm overflow-hidden h-100">
                    <div class="card-body text-center p-4">
                        <div class="position-relative d-inline-block w-100 mb-4">
                            <img src="{{ $detail->photo_filename ? asset($detail->photo_filename) : asset('image/no-images.jpg') }}"
                                class="img-fluid rounded-3 shadow-sm mx-auto d-block" alt="Product Image"
                                style="max-height: 300px; object-fit: contain; width: 100%;">

                            <span
                                class="position-absolute top-0 end-0 badge rounded-pill bg-{{ $detail->product_type == 'supply' ? 'success' : 'primary' }} m-2 px-3 py-2 shadow-sm">
                                {{ ucfirst($detail->product_type) }}
                            </span>
                        </div>

                        <h5 class="fw-bold text-dark mb-1">{{ $detail->nama_barang }}</h5>
                        <p class="text-muted small mb-3">Code: <span
                                class="fw-bold text-secondary">{{ $detail->id_barang }}</span></p>

                        <div class="bg-light p-3 rounded-3">
                            <small class="text-muted d-block mb-1">Selling Price</small>
                            <h4 class="text-primary fw-bold mb-0">
                                {{ $detail->price ? 'Rp ' . number_format($detail->price, 0, ',', '.') : 'N/A' }}
                            </h4>
                        </div>

                        <div class="d-grid mt-4">
                            <a href="{{ route('data-barang.index', $detail->id) }}"
                                class="btn btn-secondary fw-bold py-2 shadow-sm text-white border-0">
                                <i class="ti ti-chevron-left me-1"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- unit Details Section --}}
            <div class="col-lg-8">
                <div class="row">
                    {{-- Product Specifications --}}
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3 border-bottom border-light">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="ti ti-info-circle me-2 text-primary"></i>Product Specifications
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <tbody>
                                            <tr class="align-middle">
                                                <th class="ps-4 py-3 text-muted fw-normal" width="35%">Category</th>
                                                <td class="fw-medium text-dark ps-2">
                                                    {{ $detail->kategoriID->detail ?? '-' }}
                                                </td>
                                            </tr>
                                            <tr class="align-middle">
                                                <th class="ps-4 py-3 text-muted fw-normal">Primary Unit</th>
                                                <td class="fw-medium text-dark ps-2">{{ $detail->unitID->detail ?? '-' }}
                                                </td>
                                            </tr>
                                            <tr class="align-middle">
                                                <th class="ps-4 py-3 text-muted fw-normal">Minimun Stock</th>
                                                <td class="fw-medium text-dark ps-2">
                                                    {{ $detail->primary_minimum_stock ?? '-' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="ps-4 py-3 text-muted fw-normal">Description</th>
                                                <td class="text-dark lh-base py-3 ps-2">
                                                    {{ $detail->keterangan ? $detail->keterangan : 'No description available for this product.' }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">

                        {{-- Unit Conversion --}}
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3 border-bottom border-light">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="ti ti-arrows-exchange me-2 text-primary"></i>Unit Conversion
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr class="bg-light">
                                                <th class="ps-4 py-3 text-muted fw-bold" width="10%">No</th>
                                                <th class="py-3 text-muted fw-bold">From Unit</th>
                                                <th class="py-3 text-muted fw-bold text-center" width="10%">=</th>
                                                <th class="py-3 text-muted fw-bold">Conversion Result</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($unitConversion as $index => $conv)
                                                <tr class="align-middle">
                                                    <td class="ps-4 py-3 fw-medium">{{ $index + 1 }}</td>
                                                    <td class="py-3 text-dark">
                                                        1 {{ $conv->fromUnitID->detail ?? 'N/A' }}
                                                    </td>
                                                    <td class="py-3 text-center fw-bold text-primary">=</td>
                                                    <td class="py-3 text-dark fw-bold">
                                                        {{ number_format($conv->qty, 0) }}
                                                        {{ $conv->toUnitID->detail ?? 'Sub-unit' }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center py-4 text-muted italic">
                                                        <i class="ti ti-alert-circle d-block mb-2 fs-3"></i>
                                                        No conversion data available for this product.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        {{-- Product Variants Section --}}
                        <div class="card  border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3 border-bottom border-light">
                                <h5 class="card-title mb-0 fw-bold">
                                    <i class="ti ti-box me-2 text-primary"></i>Product Variants & Custom Specs
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive text-nowrap">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr class="bg-light">
                                                <th class="ps-4 py-3 text-muted fw-bold" width="10%">No</th>
                                                <th class="py-3 text-muted fw-bold" width="30%">Variant Name</th>
                                                <th class="py-3 text-muted fw-bold">Custom Specifications</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($detail->variants as $index => $variant)
                                                <tr class="align-middle">
                                                    <td class="ps-4 py-3 fw-medium">{{ $index + 1 }}</td>
                                                    <td class="py-3">
                                                        <span class="fw-bold text-dark">{{ $variant->variant_name }}</span>
                                                    </td>
                                                    <td class="py-3">
                                                        @if (!empty($variant->specifications) && count($variant->specifications) > 0)
                                                            <div class="d-flex flex-wrap gap-2">
                                                                @foreach ($variant->specifications as $label => $value)
                                                                    <span
                                                                        class="badge bg-label-secondary border border-gray-200 px-3 py-2 rounded-pill shadow-sm text-dark">
                                                                        <strong
                                                                            class="text-secondary">{{ $label }}:</strong>
                                                                        {{ $value }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <span class="text-muted small italic">- No specific fields
                                                                added
                                                                -</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center py-4 text-muted italic">
                                                        <i class="ti ti-info-circle d-block mb-2 fs-3"></i>
                                                        No variant data available for this product.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAMBAHAN BARU: Stock History Section (data_barang_stok) --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3 border-bottom border-light">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="ti ti-history me-2 text-primary"></i>Stock Movement History
                </h5>
            </div>
            <div class="card-body p-3">

                <div class="table-responsive ">
                    <div class="nav-align-top nav-tabs-shadow mb-4 ps-3">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-top-home" aria-controls="navs-top-home" aria-selected="true">
                                    History
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-top-profile" aria-controls="navs-top-profile"
                                    aria-selected="false" tabindex="-1">
                                    Warehouse
                                </button>
                            </li>

                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="navs-top-home" role="tabpanel">
                                <table class="table table-hover mb-0" id="table_history">
                                    <thead>
                                        <tr class="bg-light">
                                            <th class="ps-4 py-3 text-muted fw-bold" width="5%">No</th>
                                            <th class="py-3 text-muted fw-bold">Date</th>
                                            <th class="py-3 text-muted fw-bold">Document Number</th>
                                            <th class="py-3 text-muted fw-bold">Document Type</th>
                                            <th class="py-3 text-muted fw-bold">Description</th>
                                            <th class="py-3 text-muted fw-bold text-end">In</th>
                                            <th class="py-3 text-muted fw-bold text-end">Out</th>
                                            <th class="py-3 text-muted fw-bold text-end">Units</th>
                                            <th class="py-3 text-muted fw-bold text-end">Running Balance</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse($mutations as $index => $stock)
                                            <tr class="align-middle">

                                                <td class="ps-4 py-3 fw-medium">
                                                    {{ $index + 1 }}
                                                </td>

                                                <td class="py-3 text-dark">
                                                    {{ $stock->created_at ? $stock->created_at->translatedFormat('d M Y H:i') : '-' }}
                                                </td>
                                                <td class="py-3">
                                                    {{ $stock->document_number ?? '-' }}
                                                </td>

                                                {{-- Qty transaksi asli --}}
                                                <td class="py-3 text-end">
                                                    @php
                                                        $statuses = [
                                                            'initial_stock' => [
                                                                'label' => 'Initial Stock',
                                                                'class' => 'text-success',
                                                            ],
                                                            'receive_item' => [
                                                                'label' => 'Receive Item',
                                                                'class' => 'text-primary',
                                                            ],
                                                            'delivery_order' => [
                                                                'label' => 'Delivery Order',
                                                                'class' => 'text-warning',
                                                            ],
                                                            'adjustment' => [
                                                                'label' => 'Adjustment',
                                                                'class' => 'text-info',
                                                            ],
                                                            'item_transfer' => [
                                                                'label' => 'Item Transfer',
                                                                'class' => 'text-secondary',
                                                            ],
                                                        ];

                                                        $type = $stock->document_type ?? 'initial_stock';
                                                        $status = $statuses[$type] ?? [
                                                            'label' => $type,
                                                            'class' => 'text-dark',
                                                        ];
                                                    @endphp

                                                    <span class="fw-bold {{ $status['class'] }}">
                                                        {{ $status['label'] }}
                                                    </span>
                                                </td>
                                                <td class="py-3 text-dark">
                                                    {{ $stock->keterangan ?? '-' }}
                                                </td>
                                                <td class="py-3 text-end">
                                                    @if ($stock->type == 'in')
                                                        <span class="fw-bold text-success">
                                                            {{ number_format($stock->qty_transaksi, 0, ',', '.') }}
                                                        </span>
                                                    @endif
                                                </td>

                                                {{-- Qty transaksi asli --}}
                                                <td class="py-3 text-end">
                                                    @if ($stock->type == 'out')
                                                        <span class="fw-bold text-danger">
                                                            {{ number_format($stock->qty_transaksi, 0, ',', '.') }}
                                                        </span>
                                                    @endif
                                                </td>

                                                {{-- Unit transaksi --}}
                                                <td class="py-3 text-end fw-bold text-primary">
                                                    {{ $stock->unitID->detail ?? 'N/A' }}
                                                </td>

                                                {{-- Saldo selalu base unit --}}
                                                <td class="py-3 text-end fw-bold text-primary">
                                                    {{ number_format($stock->saldo_akhir, 0, ',', '.') }}
                                                    <small class="text-muted">
                                                        {{ $detail->unitID->detail ?? '' }}
                                                    </small>
                                                </td>

                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-4 text-muted">
                                                    <i class="ti ti-clipboard-list d-block mb-2 fs-3"></i>
                                                    No stock movement history data found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane fade" id="navs-top-profile" role="tabpanel">
                                <div class="row ">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="date" id="search_date"
                                                placeholder="Pilih tanggal..." value="{{ request('date') }}">
                                            <button class="btn btn-outline-success" type="button" id="btnFilter">
                                                <i class="ti ti-filter"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" type="button" id="btnReset">
                                                <i class="ti ti-repeat"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <table class="table table-hover mb-0" id="table_warehouse">
                                    <thead>
                                        <tr class="bg-light">
                                            <th class="ps-4 py-3 text-muted fw-bold">
                                                Warehouse
                                            </th>

                                            <th class="py-3 text-muted fw-bold text-end">
                                                Balance
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        @forelse ($warehouseHistory as $history)
                                            <tr>

                                                <td class="ps-4 py-3">
                                                    {{ $history['warehouse_name'] ?? 'N/A' }}
                                                </td>

                                                <td class="py-3 text-end fw-bold text-primary">

                                                    {{ number_format($history['total_qty'], 0, ',', '.') }}

                                                    <small class="text-muted">
                                                        {{ $detail->unitID->detail ?? '' }}
                                                    </small>

                                                </td>

                                            </tr>

                                        @empty

                                            <tr>
                                                <td colspan="2" class="text-center py-4 text-muted">
                                                    No warehouse stock found.
                                                </td>
                                            </tr>
                                        @endforelse

                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
        {{-- END TAMBAHAN BARU --}}
    </div>

    <style>
        /* Modern UI Customizations */
        .breadcrumb-item+.breadcrumb-item::before {
            content: "•";
            color: #adb5bd;
        }

        .card {
            border-radius: 15px;
            transition: transform 0.2s ease-in-out;
        }

        .table th {
            background-color: #fafbfc;
        }

        .btn {
            transition: all 0.3s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .img-fluid {
            transition: all 0.5s;
        }

        .img-fluid:hover {
            transform: scale(1.02);
        }

        /* Styling untuk custom badges di bagian spesifikasi kustom */
        .bg-label-secondary {
            background-color: #f1f2f4 !important;
            color: #4b4b4b !important;
            font-size: 0.825rem;
        }
    </style>
@endsection
@push('scripts')
    <script>
        let table_history = new DataTable('#table_history', {
            // Menambahkan opsi untuk menangani baris kosong
            "columnDefs": [{
                "targets": "_all",
                "defaultContent": ""
            }],
            // Jika masih error, tambahkan ini untuk mematikan validasi kolom
            "ordering": false
        });
        let table_warehouse = new DataTable('#table_warehouse');
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr("#search_date", {
                enableTime: false,
                dateFormat: "d-m-Y",
            });
            // Event Klik Filter
            document.getElementById('btnFilter').addEventListener('click', function() {
                let date = document.getElementById('search_date').value;
                let url = new URL(window.location.href);

                if (date) {
                    url.searchParams.set('date', date);
                }
                window.location.href = url.toString(); // Reload halaman dengan parameter baru
            });

            // Event Klik Reset
            document.getElementById('btnReset').addEventListener('click', function() {
                let url = new URL(window.location.href);
                url.searchParams.delete('date');
                window.location.href = url.toString();
            });
        });
    </script>
@endpush
