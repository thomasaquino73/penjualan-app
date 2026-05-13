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
                            <a href="{{ route('data-barang.edit', $detail->id) }}"
                                class="btn btn-warning fw-bold py-2 shadow-sm text-white border-0">
                                <i class="ti ti-edit me-1"></i> Edit Product Information
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- unit Details Section --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-3">
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
                                        <td class="fw-medium text-dark ps-2">{{ $detail->kategoriID->detail ?? '-' }}</td>
                                    </tr>
                                    <tr class="align-middle">
                                        <th class="ps-4 py-3 text-muted fw-normal">Warehouse Location</th>
                                        <td class="fw-medium text-dark ps-2">{{ $detail->warehouseID->nama_gudang ?? '-' }}
                                        </td>
                                    </tr>
                                    <tr class="align-middle">
                                        <th class="ps-4 py-3 text-muted fw-normal">Primary Unit</th>
                                        <td class="fw-medium text-dark ps-2">{{ $detail->unitID->detail ?? '-' }}</td>
                                    </tr>
                                    <tr class="align-middle">
                                        <th class="ps-4 py-3 text-muted fw-normal">Inventory Type</th>
                                        <td class="fw-medium text-dark ps-2">{{ $detail->typeID->detail ?? '-' }}</td>
                                    </tr>
                                    <tr class="align-middle">
                                        <th class="ps-4 py-3 text-muted fw-normal">Stock Availability</th>
                                        <td class="ps-2">
                                            <span
                                                class="badge bg-{{ $detail->quantity > 0 ? 'info' : 'danger' }} px-3 py-2">
                                                {{ $detail->quantity ?? '0' }} {{ $detail->unitID->detail ?? '0' }}
                                            </span>
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
                <div class="card border-0 shadow-sm ">
                    <div class="card border-0 shadow-sm ">
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
            </div>
        </div>
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
    </style>
@endsection
