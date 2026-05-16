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



                </div>
            </div>

        </div>
        <div class="card-body table-responsive p-3">
            <form action="{{ route('permintaan-pembelian.store') }}" method="POST" id="postForm"
                enctype="multipart/form-data">
                @csrf
                <div class="row mb-5">
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Request Number<small class="text-danger">*</small> </label>
                                <input type="text" name="code" id="code" class="form-control"
                                    value="{{ $idNumber }}">
                                <span class="error text-danger" id="codeError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Request Date<small class="text-danger">*</small> </label>
                                <input type="date" name="date" id="date" class="form-control" value="">
                                <span class="error text-danger" id="dateError"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">

                            <label class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control"></textarea>
                            <span class="error text-danger" id="descriptionError"></span>
                        </div>
                    </div>

                </div>
                <div class="divider divider-dashed">
                    <div class="divider-text">Purchase Requisition Detail</div>
                </div>
                {{-- <button class="btn btn-sm btn-primary"><i class="ti ti-plus me-1"></i>Add</button>
                <button class="btn btn-sm btn-primary"><i class="ti ti-edit me-1"></i>Edit</button>
                <button class="btn btn-sm btn-primary"><i class="ti ti-refresh me-1"></i>Refresh</button> --}}
                <div class="row mt-3">
                    <table class="table display responsive nowrap" id="table">
                        <thead class="border-top" style="background-color: #AEDEFC; ">
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Unit</th>
                            </tr>
                        </thead>

                    </table>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <button type="submit" id="savedata" class="btn btn-primary" data-save-and-new="false">
                        <i class="fa fa-upload me-1"></i> Save and Close
                    </button>

                    <button type="submit" id="savedata" class="btn btn-success" data-save-and-new="true">
                        <i class="fa fa-plus-circle me-1"></i> Save and Create New
                    </button>
                    <a href="{{ route('permintaan-pembelian.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="modalPrDetail">
        <div class="modal-dialog modal-dialog-centered">
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


                            <div class="col-6 mb-3">
                                <label class="form-label" for="quantity">Quantity</label>
                                <input type="number" id="quantity" name="quantity" class="form-control" placeholder="0">
                                <span class="error text-danger" id="quantityError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label" for="unit_id">Unit</label>
                                <select name="unit_id" id="unit_id" class="form-select select2-modal "
                                    data-placeholder="Select Unit">
                                    <option></option>
                                </select>
                                <span class="error text-danger" id="unit_idError"></span>
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

            $('#product_id').on('change', function() {
                let productId = $(this).val();
                let unitDropdown = $('#unit_id');

                // Kosongkan dropdown unit terlebih dahulu dan set ke keadaan loading
                unitDropdown.empty().append('<option></option>').trigger('change');

                if (productId) {
                    $.ajax({
                        url: `/get-units-by-product/${productId}`,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            // Isi kembali dropdown unit dengan data baru dari server
                            $.each(data, function(key, value) {
                                unitDropdown.append(new Option(value.name, value.id,
                                    false, false));
                            });

                            // Refresh tampilan select2 agar memperbarui opsinya
                            unitDropdown.trigger('change');
                        },
                        error: function() {
                            toastr.error('Failed to fetch units data.');
                        }
                    });
                }
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
                            return meta.row + 1; // Nomor urut otomatis (DT_RowIndex lokal)
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
                    }
                ],
                layout: {
                    topStart: {
                        buttons: [{
                                text: '<i class="ti ti-plus me-1"></i> New',
                                className: 'btn btn-primary btn-sm',
                                action: function(e, dt, node, config) {
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
                                className: 'btn btn-warning btn-sm',
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
                                    $('#product_id').val(data.product_id).trigger('change');

                                    // 4. Munculkan Modal
                                    $('#modalTitle').text('Edit entry');
                                    $('#btnSubmitModal').text('Update');
                                    $('#modalPrDetail').modal('show');
                                }
                            },
                            {
                                text: '<i class="ti ti-trash me-1"></i> Delete',
                                className: 'btn btn-danger btn-sm',
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
                                    // Kosongkan seluruh data sementara di tabel
                                    prDetailsData = [];
                                    dt.clear().draw();
                                }
                            }
                        ]
                    }
                }
            });

            // 3. Event Handler saat Form di dalam Modal di-Submit
            $('#formPrDetail').on('submit', function(e) {
                e.preventDefault();

                // Mengambil value dan text dari form modal
                let productId = $('#product_id').val();
                let productName = $('#product_id option:selected').text();
                let quantity = $('#quantity').val();
                let unitId = $('#unit_id').val();
                let unitName = $('#unit_id option:selected').text();
                let detailId = $('#detail_id').val(); // Berisi index array jika edit

                // 1. Validasi field kosong
                if (!productId || !quantity || !unitId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please fill all fields!',
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });
                    return false;
                }

                // 2. VALIDASI DUPLIKASI PRODUK BERDASARKAN ARRAY prDetailsData
                let isDuplicate = false;

                if (prDetailsData && prDetailsData.length > 0) {
                    for (let i = 0; i < prDetailsData.length; i++) {
                        // Cek apakah ada product_id yang sama di dalam array
                        if (prDetailsData[i].product_id == productId) {

                            // Kondisi A: Jika aksi TAMBAH BARU (detailId kosong) -> Langsung duplikat
                            if (detailId === '') {
                                isDuplicate = true;
                                break;
                            }
                            // Kondisi B: Jika aksi EDIT -> Duplikat hanya jika produk ditemukan di INDEX YANG BERBEDA
                            else if (detailId !== '' && i != detailId) {
                                isDuplicate = true;
                                break;
                            }
                        }
                    }
                }

                // Jika terdeteksi duplikat, batalkan submit dan munculkan SweetAlert
                if (isDuplicate) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Product Already Exists!',
                        html: `The product <b>"${productName}"</b> is already registered in the details list.<br>Please edit the item if you want to change the quantity or unit.`,
                        confirmButtonColor: '#3085d6',
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });
                    return false;
                }

                // 3. Susun objek data sesuai struktur kolom tabel jika lolos validasi
                let itemData = {
                    'product_id': productId,
                    'data_produk': productName,
                    'quantity': quantity,
                    'unit_id': unitId,
                    'unit': unitName
                };

                if (detailId === '') {
                    // AKSI NEW: Tambah baru ke dalam array
                    prDetailsData.push(itemData);
                } else {
                    // AKSI EDIT: Update data di array berdasarkan index-nya
                    prDetailsData[detailId] = itemData;
                }

                // Masukkan data array terbaru ke DataTables dan gambar ulang (render) grafisnya
                table.clear().rows.add(prDetailsData).draw();

                // Sembunyikan modal
                $('#modalPrDetail').modal('hide');
            });

            // Variabel helper untuk mendeteksi tombol aktif
            let saveAndNew = false;
            let activeBtn = null;

            // 1. Tangkap aksi klik pada tombol footer terlebih dahulu untuk menentukan mode simpan
            $(document).on('click', '.card-footer button[type="submit"]', function() {
                // Membaca attribute data-save-and-new="true/false" dari HTML tombol yang diklik
                saveAndNew = $(this).data('save-and-new');
                activeBtn = $(this);
            });

            // 2. Event Handler Submit pada Form Utama
            $('#postForm').on('submit', function(e) {
                e.preventDefault();

                let form = this;
                let formData = new FormData(form);

                // Jika user langsung tekan enter tanpa klik tombol, pasang default ke Save and Close
                if (!activeBtn) {
                    activeBtn = $('#postForm').find('button[data-save-and-new="false"]');
                    saveAndNew = false;
                }

                // --- VALIDASI: Pastikan user sudah mengisi minimal 1 item detail di tabel lokal ---
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
                    return false; // ❌ STOP SUBMIT
                }

                // Append flag status save_and_new ke form data (1 jika true, 0 jika false)
                formData.append('save_and_new', saveAndNew ? 1 : 0);

                // Append data array detail produk dari memori lokal dengan mengubahnya menjadi JSON String
                formData.append('items_detail', JSON.stringify(prDetailsData));

                $.ajax({
                    url: $(form).attr('action'),
                    method: $(form).attr('method'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    beforeSend: function() {
                        // Berikan efek loading teks spinner pada tombol yang sedang diklik aktif
                        activeBtn.html('<i class="fa fa-spin fa-spinner me-1"></i> Sending...');
                        $('.card-footer button').prop('disabled',
                            true); // Kunci semua tombol biar tidak double click
                    },
                    complete: function() {
                        // Kembalikan teks asli tombol setelah proses AJAX selesai
                        let closeBtn = $('#postForm').find('button[data-save-and-new="false"]');
                        let newBtn = $('#postForm').find('button[data-save-and-new="true"]');

                        closeBtn.html('<i class="fa fa-upload me-1"></i> Save and Close');
                        newBtn.html(
                            '<i class="fa fa-plus-circle me-1"></i> Save and Create New');

                        $('.card-footer button').prop('disabled',
                            false); // Buka kembali kunci tombol
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Data Created Successfully',
                            text: response.message,
                            showClass: {
                                popup: 'animate__animated animate__bounceIn'
                            },
                            customClass: {
                                confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                            buttonsStyling: false
                        }).then(() => {
                            // Redirect halaman sesuai instruksi url balikan dari Controller Laravel Anda
                            window.location.href = response.redirect;
                        });
                    },
                    error: function(xhr) {
                        // Bersihkan pesan error validasi lama
                        if (typeof resetValidation === "function") {
                            resetValidation();
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to Create Data',
                            text: xhr.responseJSON.message ||
                                'Please check your data again.',
                            showClass: {
                                popup: 'animate__animated animate__bounceIn'
                            },
                            customClass: {
                                confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                            buttonsStyling: false
                        });

                        // Tampilkan pesan error validasi spesifik di bawah input field masing-masing
                        let errors = xhr.responseJSON.errors || {};
                        $.each(errors, function(key, value) {
                            if (typeof displayFieldError === "function") {
                                displayFieldError(key, value[0]);
                            }
                        });
                    }
                });
            });

            $(document).on('change', '#product_id', function() {
                let productId = $(this).val();
                let unitSelect = $('#unit_id');

                // Jika produk kosong, bersihkan selectbox unit
                if (!productId) {
                    unitSelect.empty().append('<option></option>').trigger('change');
                    return;
                }

                // Panggil Controller bawaan Anda yang tidak boleh diubah tadi
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

                        // Response dari controller Anda berupa array berisi objek {id: ..., name: ...}
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
            });



        });
    </script>
@endpush
