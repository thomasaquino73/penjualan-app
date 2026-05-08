@extends('layouts.app')
@section('title', $title)
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
        <div class="card-header d-flex justify-content-between">
            <h5 class="card-title mb-0">{{ $title }}</h5>
            <div class="card-header-elements ms-auto">
                <button type="button" id="create" class="btn btn-md btn-primary waves-effect waves-light">
                    <span class="tf-icon ti ti-plus ti-md me-1"></span> Add Data
                </button>
            </div>
        </div>

        <div class="card-datatable table-responsive p-3">
            <table class="table table-bordered" id="roles_table">
                <thead class="border-top" style="background-color: #AEDEFC; ">
                    <tr>
                        <th>#</th>
                        <th>Roles</th>
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
        <div class="modal-dialog modal-dialog-centered modal-simple">
            <div class="modal-content p-3 p-md-5">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4">
                        <h3 class="mb-2" id="modal-title"></h3>
                    </div>
                    <form id="postForm" method="POST" action="{{ route('roles.store') }}">
                        @csrf
                        <input type="hidden" name="id" id="id">

                        <div class="mb-3">
                            <label class="form-label" for="name">Roles Name</label>
                            <input id="name" name="name" type="text" class="form-control"
                                placeholder="Enter Roles Name">
                            <span class="error text-danger" id="nameError"></span>
                        </div>
                        <div class="text-center">
                            <button type="submit" id="savedata" class="btn btn-primary me-sm-3 me-1">Save</button>
                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {

            var table = new DataTable('#roles_table', {
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: '{{ route('roles.index') }}',

                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'updated_at',
                        name: 'updated_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
            });
            // Create modal
            $('#create').click(function() {
                $('#modals').modal('show');
                $('#modal-title').text('Add Roles');
                $('#savedata').text('Save');
                $('#postForm').trigger('reset');
                $('#id').val('');
                $('#role_group_id').val(null).trigger('change');
                $('#status input').prop('disabled', false);
                $('#name').removeAttr('readonly');
                $('#name').removeClass('bg-light');
            });

            // Submit form
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
                        $('#savedata').html(' <i class="fa fa-save me-1"></i> Save changes');
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
                            text: 'Check your input.',
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

            // Edit
            $('body').on('click', '.editPost', function() {
                var id = $(this).data('id');
                resetValidation();

                $.get("{{ url('edit-roles') }}", {
                    id: id
                }, function(data) {
                    $('#modals').modal('show');
                    $('#modal-title').text('Edit Roles');
                    $('#id').val(data.id);
                    $('#name').val(data.name);
                    // Daftar role yang tidak boleh diedit
                    var lockedRoles = ['super admin'];

                    if (lockedRoles.includes(data.name.toLowerCase())) {
                        $('#name').prop('readonly', true);
                        $('#name').addClass('bg-light');
                        $('#status input').prop('disabled',
                            true); // optional, agar status juga tidak bisa diubah

                    } else {
                        $('#name').prop('readonly', false);
                        $('#name').removeClass('bg-light');
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
                            url: `roles/${id}`,
                            type: "DELETE",
                            cache: false,
                            data: {
                                _token: token
                            },
                            success: function(response) {
                                table.draw();
                                toastr.success('Data Deleted Successfully.', '', {
                                    timeOut: 1500,
                                    progressBar: true,
                                    closeButton: false,
                                    positionClass: 'toast-top-right',
                                });
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                let message = 'Terjadi kesalahan.';

                                if (jqXHR.status === 403 && jqXHR.responseJSON?.error) {
                                    message = jqXHR.responseJSON.error;
                                } else if (jqXHR.status === 422 && jqXHR.responseJSON
                                    ?.errors) {
                                    message = Object.values(jqXHR.responseJSON.errors)
                                        .flat().join(', ');
                                } else if (jqXHR.responseJSON?.message) {
                                    message = jqXHR.responseJSON.message;
                                }

                                Swal.fire({
                                    icon: 'error',
                                    title: '',
                                    text: message,
                                    showClass: {
                                        popup: 'animate__animated animate__bounceIn'
                                    },
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
            $('body').on('click', '.restorePost', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let token = $("meta[name='csrf-token']").attr("content");

                Swal.fire({
                    title: 'Want To Restore This Role?',
                    text: name,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
                        cancelButton: 'btn btn-label-secondary waves-effect waves-light'
                    },
                    buttonsStyling: false
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/pengaturan/restore-roles/${id}`,
                            type: "PATCH",
                            cache: false,
                            data: {
                                _token: token
                            },
                            success: function(response) {
                                table.draw();
                                toastr.success('Data Berhasil diUnpublish', '', {
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
                                        confirmButton: 'btn btn-info waves-effect waves-light'
                                    }
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
        });
    </script>
@endpush
