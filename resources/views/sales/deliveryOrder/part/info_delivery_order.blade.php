<div class="row">
    <div class="col-md-6">
        <h6><strong>Additional Information</strong></h6>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Document Number</label>
            <div class="col-md-8">
                <input name="no_document" id="no_document" class="form-control" placeholder="Enter Document Number">
                <span class="error text-danger" id="no_documentError"></span>
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
        <h6><strong>Others Information</strong></h6>
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
