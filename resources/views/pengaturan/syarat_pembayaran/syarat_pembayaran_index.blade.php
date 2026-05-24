@extends('layouts.app')
@section('konten')
    <h4><span class="text-muted fw-light">
            @foreach ($breadcrumb as $key => $item)
                @if (!empty($item['url']))
                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                @else
                    {{ $item['label'] }}
                @endif

                @if (!$loop->last)
                    /
                @endif
            @endforeach
        </span>
    </h4>

    {{-- <div class="row"> --}}
    <div class="card ">
        <div
            class="card-header d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center">

            <h5 class="card-title mb-2 mb-lg-0">{{ $title }}</h5>

            <div class="col-12 col-lg-5">

            </div>

        </div>

        <div class="card-datatable table-responsive" style="padding: 20px">
            <table class="datatables-ajax table" id="table">
                <thead style="background-color: #AEDEFC; ">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Discount(%)</th>
                        <th>Discount Periode(day)</th>
                        <th>Due Date(day)</th>
                        <th>Description</th>
                        <th>status</th>
                        <th>default</th>
                        <th>Created</th>
                        <th>Updated</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <div class="modal fade" id="modals" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="mb-2" id="modal-title"></h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="postForm" name="postForm" method="POST" action="{{ route('company-delivery.store') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="text" name="id" id="id" hidden>
                        <div class="row">
                            <div class="col-md-12 col-sm-12 mb-3">
                                <label for="nama" class="form-label">Name<small>*</small></label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti ti-user"></i></span>
                                    <input type="text" id="nama" name="nama" class="form-control" placeholder="">
                                </div>
                                <span class="error text-danger" id="namaError"></span>

                            </div>
                            <div class="col-md-12 col-sm-12 mb-3">
                                <label for="masa_diskon" class="form-label">If paying within (day)</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                                    <input type="text" id="masa_diskon" name="masa_diskon" class="form-control"
                                        placeholder="">
                                    <span class="input-group-text">Day</span>
                                </div>
                                <span class="error text-danger" id="masa_diskonError"></span>

                            </div>
                            <div class="col-md-12 col-sm-12 mb-3">
                                <label for="discount" class="form-label">Eligible for a discount</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti ti-moneybag"></i></span>
                                    <input type="text" id="discount" name="discount" class="form-control"
                                        placeholder="">
                                    <span class="input-group-text"><i class="ti ti-percentage"></i></span>
                                </div>
                                <span class="error text-danger" id="discountError"></span>

                            </div>
                            <div class="col-md-12 col-sm-12 mb-3">
                                <label for="masa_jatuh_tempo" class="form-label">Due Date</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                                    <input type="text" id="masa_jatuh_tempo" name="masa_jatuh_tempo" class="form-control"
                                        placeholder="">
                                    <span class="input-group-text">Day</span>
                                </div>
                                <span class="error text-danger" id="masa_jatuh_tempoError"></span>

                            </div>
                            <div class="col-md-12 col-sm-12 mb-3">
                                <label for="keterangan" class="form-label">Description</label>
                                <textarea type="text" id="keterangan" name="keterangan" class="form-control" placeholder=""></textarea>
                            </div>
                            <span class="error text-danger" id="keteranganError"></span>

                            <div class="col-md-6 col-sm-12 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="" selected hidden>Select Status</option>
                                    <option value="1">Active</option>
                                    <option value="2">Inactive</option>
                                </select>
                                <span class="error text-danger" id="statusError"></span>
                            </div>
                        </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary waves-effect" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="submit" id="savedata" name="savedata" class="btn btn-primary me-sm-3 me-1">
                        Save
                    </button>
                </div>
                </form>

            </div>
        </div>
    </div>
@endsection
@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/2.0.3/css/select.bootstrap5.css">
@endpush
@push('scripts')
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.js"></script>

    <script src="https://cdn.datatables.net/select/3.1.3/js/dataTables.select.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.3/js/select.bootstrap5.js"></script>

    <script>
        $(document).ready(function() {
            var table = new DataTable('#table', {
                processing: true,
                serverSide: true,
                responsive: true,
                select: true,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All']
                ],
                ajax: '{{ route('syarat-pembayaran.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'nama',
                    },
                    {
                        data: 'diskon',
                    },
                    {
                        data: 'masa_diskon',
                    },
                    {
                        data: 'masa_jatuh_tempo',
                    },
                    {
                        data: 'keterangan',
                    },
                    {
                        data: 'default',
                    },
                    {
                        data: 'status',
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
                                $('#modals').modal('show');
                                // Sesuaikan id element judul modalnya
                                $('#modal-title').html('Add Payment Term');
                                $('#savedata').html('<i class="fa fa-save me-1"></i> Save');
                                $('#postForm').trigger('reset');
                                $('#id').val('');
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
                                $('#postForm').trigger('reset');
                                if (typeof resetValidation === "function") {
                                    resetValidation();
                                }

                                $('#modal-title').html('Edit Cash & Bank');
                                $('#savedata').html(
                                    '<i class="fa fa-spinner fa-spin me-1"></i> Loading...');
                                $('#modals').modal('show');

                                $.ajax({
                                    type: "GET",
                                    url: "/cash-bank/" + id +
                                        "/edit", // Parameter ID masuk ke URL
                                    dataType: 'json',
                                    success: function(data) {
                                        $('#savedata').html(
                                            '<i class="fa fa-save me-1"></i> Update'
                                        );

                                        // 4. Isi field form modal sesuai dengan property object data dari database
                                        $('#id').val(data.id);
                                        $('#bank_name').val(data.bank_name);
                                        $('#account_name').val(data.account_name);
                                        $('#account_number').val(data
                                            .account_number);


                                    },
                                    error: function() {
                                        $('#modals').modal('hide');
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Failed',
                                            text: 'Failed to fetch data kas & bank from the server.'
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

            $('#postForm').on('submit', function(e) {
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
                        $('#savedata').html(
                            '<i class="fa fa-spin fa-spinner me-1"></i> Sending...');
                    },
                    complete: function(e) {
                        $('#savedata').html(' <i class="fa fa-save me-1"></i>Save');
                    },
                    success: function(response) {
                        $('#modals').modal('hide');
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
