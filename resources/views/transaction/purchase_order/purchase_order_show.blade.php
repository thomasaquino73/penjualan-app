@extends('layouts.app')
@section('konten')
    <div class="card">
        <div
            class="card-header d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center">

            <!-- SISI KIRI: Detail Perusahaan -->
            <div class="col-md-6">
                <div class="d-flex align-items-start gap-3">
                    <!-- Logo Perusahaan -->
                    <div class="flex-shrink-0">
                        @if (isset($company) && $company->logo)
                            <img src="{{ asset($company->logo) }}" alt="Logo {{ $company->nama_perusahaan }}"
                                class="img-fluid rounded" style="max-width: 100px; height: auto; object-fit: contain;">
                        @else
                            <img src="{{ asset('image/no-images.jpg') }}" alt="Logo {{ $company->nama_perusahaan }}"
                                class="img-fluid rounded" style="max-width: 100px; height: auto; object-fit: contain;">
                        @endif
                    </div>

                    <!-- Data Perusahaan -->
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1">{{ $company->nama_perusahaan ?? 'Nama Perusahaan' }}</h5>

                        <p class="text-muted small mb-2">
                            {{ $company->alamat ?? 'Alamat belum diatur' }}<br>
                            {{ $company->negara ?? '' }} {{ $company->kodepos ?? '' }}
                        </p>

                        <div class="row g-1 small text-secondary">
                            @if ($company->nomor_telepon)
                                <div class="col-12">
                                    <i class="bi bi-telephone-fill me-1"></i> {{ $company->nomor_telepon }}
                                </div>
                            @endif
                            @if ($company->email)
                                <div class="col-12">
                                    <i class="bi bi-envelope-fill me-1"></i> {{ $company->email }}
                                </div>
                            @endif
                            @if ($company->website)
                                <div class="col-12">
                                    <i class="bi bi-globe me-1"></i> <a href="{{ $company->website }}" target="_blank"
                                        class="text-decoration-none text-secondary">{{ $company->website }}</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-5">

            </div>

        </div>
        <div class="card-body table-responsive p-3">
            <form action="{{ route('purchase-order.update', $model->id) }}" method="POST" id="postForm"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="items_detail" id="items_detail">
                <input type="hidden" name="save_and_new" id="save_and_new" value="0">
                <div class="row mb-5">

                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Supplier</label>
                                    <input type="text" class="form-control mb-2" value="{{ $model->supplier->nama }}"
                                        disabled>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Address</label>
                                <textarea name="alamat" id="alamat" cols="" rows="5" class="form-control" disabled
                                    placeholder="Address">{{ $model->supplier->alamat }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">PO Number <small class="text-danger">*</small> </label>
                                <input type="text" name="code" id="code" class="form-control"
                                    value="{{ $model->code }}" disabled>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">PO Date<small class="text-danger">*</small> </label>
                                <input type="text" name="" id="" class="form-control"
                                    value="{{ isset($model) ? $model->date : date('d-m-Y') }}" disabled>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Expected Date<small class="text-danger">*</small> </label>
                                <input type="text" name="" id="" class="form-control"
                                    placeholder="Select Date"
                                    value="{{ isset($model) && $model->expected_date ? $model->expected_date->format('d-m-Y') : '' }}"
                                    disabled>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">FOB<small class="text-danger">*</small> </label>
                                <input type="text" name="fob_id" id="fob_id" class="form-control"
                                    value="{{ $model->fob_id }}" disabled>
                                <span class="error text-danger" id="fob_idError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Term<small class="text-danger">*</small> </label>
                                <input type="text" name="term" id="term" class="form-control"
                                    value="{{ $model->termID ? $model->termID->detail : '' }}" disabled>

                                <span class="error text-danger" id="termError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Ship via<small class="text-danger">*</small> </label>
                                <input type="text" name="ship_via" id="ship_via" class="form-control"
                                    value="{{ $model->ship->merk }} - {{ $model->ship->plat_nomor }}" disabled>

                                <span class="error text-danger" id="vehicle_idError"></span>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="divider divider-dashed">
                    <div class="divider-text">Purchase Order Detail</div>
                </div>
                <div class="row mt-3">
                    <table class="table display responsive nowrap" id="table">
                        <thead class="border-top" style="background-color: #AEDEFC; ">
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Unit</th>
                                <th>Unit Price</th>
                                <th>Disc</th>
                                <th>Tax</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="row mb-5">
                    <div class="col-lg-6 ">
                        <label class="form-label" for="">Description</label>
                        <textarea name="" id="" class="form-control" rows="8" placeholder="Enter description"
                            disabled>{{ $model->description }}</textarea>
                        <span class="error text-danger" id="descriptionError"></span>
                    </div>
                    <div class="col-lg-2 mb-3 ">

                    </div>
                    <div class="col-lg-4">
                        <div class="col-12 mb-3 ">
                            <label class="form-label" for="sub_total">Sub Total</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>
                                <input type="number" id="" name="" class="form-control"
                                    value="{{ number_format(old('sub_total', $model->sub_total), 0, ',', '.') }}"
                                    placeholder="0" readonly>
                            </div>

                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label" for="discount_all">Discount</label>
                            <div class="row">
                                <div class="col-4">
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text">%</span>
                                        <input type="number" id="" name="" min="0"
                                            step="any" class="form-control" placeholder="0"
                                            value="{{ old('percent', $model->disc_percent) }}" readonly>
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>
                                        <input type="number" id="" name="" class="form-control"
                                            placeholder="0" min='0'
                                            value="{{ number_format(old('disc_nominal', $model->disc_nominal), 0, ',', '.') }} "readonly>
                                    </div>
                                </div>
                            </div>


                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label" for=""> <strong>Total Order</strong></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>
                                <input type="number" id="" name="" class="form-control"
                                    placeholder="0" readonly
                                    value="{{ number_format(old('', $model->grand_total), 0, ',', '.') }}" readonly>
                            </div>

                        </div>
                    </div>

                </div>
                <div class="card-footer d-flex justify-content-end gap-2">

                    <a href="{{ route('purchase-order.index') }}" class="btn btn-secondary"><i
                            class="ti ti-chevron-left"></i> Back</a>
                </div>
        </div>
    </div>
@endsection
@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/2.0.3/css/select.bootstrap5.css">
@endpush
@push('scripts')
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.js"></script>

    <script src="https://cdn.datatables.net/select/3.1.3/js/dataTables.select.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.3/js/select.bootstrap5.js"></script>

    <script>
        const formatRupiah = (angka) => {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0 // Ubah ke 2 jika ingin ada ,00 di belakang
            }).format(angka);
        };
        let prDetailsData = [
            @if (isset($model))
                @foreach ($model->details as $detail)
                    {
                        'product_id': '{{ $detail->product_id }}',
                        'data_produk': '{{ $detail->produkID ? $detail->produkID->nama_barang : 'Product Not Found' }}',
                        'quantity': '{{ $detail->qty }}',
                        'unit_id': '{{ $detail->unit_id }}',
                        // Menggunakan number_format PHP
                        'unit_price': ' {{ number_format($detail->unit_price, 0, ',', '.') }}',
                        'discount': ' {{ number_format($detail->discount, 0, ',', '.') }}',
                        'tax': ' {{ number_format($detail->tax, 0, ',', '.') }}',
                        'amount': ' {{ number_format($detail->amount, 0, ',', '.') }}',
                        'unit': '{{ $detail->unitID ? $detail->unitID->name ?? ($detail->unitID->detail ?? $detail->unitID->nama) : 'Unit' }}'
                    }
                    {{ !$loop->last ? ',' : '' }}
                @endforeach
            @endif
        ];
        const originalPrDetailsData = JSON.parse(JSON.stringify(prDetailsData));
        $(function() {
            flatpickr("#date", {
                enableTime: false,
                time_24hr: true,
                enableSeconds: false,
                dateFormat: "d-m-Y",
                minDate: "today",
                defaultDate: "{{ \Carbon\Carbon::now()->format('d-m-Y') }}"
            });

            flatpickr("#expected_date", {
                enableTime: false,
                time_24hr: true,
                enableSeconds: false,
                dateFormat: "d-m-Y",
                minDate: "today",
                defaultDate: ""
            });
        });

        // HANYA GUNAKAN SATU DOCUMENT READY DI SINI
        $(document).ready(function() {
            // 3. Inisialisasi DataTable
            let table = new DataTable('#table', {
                processing: true,
                serverSide: false,
                responsive: true,
                select: true,
                searching: false,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All']
                ],
                data: prDetailsData,
                columns: [{
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: 'data_produk'
                    },
                    {
                        data: 'quantity'
                    },
                    {
                        data: 'unit'
                    },
                    {
                        data: 'unit_price'
                    },
                    {
                        data: 'discount'
                    },
                    {
                        data: 'tax'
                    },
                    {
                        data: 'amount'
                    }
                ],

            });


        });
    </script>
@endpush
