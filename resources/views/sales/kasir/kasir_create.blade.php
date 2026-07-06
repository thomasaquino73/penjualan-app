@extends('layouts.app')
@section('konten')
    <!-- HEADER -->
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">

            <!-- Kiri -->
            <h4 class="fw-bold mb-0">🛒 Store Sales (POS)</h4>

            <!-- Kanan -->
            <div class="d-flex gap-2">
                <div class="mb-3">
                    <label class="form-label">Cashier</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-user"></i></span>
                        <input type="text" value="{{ Auth()->user()->fullname }}" class="form-control" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Date</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                        <input type="text" value="" id="sales_date" name="sales_date" class="form-control"
                            readonly>
                    </div>
                </div>

            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-2 mb-lg-0"><i class="ti ti-news me-1"></i>Transaction</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Transaction Number <small class="text-danger">*</small> </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"> <i class="ti ti-barcode"></i>
                                </span>
                                <input type="text" name="id_barang" id="id_barang" class="form-control"
                                    value="{{ $idNumber }}">
                            </div>
                            <span class="error text-danger" id="id_barangError"></span>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Customer<small class="text-danger">*</small> </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"> <i class="ti ti-user"></i>
                                </span>
                                <select name="" id="" class="form-select select2"
                                    data-placeholder="Select Customer">
                                    <option></option>
                                    @foreach ($customer as $cust)
                                        <option value="{{ $cust->id }}">{{ $cust->nama_customer }}</option>
                                    @endforeach
                                </select>
                                <span class="input-group-text"> <button class="btn btn-sm btn-info"> <i
                                            class="ti ti-plus"></i></button></span>
                            </div>
                            <span class="error text-danger" id="id_barangError"></span>
                        </div>

                    </div>
                    {{-- <div class="col-md-12">
                        <label class="form-label">Barcode / Product Name<small class="text-danger">*</small> </label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="ti ti-qrcode"></i></i>
                            </span>
                            <input type="text" name="id_barang" id="id_barang" class="form-control" value="">
                        </div>
                        <span class="error text-danger" id="id_barangError"></span>
                    </div> --}}

                    <div class="mt-5">
                        <h6 class="card-title mb-2 mb-lg-0">Product List</h6>
                        <table class="table display responsive nowrap" id="table">
                            <thead class="border-top" style="background-color: #AEDEFC; ">
                                <tr>
                                    <th>NO</th>
                                    <th>PRODUCT</th>
                                    <th>PRICE</th>
                                    <th>QTY</th>
                                    <th>DISCOUNT</th>
                                    <th>AMOUNT</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0 fw-bold"><i class="ti ti-credit-card me-1"></i>Payment Summary</h6>
                </div>

                <div class="card-body">

                    <!-- Subtotal -->
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-semibold">Rp 144,000</span>
                    </div>

                    <!-- Discount -->
                    <div class="row align-items-center mb-2">
                        <div class="col-6 text-muted">Discount</div>
                        <div class="col-6">
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $mataUangDefault->symbol }}</span>
                                <input type="number" id="discount" name="discount" class="form-control" placeholder="0"
                                    min="0">
                            </div>
                        </div>
                    </div>

                    <!-- Tax -->
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Tax (11%)</span>
                        <span>Rp 0</span>
                    </div>

                    <hr>

                    <!-- Total -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold">Total</span>
                        <h4 class="text-primary fw-bold mb-0">Rp 144,000</h4>
                    </div>


                    <!-- Cash Received -->
                    <div class="mb-3">
                        <label class="form-label">Amount Received</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text">{{ $mataUangDefault->symbol }}</span>
                            <input type="number" id="amount_receive" name="amount_receive" class="form-control"
                                placeholder="0" min="0">
                        </div>
                    </div>

                    <!-- Change -->
                    <div class="mb-3">
                        <label class="form-label">Change</label>
                        <input type="text" class="form-control text-success fw-bold text-end bg-light" value=""
                            readonly>
                    </div>
                    <!-- Payment Method -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Method</label>
                        <select name="" id="" class="form-select select2"
                            data-placeholder="Select Payment">
                            <option></option>
                            @foreach ($payment as $pay)
                                <option value="{{ $pay->id }}">{{ $pay->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Shipping Method</label>
                        <select name="" id="" class="form-select select2"
                            data-placeholder="Select Shipping">
                            <option></option>
                            @foreach ($shipping as $shipping)
                                <option value="{{ $shipping->id }}">{{ $shipping->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Notes -->
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" rows="2" placeholder="Add notes..."></textarea>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary w-50">Cancel</button>
                        <button class="btn btn-success w-50 fw-bold">✔ Save & Pay</button>
                    </div>

                </div>
            </div>

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
        $(function() {
            const datePicker = flatpickr("#sales_date", {
                enableTime: false,
                dateFormat: "d-m-Y",
                defaultDate: "{{ \Carbon\Carbon::now()->format('d-m-Y') }}",
            });
        });
        let prDetailsData = [];
        $(document).ready(function() {
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
                data: prDetailsData, // Mengarah ke array di atas
                columns: [{
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: 'date',
                    },

                    {
                        data: 'quantity'
                    },
                    {
                        data: 'stok_unit_name',
                    },
                    {
                        data: 'unit_price'
                    },
                    {
                        data: 'warehouse_name'
                    },

                ],
                layout: {
                    topStart: {
                        buttons: [{
                                text: '<i class="ti ti-plus me-1"></i> New',
                                className: 'btn btn-primary btn-sm me-2',
                                action: function(e, dt, node, config) {

                                    $('#formPrDetailStock')[0].reset();
                                    $('#detail_id').val('');
                                    $('#modalTitle').text('Create new entry');
                                    $('#btnSubmitModal').text('Create');
                                    $('#modalPrDetail').modal('show');
                                }
                            },
                            {
                                text: '<i class="ti ti-edit me-1"></i> Edit',
                                className: 'btn btn-warning btn-sm me-2',
                                extend: 'selectedSingle',
                                action: function(e, dt, node, config) {
                                    let data = dt.row({
                                        selected: true
                                    }).data();
                                    let rowIndex = dt.row({
                                        selected: true
                                    }).index();

                                    // 1. Set penanda bahwa ini adalah mode EDIT
                                    window.isEditingMode = true;

                                    $('#detail_id').val(rowIndex);
                                    $('#quantity').val(data.quantity);
                                    $('#stok_unit_id').data('pending-val', data.stok_unit_id);

                                    // 2. Set value produk dan trigger change
                                    $('#product_id').val(data.product_id).trigger('change');

                                    // 3. Set harga unit price asli dari tabel data
                                    $('#unit_price').val(data.unit_price);
                                    $('#discount').val(data.discount || 0); // Jika ada diskon
                                    $('#tax').val(data.tax || 0); // Jika ada tax

                                    $('#modalTitle').text('Edit entry');
                                    $('#btnSubmitModal').text('Update');
                                    $('#modalPrDetail').modal('show');
                                }
                            },
                            {
                                text: '<i class="ti ti-trash me-1"></i> Delete',
                                className: 'btn btn-danger btn-sm me-2',
                                extend: 'selected',
                                action: function(e, dt, node, config) {
                                    let rowIndex = dt.row({
                                        selected: true
                                    }).index();
                                    let data = dt.row({
                                        selected: true
                                    }).data();
                                    let name = data.data_produk ? data.data_produk : '';

                                    Swal.fire({
                                        title: 'Are you sure?',
                                        text: "Want to delete data: " + name,
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonText: 'Yes, delete it!',
                                        cancelButtonText: 'Cancel',
                                        customClass: {
                                            confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
                                            cancelButton: 'btn btn-label-secondary waves-effect waves-light'
                                        },
                                        buttonsStyling: false
                                    }).then(function(result) {
                                        if (result.isConfirmed) {
                                            prDetailsData.splice(rowIndex, 1);
                                            dt.clear().rows.add(prDetailsData).draw();
                                            toastr.success('Deleted Data Successfully',
                                                '', {
                                                    timeOut: 1500,
                                                    progressBar: true
                                                });
                                        }
                                    });
                                }
                            },
                            {
                                text: '<i class="ti ti-refresh me-1"></i> Clear All',
                                className: 'btn btn-secondary btn-sm',
                                action: function(e, dt, node, config) {
                                    prDetailsData = [];
                                    dt.clear().draw();
                                    $('#percent').val(0); // Jika ada tax

                                }
                            }
                        ]
                    }
                }
            });
        });
    </script>
@endpush
