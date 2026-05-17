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
                    <div class="divider-text">Purchase Requisition Detail</div>
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
        <div class="modal-dialog modal-xl">
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
                            <div class="col-3 mb-3">
                                <label class="form-label" for="unit_price">Unit Price</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>
                                    <input type="number" id="unit_price" name="unit_price" class="form-control"
                                        placeholder="0">
                                </div>

                                <span class="error text-danger" id="unit_priceError"></span>
                            </div>
                            <div class="col-3 mb-3">
                                <label class="form-label" for="discount">Discount</label>
                                <input type="number" id="discount" name="discount" class="form-control"
                                    placeholder="0">
                                <span class="error text-danger" id="discountError"></span>
                            </div>
                            <div class="col-3 mb-3">
                                <label class="form-label" for="tax">Tax</label>
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
                    <h5 class="modal-title" id="modalTitle">Requisition</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
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
        $(document).ready(function() {
            $('.select2-modal').each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: $this.attr('data-placeholder'),
                    width: '100%',
                    dropdownParent: $(
                        '#modalPrDetail'
                    ) // <--- WAJIB: Sesuaikan dengan ID Modal Bootstrap kamu
                });
            });
            // Event ketika pilihan customer berubah
            $('#customer_id').on('change', function() {
                // Ambil data-alamat dari option yang sedang aktif/dipilih
                var alamatTerpilih = $(this).find(':selected').data('alamat');

                // Masukkan nilainya ke dalam textarea alamat
                if (alamatTerpilih) {
                    $('#alamat').val(alamatTerpilih);
                } else {
                    $('#alamat').val(''); // Kosongkan jika pilih opsi kosong
                }
            });

            $(document).ready(function() {
                $('#showModalpr').click(function() {
                    $('#modalRequisitionDetail').modal('show');
                });
                let prDetailsData = [];
                let table = new DataTable('#table', {
                    processing: true,
                    serverSide: false, // Diubah ke false agar bisa menambah data sementara secara lokal
                    responsive: true,
                    select: true,
                    searching: false,
                    lengthMenu: [
                        [10, 25, 50, -1],
                        [10, 25, 50, 'All']
                    ],
                    // Sumber data dialihkan menggunakan array JavaScript lokal kita
                    data: prDetailsData,
                    columns: [{
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row, meta) {
                                return meta.row +
                                    1; // Nomor urut otomatis (DT_RowIndex lokal)
                            }
                        },
                        {
                            data: 'data_produk',
                        },
                        {
                            data: 'quantity',
                        },
                        {
                            data: 'unit',
                        },
                        {
                            data: 'unit_price',
                        },
                        {
                            data: 'discount',
                        },
                        {
                            data: 'unit_price',
                        },
                        {
                            data: 'tax',
                        },
                        {
                            data: 'amount',
                        }
                    ],
                    layout: {
                        topStart: {
                            buttons: [{
                                    text: '<i class="ti ti-plus me-1"></i> New',
                                    className: 'btn btn-primary btn-sm me-2',
                                    action: function(e, dt, node, config) {
                                        // 1. Ambil nilai customer_id yang sedang terpilih
                                        var customerId = $('#customer_id').val();

                                        // 2. Cek apakah selectbox customer kosong atau belum dipilih
                                        if (!customerId || customerId === '') {
                                            // Tampilkan SweetAlert peringatan
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

                                            // Hentikan fungsi agar modal TIDAK terbuka
                                            return false;
                                        }

                                        // 3. Jika customer sudah dipilih, kode di bawah ini akan berjalan seperti biasa
                                        $('#formPrDetail')[0].reset();
                                        $('#detail_id').val('');

                                        // Reset komponen select2 jika Anda menggunakannya
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

                                        // 1. Ambil nomor index row tabel lokal untuk penanda update array
                                        $('#detail_id').val(rowIndex);
                                        $('#quantity').val(data.quantity);

                                        // 2. Simpan ID Unit yang sedang aktif ke data-attribute jQuery secara sementara
                                        // Ini agar ketika AJAX Produk selesai memuat data, nilai unit_id langsung terkunci ke nilai ini
                                        $('#unit_id').data('pending-val', data.unit_id);

                                        // 3. Set value produk (ini otomatis memicu Event Listener Ajax di atas)
                                        $('#product_id').val(data.product_id).trigger(
                                            'change');

                                        // 4. Munculkan Modal
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
                                                // Hapus data dari array JavaScript berdasarkan indexnya
                                                prDetailsData.splice(rowIndex, 1);

                                                // Refresh visual tabel lokal
                                                dt.clear().rows.add(prDetailsData)
                                                    .draw();

                                                toastr.success(
                                                    'Deleted Data Successfully',
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
                                        // Kosongkan seluruh data sementara di tabel
                                        prDetailsData = [];
                                        dt.clear().draw();
                                    }
                                }
                            ]
                        }
                    }
                });
            });
            $(document).on('change', '#product_id', function() {
                let productId = $(this).val();
                let unitSelect = $('#unit_id');
                let priceInput = $('#unit_price'); // Selector untuk elemen input Unit Price

                // Jika produk kosong, bersihkan selectbox unit dan input harga
                if (!productId) {
                    unitSelect.empty().append('<option></option>').trigger('change');
                    priceInput.val('');
                    return;
                }

                // ==========================================
                // 1. AJAX UNTUK MENGAMBIL LIST UNIT
                // ==========================================
                $.ajax({
                    url: `/purchase-order/get-units-by-product/${productId}`,
                    type: 'GET',
                    dataType: 'json',
                    beforeSend: function() {
                        unitSelect.html('<option>Loading units...</option>').prop('disabled',
                            true);
                    },
                    success: function(response) {
                        unitSelect.empty().append('<option></option>').prop('disabled', false);

                        // Response dari controller berupa array berisi objek {id: ..., name: ...}
                        if (response && response.length > 0) {
                            $.each(response, function(key, item) {
                                // Masukkan id dan name (detail nama satuan dari relation unit)
                                unitSelect.append(
                                    `<option value="${item.id}">${item.name}</option>`
                                );
                            });
                        } else {
                            // Antisipasi jika response kosong dari backend
                            unitSelect.append('<option value="">No unit available</option>');
                        }

                        unitSelect.trigger('change');

                        // [PROSES EDIT]: Jika ada unit lama yang menggantung di memori, pasang langsung nilainya di sini
                        let pendingUnitId = unitSelect.data('pending-val');
                        if (pendingUnitId) {
                            unitSelect.val(pendingUnitId).trigger('change');
                            unitSelect.removeData(
                                'pending-val'); // Hapus cache temporary setelah digunakan
                        }
                    },
                    error: function() {
                        console.error('Gagal memuat list unit dari Controller.');
                        unitSelect.empty().append('<option></option>').prop('disabled', false)
                            .trigger('change');
                    }
                });

                // ==========================================
                // 2. AJAX UNTUK MENGAMBIL UNIT PRICE
                // ==========================================
                $.ajax({
                    url: `/purchase-order/get-product-price/${productId}`, // Sesuai dengan route prefix purchase-order
                    type: 'GET',
                    dataType: 'json',
                    beforeSend: function() {
                        priceInput.attr('placeholder', 'Loading...');
                    },
                    success: function(response) {
                        if (response.success) {
                            // Masukkan nilai price (dari database data_barang) ke elemen input harga
                            priceInput.val(response.price);
                        } else {
                            priceInput.val(0);
                        }
                    },
                    error: function(xhr) {
                        console.error("Gagal mengambil data harga produk:", xhr);
                        priceInput.val(0);
                        priceInput.attr('placeholder', '0');
                    }
                });
            });

        });
    </script>
@endpush
