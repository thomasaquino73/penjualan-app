@extends('layouts.app')
@section('konten')
    <h4><span class="text-muted fw-light">
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
        <div class="card-header d-flex justify-content-between">
            <h5 class="card-title mb-0">{{ $title }}</h5>
            <div class="card-header-elements ms-auto">

            </div>
        </div>
        <div class="card-datatable table-responsive" style="padding: 20px">
            <form method="POST" action="{{ route('data-barang.update', $detail->id) }}" class="py-2" id="postForm"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Picture</label>
                                <input type="file" name="photo_filename" id="photo_filename" class="form-control">
                                <span class="error text-danger" id="photo_filenameError"></span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Product ID <small class="text-danger">*</small> </label>
                                <input type="text" name="id_barang" id="id_barang" class="form-control"
                                    value="{{ $detail->id_barang }}">
                                <span class="error text-danger" id="id_barangError"></span>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Name <small class="text-danger">*</small> </label>
                                <input type="text" name="nama_barang" id="nama_barang" class="form-control"
                                    value="{{ $detail->nama_barang }}">
                                <span class="error text-danger" id="nama_barangError"></span>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Category<small class="text-danger">*</small></label>
                                <select name="kategori_id" id="kategori_id" class="form-select select2"
                                    data-placeholder="Select category">
                                    <option></option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ $category->id == $detail->kategori_id ? 'selected' : '' }}>
                                            {{ $category->detail }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="kategori_idError"></span>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Warehouse<small class="text-danger">*</small></label>
                                <select name="gudang_id" id="gudang_id" class="form-select select2"
                                    data-placeholder="Select warehouse">
                                    <option></option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}"
                                            {{ $warehouse->id == $detail->gudang_id ? 'selected' : '' }}>
                                            {{ $warehouse->nama_gudang }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="gudang_idError"></span>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Unit<small class="text-danger">*</small></label>
                                <div class="input-group">
                                    <select name="unit_id" id="unit_id" class="form-select "
                                        data-placeholder="Select unit">
                                        <option></option>
                                        @foreach ($unit as $units)
                                            <option value="{{ $units->id }}"
                                                {{ $units->id == $detail->unit_id ? 'selected' : '' }}>
                                                {{ $units->detail }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" id="showSubUnit" data-id="{{ $detail->id }}"
                                        class="btn btn-md btn-primary waves-effect waves-light">...</button>
                                </div>
                                <span class="error text-danger" id="unit_idError"></span>
                            </div>
                            <div class="col-md-3  ">
                                <label class="form-label">Product Type<small class="text-danger">*</small></label>
                                <div class="d-flex gap-2">
                                    <div class="form-check form-check-success me-4">
                                        <input name="product_type" class="form-check-input" type="radio" value="supply"
                                            id="radioSupply" {{ $detail->product_type == 'supply' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="radioSupply"> Supply </label>
                                    </div>

                                    <div class="form-check form-check-success">
                                        <input name="product_type" class="form-check-input" type="radio"
                                            value="non_supply" id="radioNonSupply"
                                            {{ $detail->product_type == 'non_supply' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="radioNonSupply"> Non Supply </label>
                                    </div>
                                </div>
                                <span class="error text-danger" id="product_typeError"></span>
                            </div>

                            <div class="col-md-12 mb-5">
                                <label class="form-label">Description</label>
                                <textarea name="keterangan" id="keterangan" cols="30" rows="3" class="form-control">{{ $detail->keterangan }}</textarea>
                                <span class="error text-danger" id="keteranganError"></span>
                            </div>

                        </div>

                    </div>
                </div>
                <div class="divider my-7 ">
                    <div class="divider-text">Additional Information</div>
                </div>

                <div class="row">
                    <div class="col-lg-6" id="supplyForm">
                        <h6><strong>Beginning Balance</strong></h6>
                        <div class="mb-3 row">
                            <label class="col-md-4 col-form-label">Quantity</label>
                            <div class="col-md-8">
                                <input class="form-control" type="number" id="quantity" name="quantity"
                                    placeholder="Enter quantity" value="{{ $detail->quantity }}">
                                <span class="error text-danger" id="quantityError"></span>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-md-4 col-form-label">Price/Unit</label>
                            <div class="col-md-8">
                                <input class="form-control" type="number" id="price" name="price"
                                    placeholder="Enter price per unit" value="{{ $detail->price }}">
                                <span class="error text-danger" id="priceError"></span>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-md-4 col-form-label">Cost of Goods</label>
                            <div class="col-md-8">
                                <input class="form-control" type="text" id="hasil_akhir" name="hasil_akhir" readonly
                                    value="{{ $detail->hasil_akhir }}">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="html5-text-input" class="col-md-4 col-form-label">
                                date</label>
                            <div class="col-md-8">
                                <input type="date" name="date" id="date" class="form-control"
                                    value="{{ $detail->date }}">
                                <span class="error text-danger" id="dateError"></span>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <button type="submit" id="savedata" class="btn btn-primary" data-save-and-new="false">
                        <i class="fa fa-upload me-1"></i> Update
                    </button>
                    <a href="{{ route('data-barang.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
        </div>
    </div>

    <div class="modal fade" id="modals" tabindex="-1">
        <div class="modal-dialog  modal-md">
            <div class="modal-content">

                <div class="modal-header">
                    <div class="text-center ">
                        <h3 class="mb-2" id="modal-title"></h3>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center">
                    <label class="col-md-2 col-form-label">Unit 1</label>

                    <div class="col-md-10">
                        <div class="d-flex align-items-center gap-2">

                            <select name="unit_1" id="unit_1" class="form-select select2" style="width: 200px;">
                                <option></option>
                                @foreach ($unit as $un)
                                    <option value="{{ $un->id }}">{{ $un->detail }}</option>
                                @endforeach
                            </select>

                            <span>=</span>

                            <input class="form-control" type="text" placeholder="Qty" id="quantity1"
                                name="quantity1" style="width: 120px;">

                            <span>x</span>

                            <input class="form-control" type="text" id="unit1" name="unit1" readonly
                                style="width: 120px;">
                        </div>
                    </div>
                    <label class="col-md-2 mt-2 col-form-label">Unit 2</label>
                    <div class="col-md-10">
                        <div class="d-flex align-items-center gap-2">
                            <select name="unit_2" id="unit_2" class="form-select select2" style="width: 200px;">
                                <option></option>
                                @foreach ($unit as $un)
                                    <option value="{{ $un->id }}">{{ $un->detail }}</option>
                                @endforeach
                            </select>

                            <span>=</span>

                            <input class="form-control" type="text" placeholder="Qty" id="quantity2"
                                name="quantity2" style="width: 120px;">

                            <span>x</span>

                            <input class="form-control" type="text" id="unit2" name="unit2" readonly
                                style="width: 120px;">
                        </div>
                    </div>
                </div>
                <div class="card-footer"></div>
            </div>
        </div>
    </div>
    </form>
@endsection

@push('style')
    <style>
        #supplyForm {
            transition: all 0.3s ease;
        }

        #unit1 {
            border: none;
            box-shadow: none;
            /* hilangkan shadow bootstrap */
        }

        #unit2 {
            border: none;
            box-shadow: none;
            /* hilangkan shadow bootstrap */
        }

        /* Container select2 hanya untuk #unit_id */
        #unit_id+.select2-container {
            flex: 1 1 auto;
            width: 1% !important;
        }

        /* Tinggi select2 */
        #unit_id+.select2-container .select2-selection--single {
            height: 38px !important;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            border-right: 0;
            /* biar nyatu sama button */
        }

        /* Text di tengah */
        #unit_id+.select2-container .select2-selection__rendered {
            line-height: 36px;
            padding-left: 12px;
        }

        /* Arrow sejajar */
        #unit_id+.select2-container .select2-selection__arrow {
            height: 36px;
        }
    </style>
@endpush
@push('scripts')
    <script>
        let isModalOpen = false;
        let dbUnit1 = '';
        let dbUnit2 = '';
        let currentData = null;

        $(document).ready(function() {

            // =========================
            // OPEN MODAL + FETCH DATA
            // =========================
            $('#showSubUnit').on('click', function() {

                let dataBarangId = $(this).data('id');

                isModalOpen = true;

                $('#modals').modal('show');
                $('#modal-title').html('Edit Sub Unit');

                $.ajax({
                    url: '/sub-unit/' + dataBarangId,
                    type: 'GET',
                    dataType: 'json',

                    success: function(res) {

                        if (!res.success) return;

                        let d = res.data;

                        currentData = d;

                        // input biasa
                        $('#quantity1').val(d.quantity1);
                        $('#quantity2').val(d.quantity2);

                        // simpan DB fallback
                        dbUnit1 = d.unit1;
                        dbUnit2 = d.unit2;

                        $('#unit1').val(dbUnit1);
                        $('#unit2').val(dbUnit2);
                    },

                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to fetch data'
                        });
                    }
                });
            });


            // =========================
            // PASTIKAN MODAL SUDAH SIAP
            // =========================
            $('#modals').on('shown.bs.modal', function() {

                if (!currentData) return;

                setTimeout(function() {

                    $('#unit_1')
                        .val(String(currentData.unit_1))
                        .trigger('change');

                    $('#unit_2')
                        .val(String(currentData.unit_2))
                        .trigger('change');

                }, 100);
            });


            // =========================
            // UNIT SELECT CHANGE
            // =========================
            $('#unit_id').on('change', function() {

                if (!isModalOpen) return;

                let selected = $(this).select2('data');

                if (selected.length > 0) {

                    let text = selected[0].text;

                    $('#unit1').val(text);
                    $('#unit2').val(text);

                } else {

                    $('#unit1').val(dbUnit1);
                    $('#unit2').val(dbUnit2);
                }
            });


            // =========================
            // MODAL CLOSE RESET STATE
            // =========================
            $('#modals').on('hidden.bs.modal', function() {

                isModalOpen = false;
                currentData = null;
            });

        });
    </script>
    <script>
        $('.select2').select2({
            allowClear: true,
            width: '100%',
        });
        $('#unit_id').select2({
            width: '100%',
            dropdownAutoWidth: true
        });
        $('#unit_1').select2({
            dropdownParent: $('#modals'),
            placeholder: "Select unit",
            allowClear: true
        });
        $('#unit_2').select2({
            dropdownParent: $('#modals'),
            placeholder: "Select unit",
            allowClear: true
        });
        document.addEventListener("DOMContentLoaded", function() {
            const supplyRadio = document.getElementById("radioSupply");
            const nonSupplyRadio = document.getElementById("radioNonSupply");
            const form = document.getElementById("supplyForm");

            function toggleForm() {
                if (supplyRadio.checked) {
                    form.style.display = "block";
                } else {
                    form.style.display = "none";
                    $('#quantity').val('');
                    $('#price').val('');
                    $('#hasil_akhir').val('');
                    $('#date').val('');
                }
            }

            // jalankan saat pertama load
            toggleForm();

            // event change
            supplyRadio.addEventListener("change", toggleForm);
            nonSupplyRadio.addEventListener("change", toggleForm);
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
        $('#unit_id').on('change', function() {
            let data = $(this).select2('data');
            if ($(this).val()) {
                $('#button-addon2').prop('disabled', false);
            } else {
                $('#button-addon2').prop('disabled', true);
            }
            $('#unit1').val(data[0].text);
            $('#unit2').val(data[0].text);
        });
    </script>
    <script>
        const qty = document.getElementById('quantity');
        const price = document.getElementById('price');
        const total = document.getElementById('hasil_akhir');

        function hitungTotal() {
            let q = parseFloat(qty.value) || 0;
            let p = parseFloat(price.value) || 0;

            total.value = q * p;
        }

        qty.addEventListener('input', hitungTotal);
        price.addEventListener('input', hitungTotal);
    </script>
    <script>
        $('#button-addon2').on('click', function() {
            $('#subUnitSection').slideToggle(); // klik → muncul, klik lagi → hilang
        });
    </script>
    <script></script>
@endpush
