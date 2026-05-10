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
                                <label class="form-label">Items ID</label>
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
                                <select name="kategori_id" id="kategori_id" class="form-select">
                                    <option></option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->detail }}</option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="kategori_idError"></span>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Warehouse</label>
                                <select name="gudang_id" id="gudang_id" class="form-select">
                                    <option></option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">
                                            {{ $warehouse->nama_gudang }}
                                            )</option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="gudang_idError"></span>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Unit</label>
                                <select name="unit_id" id="unit_id" class="form-select">
                                    <option></option>
                                    @foreach ($unit as $units)
                                        <option value="{{ $units->id }}">{{ $units->detail }}</option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="unit_idError"></span>
                            </div>
                            <div class="col-md-3 d-flex gap-2 ">
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

                            <div class="col-md-12 mb-5">
                                <label class="form-label">Description</label>
                                <textarea name="keterangan" id="keterangan" cols="30" rows="3" class="form-control"></textarea>
                                <span class="error text-danger" id="keteranganError"></span>
                            </div>

                        </div>

                    </div>
                </div>
                <div id="supplyForm">
                    <div class="divider my-7 ">
                        <div class="divider-text">Additional Information</div>
                    </div>
                    <h6>
                        <strong>Beginning Balance</strong>
                    </h6>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3 row">
                                <label for="html5-text-input" class="col-md-4 col-form-label">Quantity</label>
                                <div class="col-md-8">
                                    <input class="form-control" type="text" placeholder="Enter quantity"
                                        id="html5-text-input">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="html5-text-input" class="col-md-4 col-form-label">Price/Unit</label>
                                <div class="col-md-8">
                                    <input class="form-control" type="text" placeholder="Enter price per unit"
                                        id="html5-text-input">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="html5-text-input" class="col-md-4 col-form-label">
                                    Cost of Goods</label>
                                <div class="col-md-8">
                                    <input class="form-control" type="text" id="html5-text-input">
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
        $('#kategori_id').select2({
            placeholder: "select category",
            allowClear: true,
            width: '100%'
        });
        $('#gudang_id').select2({
            placeholder: "select warehouse",
            allowClear: true,
            width: '100%'
        });
        $('#unit_id').select2({
            placeholder: "select unit",
            allowClear: true,
            width: '100%'
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const supplyRadio = document.getElementById("radioSupply");
            const nonSupplyRadio = document.getElementById("radioNonSupply");
            const form = document.getElementById("supplyForm");

            function toggleForm() {
                if (supplyRadio.checked) {
                    form.style.display = "block";
                } else {
                    form.style.display = "none";
                }
            }

            // jalankan saat pertama load
            toggleForm();

            // event change
            supplyRadio.addEventListener("change", toggleForm);
            nonSupplyRadio.addEventListener("change", toggleForm);
        });
    </script>
@endpush
