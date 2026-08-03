  <div class="modal fade" id="modalhutang" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">

              <form id="formPrintHutang" method="GET" action="{{ route('purchase-reports.print_hutang') }}">

                  <div class="modal-header">
                      <h5 class="modal-title">
                          Print Hutang
                      </h5>

                      <button type="button" class="btn-close" data-bs-dismiss="modal">
                      </button>
                  </div>

                  <div class="modal-body">
                      <div class="mb-3">
                          <label>Pilih Supplier</label>
                          <select class="form-select select2-modal" name="supplier_id" id="supplier_id"
                              data-placeholder="Pilih Supplier">
                              <option></option>
                              @foreach ($suppliers as $supplier)
                                  <option value="{{ $supplier->id }}">{{ $supplier->nama_supplier }}</option>
                              @endforeach
                          </select>
                      </div>
                      <div class="mb-3">
                          <label>Tanggal Awal</label>

                          <input type="text" class="form-control" name="start_date" id="start_date"
                              value="{{ date('Y-m-01') }}" required>
                      </div>

                      <div class="mb-3">
                          <label>Tanggal Akhir</label>

                          <input type="text" class="form-control" name="end_date" id="end_date"
                              value="{{ date('Y-m-d') }}" required>
                      </div>

                  </div>

                  <div class="modal-footer">

                      <button class="btn btn-primary">
                          Print
                      </button>

                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                          Cancel
                      </button>

                  </div>

              </form>

          </div>
      </div>
  </div>
