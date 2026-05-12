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
                                            {{ $detail->kategori_id == $category->id ? 'selected' : '' }}>
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
                                            {{ $detail->gudang_id == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->nama_gudang }}</option>
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
                                                {{ $detail->unit_id == $units->id ? 'selected' : '' }}>
                                                {{ $units->detail }}</option>
                                        @endforeach
                                    </select>
                                    {{-- <button type="button" id="showSubUnit"
                                        class="btn btn-sm btn-primary waves-effect waves-light">...</button> --}}
                                </div>
                                <span class="error text-danger" id="unit_idError"></span>
                            </div>
                            <div class="col-md-3  ">
                                <label class="form-label">Product Type<small class="text-danger">*</small></label>
                                <div class="d-flex gap-2">
                                    <div class="form-check form-check-success me-4">
                                        <input name="product_type" class="form-check-input" type="radio" value="supply"
                                            id="radioSupply" {{ $detail->product_type == 'supply' ? 'checked' : '' }}
                                            readonly>
                                        <label class="form-check-label" for="radioSupply"> Supply </label>
                                    </div>

                                    <div class="form-check form-check-success">
                                        <input name="product_type" class="form-check-input" type="radio"
                                            value="non_supply" id="radioNonSupply"
                                            {{ $detail->product_type == 'non_supply' ? 'checked' : '' }} readonly>
                                        <label class="form-check-label" for="radioNonSupply"> Non Supply </label>
                                    </div>
                                </div>
                                <span class="error text-danger" id="product_typeError"></span>
                            </div>

                            <div class="col-md-12 mb-5">
                                <label class="form-label">Description</label>
                                <textarea name="keterangan" id="keterangan" cols="30" rows="3" class="form-control"></textarea>
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
                                    value="{{ $detail->quantity * $detail->price }}">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="html5-text-input" class="col-md-4 col-form-label">
                                Date</label>
                            <div class="col-md-8">
                                <input type="date" name="date" id="date" class="form-control"
                                    value="{{ $detail->date ? date('Y-m-d', strtotime($detail->date)) : '' }}">
                                <span class="error text-danger" id="dateError"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <h6><strong>Unit Conversion</strong></h6>
                        <div class="conversion-item border p-3 mb-2 rounded ">
                            <div class="d-flex justify-content-between mb-2">

                            </div>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <input type="text" class="form-control from_unit_text" disabled>
                                    <input type="hidden" name="conversion[0][from_unit]" class="from_unit_id">
                                </div>
                                <div class="col-md-2 text-center">
                                    <div class="fw-bold">=</div>
                                </div>

                                <div class="col-md-3">
                                    <input type="number" name="conversion[0][qty]" class="form-control qty"
                                        placeholder="Qty" disabled>
                                </div>

                                <div class="col-md-3">
                                    <select name="conversion[0][to_unit]" class="form-select to_unit" disabled>
                                        <option value="">Select</option>
                                        @foreach ($unit as $u)
                                            <option value="{{ $u->id }}">{{ $u->detail }}</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>
                        </div>

                        <div class="conversion-item border p-3 mb-2 rounded">
                            <div class="d-flex justify-content-between mb-2">

                            </div>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label>From Unit</label>
                                    <input type="text" class="form-control from_unit_text" disabled>
                                    <input type="hidden" name="conversion[1][from_unit]" class="from_unit_id">

                                </div>
                                <div class="col-md-2 text-center">
                                    <label>&nbsp;</label>
                                    <div class="fw-bold">=</div>
                                </div>

                                <div class="col-md-3">
                                    <label>Quantity</label>
                                    <input type="number" name="conversion[1][qty]" class="form-control qty"
                                        placeholder="Qty" disabled>
                                </div>

                                <div class="col-md-3">
                                    <label>To Unit</label>
                                    <select name="conversion[1][to_unit]" class="form-select to_unit" disabled>
                                        <option value="">Select</option>
                                        @foreach ($unit as $u)
                                            <option value="{{ $u->id }}"
                                                {{ isset($detail->conversions[0]) && $detail->conversions[0]->to_unit == $u->id ? 'selected' : '' }}>
                                                {{ $u->detail }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
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


    </form>
@endsection

{{-- @push('style')
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
@endpush --}}
@push('scripts')
    <script>
        $('.select2').select2({
            allowClear: true,
            width: '100%',
        });
        $('#unit_id').select2({
            width: '100%',
            dropdownAutoWidth: true
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
            let isValid = true;
            let errorMessage = '';

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
        // $(document).on('click', '#showSubUnit', function() {

        //     let unitId = $('#unit_id').val();
        //     let unitText = $('#unit_id option:selected').text();

        //     if (!unitId) {
        //         Swal.fire({
        //             icon: 'error',
        //             title: 'Oops...',
        //             text: 'Please select a unit first.',
        //         });
        //         return;
        //     }

        //     // isi ke input TEXT
        //     $('.from_unit_text').val(unitText);

        //     // isi ke hidden input (buat backend)
        //     $('.from_unit_id').val(unitId);
        //     // 🔥 AKTIFKAN INPUT
        //     $('.qty').prop('disabled', false);
        //     $('.to_unit').prop('disabled', false);
        // });

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
    </script>
@endpush
