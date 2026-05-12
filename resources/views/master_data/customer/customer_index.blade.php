@extends('layouts.app')
@section('konten')
    <h4>
        <span class="text-muted fw-light">
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

    <div class="card">
        <div
            class="card-header d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center">

            <h5 class="card-title mb-2 mb-lg-0">{{ $title }}</h5>

            <div class="col-12 col-lg-5">
                <div
                    class="d-flex flex-column flex-md-row gap-2 
                    justify-content-start justify-content-lg-end">

                    @canany(['customer-create'])
                        <button id="create" class="btn  btn-sm btn-primary">
                            <i class="ti ti-plus me-1"></i> Add Data
                        </button>
                    @endcanany
                    @canany(['customer-trash'])
                        <a href="{{ route('customer.trash') }}" class="btn btn-sm btn-secondary">
                            <i class="ti ti-trash me-1"></i>Trash Bin
                        </a>
                    @endcanany

                    @canany(['barang-delete'])
                        <button id="deleteSelected" class="btn btn-danger btn-sm">
                            <i class="ti ti-trash me-1"></i> Delete Selected
                        </button>
                    @endcanany

                </div>
            </div>

        </div>
        <div class="card-datatable table-responsive p-3">
            <table class="table table-bordered" id="table">
                <thead class="border-top" style="background-color: #AEDEFC; ">
                    <tr>
                        <th>
                            <div class="form-check form-check-primary mt-3">
                                <input class="form-check-input" type="checkbox" value="" id="checkAll">
                            </div>
                        </th>
                        <th>#</th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Updated</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection
@push('scripts')
    <div class="modal fade" id="modals" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="mb-2" id="modal-title"></h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="postForm" name="postForm" method="POST" action="{{ route('customer.store') }}">
                        @csrf
                        <input type="text" name="id" id="id" hidden>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="id_pelanggan" class="form-label">Customer ID<small>*</small></label>
                                <input type="text" id="id_pelanggan" name="id_pelanggan" class="form-control"
                                    placeholder="Enter Customer ID">
                                <span class="error text-danger" id="id_pelangganError"></span>

                            </div>
                            <div class="col-6 mb-3">
                                <label for="nama" class="form-label">Customer Name<small>*</small></label>
                                <input type="text" id="nama" name="nama" class="form-control"
                                    placeholder="Enter Customer Name">
                                <span class="error text-danger" id="namaError"></span>

                            </div>
                            <div class="col-12 mb-3">
                                <label for="alamat" class="form-label">Address<small>*</small></label>
                                <input type="text" id="alamat" name="alamat" class="form-control"
                                    placeholder="Enter Customer Address">
                                <span class="error text-danger" id="alamatError"></span>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="alamat_pajak" class="form-label">Tax Address</label>
                                <input type="text" id="alamat_pajak" name="alamat_pajak" class="form-control"
                                    placeholder="Enter Customer Tax Address">
                                <span class="error text-danger" id="alamat_pajakError"></span>
                            </div>

                            <div class="col-3 mb-3">
                                <label for="kodepos" class="form-label">Postal Code</label>
                                <input type="text" id="kodepos" name="kodepos" class="form-control"
                                    placeholder="Enter Postal Code">
                                <span class="error text-danger" id="kodeposError"></span>
                            </div>
                            <div class="col-3 mb-3">
                                <label for="negara" class="form-label">Country<small>*</small></label>
                                <input type="text" id="negara" name="negara" class="form-control"
                                    placeholder="Enter Country">
                                <span class="error text-danger" id="negaraError"></span>
                            </div>
                            <div class="col-3 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="text" id="email" name="email" class="form-control"
                                    placeholder="Enter Email">
                                <span class="error text-danger" id="emailError"></span>
                            </div>
                            <div class="col-3 mb-3">
                                <label for="website" class="form-label">Website</label>
                                <input type="text" id="website" name="website" class="form-control"
                                    placeholder="Enter Website">
                                <span class="error text-danger" id="websiteError"></span>
                            </div>
                            <div class="col-3 mb-3">
                                <label for="telepon" class="form-label">Phone Number<small>*</small></label>
                                <input type="text" id="telepon" name="telepon" class="form-control"
                                    placeholder="Enter Phone Number">
                                <span class="error text-danger" id="teleponError"></span>
                            </div>
                            <div class="col-3 mb-3">
                                <label for="personal_kontak" class="form-label">Contact Person</label>
                                <input type="text" id="personal_kontak" name="personal_kontak" class="form-control"
                                    placeholder="Enter Contact Person">
                                <span class="error text-danger" id="personal_kontakError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Status<small>*</small></label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">Select Status</option>
                                    <option value="1">Active</option>
                                    <option value="2">Not Active</option>
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
                    </button>
                </div>
                </form>

            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('#checkAll').on('click', function() {
                $('.checkItem').prop('checked', this.checked);
            });

            // kalau salah satu di uncheck → header ikut off
            $(document).on('click', '.checkItem', function() {
                $('#checkAll').prop(
                    'checked',
                    $('.checkItem:checked').length === $('.checkItem').length
                );
            });
            var table = new DataTable('#table', {
                processing: true,
                serverSide: true,
                responsive: true,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All']
                ],
                ajax: '{{ route('customer.index') }}',
                columns: [{
                        data: 'cekbok',
                        name: 'cekbok',
                        orderable: false,
                        searchable: false
                    }, {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'id_pelanggan',
                    },
                    {
                        data: 'nama',
                    },
                    {
                        data: 'email',
                    },
                    {
                        data: 'telepon',
                    },
                    {
                        data: 'alamat',
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
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            $('#create').click(function() {

                $('#modals').modal('show');
                $('#modal-title').html('Add Customer');
                $('#savedata').html('<i class="fa fa-save me-1"></i> Save');

                $('#postForm').trigger('reset');
                $('#id').val('');

                resetValidation();

                // 🔥 AUTO GENERATE ID LANGSUNG KE MODAL
                $.get('/customer/generate-id', function(res) {
                    console.log(res); // 🔥 lihat di inspect
                    $('#id_pelanggan').val(res.id_pelanggan);
                });

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
                        table.draw();
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
            $('body').on('click', '.editPost', function(a) {
                $('#modals').modal('show');
                $('#savedata').html('<i class="fa fa-save me-1"></i>Save');
                resetValidation();

                var id = $(this).data('id');

                $.ajax({
                    type: "GET",
                    url: "/customer/" + id + "/edit",
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    success: function(data) {
                        console.log(data);
                        $('#modal-title').html('Edit Customer');
                        $('#id').val(data.id);
                        $('#id_pelanggan').val(data.id_pelanggan);
                        $('#nama').val(data.nama);
                        $('#alamat').val(data.alamat);
                        $('#alamat_pajak').val(data.alamat_pajak);
                        $('#kodepos').val(data.kodepos);
                        $('#negara').val(data.negara);
                        $('#telepon').val(data.telepon);
                        $('#personal_kontak').val(data.personal_kontak);
                        $('#email').val(data.email);
                        $('#website').val(data.website);
                        $('#status').val(data.status).trigger('change');
                        resetValidation();
                    }
                });
            });
            $('body').on('click', '#delete', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let token = $("meta[name='csrf-token']").attr("content");

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
                            url: `/customer/${id}`,
                            type: "DELETE",
                            cache: false,
                            data: {
                                _token: token
                            },
                            success: function(response) {
                                table.draw();
                                toastr.success('Deleted Data Successfully', '', {
                                    timeOut: 1500,
                                    progressBar: true,
                                    closeButton: false,
                                    positionClass: 'toast-top-right',
                                });
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Failed to delete',
                                    text: 'An error occurred. Please try again later.',
                                    timer: 5000,
                                    customClass: {
                                        confirmButton: 'btn btn-primary waves-effect waves-light'
                                    },
                                    buttonsStyling: false
                                });
                            }
                        });
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Cancelled',
                            text: 'Your data is safe.',
                            customClass: {
                                confirmButton: 'btn btn-info waves-effect waves-light'
                            }
                        });
                    }
                });
            });
            $('#deleteSelected').on('click', function() {

                let ids = [];

                $('.checkItem:checked').each(function() {
                    ids.push($(this).val());
                });

                if (ids.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'An error occurred. Please try again later.',
                        text: 'Please select data first!',
                        timer: 5000,
                        customClass: {
                            confirmButton: 'btn btn-primary waves-effect waves-light'
                        },
                        buttonsStyling: false
                    });
                    return;
                }

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Data will be deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
                        cancelButton: 'btn btn-label-secondary waves-effect waves-light'
                    },
                    buttonsStyling: false
                }).then((result) => {

                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/customer/delete-multiple',
                            type: 'POST',
                            data: {
                                ids: ids,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                toastr.success('Deleted Data Successfully', '', {
                                    timeOut: 1500,
                                    progressBar: true,
                                    closeButton: false,
                                    positionClass: 'toast-top-right',
                                });
                                $('#table').DataTable().ajax.reload();
                            },
                            error: function() {
                                Swal.fire('Error!', 'Failed to delete data.', 'error');
                            }
                        });
                    }

                });

            });
        });
    </script>
@endpush
