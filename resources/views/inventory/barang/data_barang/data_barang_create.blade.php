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
        <form id="postForm" name="postForm" method="POST" action="{{ route('data-barang.store') }}">
            @csrf
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
                                    @include('inventory.barang.data_barang.part.data_umum')

                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-top-term" role="tabpanel">
                                <div class="row">
                                    @include('inventory.barang.data_barang.part.stock_table')
                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-top-tax" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-12 mb-5">
                                        <label class="form-label">Description</label>
                                        <textarea name="keterangan" id="keterangan" cols="30" rows="3" class="form-control"></textarea>
                                        <span class="error text-danger" id="keteranganError"></span>
                                    </div>
                                </div>
                                <div class="divider my-7 ">
                                    <div class="divider-text">Varian.</div>
                                </div>
                                <div class="space-y-6">
                                    <div id="variant-wrapper" class="space-y-6">

                                        <div class="variant-card border border-gray-300 rounded-xl p-5 bg-white shadow-sm relative mb-4"
                                            data-variant-index="0">

                                            <div class="col-12 mb-3">
                                                <label class="form-label">Nama Varian <small
                                                        class="text-danger">*</small></label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text">
                                                        <i class="ti ti-barcode"></i>
                                                    </span>
                                                    <input type="text" name="variants[0][name]" class="form-control"
                                                        placeholder="Contoh: Merah Ukuran L">
                                                </div>
                                            </div>

                                            <div class="col-12 mb-2">
                                                <label class="form-label font-weight-bold">Spesifikasi / Dimensi Kustom
                                                    <small class="text-danger">*</small></label>
                                            </div>

                                            <div class="spec-container space-y-3 mb-3">
                                                <div class="row align-items-center spec-row mb-2">
                                                    <div class="col-md-5">
                                                        <div class="input-group input-group-merge">
                                                            <span class="input-group-text"><i class="ti ti-tag"></i></span>
                                                            <input type="text" name="variants[0][specs][0][label]"
                                                                class="form-control"
                                                                placeholder="Nama Label (cth: Panjang)">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="input-group input-group-merge">
                                                            <span class="input-group-text"><i class="ti ti-edit"></i></span>
                                                            <input type="text" name="variants[0][specs][0][value]"
                                                                class="form-control"
                                                                placeholder="Nilai (cth: 120 cm atau 500 gr)">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-1 text-end">
                                                        <button type="button"
                                                            class="btn btn-sm btn-danger btn-remove-spec d-none">&times;</button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                <button type="button" class="btn btn-success btn-sm btn-add-spec">
                                                    + Tambah Atribut/Dimensi
                                                </button>
                                                <button type="button"
                                                    class="btn btn-danger btn-sm btn-remove-variant d-none">
                                                    Hapus Varian
                                                </button>
                                            </div>
                                        </div>

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
                <div class="card-footer d-flex justify-content-end gap-2">
                    <button type="submit" id="savedata" class="btn btn-primary" data-save-and-new="false">
                        <i class="fa fa-upload me-1"></i> Save and Close
                    </button>

                    <button type="submit" id="savedatamore" class="btn btn-success" data-save-and-new="true">
                        <i class="fa fa-plus-circle me-1"></i> Save and Create New
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
                                <input type="text" id="date_stock" name="date_stock" class="form-control ">
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
                                    @foreach ($unit as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->detail }}</option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="unit_id_modalsError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label" for="unit_price">Unit Price</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text">{{ $mataUangDefault->symbol }}</span>
                                    <input type="number" id="unit_price" name="unit_price" class="form-control"
                                        placeholder="0" min="0">
                                </div>
                                <span class="error text-danger" id="unit_priceError"></span>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label" for="discount">Total Price</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text">{{ $mataUangDefault->symbol }}
                                    </span>
                                    <input type="number" id="total_price" name="total_price" class="form-control"
                                        placeholder="0" min="0" readonly>
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
                    <div class="row g-2 mb-3">
                           <div class="col-md-3">
                            <select name="conversion[${index}][to_unit]" class="form-select to_unit" ${$('.to_unit').first().is(':disabled') ? 'disabled' : ''}>
                                <option value="">Select</option>
                                ${$('.to_unit').first().html().split('</option>').slice(1).join('</option>')}
                            </select>
                        </div>

                        <div class="col-md-2 text-center">
                            <div class="fw-bold mt-2">=</div>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="conversion[${index}][qty]" class="form-control qty" placeholder="Qty" ${$('.qty').first().is(':disabled') ? 'disabled' : ''}>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control from_unit_text" disabled value="${$('.from_unit_text').first().val() || ''}">
                            <input type="hidden" name="conversion[${index}][from_unit]" class="from_unit_id" value="${$('.from_unit_id').first().val() || ''}">
                        </div>
                    </div>
                 <div class="mb-3 row d-none grupsell">
                       <label class="col-md-4 col-form-label">Default Sell Price #${index + 1}</label>
                       <div class="col-md-8">
                           <div class="input-group input-group-merge disabled-group">
                               <span class="input-group-text ">{{ $mataUangDefault->symbol }}</span>
                               <input type="number" name="sell_price[${index}][to_unit]" class="form-control sell_price"
                                   placeholder="0" min="0" >
                               <span class="input-group-text sellPrice" id=""></span>
                           </div>
                       </div>
                   </div>
                </div>
                   `;

                // Masukkan ke dalam container
                $('#conversion-container').append(html);
            });

            // 2. Aksi Hapus Baris Konversi (Menggunakan event delegation)
            $('#conversion-container').on('click', '.btn-remove-conversion', function() {
                // Cegah penghapusan jika hanya tersisa 1 baris (Opsional)
                if ($('.conversion-item').length > 1) {
                    $(this).closest('.conversion-item').remove();
                    reorderConversionIndices();
                } else {
                    alert('Minimal harus ada 1 baris konversi unit.');
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
        function checkConversionComplete() {

            let allFilled = true;

            $('.conversion-item').each(function() {

                let unit = $(this).find('.to_unit').val();
                let qty = $(this).find('.qty').val();

                // kalau salah satu kosong → belum lengkap
                if (!unit || !qty) {
                    allFilled = false;
                }

            });

            $('#btn-add-conversion').prop('disabled', !allFilled);
        }
        $(document).on('change keyup', '.to_unit, .qty', function() {
            checkConversionComplete();
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
            let formData = new FormData(form);
            formData.append('save_and_new', saveAndNew ? 1 : 0);
            let isValid = true;
            let errorMessage = '';
            formData.append('items_detail', JSON.stringify(prDetailsData));

            $('.conversion-item').each(function() {
                let qty = $(this).find('.qty').val();
                let toUnit = $(this).find('.to_unit').val();
                let fromUnit = $(this).find('.from_unit_id').val();

                // 1. qty ada tapi to_unit kosong
                if (qty && !toUnit) {
                    isValid = false;
                    errorMessage = 'Please select a destination unit for the entered quantity.';
                    return false;
                }

                // 2. to_unit ada tapi qty kosong
                if (!qty && toUnit) {
                    isValid = false;
                    errorMessage = 'Please enter a quantity for the selected unit.';
                    return false;
                }

                // 3. to_unit tidak boleh sama dengan from_unit
                if (toUnit && fromUnit && toUnit == fromUnit) {
                    isValid = false;
                    errorMessage = 'The destination unit must be different from the source unit.';
                    return false;
                }
            });

            if (!isValid) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Input',
                    text: errorMessage,
                    confirmButtonText: 'OK',
                    showClass: {
                        popup: 'animate__animated animate__bounceIn'
                    },
                    customClass: {
                        confirmButton: 'btn btn-primary waves-effect waves-light'
                    },
                    buttonsStyling: false
                });
                return; // ❌ STOP submit
            }


            $.ajax({
                url: $(form).attr('action'),
                method: $(form).attr('method'),
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function() {
                    btn.html('<i class="fa fa-spin fa-spinner me-1"></i> Sending...');
                    btn.prop('disabled', true);
                },
                complete: function() {
                    if (saveAndNew) {
                        btn.html('<i class="fa fa-plus-circle me-1"></i> Save and Create New');
                    } else {
                        btn.html('<i class="fa fa-upload me-1"></i> Save and Close');
                    }
                    btn.prop('disabled', false);
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
                        window.location.href = response.redirect;
                    });
                },
                error: function(xhr) {
                    // reset validation messages (buat kamu implement sendiri)
                    resetValidation();

                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to Create Data',
                        text: 'Please check your data again.',
                        showClass: {
                            popup: 'animate__animated animate__bounceIn'
                        },
                        customClass: {
                            confirmButton: 'btn btn-primary waves-effect waves-light'
                        },
                        buttonsStyling: false
                    });

                    let errors = xhr.responseJSON.errors || {};

                    $.each(errors, function(key, value) {
                        displayFieldError(key, value[
                            0]); // fungsi buat nampilin error per field
                    });
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let variantCount = 1; // Counter untuk index variant-card baru

            // Fungsi untuk mengaktifkan event listener di setiap variant-card (baik lama maupun hasil clone)
            function bindVariantEvents(card) {
                const specContainer = card.querySelector('.spec-container');
                const btnAddSpec = card.querySelector('.btn-add-spec');
                const vIndex = card.getAttribute('data-variant-index');

                // Menggunakan closure/counter khusus internal card untuk melacak jumlah spesifikasinya
                let specCount = specContainer.querySelectorAll('.spec-row').length;

                // Event Klik Tombol "+ Tambah Atribut/Dimensi"
                btnAddSpec.onclick = function() {
                    const firstRow = specContainer.querySelector('.spec-row');
                    const newRow = firstRow.cloneNode(true);

                    // Ambil element input di baris baru
                    const inputLabel = newRow.querySelector('input[name*="[label]"]');
                    const inputValue = newRow.querySelector('input[name*="[value]"]');

                    // Reset nilai value agar kosong
                    inputLabel.value = '';
                    inputValue.value = '';

                    // Perbarui name attribute agar unik mengikuti pola array Laravel: variants[vIndex][specs][specCount][...]
                    inputLabel.setAttribute('name', `variants[${vIndex}][specs][${specCount}][label]`);
                    inputValue.setAttribute('name', `variants[${vIndex}][specs][${specCount}][value]`);

                    // Tampilkan tombol hapus spesifikasi (&times;) di baris baru ini
                    const btnRemoveSpec = newRow.querySelector('.btn-remove-spec');
                    btnRemoveSpec.classList.remove('d-none');

                    // Pasang fungsi hapus baris spesifikasi
                    btnRemoveSpec.onclick = function() {
                        newRow.remove();
                    };

                    // Masukkan ke dalam kontainer spesifikasi kartu ini
                    specContainer.appendChild(newRow);
                    specCount++;
                };
            }

            // Inisialisasi kartu varian pertama (Index 0) yang sudah ada semenjak halaman dimuat
            bindVariantEvents(document.querySelector('.variant-card'));

            // Event Klik Tombol Utama "+ Tambah Varian Baru"
            document.getElementById('btn-add-variant').addEventListener('click', function() {
                const wrapper = document.getElementById('variant-wrapper');
                const firstCard = wrapper.querySelector('.variant-card');

                // Clone struktur kartu varian pertama
                const newCard = firstCard.cloneNode(true);

                // Update index data attribute pada kartu baru
                newCard.setAttribute('data-variant-index', variantCount);

                // Reset & Update input Nama Varian Utama
                const mainInput = newCard.querySelector('input[name^="variants[0][name]"]');
                mainInput.value = '';
                mainInput.setAttribute('name', `variants[${variantCount}][name]`);

                // Bersihkan area spesifikasi, sisakan 1 baris bersih sebagai default
                const specContainer = newCard.querySelector('.spec-container');
                specContainer.innerHTML = `
                <div class="row align-items-center spec-row mb-2">
                    <div class="col-md-5">
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="ti ti-tag"></i></span>
                            <input type="text" name="variants[${variantCount}][specs][0][label]" class="form-control" placeholder="Nama Label (cth: Panjang)" >
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="ti ti-edit"></i></span>
                            <input type="text" name="variants[${variantCount}][specs][0][value]" class="form-control" placeholder="Nilai (cth: 120 cm atau 500 gr)" >
                        </div>
                    </div>
                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-sm btn-danger btn-remove-spec d-none">&times;</button>
                    </div>
                </div>

             `;

                // Tampilkan dan aktifkan tombol "Hapus Varian" untuk blok kartu baru ini
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
    {{-- RADIO BUTTON --}}
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
    {{-- HITUNG TOTAL --}}
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
    {{-- UNIT CHANGE --}}
    <script>
        $('#unit_id').on('change', function() {
            let unitId = $(this).val();
            let unitText = $('#unit_id option:selected').text();
            if (unitId !== "") {
                $('.from_unit_text').val(unitText);

                // isi ke hidden input (buat backend)
                $('.from_unit_id').val(unitId);
                $('#selMin').html('/ ' + unitText);
                $('#sellPrice').html('/ ' + unitText);
                // 🔥 AKTIFKAN INPUT
                $('.qty').prop('disabled', false);
                $('.to_unit').prop('disabled', false);
                $('.sell_price').prop('disabled', false);
                // $('#btn-add-conversion').prop('disabled', false);

            } else {
                $('.from_unit_text').val(unitText);

                // isi ke hidden input (buat backend)
                $('.from_unit_id').val(unitId);
                $('#selMin').html('/ ' + unitText);
                $('#sellPrice').html('/ ' + unitText);
                // 🔥 AKTIFKAN INPUT
                $('.qty').prop('disabled', true);
                $('.to_unit').prop('disabled', true);
                $('.sell_price').prop('disabled', true);
                // $('#btn-add-conversion').prop('disabled', true);
            }
        });
        $(document).on('change', '.to_unit', function() {

            let unitValue = $(this).val();
            let row = $(this).closest('.conversion-item');

            if (unitValue === "" || unitValue === null) {

                row.find('.sellPrice').text('');
                row.find('.grupsell').addClass('d-none');

                return;
            }

            let unitText = $(this).find('option:selected').text();

            row.find('.sellPrice').text('/ ' + unitText);

            // 👉 TAMPILKAN GROUP SELL PRICE
            row.find('.grupsell').removeClass('d-none');
        });
        // $(document).on('change', '.to_unit', function() {

        //     let unitValue = $(this).val();
        //     let row = $(this).closest('.conversion-item');

        //     if (unitValue === "" || unitValue === null) {
        //         row.find('.sellPrice').text('');
        //         return;
        //     }

        //     let unitText = $(this).find('option:selected').text();

        //     row.find('.sellPrice').text('/ ' + unitText);
        // });
        let prDetailsData = [];
        $(function() {
            $('#modalPrDetail').on('shown.bs.modal', function() {
                flatpickr("#date_stock", {
                    enableTime: false,
                    dateFormat: "d-m-Y",
                    // minDate: "today",
                    // defaultDate: new Date()
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
