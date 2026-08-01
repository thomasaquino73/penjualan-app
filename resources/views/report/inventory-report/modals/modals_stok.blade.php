  <div class="modal fade" id="modalstok" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">

              <form id="formPrintStock" method="GET" action="{{ route('data-barang.print_all') }}">

                  <div class="modal-header">
                      <h5 class="modal-title">
                          Print Semua Barang
                      </h5>

                      <button type="button" class="btn-close" data-bs-dismiss="modal">
                      </button>
                  </div>

                  <div class="modal-body">

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
