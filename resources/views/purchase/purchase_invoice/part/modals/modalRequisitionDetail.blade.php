  <div class="modal fade" id="modalRequisitionDetail">
      <div class="modal-dialog modal-xl">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="modalTitle">Receive Item Processing</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                  <div class="row">
                      <div class="col-12 mb-3">
                          <label class="form-label" for="sq_number">Purchase Receive Item Number</label>
                          <select name="sq_number" id="sq_number" class="form-select select2-modal2"
                              data-placeholder="Select Receive Item Number" multiple>
                              <option></option>
                              @foreach ($number as $item)
                                  <option value="{{ $item->id }}">{{ $item->code }}</option>
                              @endforeach
                          </select>
                          <span class="error text-danger" id="sq_numberError"></span>
                      </div>
                      <table class="table table-bordered" id="quotationTable">
                          <thead>
                              <tr>
                                  <th>
                                      <div class="form-check form-check-primary">
                                          <input class="form-check-input" type="checkbox" id="checkAll">
                                      </div>
                                  </th>
                                  <th>Item</th>
                                  <th>Quantity</th>
                                  <th>Unit</th>
                                  <th>Warehouse</th>
                                  <th>Price</th>
                              </tr>
                          </thead>
                          <tbody id="quotationTableBody">
                          </tbody>
                      </table>
                  </div>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="button" class="btn btn-primary" id="btnSubmitSelected">
                      <i class="ti ti-check me-1"></i> Process Selected
                  </button>
              </div>
          </div>
      </div>
  </div>
