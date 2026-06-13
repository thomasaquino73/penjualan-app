  <div class="modal fade" id="modalPrDetail">
      <div class="modal-dialog modal-md">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="modalTitle">Create new entry</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form id="formPrDetail">
                  @csrf
                  <input type="hidden" name="id" id="detail_id">
                  <input type="hidden" name="purchase_requisition_detail_id" id="modal_purchase_requisition_detail_id">
                  <div class="modal-body">
                      <div class="row">
                          <div class="col-12 mb-3">
                              <label class="form-label" for="product_id">Product / Item</label>
                              <select name="product_id" id="product_id" class="form-select select2-modal"
                                  data-placeholder="Select Product">
                                  <option></option>
                                  @foreach ($product as $product)
                                      <option value="{{ $product->id }}">{{ $product->nama_barang }}</option>
                                  @endforeach
                              </select>
                              <span class="error text-danger" id="product_idError"></span>
                          </div>
                          <div class="col-6 mb-3">
                              <label class="form-label" for="quantity">Quantity</label>
                              <input type="number" id="quantity" name="quantity" class="form-control" placeholder="0"
                                  min="0">
                              <span class="error text-danger" id="quantityError"></span>
                          </div>
                          <div class="col-6 mb-3">
                              <label class="form-label" for="unit_id">Unit</label>
                              <select name="unit_id" id="unit_id" class="form-select select2-modal "
                                  data-placeholder="Select Unit">
                                  <option></option>
                              </select>
                              <span class="error text-danger" id="unit_idError"></span>
                          </div>
                          <div class="col-12 mb-3">
                              <label class="form-label" for="warehouse_id">Warehouse</label>
                              <select name="warehouse_id" id="warehouse_id" class="form-select select2-modal"
                                  data-placeholder="Select Warehouse">
                                  <option></option>
                                  @foreach ($warehouse as $warehouse)
                                      <option value="{{ $warehouse->id }}">{{ $warehouse->nama_gudang }}</option>
                                  @endforeach
                              </select>
                              <span class="error text-danger" id="warehouse_idError"></span>
                          </div>
                      </div>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                      <button type="submit" class="btn btn-primary" id="btnSubmitModal">Create</button>
                  </div>
              </form>
          </div>
      </div>
  </div>
