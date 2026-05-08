@extends('layouts.app')
@section('konten')
    <h4>
        <span class="text-muted fw-light">
            @foreach ($breadcrumb as $item)
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

    <div class="row">
        <div class="col-md-12">
            @include('partials.pengaturan.navbar_pengaturan')

            <div class="card mb-4">

                <div class="card-header bg-white">
                    <div class="row">
                        <div class="col-12 col-lg-6">
                            <h5 class="mb-0">{{ $title }}</h5>
                        </div>
                        <div class="col-12 col-lg-6 text-lg-end">
                            <button id="create" class="btn  btn-sm btn-primary">
                                <i class="ti ti-plus me-1"></i> Add Data
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="card-datatable table-responsive" style="padding: 20px">
                            <table class="table table-bordered " id="table">
                                <thead style="background-color: #AEDEFC; ">
                                    <tr>
                                        <th>#</th>
                                        <th>Currency</th>
                                        <th>Description</th>
                                        <th>Created</th>
                                        <th>Updated</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="mt-3">

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <div class="modal fade" id="modals" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="mb-2" id="modal-title"></h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="postForm" name="postForm" method="POST" action="{{ route('pengaturan.mata_uang.store') }}">
                        @csrf
                        <input type="text" name="id" id="id" hidden>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="detail" class="form-label">Currency<small>*</small></label>
                                <input type="text" id="detail" name="detail" class="form-control"
                                    placeholder="Enter Currency">
                                <span class="error text-danger" id="detailError"></span>

                            </div>
                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">Description<small>*</small></label>
                                <input type="text" id="description" name="description" class="form-control"
                                    placeholder="Enter Description">
                                <span class="error text-danger" id="descriptionError"></span>

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
    --}}
    <script>
        $(document).ready(function() {
            var table = new DataTable('#table', {
                processing: true,
                serverSide: true,
                responsive: true,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All']
                ],
                ajax: '{{ route('pengaturan.mata_uang.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'detail',
                    },
                    {
                        data: 'description',
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
                $('#modal-title').html('Add Currency');
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
                    url: "/mata-uang/" + id + "/edit",
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    success: function(data) {
                        console.log(data);
                        $('#modal-title').html('Edit Currency');
                        $('#id').val(data.id);
                        $('#detail').val(data.detail);
                        $('#description').val(data.description);
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
                            url: `/mata-uang/${id}`,
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
            $('body').on('click', '.detail', function() {

                let gambar = $(this).data('gambar');
                let alias = $(this).data('alias');

                $('#detail_gambar').attr('src', gambar);
                $('#detail_alias').text(alias);

                $('#modalDetail').modal('show');

            });
        });
    </script>
@endpush
