<div class="row">
    <div class="col-lg-12">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Product Type<small class="text-danger">*</small></label>
                <div class="d-flex gap-2">
                    <div class="form-check form-check-success me-4">
                        <input name="product_type_disabled" class="form-check-input" type="radio" value="supply"
                            id="radioSupply" {{ $detail->product_type == 'supply' ? 'checked' : '' }} disabled>
                        <label class="form-check-label" for="radioSupply"> Supply </label>
                    </div>

                    <div class="form-check form-check-success">
                        <input name="product_type_disabled" class="form-check-input" type="radio" value="non_supply"
                            id="radioNonSupply" {{ $detail->product_type == 'non_supply' ? 'checked' : '' }} disabled>
                        <label class="form-check-label" for="radioNonSupply"> Non Supply </label>
                    </div>
                </div>

                <input type="hidden" name="product_type" value="{{ $detail->product_type }}">

                <span class="error text-danger" id="product_typeError"></span>
            </div>

            <div class="col-md-3">
                <label class="form-label">Status</label>
                <div class="form-check form-check-primary">
                    <input class="form-check-input" type="checkbox" value="1" id="status" name="status"
                        {{ old('status', $detail->status ?? 0) == 1 ? 'checked' : '' }}>
                    <label class="form-check-label" for="status">
                        Set as Not Active
                    </label>
                </div>
                <span class="error text-danger" id="statusError"></span>
            </div>

            <div class="row mt-3">
                <div class="col-lg-6">
                    <div class="col-12 mb-3">
                        <label class="form-label">Picture</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"> <i class="ti ti-photo"></i></span>
                            <input type="file" name="photo_filename" id="photo_filename" class="form-control">
                        </div>
                        @if (!empty($detail->photo_filename))
                            <small class="text-muted">Current file: {{ $detail->photo_filename }}</small>
                        @endif
                        <span class="error text-danger" id="photo_filenameError"></span>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Product ID <small class="text-danger">*</small> </label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"> <i class="ti ti-barcode"></i></span>
                            <input type="text" name="id_barang" id="id_barang" class="form-control"
                                value="{{ old('id_barang', $detail->id_barang ?? '') }}">
                        </div>
                        <span class="error text-danger" id="id_barangError"></span>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Product Name <small class="text-danger">*</small> </label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"> <i class="ti ti-quote"></i></span>
                            <input type="text" name="nama_barang" id="nama_barang" class="form-control"
                                value="{{ old('nama_barang', $detail->nama_barang ?? '') }}">
                        </div>
                        <span class="error text-danger" id="nama_barangError"></span>
                    </div>

                    {{-- <div class="col-12 mb-3" id="barcodeField">
                        <label class="form-label">Barcode <small class="text-danger">*</small> </label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="ti ti-barcode"></i></span>
                            <input type="number" id="barcode" name="barcode" class="form-control" placeholder="0"
                                value="{{ old('barcode', $detail->barcode ?? '') }}" min="0">
                        </div>
                        <span class="error text-danger" id="barcodeError"></span>
                    </div> --}}
                </div>

                <div class="col-lg-6">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Category<small class="text-danger">*</small></label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"> <i class="ti ti-list-details"></i></span>
                            <select name="kategori_id" id="kategori_id" class="form-select select2"
                                data-placeholder="Select category">
                                <option></option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('kategori_id', $detail->kategori_id ?? '') == $category->id ? 'selected' : '' }}>
                                        {{ $category->detail }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <span class="error text-danger" id="kategori_idError"></span>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Unit<small class="text-danger">*</small></label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"> <i class="ti ti-list-details"></i></span>
                            <select name="unit_id" id="unit_id" class="form-select select2"
                                data-placeholder="Select unit">
                                <option></option>
                                @foreach ($unit as $units)
                                    <option value="{{ $units->id }}"
                                        {{ old('unit_id', $detail->unit_id ?? '') == $units->id ? 'selected' : '' }}>
                                        {{ $units->detail }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <span class="error text-danger" id="unit_idError"></span>
                    </div>
                    {{-- <div class="col-md-12 mb-3">
                        <label class="form-label">Brand<small class="text-danger">*</small></label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"> <i class="ti ti-medal"></i>
                            </span>
                            <select name="brand_id" id="brand_id" class="form-select select2 "
                                data-placeholder="Select Brand">
                                <option></option>
                                @foreach ($brand as $brands)
                                    <option value="{{ $brands->id }}"
                                        {{ old('unit_id', $detail->brand_id ?? '') == $brands->id ? 'selected' : '' }}>
                                        {{ $brands->detail }}</option>
                                @endforeach
                            </select>
                        </div>

                        <span class="error text-danger" id="brand_idError"></span>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="divider my-7">
    <div class="divider-text">Please provide additional information if needed.</div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0"><strong>Unit Conversion</strong></h6>
            <div class="d-flex gap-2">
                <button type="button" id="btn-add-conversion" class="btn btn-primary btn-sm rounded-pill">
                    <i class="ti ti-plus me-1"></i> Add Unit
                </button>
                <button type="button" id="btn-reset-conversion" class="btn btn-secondary btn-sm rounded-pill">
                    <i class="ti ti-refresh me-1"></i> Reset
                </button>
            </div>
        </div>
        <div id="conversion-wrapper">
            <div id="conversion-container">
                @if (!empty($detail->conversions) && $detail->conversions->count() > 0)
                    @foreach ($detail->conversions as $cIndex => $conversion)
                        <div class="conversion-item border p-3 mb-2 rounded position-relative">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-label-secondary conversion-number">Unit
                                    #{{ $cIndex + 1 }}</span>
                                <button type="button" class="btn btn-sm btn-text-danger btn-remove-conversion p-1">
                                    <i class="ti ti-trash fs-5"></i>
                                </button>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-3">
                                    <select name="conversion[{{ $cIndex }}][to_unit]"
                                        class="form-select to_unit">
                                        <option value="">Select</option>
                                        @foreach ($sub_unit as $sub)
                                            <option value="{{ $sub->id }}"
                                                {{ $conversion->from_unit_id == $sub->id ? 'selected' : '' }}>
                                                {{ $sub->detail }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2 text-center">
                                    <div class="fw-bold mt-2">=</div>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" name="conversion[{{ $cIndex }}][qty]"
                                        class="form-control qty" placeholder="Qty"
                                        value="{{ rtrim(rtrim($conversion->qty, '0'), '.') }}">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control from_unit_text"
                                        value="{{ $detail->unitID->detail ?? '' }}" disabled>
                                    <input type="hidden" class="from_unit_id"
                                        name="conversion[{{ $cIndex }}][from_unit]"
                                        value="{{ $detail->unit_id }}">
                                </div>
                            </div>
                            <div class="mb-3 row {{ empty($conversion->sell_price) ? 'd-none' : '' }} grupsell">
                                <label class="col-md-4 col-form-label">Default Sell Price #{{ $cIndex + 1 }}</label>
                                <div class="col-md-8">
                                    <div class="input-group input-group-merge disabled-group">
                                        <span class="input-group-text ">{{ $mataUangDefault->symbol }}</span>
                                        <input type="number" name="sell_price[0][to_unit]"
                                            class="form-control sell_price" placeholder="0" min="0"
                                            value="{{ rtrim(rtrim($conversion->sell_price, '-'), '.') }}">
                                        <span class="input-group-text sellPrice"
                                            id="">{{ $conversion->fromUnitID->detail ?? '' }}</span>
                                    </div>
                                    <span class="error text-danger" id="sell_priceError"></span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="conversion-item border p-3 mb-2 rounded position-relative">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-label-secondary conversion-number">Unit #1</span>
                            <button type="button" class="btn btn-sm btn-text-danger btn-remove-conversion p-1">
                                <i class="ti ti-trash fs-5"></i>
                            </button>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <select name="conversion[0][to_unit]" class="form-select to_unit">
                                    <option value="">Select</option>
                                    @foreach ($sub_unit as $sub)
                                        <option value="{{ $sub->id }}">{{ $sub->detail }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 text-center">
                                <div class="fw-bold mt-2">=</div>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="conversion[0][qty]" class="form-control qty"
                                    placeholder="Qty">
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control from_unit_text"
                                    value="{{ $detail->unitID->detail ?? '' }}" disabled>
                                <input type="hidden" name="conversion[0][from_unit]" class="from_unit_id"
                                    value="{{ $detail->unit_id }}">
                            </div>
                        </div>
                        <div class="mb-3 row d-none grupsell">
                            <label class="col-md-4 col-form-label">Default Sell Price #2</label>
                            <div class="col-md-8">
                                <div class="input-group input-group-merge disabled-group">
                                    <span class="input-group-text ">{{ $mataUangDefault->symbol }}</span>
                                    <input type="number" name="sell_price[1][to_unit]"
                                        class="form-control sell_price" placeholder="0" min="0"
                                        value="">
                                    <span class="input-group-text sellPrice"
                                        id="">{{ $conversion->fromUnitID->detail ?? '' }}</span>
                                </div>
                                <span class="error text-danger" id="sell_priceError"></span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>

    <div class="col-lg-6" id="supplyForm">
        <h6><strong>Sales Information</strong></h6>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Default Discount </label>
            <div class="col-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-tag"></i></span>
                    <input type="number" id="default_discount" name="default_discount" class="form-control"
                        placeholder="0" min="0" value="{{ $detail->default_discount }}">
                    <span class="input-group-text">%</span>
                </div>
                <span class="error text-danger" id="default_discountError"></span>
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Default Sell Price </label>
            <div class="col-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text">{{ $mataUangDefault->symbol }}</span>
                    <input type="number" id="default_price" name="default_price" class="form-control"
                        placeholder="0" min="0" value="{{ $detail->default_price }}">
                    <span class="input-group-text" id="sellPrice">/ {{ $detail->unitID->detail ?? '' }}</span>
                </div>
                <span class="error text-danger" id="default_priceError"></span>
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Selling Minimum </label>
            <div class="col-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-tag-minus"></i></span>
                    <input type="number" id="selling_minimun" name="selling_minimun" class="form-control"
                        placeholder="0" min="0" value="{{ $detail->selling_minimun }}">
                    <span class="input-group-text" id="selMin">/ {{ $detail->unitID->detail ?? '' }}</span>
                </div>
                <span class="error text-danger" id="selling_minimunError"></span>
            </div>
        </div>
        <h6><strong>Purchase Information</strong></h6>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Primary Supplier</label>
            <div class="col-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-user"></i></span>
                    <select name="primary_supplier_id" id="primary_supplier_id" class="form-select select2"
                        data-placeholder="Select Supplier">
                        <option></option>
                        @foreach ($supplier as $sup)
                            <option value="{{ $sup->id }}"
                                {{ old('primary_supplier_id', $detail->primary_supplier_id ?? '') == $sup->id ? 'selected' : '' }}>
                                {{ $sup->nama_supplier }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <span class="error text-danger" id="primary_supplier_idError"></span>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Unit</label>
            <div class="col-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-list-details"></i></span>
                    <select name="primary_unit_id" id="primary_unit_id" class="form-select select2"
                        data-placeholder="Select Unit">
                        <option></option>
                        @foreach ($unit as $sat)
                            <option value="{{ $sat->id }}"
                                {{ old('primary_unit_id', $detail->primary_unit_id ?? '') == $sat->id ? 'selected' : '' }}>
                                {{ $sat->detail }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <span class="error text-danger" id="primary_unit_idError"></span>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Price/Unit</label>
            <div class="col-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text"> {{ $mataUangDefault->symbol }}</span>
                    <input type="number" id="primary_price" name="primary_price" class="form-control"
                        placeholder="0" value="{{ old('primary_price', $detail->primary_price ?? '') }}"
                        min="0">
                </div>
                <span class="error text-danger" id="primary_priceError"></span>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Minimum Order Quantity</label>
            <div class="col-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text"> <i class="ti ti-arrow-bar-to-down"></i></span>
                    <input class="form-control" type="number" id="primary_minimum_order"
                        name="primary_minimum_order" placeholder="0"
                        value="{{ old('primary_minimum_order', $detail->primary_minimum_order ?? '') }}"
                        min="0">
                </div>
                <span class="error text-danger" id="primary_minimum_orderError"></span>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Minimum Stock Threshold</label>
            <div class="col-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text"> <i class="ti ti-arrow-bar-to-down"></i></span>
                    <input type="number" name="primary_minimum_stock" id="primary_minimum_stock"
                        class="form-control" placeholder="0"
                        value="{{ old('primary_minimum_stock', $detail->primary_minimum_stock ?? '') }}"
                        min="0">
                </div>
                <span class="error text-danger" id="primary_minimum_stockError"></span>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.qty').forEach(function(input) {
                if (input.value == 0 || input.value == '0.0000') {
                    input.value = '';
                }
            });
        });
    </script>
@endpush
