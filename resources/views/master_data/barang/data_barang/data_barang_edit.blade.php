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
        <form id="postForm" name="postForm" method="POST" action="{{ route('data-barang.update', $detail->id) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="items_detail" id="items_detail">
            <input type="hidden" name="save_and_new" id="save_and_new" value="0">
            <div class="card-body table-responsive p-3">
                <div class="col-xl-12">
                    <div class="nav-align-top mb-4">
                        <ul class="nav nav-pills mb-3" role="tablist">
                            <li class="nav-item">
                                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-pills-top-general" aria-controls="navs-pills-top-general"
                                    aria-selected="true">
                                    General Information
                                </button>
                            </li>

                            <li class="nav-item" id="stockTab">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-pills-top-term" aria-controls="navs-pills-top-term"
                                    aria-selected="false">
                                    Stock Information
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-pills-top-tax" aria-controls="navs-pills-top-tax"
                                    aria-selected="false">
                                    Other Information
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="navs-pills-top-general" role="tabpanel">
                                <div class="row">
                                    @include('master_data.barang.data_barang.part.edit_data_umum')
                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-top-term" role="tabpanel">
                                <div class="row">
                                    <div class="row mt-3">
                                        @include('master_data.barang.data_barang.part.stock_table')
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-top-tax" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-12 mb-5">
                                        <label class="form-label">Description</label>
                                        <textarea name="keterangan" id="keterangan" cols="30" rows="3" class="form-control">{{ old('keterangan', $detail->keterangan ?? '') }}</textarea>
                                        <span class="error text-danger" id="keteranganError"></span>
                                    </div>
                                </div>
                                <div class="divider my-7 ">
                                    <div class="divider-text">Varian.</div>
                                </div>

                                <div class="space-y-6">
                                    <div id="variant-wrapper" class="space-y-6">

                                        @foreach ($detail->variants as $vIndex => $variant)
                                            <div class="variant-card border border-gray-300 rounded-xl p-5 bg-white shadow-sm relative mb-4"
                                                data-variant-index="{{ $vIndex }}">

                                                <div class="col-12 mb-3">
                                                    <label class="form-label">Nama Varian <small
                                                            class="text-danger">*</small></label>
                                                    <div class="input-group input-group-merge">
                                                        <span class="input-group-text"><i class="ti ti-barcode"></i></span>
                                                        <input type="text" name="variants[{{ $vIndex }}][name]"
                                                            class="form-control variant-name"
                                                            placeholder="Contoh: Merah Ukuran L"
                                                            value="{{ $variant->variant_name }}">
                                                    </div>
                                                </div>

                                                <div class="col-12 mb-2">
                                                    <label class="form-label font-weight-bold">Spesifikasi / Dimensi Kustom
                                                        <small class="text-danger">*</small></label>
                                                </div>

                                                <div class="spec-container space-y-3 mb-3">
                                                    @php $sIndex = 0; @endphp
                                                    @if (!empty($variant->specifications) && is_array($variant->specifications))
                                                        @foreach ($variant->specifications as $label => $value)
                                                            <div class="row align-items-center spec-row mb-2">
                                                                <div class="col-md-5">
                                                                    <div class="input-group input-group-merge">
                                                                        <span class="input-group-text"><i
                                                                                class="ti ti-tag"></i></span>
                                                                        <input type="text"
                                                                            name="variants[{{ $vIndex }}][specs][{{ $sIndex }}][label]"
                                                                            class="form-control spec-label"
                                                                            placeholder="Nama Label (cth: Panjang)"
                                                                            value="{{ $label }}">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="input-group input-group-merge">
                                                                        <span class="input-group-text"><i
                                                                                class="ti ti-edit"></i></span>
                                                                        <input type="text"
                                                                            name="variants[{{ $vIndex }}][specs][{{ $sIndex }}][value]"
                                                                            class="form-control spec-value"
                                                                            placeholder="Nilai (cth: 120 cm atau 500 gr)"
                                                                            value="{{ $value }}">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-1 text-end">
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-danger btn-remove-spec">&times;</button>
                                                                </div>
                                                            </div>
                                                            @php $sIndex++; @endphp
                                                        @endforeach
                                                    @endif
                                                </div>

                                                <div class="d-flex justify-content-between align-items-center mt-3">
                                                    <button type="button" class="btn btn-success btn-sm btn-add-spec">
                                                        + Tambah Atribut/Dimensi
                                                    </button>
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm btn-remove-variant">
                                                        Hapus Varian
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach

                                    </div>

                                    <div class="flex justify-start mt-3">
                                        <button type="button" id="btn-add-variant"
                                            class="btn btn-sm btn-info text-white">
                                            + Tambah Varian Baru
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end gap-2">
                <button type="submit" id="savedata" class="btn btn-primary" data-save-and-new="false">
                    <i class="fa fa-upload me-1"></i> Update and Close
                </button>
                <a href="{{ route('data-barang.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <div class="modal fade" id="modalPrDetail">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Create new entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formPrDetailStock">
                    @csrf
                    <input type="hidden" name="id" id="detail_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label" for="warehouse_id">Warehouse</label>
                                <select name="warehouse_id" id="warehouse_id" class="form-select select2-warehouse "
                                    data-placeholder="Select Warehouse">
                                    <option></option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->nama_gudang }}</option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="warehouse_idError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label" for="date_stock">Date</label>
                                <input type="text" id="date_stock" name="date_stock" class="form-control">
                                <span class="error text-danger" id="date_stockError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label" for="quantity">Quantity</label>
                                <input type="number" id="quantity" name="quantity" class="form-control"
                                    placeholder="0" min="0">
                                <span class="error text-danger" id="quantityError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label" for="unit_id_modals">Unit</label>
                                <select name="unit_id_modals" id="unit_id_modals" class="form-select select2-unit "
                                    data-placeholder="Select Unit">
                                    <option></option>
                                    @foreach ($sub_unit as $unit_row)
                                        <option value="{{ $unit_row->id }}">{{ $unit_row->detail }}</option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="unit_id_modalsError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label" for="unit_price">Unit Price</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"> {{ $mataUangDefault ?? 'Rp' }}</span>
                                    <input type="number" id="unit_price" name="unit_price" class="form-control"
                                        placeholder="0" min="0">
                                </div>
                                <span class="error text-danger" id="unit_priceError"></span>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label" for="discount">Total Price</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"> {{ $mataUangDefault ?? 'Rp' }}</span>
                                    <input type="number" id="total_price" name="total_price" class="form-control"
                                        placeholder="0" min="0" readonly>
                                </div>
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
    <style>
        .select2-container .select2-selection--single {
            height: 38px !important;
            /* samain dengan bootstrap */
            display: flex;
            align-items: center;
        }

        .select2-selection__rendered {
            line-height: normal !important;
        }

        .select2-selection__arrow {
            height: 100% !important;
        }

        .input-group .select2-container {
            flex: 1 1 auto;
            width: 1% !important;
        }

        .input-group .select2-selection {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/2.0.3/css/select.bootstrap5.css">
@endpush
@push('scripts')
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.js"></script>

    <script src="https://cdn.datatables.net/select/3.1.3/js/dataTables.select.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.3/js/select.bootstrap5.js"></script>
    {{-- <script>
        const radioSupply = document.getElementById('radioSupply');
        const radioNonSupply = document.getElementById('radioNonSupply');
        const stockTab = document.getElementById('stockTab');
        const barcodeField = document.getElementById('barcodeField');

        function toggleStockTab() {
            if (radioNonSupply.checked) {
                // sembunyikan
                stockTab.style.display = 'none';
                barcodeField.style.display = 'none';

                // optional: clear value barcode biar aman
                document.getElementById('barcode').value = '';

                // pindah tab biar ga blank
                const firstTab = document.querySelector('.nav-link');
                if (firstTab) {
                    new bootstrap.Tab(firstTab).show();
                }

            } else {
                // tampilkan
                stockTab.style.display = 'block';
                barcodeField.style.display = 'block';
            }
        }

        // run awal
        toggleStockTab();

        // listener
        radioSupply.addEventListener('change', toggleStockTab);
        radioNonSupply.addEventListener('change', toggleStockTab);
    </script> --}}
    <script>
        let prDetailsData = [
            @if (isset($detail) && $detail->stockHistories)
                @foreach ($detail->stockHistories as $stock)
                    {
                        // Ambil ID Gudang langsung dari property atau dari relasi
                        'date': '{{ $stock->date }}',
                        'warehouse_id': '{{ $stock->warehouse_id }}',

                        // Mengambil nama gudang dari relasi warehouseID yang ada di model DataBarangStok
                        'warehouse_name': '{{ $stock->warehouseID ? $stock->warehouseID->nama_gudang : 'Gudang Tidak Ditemukan' }}',

                        'date': '{{ $stock->date }}',
                        'quantity': '{{ $stock->quantity }}',

                        'stok_unit_id': '{{ $stock->stok_unit_id }}',

                        // Mengambil nama satuan/unit (misal: 'Pcs', 'Box') menggunakan relasi unitID ke BasicCodeDetail
                        'stok_unit_name': '{{ $stock->unitID ? $stock->unitID->detail : 'Unit Tidak Ditemukan' }}',

                        'unit_price': '{{ $stock->price }}',
                    }
                    {{ !$loop->last ? ',' : '' }}
                @endforeach
            @endif
        ];
        const originalPrDetailsData = JSON.parse(JSON.stringify(prDetailsData));
        $(document).ready(function() {
            const originalConversionHTML = $('#conversion-container').clone(true, true);
            $(document).on('click', '.btn-remove-conversion', function(e) {
                e.preventDefault();

                // Hitung berapa jumlah baris konversi yang ada saat ini
                const totalItems = $('.conversion-item').length;

                if (totalItems > 1) {
                    // Jika baris lebih dari 1, hapus baris tempat tombol sampah ini berada
                    $(this).closest('.conversion-item').remove();

                    // Opsional: Jalankan fungsi untuk merapikan ulang nomor urut (Unit #1, Unit #2, dst)
                    reorderConversionNumbers();
                } else {
                    // Jika sisa 1 baris terakhir, jangan dihapus, melainkan kosongkan saja isinya
                    const $lastItem = $(this).closest('.conversion-item');
                    $lastItem.find('.qty').val('');
                    $lastItem.find('.to_unit').val('').trigger('change');
                }
            });

            // 2. Logika ketika tombol reset diklik
            $(document).on('click', '#btn-reset-conversion', function(e) {
                e.preventDefault();

                const $form = $(this).closest('form');

                if ($form.length > 0) {
                    // Buang container lama, ganti dengan struktur asli hasil kloning
                    $('#conversion-container').remove();
                    $('#conversion-wrapper').append(originalConversionHTML.clone(true, true));

                    // Kembalikan semua nilai input (Dimensi, Berat, dll) ke isi asli database secara instan
                    $form[0].reset();

                    // Pemicu refresh visual jika Anda menggunakan library Select2
                    if ($.fn.select2) {
                        $form.find('.to_unit').trigger('change');
                    }
                }
            });

            // Fungsi pembantu untuk mengurutkan kembali nomor Unit #1, Unit #2 setelah ada yang dihapus
            function reorderConversionNumbers() {
                $('.conversion-item').each(function(index) {
                    $(this).find('.conversion-number').text('Unit #' + (index + 1));
                });
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            // 1. Aksi Tambah Baris Konversi
            $('#btn-add-conversion').on('click', function() {
                // Hitung baris yang sudah ada untuk menentukan index baru
                let index = $('.conversion-item').length;

                // Buat element html baru secara dinamis
                let html = `
                <div class="conversion-item border p-3 mb-2 rounded position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-label-secondary conversion-number">Unit #${index + 1}</span>
                        <button type="button" class="btn btn-sm btn-text-danger btn-remove-conversion p-1">
                            <i class="ti ti-trash fs-5"></i>
                        </button>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <input type="text" class="form-control from_unit_text" disabled value="${$('.from_unit_text').first().val() || ''}">
                            <input type="hidden" name="conversion[${index}][from_unit]" class="from_unit_id" value="${$('.from_unit_id').first().val() || ''}">
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="fw-bold mt-2">=</div>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="conversion[${index}][qty]" class="form-control qty" placeholder="Qty" ${$('.qty').first().is(':disabled') ? 'disabled' : ''}>
                        </div>
                        <div class="col-md-3">
                            <select name="conversion[${index}][to_unit]" class="form-select to_unit" ${$('.to_unit').first().is(':disabled') ? 'disabled' : ''}>
                                <option value="">Select</option>
                                ${$('.to_unit').first().html().split('</option>').slice(1).join('</option>')}
                            </select>
                        </div>
                    </div>
                </div>`;

                // Masukkan ke dalam container
                $('#conversion-container').append(html);
            });

            // 2. Aksi Hapus Baris Konversi (Menggunakan event delegation)
            $('#conversion-container').on('click', '.btn-remove-conversion', function() {
                if ($('.conversion-item').length > 1) {
                    $(this).closest('.conversion-item').remove();
                    reorderConversionIndices();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'There must be at least 1 unit conversion line.',
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });
                }
            });

            // 3. Fungsi Reset Indexing agar data request berurutan (0, 1, 2, dst..)
            function reorderConversionIndices() {
                $('.conversion-item').each(function(index) {
                    // Update Text Badge Nomor urut
                    $(this).find('.conversion-number').text(`Unit #${index + 1}`);

                    // Update Atribut Name Input HTML
                    $(this).find('.from_unit_id').attr('name', `conversion[${index}][from_unit]`);
                    $(this).find('.qty').attr('name', `conversion[${index}][qty]`);
                    $(this).find('.to_unit').attr('name', `conversion[${index}][to_unit]`);
                });
            }
        });
    </script>
    <script>
        let saveAndNew = false;

        $('#savedata').click(function(e) {
            saveAndNew = false;
        });

        $('#savedatamore').click(function(e) {
            saveAndNew = true;
        });

        $('#postForm').on('submit', function(e) {
            e.preventDefault();
            let form = this;
            let btn = saveAndNew ? $('#savedatamore') : $('#savedata');

            // Pindahkan inisialisasi FormData ke bawah agar menangkap data paling update
            let isValid = true;
            let errorMessage = '';

            // 1. LANGSUNG UBAH BUTTON JADI SPINNING DULUAN
            btn.html('<i class="fa fa-spin fa-spinner me-1"></i> Checking data...');
            btn.prop('disabled', true);

            // 2. BERI JEDA SEDIKIT (misal: 600ms) AGAR ANIMASI SPINNING KELIHATAN
            setTimeout(function() {

                // Ambil data terbaru dari instansiasi DataTables Anda
                // Pastikan variabel 'prDetailsData' atau selector tabel di bawah ini sudah sesuai nama tabel Anda
                let currentTableData = [];
                if ($.fn.DataTable.isDataTable('#tableStok')) {
                    currentTableData = $('#tableStok').DataTable().rows().data().toArray();
                } else if (typeof prDetailsData !== 'undefined') {
                    currentTableData = prDetailsData;
                }

                // Simpan string JSON ke input element fisik
                $('#items_detail').val(JSON.stringify(currentTableData));

                $('.conversion-item').each(function(index) {
                    let qtyRaw = $(this).find('.qty').val() ? $(this).find('.qty').val().trim() :
                        '';
                    let qty = parseFloat(qtyRaw) || 0;
                    let toUnit = $(this).find('.to_unit').val();
                    let fromUnit = $(this).find('.from_unit_id').val();

                    let hasQty = qtyRaw !== '' && qtyRaw !== '0';
                    let hasUnit = toUnit !== '' && toUnit !== null;

                    // 1. JIKA KEDUANYA KOSONG: LANJUT / ABAIKAN
                    if (!hasQty && !hasUnit) {
                        return true;
                    }

                    // 2. QTY DIISI TAPI UNIT KOSONG
                    if (hasQty && !hasUnit) {
                        isValid = false;
                        errorMessage = 'Please select a destination unit for Unit #' + (index + 1) +
                            '.';
                        return false;
                    }

                    // 3. UNIT DIPILIH TAPI QTY KOSONG ATAU 0
                    if (!hasQty && hasUnit) {
                        isValid = false;
                        errorMessage = 'Please enter a valid quantity (greater than 0) for Unit #' +
                            (index + 1) + '.';
                        return false;
                    }

                    // 4. UNIT SAMA DENGAN UNIT UTAMA
                    if (hasUnit && fromUnit && toUnit == fromUnit) {
                        isValid = false;
                        errorMessage = 'The destination unit for Unit #' + (index + 1) +
                            ' must be different from the source unit.';
                        return false;
                    }
                });

                // 3. JIKA VALIDASI JAVASCRIPT GAGAL
                if (!isValid) {
                    if (saveAndNew) {
                        btn.html('<i class="fa fa-plus-circle me-1"></i> Save and Create New');
                    } else {
                        btn.html('<i class="fa fa-upload me-1"></i> Save and Close');
                    }
                    btn.prop('disabled', false);

                    Swal.fire({
                        icon: 'warning',
                        title: 'Invalid Input',
                        text: errorMessage,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary waves-effect waves-light'
                        },
                        buttonsStyling: false
                    });
                    return;
                }

                // =========================================================================
                // 🛠️ BENTUK FORMDATA DI SINI (Tepat Sebelum Kirim, Setelah Input Terisi)
                // =========================================================================
                let formData = new FormData(form);
                formData.append('save_and_new', saveAndNew ? 1 : 0);

                // Solusi cadangan: Paksa tumpuk isian data agar diserialisasikan dengan sempurna ke controller
                formData.set('items_detail', JSON.stringify(currentTableData));

                // 4. JIKA LOLOS VALIDASI, LANJUTKAN KE AJAX
                $.ajax({
                    url: $(form).attr('action'),
                    method: $(form).attr('method'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    beforeSend: function() {
                        btn.html('<i class="fa fa-spin fa-spinner me-1"></i> Sending...');
                    },
                    complete: function() {
                        if (saveAndNew) {
                            btn.html(
                                '<i class="fa fa-plus-circle me-1"></i> Save and Create New'
                            );
                        } else {
                            btn.html('<i class="fa fa-upload me-1"></i> Save and Close');
                        }
                        btn.prop('disabled', false);
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated Data Successfully',
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
                        resetValidation();

                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to Save Data',
                            text: 'Please check your data again.',
                            customClass: {
                                confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                            buttonsStyling: false
                        });

                        let errors = xhr.responseJSON.errors || {};
                        $.each(errors, function(key, value) {
                            displayFieldError(key, value[0]);
                        });
                    }
                });

            }, 600);
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ambil semua kartu varian yang sudah dirender oleh Blade (jika ada)
            const existingCards = document.querySelectorAll('.variant-card');

            // Counter diatur dinamis: jika ada data pakai jumlah yang ada, jika kosong mulai dari 0
            let variantCount = existingCards.length;

            // Fungsi untuk mengaktifkan event listener di setiap variant-card (baik lama maupun hasil clone)
            function bindVariantEvents(card) {
                if (!card) return; // Pengaman jika card yang dimasukkan null

                const specContainer = card.querySelector('.spec-container');
                const btnAddSpec = card.querySelector('.btn-add-spec');
                const vIndex = card.getAttribute('data-variant-index');

                // Hitung jumlah spesifikasi awal secara dinamis
                let specCount = specContainer.querySelectorAll('.spec-row').length;

                // Event Klik Tombol "+ Tambah Atribut/Dimensi"
                btnAddSpec.onclick = function() {
                    const firstRow = specContainer.querySelector('.spec-row');
                    let newRow;

                    if (firstRow) {
                        // Jika sudah ada baris spesifikasi, kloning dari yang pertama
                        newRow = firstRow.cloneNode(true);
                    } else {
                        // Jika kosong (clear area), buat struktur baris baru dari nol
                        newRow = document.createElement('div');
                        newRow.className = 'row align-items-center spec-row mb-2';
                        newRow.innerHTML = `
                    <div class="col-md-5">
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="ti ti-tag"></i></span>
                            <input type="text" class="form-control spec-label" placeholder="Nama Label (cth: Panjang)">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="ti ti-edit"></i></span>
                            <input type="text" class="form-control spec-value" placeholder="Nilai (cth: 120 cm atau 500 gr)">
                        </div>
                    </div>
                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-sm btn-danger btn-remove-spec">&times;</button>
                    </div>
                `;
                    }

                    // Ambil element input di baris baru untuk diatur namanya
                    const inputLabel = newRow.querySelector('input, .spec-label');
                    const inputValue = newRow.querySelectorAll('input')[1] || newRow.querySelector(
                        '.spec-value');

                    // Reset nilai value agar kosong
                    inputLabel.value = '';
                    inputValue.value = '';

                    // Perbarui name attribute agar unik mengikuti pola array Laravel
                    inputLabel.setAttribute('name', `variants[${vIndex}][specs][${specCount}][label]`);
                    inputValue.setAttribute('name', `variants[${vIndex}][specs][${specCount}][value]`);

                    // Pastikan tombol hapus spesifikasi terlihat
                    const btnRemoveSpec = newRow.querySelector('.btn-remove-spec');
                    if (btnRemoveSpec) {
                        btnRemoveSpec.classList.remove('d-none');
                        btnRemoveSpec.onclick = function() {
                            newRow.remove();
                        };
                    }

                    // Masukkan ke dalam kontainer spesifikasi kartu ini
                    specContainer.appendChild(newRow);
                    specCount++;
                };

                // Pasang listener hapus untuk baris spesifikasi bawaan database yang sudah ada di layar
                card.querySelectorAll('.btn-remove-spec').forEach(function(btn) {
                    btn.onclick = function() {
                        btn.closest('.spec-row').remove();
                    };
                });
            }

            // Inisialisasi semua kartu varian yang lama (jika ada data dari DB)
            if (existingCards.length > 0) {
                existingCards.forEach(function(card) {
                    bindVariantEvents(card);

                    // Pasang fungsi hapus untuk kartu bawaan lama
                    const btnRemoveVariant = card.querySelector('.btn-remove-variant');
                    if (btnRemoveVariant) {
                        btnRemoveVariant.onclick = function() {
                            card.remove();
                        };
                    }
                });
            }

            // Event Klik Tombol Utama "+ Tambah Varian Baru"
            document.getElementById('btn-add-variant').addEventListener('click', function() {
                const wrapper = document.getElementById('variant-wrapper');
                const firstCard = wrapper.querySelector('.variant-card');
                let newCard;

                if (firstCard) {
                    // A. JIKA DATA ADA: Clone struktur kartu varian pertama
                    newCard = firstCard.cloneNode(true);
                    newCard.setAttribute('data-variant-index', variantCount);

                    // Reset & Update input Nama Varian Utama
                    const mainInput = newCard.querySelector('input[name*="[name]"]');
                    mainInput.value = '';
                    mainInput.setAttribute('name', `variants[${variantCount}][name]`);

                    // Bersihkan area spesifikasi, sisakan 1 baris bersih sebagai default
                    const specContainer = newCard.querySelector('.spec-container');
                    specContainer.innerHTML = `
                <div class="row align-items-center spec-row mb-2">
                    <div class="col-md-5">
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="ti ti-tag"></i></span>
                            <input type="text" name="variants[${variantCount}][specs][0][label]" class="form-control spec-label" placeholder="Nama Label (cth: Panjang)" >
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="ti ti-edit"></i></span>
                            <input type="text" name="variants[${variantCount}][specs][0][value]" class="form-control spec-value" placeholder="Nilai (cth: 120 cm atau 500 gr)" >
                        </div>
                    </div>
                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-sm btn-danger btn-remove-spec d-none">&times;</button>
                    </div>
                </div>
            `;
                } else {
                    // B. JIKA DATA KOSONG TOTAL: Buat struktur baru utuh dari string HTML
                    newCard = document.createElement('div');
                    newCard.className =
                        'variant-card border border-gray-300 rounded-xl p-5 bg-white shadow-sm relative mb-4';
                    newCard.setAttribute('data-variant-index', variantCount);
                    newCard.innerHTML = `
                <div class="col-12 mb-3">
                    <label class="form-label">Nama Varian <small class="text-danger">*</small></label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-barcode"></i></span>
                        <input type="text" name="variants[${variantCount}][name]" class="form-control variant-name" placeholder="Contoh: Merah Ukuran L">
                    </div>
                </div>

                <div class="col-12 mb-2">
                    <label class="form-label font-weight-bold">Spesifikasi / Dimensi Kustom <small class="text-danger">*</small></label>
                </div>

                <div class="spec-container space-y-3 mb-3">
                    <div class="row align-items-center spec-row mb-2">
                        <div class="col-md-5">
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti ti-tag"></i></span>
                                <input type="text" name="variants[${variantCount}][specs][0][label]" class="form-control spec-label" placeholder="Nama Label (cth: Panjang)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti ti-edit"></i></span>
                                <input type="text" name="variants[${variantCount}][specs][0][value]" class="form-control spec-value" placeholder="Nilai (cth: 120 cm atau 500 gr)">
                            </div>
                        </div>
                        <div class="col-md-1 text-end">
                            <button type="button" class="btn btn-sm btn-danger btn-remove-spec d-none">&times;</button>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button type="button" class="btn btn-success btn-sm btn-add-spec">+ Tambah Atribut/Dimensi</button>
                    <button type="button" class="btn btn-danger btn-sm btn-remove-variant">Hapus Varian</button>
                </div>
            `;
                }

                // Aktifkan tombol "Hapus Varian" untuk kartu baru ini
                const btnRemoveVariant = newCard.querySelector('.btn-remove-variant');
                btnRemoveVariant.classList.remove('d-none');
                btnRemoveVariant.onclick = function() {
                    newCard.remove();
                };

                // Daftarkan event internal (+ Atribut) untuk kartu baru ini
                bindVariantEvents(newCard);

                // Masukkan blok kartu baru ke dalam wrapper utama
                wrapper.appendChild(newCard);
                variantCount++;
            });
        });
    </script>
    <script>
        const radioSupply = document.getElementById('radioSupply');
        const radioNonSupply = document.getElementById('radioNonSupply');
        const stockTab = document.getElementById('stockTab');
        const barcodeField = document.getElementById('barcodeField');

        function toggleStockTab() {
            if (radioNonSupply.checked) {
                // sembunyikan
                stockTab.style.display = 'none';
                barcodeField.style.display = 'none';

                // optional: clear value barcode biar aman
                document.getElementById('barcode').value = '';

                // pindah tab biar ga blank
                const firstTab = document.querySelector('.nav-link');
                if (firstTab) {
                    new bootstrap.Tab(firstTab).show();
                }

            } else {
                // tampilkan
                stockTab.style.display = 'block';
                barcodeField.style.display = 'block';
            }
        }

        // run awal
        toggleStockTab();

        // listener
        radioSupply.addEventListener('change', toggleStockTab);
        radioNonSupply.addEventListener('change', toggleStockTab);
    </script>
    <script>
        function hitungTotal() {
            let qty = parseFloat(document.getElementById('quantity').value) || 0;
            let price = parseFloat(document.getElementById('unit_price').value) || 0;

            let total = qty * price;

            document.getElementById('total_price').value = total;
        }

        // trigger saat input berubah
        document.getElementById('quantity').addEventListener('input', hitungTotal);
        document.getElementById('unit_price').addEventListener('input', hitungTotal);
    </script>
    <script>
        $('#unit_id').on('change', function() {
            let unitId = $(this).val();
            let unitText = $('#unit_id option:selected').text();

            $('.from_unit_text').val(unitText);

            // isi ke hidden input (buat backend)
            $('.from_unit_id').val(unitId);
            // 🔥 AKTIFKAN INPUT
            $('.qty').prop('disabled', false);
            $('.to_unit').prop('disabled', false);
        });
        $(function() {
            $('#modalPrDetail').on('shown.bs.modal', function() {
                flatpickr("#date_stock", {
                    enableTime: false,
                    dateFormat: "d-m-Y",
                    minDate: "today",
                    defaultDate: new Date()
                });
            });

        });
        $(document).ready(function() {

            $('.select2-warehouse').each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: $this.attr('data-placeholder'),
                    width: '100%',
                    dropdownParent: $('#modalPrDetail'),
                });
            });
            $('.select2-unit').each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: $this.attr('data-placeholder'),
                    width: '100%',
                    dropdownParent: $('#modalPrDetail'),
                });
            });
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

                                    window.isEditingMode = true;

                                    // 1. Set index baris ke input hidden
                                    $('#detail_id').val(rowIndex);

                                    // 2. Set nilai text & number (Quantity, Unit Price, Date)
                                    $('#quantity').val(data.quantity);
                                    $('#unit_price').val(data.unit_price);
                                    $('#date_stock').val(data
                                        .date); // Menyesuaikan id="date_stock" di modal kamu

                                    // 3. Picu fungsi hitung total price agar langsung kalkulasi saat modal buka
                                    hitungTotal();

                                    // 4. Set Dropdown Warehouse
                                    if ($('#warehouse_id').length) {
                                        $('#warehouse_id').val(data.warehouse_id).trigger('change');
                                    }

                                    // 5. Set Dropdown Unit (Menyesuaikan id="unit_id_modals" di modal kamu)
                                    if ($('#unit_id_modals').length) {
                                        $('#unit_id_modals').val(data.stok_unit_id).trigger(
                                            'change');
                                    }

                                    // 6. Atur teks modal tombol & title
                                    $('#modalTitle').text('Edit entry');
                                    $('#btnSubmitModal').text('Update');

                                    // 7. Tampilkan modal
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
            $('#formPrDetailStock').on('submit', function(e) {
                e.preventDefault();
                let warehouseID = $('#warehouse_id').val();
                let warehouseName = $('#warehouse_id option:selected').text();
                let quantity = parseFloat($('#quantity').val()) || 0;
                let unitId = $('#unit_id_modals').val();
                let unitName = $('#unit_id_modals option:selected').text();
                let unitPrice = parseFloat($('#unit_price').val()) || 0;
                let date = $('#date_stock').val() || '';
                let detailId = $('#detail_id').val();
                if (!warehouseID) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops...',
                        text: 'Warehouse must be selected!',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-warning'
                        },
                        buttonsStyling: false
                    });
                    return;
                }
                if (!quantity) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops...',
                        text: 'Quantity must be selected!',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-warning'
                        },
                        buttonsStyling: false
                    });
                    return;
                }

                if (!unitId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops...',
                        text: 'Unit must be selected!',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-warning'
                        },
                        buttonsStyling: false
                    });
                    return;
                }
                let itemData = {
                    'date': date,
                    'warehouse_name': warehouseName,
                    'warehouse_id': warehouseID,
                    'quantity': quantity,
                    'stok_unit_id': unitId,
                    'stok_unit_name': unitName,
                    'unit_price': unitPrice,
                };

                if (detailId === '') {
                    prDetailsData.push(itemData);
                } else {
                    prDetailsData[detailId] = itemData;
                }

                // Render ulang ke DataTable visual
                table.clear().rows.add(prDetailsData).draw();
                $('#modalPrDetail').modal('hide');
            });
        });
    </script>
@endpush
