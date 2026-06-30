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
                  <input type="hidden" name="sales_quotation_detail_id" id="modal_sales_quotation_detail_id">
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
                          <div class="col-md-6 col-sm-12 mb-3">
                              <label class="form-label" for="quantity">Quantity</label>
                              <input type="number" id="quantity" name="quantity" class="form-control" placeholder="0"
                                  min="0">
                              <span class="error text-danger" id="quantityError"></span>
                          </div>
                          <div class="col-md-6 col-sm-12 mb-3">
                              <label class="form-label" for="unit_id">Unit</label>
                              <select name="unit_id" id="unit_id" class="form-select select2-modal "
                                  data-placeholder="Select Unit">
                                  <option></option>
                              </select>
                              <span class="error text-danger" id="unit_idError"></span>
                          </div>
                          <div class="col-md-12 col-sm-12 mb-3">
                              <label class="form-label" for="unit_price">Unit Price</label>
                              <div class="input-group input-group-merge">
                                  <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>

                                  <!-- Input Box Utama -->
                                  <input type="number" id="unit_price" name="unit_price" class="form-control" step="any"
                                     min="0" >

                                  <!-- Tombol Dropdown History Terintegrasi -->

                                  <button class="btn btn-outline-secondary dropdown-toggle no-caret" type="button"
                                      id="btn-history-po" data-bs-toggle="dropdown" data-bs-placement="top"
                                      data-bs-original-title="History" disabled>
                                      <i class="ti ti-history"></i> <!-- Menggunakan icon history biar lebih pas -->
                                  </button>

                                  <!-- Wadah List History Harga -->
                                  <ul class="dropdown-menu dropdown-menu-end" id="po-price-dropdown-menu"
                                      style="max-width: 250px;">
                                      <!-- Diisi via JavaScript -->
                                  </ul>
                              </div>

                              <span class="error text-danger" id="unit_priceError"></span>
                              <small id="po-history-helper" class="form-text text-muted" style="font-size: 11px;">Pilih
                                  produk untuk melacak riwayat harga beli.</small>
                          </div>
                          <div class="col-md-12 col-sm-12  mb-3">
                              <label class="form-label" for="discount">Discount</label>
                              <div class="row">
                                  <div class="col-lg-4 col-sm-12">
                                      <div class="input-group input-group-merge">
                                          <input type="text" id="discount_percent" name="discount_percent"
                                              class="form-control" placeholder="0" min="0">
                                          <span class="input-group-text"><i class="ti ti-percentage"></i></span>
                                      </div>
                                  </div>
                                  <div class="col-lg-8 col-sm-12">
                                      <div class="input-group input-group-merge">
                                          <span class="input-group-text">{{ $company->symbol ?? '' }}</span>
                                          <input type="number" id="discount" name="discount" class="form-control"
                                              step="any" placeholder="0">
                                      </div>

                                  </div>
                              </div>
                              <span class="error text-danger" id="discountError"></span>
                          </div>
                          <div class="col-12 mb-3">
                              <label class="form-label" for="total_price">Total Price</label>
                              <input type="number" id="total_price" name="total_price" class="form-control"
                                  placeholder="0" readonly>
                              <span class="error text-danger" id="total_priceError"></span>
                          </div>
                          <div class="col-md-6 col-sm-12  mb-3">
                              <label class="form-label" for="warehouse_id">Warehouse</label>
                              <select name="warehouse_id" id="warehouse_id" class="form-select select2-modal"
                                  data-placeholder="Select Warehouse">
                                  <option></option>
                                  @foreach ($warehouse as $wh)
                                      <option value="{{ $wh->id }}">{{ $wh->nama_gudang }}</option>
                                  @endforeach
                              </select>
                              <span class="error text-danger" id="warehouse_idError"></span>
                          </div>
                          <div class="col-md-6 col-sm-12 mb-3">
                              <label class="form-label" for="available_stok">Available Stock</label>
                              <input type="number" id="available_stok" name="available_stok" class="form-control"
                                  readonly style="background-color: beige">
                              <span class="error text-danger" id="available_stokError"></span>
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
