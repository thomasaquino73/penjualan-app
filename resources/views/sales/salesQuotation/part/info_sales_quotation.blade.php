<div class="row">
    <div class="col-md-6">
        <h6><strong>Additional Information</strong></h6>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Payment Term</label>
            <div class="col-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-credit-card"></i> </span>
                    <select name="payment_term_id" id="payment_term_id" class="form-select">
                        <option></option>
                        @foreach ($paymentTerm as $pay)
                            <option value="{{ $pay->id }}">{{ $pay->nama }}</option>
                        @endforeach
                        <option></option>
                    </select>
                </div>
                <span class="error text-danger" id="payment_term_idError"></span>

            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Address</label>
            <div class="col-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text">
                        <i class="ti ti-map"></i>
                    </span>
                    <textarea name="address" id="address" class="form-control" placeholder="Enter address"></textarea>
                </div>
                <span class="error text-danger" id="addressError"></span>

            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Description</label>
            <div class="col-md-8">
                <textarea name="description" id="description" class="form-control" rows="8" placeholder="Enter description"></textarea>
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
                                id="kena_pajak">
                            <label class="form-check-label" for="kena_pajak">Including Tax</label>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="form-check form-check-primary">
                            <input class="form-check-input" type="checkbox" value="1" name="total_termasuk_pajak"
                                id="total_termasuk_pajak">
                            <label class="form-check-label" for="total_termasuk_pajak">Total Including Tax</label>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="mb-3 " id="tax_container" style="display: none;">
            <div class="mb-3 row">
                <label class="col-md-4 col-form-label">Tax</label>
                <div class="col-md-8">
                    <select id="tax_id" name="tax_id" class="form-control select2" data-placeholder="Select Tax">
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
                    <input type="text" class="form-control" name="taxpayer_data" id="taxpayer_data">
                </div>
            </div>
        </div>
    </div>

</div>
@push('scripts')
@endpush
