<div class="row">
    <div class="col-md-6">
        <h6><strong>Additional Information</strong></h6>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Address</label>
            <div class="col-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-map"></i></span>
                    <textarea name="address" id="address" rows="3" class="form-control">{{ $model->address }}</textarea>
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
    </div>
    <div class="col-md-6">
        <h6><strong>Shipment Information</strong></h6>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Shipment Date</label>
            <div class="col-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti ti-calendar"></i> </span>
                    <input type="text" name="tanggal_kirim" id="tanggal_kirim" class="form-control"
                        placeholder="DD/MM/YYYY"
                        value="{{ Carbon\Carbon::parse($model->tanggal_kirim)->format('d-m-Y') }}">
                </div>
            </div>
            <span class="error text-danger" id="tanggal_kirimError"></span>
        </div>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Ship via</label>
            <div class="col-md-8">
                <div class="input-group input-group-merge">
                    <span class="input-group-text">
                        <i class="ti ti-truck"></i>
                    </span>
                    <select name="shipping_id" id="shipping_id" class="form-select select2">
                        <option value="">Select Shipping</option>
                        @foreach ($shipping as $item)
                            <option value="{{ $item->id }}"
                                {{ $model->shipping_id == $item->id ? 'selected' : '' }}>
                                {{ $item->nama }}
                            </option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-primary btn-sm" id="btnAddShipping">
                        <i class="ti ti-plus"></i>
                    </button>
                </div>
                <span class="error text-danger" id="shipping_idError"></span>

            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">FOB</label>
            <div class="col-md-8">
                <select name="fob_id" id="fob_id" class="form-select select2" data-placeholder="Select FOB">
                    <option></option>
                    @foreach ($fob as $f)
                        <option value="{{ $f->detail }}"{{ $model->fob_id == $f->detail ? 'selected' : '' }}>
                            {{ $f->detail }}</option>
                    @endforeach
                </select>
                <span class="error text-danger" id="fob_idError"></span>
            </div>
        </div>
    </div>

</div>

@push('scripts')
@endpush
