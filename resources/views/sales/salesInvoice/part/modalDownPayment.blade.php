<div class="modal fade" id="modalDownPayment">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Down Payment Processing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label" for="dp_number">Down Payment Number</label>
                        <select name="dp_number" id="dp_number" class="form-select select2-modaldp"
                            data-placeholder="Select Down Payment Number" multiple>
                            <option></option>

                        </select>
                        <span class="error text-danger" id="dp_numberError"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btnSubmitDp">
                    <i class="ti ti-check me-1"></i> Process Selected
                </button>
            </div>
        </div>
    </div>
</div>
