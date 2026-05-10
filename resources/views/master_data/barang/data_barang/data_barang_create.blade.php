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

            <form method="POST" action="{{ route('data-barang.store') }}" class="py-2" id="postForm"
                enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Picture</label>
                                <input type="file" name="photo_filename" id="photo_filename" class="form-control">
                                <span class="error text-danger" id="photo_filenameError"></span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Product ID</label>
                                <input type="text" name="id_barang" id="id_barang" class="form-control"
                                    value="{{ $idNumber }}" readonly>
                                <span class="error text-danger" id="id_barangError"></span>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Name</label>
                                <input type="text" name="nama_barang" id="nama_barang" class="form-control">
                                <span class="error text-danger" id="nama_barangError"></span>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Category</label>
                                <select name="kategori_id" id="kategori_id" class="form-select select2"
                                    data-placeholder="Select category">
                                    <option></option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->detail }}</option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="kategori_idError"></span>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Warehouse</label>
                                <select name="gudang_id" id="gudang_id" class="form-select select2"
                                    data-placeholder="Select warehouse">
                                    <option></option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">
                                            {{ $warehouse->nama_gudang }}</option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="gudang_idError"></span>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Unit</label>
                                <select name="unit_id" id="unit_id" class="form-select select2"
                                    data-placeholder="Select unit">
                                    <option></option>
                                    @foreach ($unit as $units)
                                        <option value="{{ $units->id }}">{{ $units->detail }}</option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="unit_idError"></span>
                            </div>
                            <div class="col-md-3  ">
                                <label class="form-label">Product Type</label>
                                <div class="d-flex gap-2">
                                    <div class="form-check form-check-success me-4">
                                        <input name="type" class="form-check-input" type="radio" value="supply"
                                            id="radioSupply" checked>
                                        <label class="form-check-label" for="radioSupply"> Supply </label>
                                    </div>

                                    <div class="form-check form-check-success">
                                        <input name="type" class="form-check-input" type="radio" value="non_supply"
                                            id="radioNonSupply">
                                        <label class="form-check-label" for="radioNonSupply"> Non Supply </label>
                                    </div>
                                </div>
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
                                <input class="form-control" type="number" id="quantity" placeholder="Enter quantity">
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-md-4 col-form-label">Price/Unit</label>
                            <div class="col-md-8">
                                <input class="form-control" type="number" id="price"
                                    placeholder="Enter price per unit">
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-md-4 col-form-label">Cost of Goods</label>
                            <div class="col-md-8">
                                <input class="form-control" type="text" id="hasil_akhir" readonly>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="html5-text-input" class="col-md-4 col-form-label">
                                date</label>
                            <div class="col-md-8">
                                <input type="date" name="date" id="date" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <h6><strong>Sub Unit</strong></h6>
                        <div class="mb-3 row">
                            <label for="html5-text-input" class="col-md-2 col-form-label">Unit 1</label>
                            <div class="col-md-10 d-flex gap-4">
                                <select name="unit_1" id="unit_1" class="form-select select2"
                                    data-placeholder="Select unit">
                                    <option></option>
                                    @foreach ($unit as $un)
                                        <option value="{{ $un->id }}">{{ $un->detail }}
                                        </option>
                                    @endforeach
                                </select>
                                =
                                <input class="form-control" type="text" placeholder="Enter quantity" id="quantity1">
                                x
                                <input class="form-control" type="text" placeholder="Unit" id="unit1" readonly>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="html5-text-input" class="col-md-2 col-form-label">Unit 2</label>
                            <div class="col-md-10 d-flex gap-4">
                                <select name="unit_2" id="unit_2" class="form-select select2"
                                    data-placeholder="Select unit">
                                    <option></option>
                                    @foreach ($unit as $un)
                                        <option value="{{ $un->id }}">{{ $un->detail }}
                                        </option>
                                    @endforeach
                                </select>
                                =
                                <input class="form-control" type="text" placeholder="Enter quantity" id="quantity2">
                                x
                                <input class="form-control" type="text" placeholder="Unit" id="unit2" readonly>
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
    </div>
@endsection
@push('style')
    <style>
        #supplyForm {
            transition: all 0.3s ease;
        }
    </style>
@endpush
@push('scripts')
    <script>
        $('.select2').select2({
            allowClear: true,
            width: '100%'
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
@endpush
