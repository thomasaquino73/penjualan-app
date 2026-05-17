<table class="table table-bordered " id="table_bank">
    <thead style="background-color: #AEDEFC; ">
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Currency</th>
            <th>Created</th>
            <th>Updated</th>
        </tr>
    </thead>
</table>
<div class="modal fade" id="modalsBank">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="mb-2" id="modal-titleBank"></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="postFormBank" name="postFormBank" method="POST" action="{{ route('cash-bank.store') }}">
                    @csrf
                    <input type="hidden" name="id" id="idBank">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="name" class="form-label">Name<small>*</small></label>
                            <input type="text" id="nameBank" name="name" class="form-control"
                                placeholder="Enter Name">
                            <span class="error text-danger" id="nameError"></span>

                        </div>
                        <div class="col-12 mb-3">
                            <label for="currency_id" class="form-label">Currency<small>*</small></label>
                            <select name="currency_id" id="currency_idBank" class="form-control">
                                <option value="" selected hidden>Select Currency</option>
                                @foreach ($currencies as $c_bank)
                                    {{-- <--- Ubah $currency menjadi $c_bank --}}
                                    <option value="{{ $c_bank->id }}">{{ $c_bank->name }}</option>
                                @endforeach
                            </select>
                            <span class="error text-danger" id="currency_idBankError"></span>
                        </div>

                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary waves-effect" data-bs-dismiss="modal">
                    Close
                </button>
                <button type="submit" id="savedataBank" name="savedataBank" class="btn btn-primary me-sm-3 me-1">
                </button>
            </div>
            </form>
        </div>
    </div>
</div>


@push('scripts')
    <script>
        $(document).ready(function() {
            var table_bank = new DataTable('#table_bank', {
                processing: true,
                serverSide: true,
                responsive: true,
                select: true,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All']
                ],
                ajax: '{{ route('cash-bank.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                    },
                    {
                        data: 'currency',
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
                                $('#modalsBank').modal('show');
                                // Sesuaikan id element judul modalnya
                                $('#modal-titleBank').html('Add Cash & Bank');
                                $('#savedataBank').html('<i class="fa fa-save me-1"></i> Save');
                                $('#postFormBank').trigger('reset');
                                $('#idBank').val('');
                                resetValidation();
                            }
                        }, {
                            text: '<i class="ti ti-edit me-1"></i> Edit',
                            className: 'btn btn-warning btn-sm me-2',
                            extend: 'selectedSingle',
                            action: function(e, dt, node, config) {
                                // 1. Ambil data row yang sedang dipilih/dicentang
                                var selectedData = dt.row({
                                    selected: true
                                }).data();

                                if (!selectedData) {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Pilih data terlebih dahulu!'
                                    });
                                    return;
                                }

                                // Ambil ID dari row data tersebut
                                var id = selectedData.id;

                                // 2. Reset form modal lama dan persiapkan teks loading
                                $('#postFormBank').trigger('reset');
                                if (typeof resetValidation === "function") {
                                    resetValidation();
                                }

                                $('#modal-titleBank').html('Edit Cash & Bank');
                                $('#savedataBank').html(
                                    '<i class="fa fa-spinner fa-spin me-1"></i> Loading...');
                                $('#modalsBank').modal('show');

                                $.ajax({
                                    type: "GET",
                                    url: "/cash-bank/" + id +
                                        "/edit", // Parameter ID masuk ke URL
                                    dataType: 'json',
                                    success: function(data) {
                                        $('#savedataBank').html(
                                            '<i class="fa fa-save me-1"></i> Update'
                                        );

                                        // 4. Isi field form modal sesuai dengan property object data dari database
                                        $('#idBank').val(data.id);
                                        $('#nameBank').val(data.name);
                                        $('#currency_idBank').val(data.currency_id)
                                            .trigger('change');

                                    },
                                    error: function() {
                                        $('#modalsBank').modal('hide');
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Gagal',
                                            text: 'Gagal mengambil data mata uang dari server.'
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
                                var name = selectedData.name;
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
                                            url: `/cash-bank/${id}`,
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

            $('#postFormBank').on('submit', function(e) {
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
                        $('#savedatabank').html(
                            '<i class="fa fa-spin fa-spinner me-1"></i> Sending...');
                    },
                    complete: function(e) {
                        $('#savedatabank').html(' <i class="fa fa-save me-1"></i>Save');
                    },
                    success: function(response) {
                        $('#modalsBank').modal('hide');
                        table_bank.draw();
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
                            title: 'Gagal',
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
