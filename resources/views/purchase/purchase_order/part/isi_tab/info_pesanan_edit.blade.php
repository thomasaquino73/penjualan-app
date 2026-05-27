<div class="row">
    <div class="col-md-6">
        <h6><strong>Delivery Information</strong></h6>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Shipment Date</label>
            <div class="col-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-calendar"></i> </span>
                    <input type="text" name="tanggal_kirim" id="tanggal_kirim" class="form-control"
                        placeholder="DD/MM/YYYY" value="{{ Carbon\Carbon::parse($model->tanggal_kirim) }}">
                    <span class="error text-danger" id="tanggal_kirimError"></span>
                </div>

            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Ship via</label>
            <div class="col-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text">
                        <i class="ti ti-truck"></i>
                    </span>
                    <select name="vehicle_id" id="vehicle_id" class="form-select select2">
                        <option value="">Select Shipping</option>
                        @foreach ($shipping as $item)
                            <option value="{{ $item->id }}" {{ $item->id == $model->vehicle_id ? 'selected' : '' }}>
                                {{ $item->nama }}
                            </option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-primary btn-sm" id="btnAddShipping">
                        <i class="ti ti-plus"></i>
                    </button>
                    <span class="error text-danger" id="vehicle_idError"></span>
                </div>

            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Payment Term</label>
            <div class="col-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-user"></i>
                    </span>
                    <select name="payment_term" id="payment_term" class="form-select select2 "
                        data-placeholder="Select Payment Term">
                        <option></option>
                        @foreach ($paymentTerm as $pay)
                            <option value="{{ $pay->id }}"
                                {{ $pay->id == $model->payment_term ? 'selected' : '' }}>{{ $pay->nama }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-primary btn-sm" id="btnAddTerm">
                        <i class="ti ti-plus"></i>
                    </button>
                </div>

                <span class="error text-danger" id="payment_termError"></span>
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">
                Address
                <span class="dropdown d-inline-block">
                    <button class="btn btn-sm  dropdown-toggle no-caret" type="button" id="btn-history-address"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-map me-1"></i>Choose Address
                    </button>
                    <ul class="dropdown-menu shadow" id="address-dropdown-menu"
                        style="min-width: 320px; max-height: 250px; overflow-y: auto; background-color: #ffffff !important;">
                    </ul>
                </span>
            </label>

            <div class="col-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-map"></i></span>
                    <textarea name="shipping_address" id="shipping_address" rows="3" class="form-control">{{ $model->shipping_address }}</textarea>
                </div>
                <span class="error text-danger" id="shipping_addressError"></span>
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Description</label>
            <div class="col-md-8">
                <textarea name="description" id="description" class="form-control" rows="8" placeholder="Enter description"></textarea>
                <span class="error text-danger" id="descriptionError"></span>
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
                                id="kena_pajak" {{ $model->kena_pajak == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="kena_pajak">Including Tax</label>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="form-check form-check-primary">
                            <input class="form-check-input" type="checkbox" value="1" name="total_termasuk_pajak"
                                id="total_termasuk_pajak" {{ $model->total_termasuk_pajak == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="total_termasuk_pajak">Total Including Tax</label>
                        </div>
                    </div>
                </div>

            </div>
        </div>



        <h6><strong>Other Information</strong></h6>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">FOB</label>
            <div class="col-md-8">
                <select name="fob_id" id="fob_id" class="form-select select2" data-placeholder="Select FOB">
                    <option></option>
                    @foreach ($fob as $f)
                        <option value="{{ $f->detail }}"> {{ $f->detail }}</option>
                    @endforeach
                </select>
                <span class="error text-danger" id="fob_idError"></span>
            </div>
        </div>
    </div>
</div>
@push('scripts')
@endpush
