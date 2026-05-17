@extends('layouts.app')
@section('konten')
    <h4>
        <span class="text-muted fw-light">
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

    <div class="card">
        <div
            class="card-header d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center">

            <h5 class="card-title mb-2 mb-lg-0">{{ $title }}</h5>

            <div class="col-12 col-lg-5">
                <div
                    class="d-flex flex-column flex-md-row gap-2
                    justify-content-start justify-content-lg-end">
                    <button class="btn btn-success btn-sm " id="showModalpr">
                        <i class="ti ti-clipboard me-1"></i> REQUISITION
                    </button>

                </div>
            </div>

        </div>
        <div class="card-body table-responsive p-3">
            <form action="{{ route('user.store') }}" method="POST" id="postForm" enctype="multipart/form-data">
                @csrf
                <div class="row mb-5">

                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Order By</label>
                                    <select name="customer_id" id="customer_id" class="form-select select2"
                                        data-placeholder="Select Customer">
                                        <option></option>
                                        @foreach ($customer as $cust)
                                            <option value="{{ $cust->id }}" data-alamat="{{ $cust->alamat }}">
                                                {{ $cust->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="error text-danger" id="avatarError"></span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Address</label>
                                <textarea name="alamat" id="alamat" cols="" rows="5" class="form-control" disabled
                                    placeholder="Address"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">PO Number <small class="text-danger">*</small> </label>
                                <input type="text" name="code" id="code" class="form-control"
                                    value="{{ $idNumber }}">
                                <span class="error text-danger" id="codeError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">PO Date<small class="text-danger">*</small> </label>
                                <input type="text" name="date" id="date" class="form-control" value="">
                                <span class="error text-danger" id="dateError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Expected Date<small class="text-danger">*</small> </label>
                                <input type="text" name="expected_date" id="expected_date" class="form-control"
                                    placeholder="Select Date">
                                <span class="error text-danger" id="expected_dateError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">FOB<small class="text-danger">*</small> </label>
                                <select name="fob_id" id="fob_id" class="form-select select2"
                                    data-placeholder="Select FOB">
                                    <option></option>
                                    @foreach ($fob as $f)
                                        <option value="{{ $f->id }}"> {{ $f->detail }}</option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="fob_idError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Term<small class="text-danger">*</small> </label>
                                <select name="term" id="term" class="form-select select2"
                                    data-placeholder="Select Term">
                                    <option></option>
                                    @foreach ($term as $term)
                                        <option value="{{ $term->id }}">{{ $term->detail }}</option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="termError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Ship via<small class="text-danger">*</small> </label>
                                <select name="vehicle_id" id="vehicle_id" class="form-select select2"
                                    data-placeholder="Select Vehicle">
                                    <option></option>
                                    @foreach ($kendaraan as $kendaraan)
                                        <option value="{{ $kendaraan->id }}"> {{ $kendaraan->merk }} -
                                            {{ $kendaraan->plat_nomor }}</option>
                                    @endforeach
                                </select>
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
                                <th>Disc %</th>
                                <th>Tax</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="row mb-5">
                    <div class="col-lg-6 ">
                        <label class="form-label" for="description">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="8" placeholder="Enter description"></textarea>
                        <span class="error text-danger" id="descriptionError"></span>
                    </div>
                    <div class="col-lg-2 mb-3 ">

                    </div>
                    <div class="col-lg-4">
                        <div class="col-12 mb-3 ">
                            <label class="form-label" for="sub_total">Sub Total</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>
                                <input type="number" id="sub_total" name="sub_total" class="form-control"
                                    placeholder="0" readonly>
                            </div>

                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label" for="discount_all">Discount</label>
                            <div class="row">
                                <div class="col-4">
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text">%</span>
                                        <input type="number" id="percent" name="percent" min='0'
                                            class="form-control" placeholder="0">
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>
                                        <input type="number" id="discount_all" name="discount_all" class="form-control"
                                            placeholder="0" min='0'>
                                    </div>
                                </div>
                            </div>


                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label" for="total_order"> <strong>Total Order</strong></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>
                                <input type="number" id="total_order" name="total_order" class="form-control"
                                    placeholder="0" readonly>
                            </div>

                        </div>
                    </div>

                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <button type="submit" id="savedata" class="btn btn-primary" data-save-and-new="false">
                        <i class="fa fa-upload me-1"></i> Save and Close
                    </button>

                    <button type="submit" id="savedatamore" class="btn btn-success" data-save-and-new="true">
                        <i class="fa fa-plus-circle me-1"></i> Save and Create New
                    </button>
                    <a href="{{ route('purchase-order.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="modalPrDetail">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Create new entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formPrDetail">
                    @csrf
                    <input type="hidden" name="id" id="detail_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label" for="product_id">Product / Item</label>
                                <select name="product_id" id="product_id" class="form-select select2-modal"
                                    data-placeholder="Select Product">
                                    <option></option>
                                    @foreach ($product as $product)
                                        <option value="{{ $product->id }}">{{ $product->nama_barang }}</option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="product_idError"></span>
                            </div>


                            <div class="col-3 mb-3">
                                <label class="form-label" for="quantity">Quantity</label>
                                <input type="number" id="quantity" name="quantity" class="form-control"
                                    placeholder="0">
                                <span class="error text-danger" id="quantityError"></span>
                            </div>
                            <div class="col-3 mb-3">
                                <label class="form-label" for="unit_id">Unit</label>
                                <select name="unit_id" id="unit_id" class="form-select select2-modal "
                                    data-placeholder="Select Unit">
                                    <option></option>
                                </select>
                                <span class="error text-danger" id="unit_idError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label" for="unit_price">Unit Price</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>
                                    <input type="number" id="unit_price" name="unit_price" class="form-control"
                                        placeholder="0">
                                </div>

                                <span class="error text-danger" id="unit_priceError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label" for="discount">Discount</label>
                                <input type="number" id="discount" name="discount" class="form-control"
                                    placeholder="0">
                                <span class="error text-danger" id="discountError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label" for="tax">Tax %</label>
                                <input type="number" id="tax" name="tax" class="form-control"
                                    placeholder="0">
                                <span class="error text-danger" id="taxError"></span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitModal">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalRequisitionDetail">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Requisition Processing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>
                                    <div class="form-check form-check-primary">
                                        <input class="form-check-input" type="checkbox" id="checkAll">
                                    </div>
                                </th>
                                <th>Invoice Number</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="requisitionTableBody">
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btnSubmitSelected">
                        <i class="ti ti-check me-1"></i> Process Selected
                    </button>
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

            // 1. Inisialisasi Select2 Modal
            $('.select2-modal').each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: $this.attr('data-placeholder'),
                    width: '100%',
                    dropdownParent: $('#modalPrDetail')
                });
            });

            // 2. Deklarasikan Variabel Global Scope (Di dalam Ready Utama)
            let prDetailsData = [];

            // Tampilkan Modal via Tombol Custom jika ada
            $('#showModalpr').on('click', function(e) {
                e.preventDefault();

                let tbody = $('#requisitionTableBody');

                // Reset checkbox 'Check All' menjadi tidak tercentang saat modal dibuka
                $('#checkAll').prop('checked', false);

                tbody.html(
                    '<tr><td colspan="3" class="text-center"><i class="fa fa-spin fa-spinner me-1"></i> Loading data...</td></tr>'
                );
                $('#modalRequisitionDetail').modal('show');

                $.ajax({
                    url: "{{ route('purchase-order.requisitions.processing') }}",
                    type: "GET",
                    dataType: "json",
                    success: function(response) {
                        tbody.empty();

                        if (response && response.length > 0) {
                            $.each(response, function(key, item) {
                                let dateFormatted = new Date(item.created_at)
                                    .toLocaleDateString('id-ID');

                                // Tambahkan checkbox dengan class 'checkItem' dan value berupa ID data
                                tbody.append(`
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input checkItem" type="checkbox" value="${item.id}">
                                    </div>
                                </td>
                                <td><strong>${item.code}</strong></td>
                                <td>${dateFormatted}</td>
                            </tr>
                        `);
                            });
                        } else {
                            tbody.html(
                                '<tr><td colspan="3" class="text-center text-muted">No processing data found.</td></tr>'
                            );
                        }
                    },
                    error: function(xhr) {
                        tbody.html(
                            '<tr><td colspan="3" class="text-center text-danger">Failed to fetch data.</td></tr>'
                        );
                    }
                });
            });

            // 2. LOGIC LOCK: CHECK ALL / UNCHECK ALL
            $('#checkAll').on('change', function() {
                // Jika checkAll dicentang, semua .checkItem ikut dicentang, begitu sebaliknya
                $('.checkItem').prop('checked', $(this).prop('checked'));
            });

            // Jika salah satu item diuncheck secara manual, matikan checkAll di atas head tabel
            $(document).on('change', '.checkItem', function() {
                if ($('.checkItem:checked').length === $('.checkItem').length) {
                    $('#checkAll').prop('checked', true);
                } else {
                    $('#checkAll').prop('checked', false);
                }
            });

            // TOMBOL UNTUK MASUKKAN KE TABEL DARI REQUISITION YANG DIPULIH
            $('#btnSubmitSelected').on('click', function() {
                let checkedBoxes = $('.checkItem:checked');

                // Validasi jika user belum memilih data sama sekali
                if (checkedBoxes.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: 'Silakan pilih minimal satu data requisition!',
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });
                    return;
                }

                // Tampilkan konfirmasi klak-klik lokal
                Swal.fire({
                    title: 'Proses data terpilih?',
                    text: `Anda memilih ${checkedBoxes.length} data untuk dimasukkan ke tabel.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Masukkan!',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {

                    }
                });
            });

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
                layout: {
                    topStart: {
                        buttons: [{
                                text: '<i class="ti ti-plus me-1"></i> New',
                                className: 'btn btn-primary btn-sm me-2',
                                action: function(e, dt, node, config) {
                                    var customerId = $('#customer_id').val();

                                    if (!customerId || customerId === '') {
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Warning!',
                                            text: 'Please select Customer first before adding new data.',
                                            confirmButtonColor: '#3085d6',
                                            confirmButtonText: 'OK',
                                            customClass: {
                                                confirmButton: 'btn btn-danger'
                                            },
                                            buttonsStyling: false
                                        });
                                        return false;
                                    }

                                    $('#formPrDetail')[0].reset();
                                    $('#detail_id').val('');

                                    if ($.fn.select2) {
                                        $('#product_id').val('').trigger('change');
                                        $('#unit_id').val('').trigger('change');
                                    }

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
                                    $('#unit_id').data('pending-val', data.unit_id);

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
                                            calculateGrandTotal();
                                            calculateTotalOrder()
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
                                    calculateGrandTotal();
                                    calculateTotalOrder()
                                    $('#percent').val(0); // Jika ada tax

                                }
                            }
                        ]
                    }
                }
            });

            // 4. Event Handler: Mengubah Produk (AJAX List Unit & Harga)
            $(document).on('change', '#product_id', function() {
                let productId = $(this).val();
                let unitSelect = $('#unit_id');
                let priceInput = $('#unit_price');

                if (!productId) {
                    unitSelect.empty().append('<option></option>').trigger('change');
                    priceInput.val('');
                    return;
                }

                // AJAX List Unit
                $.ajax({
                    url: `/get-units-by-product/${productId}`,
                    type: 'GET',
                    dataType: 'json',
                    beforeSend: function() {
                        unitSelect.html('<option>Loading units...</option>').prop('disabled',
                            true);
                    },
                    success: function(response) {
                        unitSelect.empty().append('<option></option>').prop('disabled', false);

                        if (response && response.length > 0) {
                            $.each(response, function(key, item) {
                                unitSelect.append(
                                    `<option value="${item.id}">${item.name}</option>`
                                );
                            });
                        } else {
                            unitSelect.append('<option value="">No unit available</option>');
                        }

                        unitSelect.trigger('change');

                        let pendingUnitId = unitSelect.data('pending-val');
                        if (pendingUnitId) {
                            unitSelect.val(pendingUnitId).trigger('change');
                            unitSelect.removeData('pending-val');
                        }
                    },
                    error: function() {
                        console.error('Gagal memuat list unit dari Controller.');
                        unitSelect.empty().append('<option></option>').prop('disabled', false)
                            .trigger('change');
                    }
                });

                // AJAX Harga Produk
                $.ajax({
                    url: `/purchase-order/get-product-price/${productId}`,
                    type: 'GET',
                    dataType: 'json',
                    beforeSend: function() {
                        priceInput.val('').attr('placeholder', 'Loading...');
                    },
                    success: function(response) {
                        if (response.success && response.price !== null && response.price !==
                            '') {
                            priceInput.val(response.price);
                        } else {
                            priceInput.val('').attr('placeholder', '0');
                        }
                    },
                    error: function(xhr) {
                        console.error("Gagal mengambil data harga produk:", xhr);
                        priceInput.val('').attr('placeholder', '0');
                    }
                });
            });

            // 5. Event Handler: Submit Form Modal Detail (Sekarang baris prDetailsData PASTI terbaca)
            $('#formPrDetail').on('submit', function(e) {
                e.preventDefault();

                let productId = $('#product_id').val();
                let productName = $('#product_id option:selected').text();
                let quantity = parseFloat($('#quantity').val()) || 0;
                let unitId = $('#unit_id').val();
                let unitName = $('#unit_id option:selected').text();
                let detailId = $('#detail_id').val();

                let unitPrice = parseFloat($('#unit_price').val()) || 0;
                let discount = parseFloat($('#discount').val()) || 0;
                let tax = parseFloat($('#tax').val()) || 0;

                let requiredDate = $('#required_date').val() || '';
                let notes = $('#notes').val() || '';

                if (!productId || quantity <= 0 || !unitId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please fill all required fields! (Product, Valid Quantity, and Unit)',
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });
                    return false;
                }

                // Validasi Duplikasi
                let isDuplicate = false;
                if (prDetailsData && prDetailsData.length > 0) {
                    for (let i = 0; i < prDetailsData.length; i++) {
                        if (prDetailsData[i].product_id == productId) {
                            if (detailId === '') {
                                isDuplicate = true;
                                break;
                            } else if (detailId !== '' && i != detailId) {
                                isDuplicate = true;
                                break;
                            }
                        }
                    }
                }

                if (isDuplicate) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Product Already Exists!',
                        html: `The product <b>"${productName}"</b> is already registered.<br>Please edit the item if you want to change it.`,
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });
                    return false;
                }

                // Kalkulasi Amount
                // ==========================================
                // MATEMATIKA KALKULASI AMOUNT (TAX DALAM PERSEN)
                // ==========================================
                // 1. Hitung Subtotal Kotor (Qty * Harga)
                let subTotal = quantity * unitPrice;

                // 2. Potong Diskon Nilai Tetap (jika diskon Anda inputnya nominal, misal: 50000)
                // Jika diskon Anda juga persen, gunakan: let totalDiscount = subTotal * (discount / 100);
                let totalDiscount = discount;

                // 3. Hitung DPP (Dasar Pengenaan Pajak) setelah diskon
                let setelahDiskon = subTotal - totalDiscount;

                // 4. Hitung Nilai Pajak Rupiah dari Persen (Misal input tax = 11, maka 11 / 100)
                let totalTax = setelahDiskon * (tax / 100);

                // 5. Grand Total Akhir
                let amount = setelahDiskon + totalTax;

                let itemData = {
                    'product_id': productId,
                    'data_produk': productName,
                    'quantity': quantity,
                    'unit_id': unitId,
                    'unit': unitName,
                    'unit_price': unitPrice,
                    'discount': discount,
                    'tax': tax,
                    'amount': amount,
                    'required_date': requiredDate,
                    'notes': notes
                };

                if (detailId === '') {
                    prDetailsData.push(itemData);
                } else {
                    prDetailsData[detailId] = itemData;
                }

                // Render ulang ke DataTable visual
                table.clear().rows.add(prDetailsData).draw();
                calculateGrandTotal();
                calculateTotalOrder();
                $('#modalPrDetail').modal('hide');
            });

            let saveAndNew = false;
            let activeBtn = null;

            $(document).on('click', '.card-footer button[type="submit"]', function() {
                saveAndNew = $(this).data('save-and-new');
                activeBtn = $(this);
            });

            $('#postForm').on('submit', function(e) {
                e.preventDefault();

                let form = this;
                let formData = new FormData(form);

                if (!activeBtn) {
                    activeBtn = $('#postForm').find('button[data-save-and-new="false"]');
                    saveAndNew = false;
                }

                if (typeof prDetailsData === 'undefined' || prDetailsData.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Empty Items',
                        text: 'Please add at least one item detail to the table before saving.',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary waves-effect waves-light'
                        },
                        buttonsStyling: false
                    });
                    return false;
                }

                formData.append('save_and_new', saveAndNew ? 1 : 0);
                formData.append('items_detail', JSON.stringify(prDetailsData));

                $.ajax({
                    url: $(form).attr('action'),
                    method: $(form).attr('method'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    beforeSend: function() {
                        activeBtn.html('<i class="fa fa-spin fa-spinner me-1"></i> Sending...');
                        $('.card-footer button').prop('disabled', true);
                    },
                    complete: function() {
                        let closeBtn = $('#postForm').find('button[data-save-and-new="false"]');
                        let newBtn = $('#postForm').find('button[data-save-and-new="true"]');
                        closeBtn.html('<i class="fa fa-upload me-1"></i> Save and Close');
                        newBtn.html(
                            '<i class="fa fa-plus-circle me-1"></i> Save and Create New');
                        $('.card-footer button').prop('disabled', false);
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Data Created Successfully',
                            text: response.message,
                            customClass: {
                                confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                            buttonsStyling: false
                        }).then(() => {
                            window.location.href = response.redirect;
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to Create Data',
                            text: xhr.responseJSON.message ||
                                'Please check your data again.',
                            customClass: {
                                confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                            buttonsStyling: false
                        });

                        let errors = xhr.responseJSON.errors || {};
                        $.each(errors, function(key, value) {
                            $(`#${key}Error`).text(value[0]);
                        });
                    }
                });
            });

            // 6. Event Handler: Ganti Customer Otomatis Isi Alamat
            $('#customer_id').on('change', function() {
                var alamatTerpilih = $(this).find(':selected').data('alamat');
                if (alamatTerpilih) {
                    $('#alamat').val(alamatTerpilih);
                } else {
                    $('#alamat').val('');
                }
            });


            // Jalankan fungsi setiap kali user mengetik sesuatu di Sub Total atau Discount
            $('#sub_total, #discount_all').on('input', function() {
                calculateTotalOrder();
            });

            function calculateGrandTotal() {
                let grandSubTotal = 0;

                // 1. Iterasi/looping semua data amount yang ada di array lokal
                $.each(prDetailsData, function(index, item) {
                    grandSubTotal += parseFloat(item.amount) || 0;
                });

                // 2. Masukkan hasil penjumlahan ke input field Sub Total
                $('#sub_total').val(Math.round(grandSubTotal));

                // 3. Hitung ulang diskon global secara otomatis saat isi tabel berubah
                let currentPercent = parseFloat($('#percent').val()) || 0;

                if (currentPercent > 0) {
                    // Jika awalnya diisi persen, hitung ulang nominal Rupiahnya berdasarkan Sub Total baru
                    let newDiscountNominal = grandSubTotal * (currentPercent / 100);
                    $('#discount_all').val(Math.round(newDiscountNominal));
                } else {
                    // Jika awalnya diisi nominal Rupiah, validasi agar tidak melebihi Sub Total baru
                    let currentNominal = parseFloat($('#discount_all').val()) || 0;
                    if (currentNominal > grandSubTotal) {
                        currentNominal = grandSubTotal;
                        $('#discount_all').val(Math.round(grandSubTotal));
                    }
                    // Set ulang nilai persen barunya
                    let newPercent = grandSubTotal > 0 ? (currentNominal / grandSubTotal) * 100 : 0;
                    $('#percent').val(newPercent % 1 === 0 ? newPercent : newPercent.toFixed(2));
                }

                // 4. Update hasil akhir ke Total Order
                calculateTotalOrder();
            }

            function calculateTotalOrder() {
                // Ambil nilai dari input, jika kosong atau bukan angka, default ke 0
                let subTotal = parseFloat($('#sub_total').val()) || 0;
                let discount = parseFloat($('#discount_all').val()) || 0;

                // Rumus: Total Order = Sub Total - Discount
                let totalOrder = subTotal - discount;

                // Cegah nilai total order menjadi minus jika discount lebih besar dari subtotal
                if (totalOrder < 0) {
                    totalOrder = 0;
                }

                // Masukkan hasil kalkulasi ke input Total Order
                $('#total_order').val(Math.round(totalOrder));
            }


            // ==========================================
            // KALKULASI DISKON GLOBAL (DUA ARAH)
            // ==========================================

            // A. Jika User Mengetik di Kolom PERSEN (%)
            $('#percent').on('input', function() {
                let subTotal = parseFloat($('#sub_total').val()) || 0;
                let percent = parseFloat($(this).val()) || 0;

                // Batasi agar persen tidak minus atau lebih dari 100
                if (percent < 0) {
                    percent = 0;
                    $(this).val(0);
                }
                if (percent > 100) {
                    percent = 100;
                    $(this).val(100);
                }

                // Hitung nominal Rupiahnya
                let discountNominal = subTotal * (percent / 100);

                // Masukkan hasil ke kolom Rupiah (discount_all)
                $('#discount_all').val(Math.round(discountNominal));

                // Hitung ulang Grand Total Akhir (Memanggil fungsi yang benar)
                calculateTotalOrder();
            });

            // B. Jika User Mengetik di Kolom NOMINAL (Rp)
            $('#discount_all').on('input', function() {
                let subTotal = parseFloat($('#sub_total').val()) || 0;
                let discountNominal = parseFloat($(this).val()) || 0;

                // Batasi agar nominal diskon tidak melebihi subtotal
                if (discountNominal < 0) {
                    discountNominal = 0;
                    $(this).val(0);
                }
                if (discountNominal > subTotal) {
                    discountNominal = subTotal;
                    $(this).val(subTotal);
                }

                // Hitung Persentasenya
                let percent = 0;
                if (subTotal > 0) {
                    percent = (discountNominal / subTotal) * 100;
                }

                // Masukkan hasil ke kolom persen (ambil 2 angka di belakang koma agar presisi)
                $('#percent').val(percent % 1 === 0 ? percent : percent.toFixed(2));

                // Hitung ulang Grand Total Akhir (Memanggil fungsi yang benar)
                calculateTotalOrder();
            });


        });
    </script>
@endpush
