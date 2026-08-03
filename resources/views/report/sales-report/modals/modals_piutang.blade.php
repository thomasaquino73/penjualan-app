  <div class="modal fade" id="modalpiutang" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">

              <form id="formPrintPiutang" method="GET" action="{{ route('sales-reports.print_piutang') }}">

                  <div class="modal-header">
                      <h5 class="modal-title">
                          Print Piutang
                      </h5>

                      <button type="button" class="btn-close" data-bs-dismiss="modal">
                      </button>
                  </div>

                  <div class="modal-body">
                      <div class="mb-3">
                          <label>Pilih Supplier</label>
                          <select class="form-select select2-modal" name="customer_id" id="customer_id"
                              data-placeholder="Pilih Supplier">
                              <option></option>
                              @foreach ($customers as $customer)
                                  <option value="{{ $customer->id }}">{{ $customer->nama_customer }}</option>
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
