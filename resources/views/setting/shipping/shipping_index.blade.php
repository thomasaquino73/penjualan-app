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
                <div
                    class="d-flex flex-column flex-md-row gap-2
                    justify-content-start justify-content-lg-end">

                    @canany(['shipping-create'])
                        <button id="create" class="btn  btn-sm btn-primary">
                            <i class="ti ti-plus me-1"></i> Add Data
                        </button>
                    @endcanany
                    @canany(['shipping-trash'])
                        <a href="{{ route('shipping.trash') }}" class="btn  btn-sm btn-secondary">
                            <i class="ti ti-trash"></i> Trash Bin
                        </a>
                    @endcanany
                    @canany(['shipping-delete'])
                        <button id="deleteSelected" class="btn btn-sm btn-danger ">
                            <i class="ti ti-trash me-1"></i> Delete Selected
                        </button>
                    @endcanany


                </div>
            </div>

        </div>

        <div class="card-datatable table-responsive" style="padding: 20px">
            <table class="datatables-ajax table" id="table">
                <thead style="background-color: #AEDEFC; ">
                    <tr>
                        <th>
                            <div class="form-check form-check-primary mt-3">
                                <input class="form-check-input" type="checkbox" value="" id="checkAll">
                            </div>
                        </th>
                        <th>#</th>
                        <th>Name</th>
                        <th>PIC</th>
                        <th>Phone Number</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Updated</th>
                        <th>Actions</th>
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
                    <form id="postForm" name="postForm" method="POST" action="{{ route('shipping.store') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="text" name="id" id="id" hidden>
                        <div class="row">
                            <div class="col-md-12 col-sm-12 mb-3">
                                <label for="nama" class="form-label">Name<small>*</small></label>
                                <input type="text" id="nama" name="nama" class="form-control"
                                    placeholder="Enter Name">
                                <span class="error text-danger" id="namaError"></span>
                            </div>

                            <div class="col-md-12 col-sm-12 mb-3">
                                <label for="pic" class="form-label">PIC<small>*</small></label>
                                <input type="text" id="pic" name="pic" class="form-control"
                                    placeholder="Enter PIC">
                                <span class="error text-danger" id="picError"></span>
                            </div>

                            <div class="col-md-12 col-sm-12 mb-3">
                                <label for="phone" class="form-label">Phone Number<small>*</small></label>
                                <input type="text" id="phone" name="phone" class="form-control"
                                    placeholder="Enter Phone Number">
                                <span class="error text-danger" id="phoneError"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 mb-3">
                                <label for="address" class="form-label">Address<small>*</small></label>
                                <textarea type="text" id="address" name="address" class="form-control" placeholder="Enter Address"></textarea>
                                <span class="error text-danger" id="addressError"></span>
                            </div>

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
@push('scripts')
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
                ajax: '{{ route('shipping.index') }}',
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
                        data: 'nama',
                    },
                    {
                        data: 'pic',
                    },
                    {
                        data: 'phone',
                    },
                    {
                        data: 'address',
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
                let kodeOtomatis = 'IUR' + Date.now();
                $('#modals').modal('show');
                $('#modal-title').html('Add Shipping');
                $('#savedata').html('<i class="fa fa-save me-1"></i> Save');
                $('#postForm').trigger('reset');
                $('#id').val('');
                resetValidation();
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
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Check Your Data.',
                            showClass: {
                                popup: 'animate__animated animate__bounceIn'
                            },
                            customClass: {
                                confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                            buttonsStyling: false
                        });
                        let errors = xhr.responseJSON.errors;

                        $.each(errors, function(key, value) {
                            // For other fields, display individual field errors if any
                            displayFieldError(key, value[0]);
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
                    url: "/shipping/" + id + "/edit",
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    success: function(data) {
                        console.log(data);
                        $('#modal-title').html('Edit Vehicle');
                        $('#id').val(data.id);
                        $('#nama').val(data.nama);
                        $('#pic').val(data.pic);
                        $('#phone').val(data.phone);
                        $('#address').val(data.address);
                        $('#status').val(data.status).trigger('change');
                        $('#deskripsi').val(data.deskripsi);

                        // Set plat nomor terpisah
                        $('#plat_depan').val(data.plat_depan);
                        $('#plat_tengah').val(data.plat_tengah);
                        $('#plat_belakang').val(data.plat_belakang);

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
                            url: `/shipping/${id}`,
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
                            url: '/shipping/delete-multiple',
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
