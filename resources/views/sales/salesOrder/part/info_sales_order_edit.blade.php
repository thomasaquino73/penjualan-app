<div class="row">
    <div class="col-md-6">
        <h6><strong>Additional Information</strong></h6>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Payment Term</label>
            <div class="col-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-credit-card"></i> </span>
                    <select name="payment_term_id" id="payment_term_id" class="form-select select2">
                        <option></option>
                        @foreach ($paymentTerm as $pay)
                            <option value="{{ $pay->id }}"
                                {{ $model->payment_term_id == $pay->id ? 'selected' : '' }}>{{ $pay->nama }}
                            </option>
                        @endforeach
                        <option></option>
                    </select>
                    <button type="button" class="btn btn-primary btn-sm" id="btnAddTerm">
                        <i class="ti ti-plus"></i>
                    </button>
                </div>
                <span class="error text-danger" id="payment_term_idError"></span>

            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">PO Number</label>
            <div class="col-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text">
                        <i class="ti ti-file-code-2"></i>
                    </span>
                    <input type="text" name="po_number" id="po_number" class="form-control"
                        value="{{ $model->po_number }}">
                </div>
                <span class="error text-danger" id="po_numberError"></span>

            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Address</label>
            <div class="col-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text">
                        <i class="ti ti-map"></i>
                    </span>
                    <textarea name="address" id="address" class="form-control" placeholder="Enter address">{{ $model->address }}</textarea>
                </div>
                <span class="error text-danger" id="addressError"></span>

            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Description</label>
            <div class="col-md-8">
                <textarea name="description" id="description" class="form-control" rows="8" placeholder="Enter description">{{ $model->description }}</textarea>
                <span class="error text-danger" id="descriptionError"></span>
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Contact</label>
            <div class="col-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-user"></i> </span>
                    <select name="customer_contact_id" id="customer_contact_id" class="form-select">
                        <option></option>
                    </select>
                    <span class="error text-danger" id="customer_contact_idError"></span>
                </div>

            </div>
        </div>
    </div>
    <div class="col-md-6">
        <h6><strong>Tax Information</strong></h6>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Tax</label>
            <div class="col-md-8">
                <div class="row">
                    <div class="col-6">
                        <div class="form-check form-check-primary">
                            <input class="form-check-input" type="checkbox" value="1" name="kena_pajak"
                                id="kena_pajak" {{ $model->kena_pajak ? 'checked' : '' }}>
                            <label class="form-check-label" for="kena_pajak">Including Tax</label>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="form-check form-check-primary">
                            <input class="form-check-input" type="checkbox" value="1" name="total_termasuk_pajak"
                                id="total_termasuk_pajak" {{ $model->total_termasuk_pajak ? 'checked' : '' }}>
                            <label class="form-check-label" for="total_termasuk_pajak">Total Including Tax</label>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <h6><strong>Shipment Information</strong></h6>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Shipping Date</label>
            <div class="col-md-8">
                <input name="shipping_date" id="shipping_date" class="form-control" placeholder="Enter shipping date"
                    value="{{ Carbon\Carbon::parse($model->tanggal_pengiriman)->format('d-m-Y') }}">
                <span class="error text-danger" id="shipping_dateError"></span>
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Ship via</label>
            <div class="col-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text">
                        <i class="ti ti-truck"></i>
                    </span>
                    <select name="jenis_pengiriman" id="jenis_pengiriman" class="form-select select2"
                        data-placeholder="Select Payment Term">
                        <option value="">Select Shipping</option>
                        @foreach ($shipping as $item)
                            <option value="{{ $item->id }}"
                                {{ $model->jenis_pengiriman == $item->id ? 'selected' : '' }}>
                                {{ $item->nama }}
                            </option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-primary btn-sm" id="btnAddShipping">
                        <i class="ti ti-plus"></i>
                    </button>
                    <span class="error text-danger" id="jenis_pengirimanError"></span>
                </div>

            </div>
        </div>
        <h6><strong>Others Information</strong></h6>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">FOB</label>
            <div class="col-md-8 mb-3">
                <select name="fob_id" id="fob_id" class="form-select select2" data-placeholder="Select FOB">
                    <option></option>
                    @foreach ($fob as $f)
                        <option value="{{ $f->detail }}" {{ $model->fob_id == $f->detail ? 'selected' : '' }}>
                            {{ $f->detail }}</option>
                    @endforeach
                </select>
                <span class="error text-danger" id="fob_idError"></span>
            </div>
            <div class="mb-3 " id="tax_container" style="display: none;">
                <div class="mb-3 row">
                    <label class="col-md-4 col-form-label">Tax</label>
                    <div class="col-md-8">
                        <select id="tax_id" name="tax_id" class="form-control select2"
                            data-placeholder="Select Tax">
                            <option></option>
                            @foreach ($taxes as $tax)
                                <option value="{{ $tax->id }}"
                                    {{ $defaultTax && $defaultTax->id == $tax->id ? 'selected' : '' }}>
                                    {{ $tax->tax_name }} ({{ $tax->percentage }}%)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-md-4 col-form-label">Taxpayer data</label>
                    <div class="col-md-8">
                        <input type="text" class="form-control" name="taxpayer_data" id="taxpayer_data"
                            value="{{ $model->taxpayer_data }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            $('#shipping_date').flatpickr({
                dateFormat: 'd-m-Y',
            });
        </script>
    @endpush
