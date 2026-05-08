@extends('layouts.app')
@section('title', $title)
@section('konten')
    <div class="container-xxl flex-guser-1 container-p-y">
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
        <div class="row">

            <div class="col-sm-6 col-lg-3 mb-4">
                <div class="card card-border-shadow-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2 pb-1">
                            <div class="avatar me-2">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="ti ti-users ti-md"></i>
                                </span>
                            </div>
                            <h4 class="ms-1 mb-0">{{ $totalUsers }}</h4>
                        </div>
                        <p class="mb-1">Total Users</p>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3 mb-4">
                <div class="card card-border-shadow-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2 pb-1">
                            <div class="avatar me-2">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="ti ti-user-star ti-md"></i>
                                </span>
                            </div>
                            <h4 class="ms-1 mb-0">{{ $totalActive }}</h4>
                        </div>
                        <p class="mb-1">Total Active Users</p>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3 mb-4">
                <div class="card card-border-shadow-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2 pb-1">
                            <div class="avatar me-2">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="ti ti-user-up ti-md"></i>
                                </span>
                            </div>
                            <h4 class="ms-1 mb-0">{{ $totalLogin }}</h4>
                        </div>
                        <p class="mb-1">Total Logged-in Users</p>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3 mb-4">
                <div class="card card-border-shadow-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2 pb-1">
                            <div class="avatar me-2">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="ti ti-user-check ti-md"></i>
                                </span>
                            </div>
                            <h4 class="ms-1 mb-0">{{ $totalVerified }}</h4>
                        </div>
                        <p class="mb-1">Total Verified Users</p>
                    </div>
                </div>
            </div>

        </div>
        <div class="card shadow-sm border-0">

            <!-- HEADER -->
            <div class="card-header bg-white">
                <div class="row g-3 align-items-center">

                    <!-- FILTER -->
                    <div class="col-12 col-lg-7">
                        <div class="row g-2 align-items-center">

                            <!-- Status -->
                            <div class="col-md">
                                <select class="form-select select2" id="selectFilter" data-placeholder="Choose status...">
                                    <option></option>
                                    <option value="Active">Active</option>
                                    <option value="Not Active">Not Active</option>
                                </select>
                            </div>

                            <!-- Verify -->
                            <div class="col-md">
                                <select class="form-select select2" id="selectVerify"
                                    data-placeholder="Choose verify status...">
                                    <option></option>
                                    <option value="Verify">Verify</option>
                                    <option value="Not Verify">Not Verify</option>
                                </select>
                            </div>

                            <!-- Reset button -->
                            <div class="col-md-auto">
                                <button class="btn btn-outline-secondary w-100" id="resetFilter">
                                    <i class="ti ti-refresh me-1"></i> Reset
                                </button>
                            </div>

                        </div>
                    </div>

                    <!-- BUTTON ACTION -->
                    <div class="col-12 col-lg-5 text-lg-end">
                        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-lg-end">
                            @canany(['user-create'])
                                <a href="{{ route('user.create') }}" class="btn btn-primary">
                                    <i class="ti ti-plus me-1"></i> Add Data
                                </a>
                            @endcanany
                            @canany(['user-trash'])
                                <a href="{{ route('user.trash') }}" class="btn btn-secondary">
                                    <i class="ti ti-trash me-1"></i>
                                </a>
                            @endcanany
                        </div>
                    </div>

                </div>
            </div>

            <!-- TABLE -->
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="display responsive nowrap" id="pengguna_table">
                        <thead class="table-light" style="background-color: #AEDEFC; ">
                            <tr>
                                <th width="40">#</th>
                                <th>Avatar</th>
                                <th>ID Number</th>
                                <th>Fullname</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Roles</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Created</th>
                                <th>Updated</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white"></div>

        </div>
    </div>
@endsection
@push('style')
    <style>
        .select2-container {
            width: 100% !important;
        }

        .table th {
            white-space: nowrap;
        }

        @media (max-width: 768px) {
            .card-header .btn {
                width: 100%;
            }
        }
    </style>
@endpush
@push('scripts')
    <script>
        $(document).ready(function() {
            $('#selectFilter').select2({
                allowClear: true,
                theme: 'bootstrap-5',
            });
            $('#selectVerify').select2({
                allowClear: true,
                theme: 'bootstrap-5',
            });

            var table = new DataTable('#pengguna_table', {
                processing: true,
                serverSide: true,
                responsive: true,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All']
                ],
                ajax: {
                    url: '{{ route('user.index') }}',
                    data: function(d) {
                        d.status = $('#selectFilter').val();
                        d.verify = $('#selectVerify').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'avatar'
                    },
                    {
                        data: 'no_ID'
                    },
                    {
                        data: 'fullname',
                    },
                    {
                        data: 'username'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'roles'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'last_seen'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'updated_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },

                ]
            });

            $('#selectFilter').on('change', function() {
                table.ajax.reload();
            });

            $('#selectVerify').on('change', function() {
                table.ajax.reload();
            });

            $('#resetFilter').on('click', function() {
                $('#selectFilter').val(null).trigger('change');
                $('#selectVerify').val(null).trigger('change');
                table.ajax.reload(); // reload datatable
            });

            $('body').on('click', '#verify', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let token = $("meta[name='csrf-token']").attr("content");

                // Simpan baris <tr> yang akan dihapus
                let row = $(this).closest('tr');

                Swal.fire({
                    title: 'Are you sure want to verify?',
                    text: name,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, verify it!',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
                        cancelButton: 'btn btn-label-secondary waves-effect waves-light'
                    },
                    buttonsStyling: false
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/user/verify-user/${id}`,
                            type: "PUT",
                            headers: {
                                'X-CSRF-TOKEN': token
                            },
                            cache: false,
                            data: {
                                _token: token
                            },
                            success: function(response) {
                                // Efek fadeOut sebelum hapus
                                table.draw();
                                toastr.success('User Berhasil di verifikasi', '', {
                                    timeOut: 1500,
                                    progressBar: true,
                                    closeButton: false,
                                    positionClass: 'toast-top-right',
                                });
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                // Kembalikan opacity jika error

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
            $('body').on('click', '#delete', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let token = $("meta[name='csrf-token']").attr("content");

                // Simpan baris <tr> yang akan dihapus
                let row = $(this).closest('tr');

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
                            url: `/user/${id}`,
                            type: "DELETE",
                            headers: {
                                'X-CSRF-TOKEN': token
                            },
                            cache: false,
                            data: {
                                _token: token
                            },
                            success: function(response) {
                                table.draw();
                                toastr.success('Data Deleted Successfully', '', {
                                    timeOut: 1500,
                                    progressBar: true,
                                    closeButton: false,
                                    positionClass: 'toast-top-right',
                                });
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                let message = 'Terjadi kesalahan.';

                                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                                    message = jqXHR.responseJSON.message;
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Failed to delete',
                                    text: message,
                                    timer: 5000,
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

        });
    </script>
@endpush
