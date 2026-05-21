<table class="table table-bordered " id="table_exchange_rate">
    <thead style="background-color: #AEDEFC; ">
        <tr>
            <th>#</th>
            <th>From</th>
            <th>To</th>
            <th>Rate</th>
            <th>Date Rate</th>
            <th>Created</th>
            <th>Updated</th>
        </tr>
    </thead>
</table>
<div class="modal fade" id="modalsExchangeRate">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="mb-2" id="modal-titleExchangeRate">Add Exchange Rate</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="postFormExchangeRate" method="POST" action="{{ route('exchange-rate.store') }}">
                @csrf
                <input type="hidden" name="id" id="idExchangeRate">

                <div class="modal-body">
                    <div class="row">

                        <!-- FROM -->
                        <div class="col-12 mb-3">
                            <label class="form-label">From<small>*</small></label>
                            <select name="from_currency_id" id="from_currency_id" class="form-control">
                                <option value="">-- Select Currency --</option>
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}">
                                        {{ $currency->code }} - {{ $currency->name }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="text-danger" id="from_currency_idError"></span>
                        </div>

                        <!-- TO -->
                        <div class="col-12 mb-3">
                            <label class="form-label">To<small>*</small></label>
                            <select name="to_currency_id" id="to_currency_id" class="form-control">
                                <option value="">-- Select Currency --</option>
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}">
                                        {{ $currency->code }} - {{ $currency->name }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="text-danger" id="to_currency_idError"></span>
                        </div>

                        <!-- RATE -->
                        <div class="col-12 mb-3">
                            <label class="form-label">Rate<small>*</small></label>
                            <input type="number" step="0.000001" id="rate" name="rate" class="form-control">
                            <span class="text-danger" id="rateError"></span>
                        </div>

                        <!-- DATE -->
                        <div class="col-12 mb-3">
                            <label class="form-label">Date Rate<small>*</small></label>
                            <input type="date" id="rate_date" name="rate_date" class="form-control"
                                value="{{ date('Y-m-d') }}">
                            <span class="text-danger" id="rate_dateError"></span>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="submit" id="savedataExchangeRate" class="btn btn-primary">
                        Save
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


@push('scripts')
    <script>
        $(document).ready(function() {
            var table_exchange_rate = new DataTable('#table_exchange_rate', {
                processing: true,
                serverSide: true,
                responsive: true,
                select: true,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All']
                ],
                ajax: '{{ route('exchange-rate.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'from_currency_id',
                    },
                    {
                        data: 'to_currency_id',
                    },
                    {
                        data: 'rate',
                    },
                    {
                        data: 'rate_date',
                    },

                    {
                        data: 'created_at',
                    },
                    {
                        data: 'updated_at',
                    },

                ],
                layout: {
                    topStart: {
                        buttons: [{
                            text: '<i class="ti ti-plus me-1"></i> Add Data',
                            className: 'btn btn-primary btn-sm me-2',
                            action: function(e, dt, node, config) {
                                $('#modalsExchangeRate').modal('show');
                                // Sesuaikan id element judul modalnya
                                $('#modal-titleExchangeRate').html('Add Exchange Rate');
                                $('#savedataExchangeRate').html(
                                    '<i class="fa fa-save me-1"></i> Save');
                                $('#postFormExchangeRate').trigger('reset');
                                $('#idExchangeRate').val('');
                                resetValidation();
                            }
                        }, {
                            text: '<i class="ti ti-edit me-1"></i> Edit',
                            className: 'btn btn-warning btn-sm me-2',
                            extend: 'selectedSingle',
                            action: function(e, dt, node, config) {

                                // 1. Ambil data row terpilih
                                let selectedData = dt.row({
                                    selected: true
                                }).data();

                                if (!selectedData) {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Pilih data terlebih dahulu!'
                                    });
                                    return;
                                }

                                let id = selectedData.id;

                                // 2. Reset form
                                let form = $('#postFormExchangeRate');
                                form.trigger('reset');

                                if (typeof resetValidation === "function") {
                                    resetValidation();
                                }

                                // 3. Set modal awal
                                $('#modal-titleExchangeRate').html('Edit Exchange Rate');
                                $('#savedataExchangeRate')
                                    .html(
                                        '<i class="fa fa-spinner fa-spin me-1"></i> Loading...')
                                    .prop('disabled', true);

                                $('#modalsExchangeRate').modal('show');

                                // 4. Ajax ambil data
                                $.ajax({
                                    type: "GET",
                                    url: `/exchange-rate/${id}/edit`,
                                    dataType: 'json',

                                    success: function(data) {

                                        // 5. Aktifkan tombol kembali
                                        $('#savedataExchangeRate')
                                            .html(
                                                '<i class="fa fa-save me-1"></i> Update'
                                            )
                                            .prop('disabled', false);

                                        // 6. Isi form (SESUAI FIELD BARU)
                                        $('#idExchangeRate').val(data.id);

                                        $('select[name="from_currency_id"]')
                                            .val(data.from_currency_id)
                                            .trigger('change');

                                        $('select[name="to_currency_id"]')
                                            .val(data.to_currency_id)
                                            .trigger('change');

                                        $('#rate').val(parseFloat(data.rate)
                                            .toFixed(2));

                                        $('#rate_date').val(data.rate_date);

                                    },

                                    error: function() {
                                        $('#modalsExchangeRate').modal('hide');

                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Failed',
                                            text: 'Gagal mengambil data exchange rate.'
                                        });
                                    }
                                });
                            }
                        }, {
                            text: '<i class="ti ti-trash me-1"></i> Delete',
                            className: 'btn btn-danger btn-sm me-2',
                            extend: 'selectedSingle', // Tombol otomatis menyala jika ada 1 baris dipilih
                            action: function(e, dt, node, config) {
                                // 1. Ambil data baris yang di-select
                                var selectedData = dt.row({
                                    selected: true
                                }).data();
                                if (!selectedData) return;

                                var id = selectedData.id;
                                var name = selectedData.from_currency_id;
                                let token = $("meta[name='csrf-token']").attr("content");

                                // 2. Jalankan SweetAlert Konfirmasi
                                Swal.fire({
                                    title: 'Are you sure?',
                                    text: "Want to delete data: " + name,
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Yes, delete it!',
                                    cancelButtonText: 'Cancel',
                                    customClass: {
                                        confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
                                        cancelButton: 'btn btn-label-secondary waves-effect waves-light'
                                    },
                                    buttonsStyling: false
                                }).then(function(result) {
                                    if (result.isConfirmed) {
                                        $.ajax({
                                            url: `/exchange-rate/${id}`,
                                            type: "DELETE",
                                            data: {
                                                _token: token
                                            },
                                            success: function(response) {
                                                // Dipicu jika status code 200 (Berhasil Hapus)
                                                dt.draw();
                                                toastr.success(response
                                                    .message, '', {
                                                        timeOut: 1500,
                                                        progressBar: true,
                                                    });
                                            },
                                            error: function(xhr) {
                                                // Dipicu jika status code 422 atau 500 (Gagal karena Foreign Key, dll)
                                                let errorTitle =
                                                    'Failed to delete data!';
                                                let errorMessage =
                                                    'An error occurred. Please try again.';

                                                // Ambil pesan kustom dari controller jika ada
                                                if (xhr.responseJSON && xhr
                                                    .responseJSON.message) {
                                                    errorMessage = xhr
                                                        .responseJSON
                                                        .message;
                                                }

                                                Swal.fire({
                                                    icon: 'error',
                                                    title: errorTitle,
                                                    html: `<strong>${errorMessage}</strong>`,
                                                    customClass: {
                                                        confirmButton: 'btn btn-primary waves-effect waves-light'
                                                    },
                                                    buttonsStyling: false
                                                });
                                            }
                                        });
                                    }
                                });
                            }
                        }]
                    }
                }
            });

            $('#postFormExchangeRate').on('submit', function(e) {
                e.preventDefault();
                var form = this;
                $.ajax({
                    url: $(form).attr('action'),
                    method: $(form).attr('method'),
                    data: new FormData(form),
                    processData: false,
                    contentType: false,
                    datatype: 'json',
                    beforeSend: function(e) {
                        $('#savedataExchangeRate').html(
                            '<i class="fa fa-spin fa-spinner me-1"></i> Sending...');
                    },
                    complete: function(e) {
                        $('#savedataExchangeRate').html(' <i class="fa fa-save me-1"></i>Save');
                    },
                    success: function(response) {
                        $('#modalsExchangeRate').modal('hide');
                        table_exchange_rate.draw();
                        Swal.fire({
                            icon: 'success',
                            title: response.title,
                            text: response.message,
                            showClass: {
                                popup: 'animate__animated animate__bounceIn'
                            },
                            customClass: {
                                confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                            buttonsStyling: false
                        });

                    },
                    error: function(xhr) {

                        resetValidation();

                        let message = 'Terjadi kesalahan';

                        if (xhr.responseJSON) {

                            // jika ada message dari controller
                            if (xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }

                            // jika error validasi
                            if (xhr.responseJSON.errors) {

                                let errors = xhr.responseJSON.errors;
                                let errorList = '';

                                $.each(errors, function(key, value) {
                                    errorList += value[0] + '<br>';
                                    displayFieldError(key, value[0]);
                                });

                                message = errorList;
                            }
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            html: message,
                            customClass: {
                                confirmButton: 'btn btn-danger'
                            },
                            buttonsStyling: false
                        });
                    }
                });
            });
        });
    </script>
@endpush
