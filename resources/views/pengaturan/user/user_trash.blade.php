@extends('layouts.app')
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
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <form action="{{ route('user.index') }}" method="GET" class="d-flex">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control me-2"
                        placeholder="Find User...">
                    <button type="submit" class="btn btn-outline-primary me-1">Find</button>
                    @if (request('search'))
                        <a href="{{ route('user.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </form>
                <div class="card-header-elements ms-auto">
                    <a href="{{ route('user.index') }}" class="btn btn-md btn-secondary waves-effect waves-light">
                        <span class="tf-icon ti ti-chevron-left ti-md me-1"></span>{{ __('Back') }}
                    </a>
                </div>
            </div>
            <div class="card-datatable table-responsive" style="padding: 20px">
                <table class="table table-bordered" id="pengguna_table">
                    <thead class="border-top" style="   background-color: #EA7B7B !important; ">
                        <tr>
                            <th>#</th>
                            <th>Avatar</th>
                            <th>Fullname</th>
                            <th>Username</th>
                            <th class="text-center">email</th>
                            <th>Roles</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                                <td>
                                    <img class="avatar avatar-md rounded-circle me-2  avatar-online"
                                        src="{{ $user->avatar ? asset($user->avatar) : asset('image/foto_user/avatar_user_default.png') }}"
                                        alt="User profile picture">
                                </td>
                                <td>{{ $user->fullname }} </td>
                                <td>{{ $user->username }} </td>

                                <td>{{ $user->email }} </td>

                                <td>
                                    @if ($user->getRoleNames()->isNotEmpty())
                                        @foreach ($user->getRoleNames() as $role)
                                            <span class="badge bg-info">{{ $role }}</span>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button"
                                            class="btn btn-primary dropdown-toggle waves-effect waves-light"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ti ti-menu-2 ti-xs me-1"></i>
                                            Action
                                        </button>

                                        <div class="dropdown-menu">
                                            @canany(['user-trash'])
                                                <button class="dropdown-item restore "data-id="{{ $user->id }}"
                                                    data-name="{{ $user->fullname }}">
                                                    <i class="ti ti-undo me-1"></i> Restore
                                                </button>
                                            @endcanany

                                        </div>
                                    </div>
                                </td>
                                </td>


                            </tr>
                        @endforeach

                    </tbody>
                </table>

            </div>
            <div class="card-footer ">
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {

            $('body').on('click', '.restore', function() {
                let id = $(this).data('id');
                let token = $("meta[name='csrf-token']").attr("content");
                let row = $(this).closest('tr');
                Swal.fire({
                    title: 'Restore this user?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, restore!',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        confirmButton: 'btn btn-success me-3 waves-effect waves-light',
                        cancelButton: 'btn btn-secondary waves-effect waves-light'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        row.css('opacity', '0.5');

                        // Bisa juga tambahkan spinner di salah satu kolom
                        row.find('td:last').html(
                            '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
                        );
                        $.ajax({
                            url: `/user/restore/${id}`,
                            type: 'PUT',
                            data: {
                                _token: token
                            },
                            success: function(response) {
                                if (response.redirect) {
                                    toastr.success(response.message, '', {
                                        timeOut: 2000,
                                        progressBar: true,
                                        positionClass: 'toast-top-right'
                                    });
                                    row.fadeOut(500, function() {
                                        row.remove();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Failed',
                                        text: response.message ||
                                            'Error restoring user'
                                    });
                                }
                            },
                            error: function(xhr) {
                                let errMsg = 'Error restoring user';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errMsg = xhr.responseJSON.message;
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Failed',
                                    text: errMsg,
                                    timer: 5000,
                                    customClass: {
                                        confirmButton: 'btn btn-info waves-effect waves-light'
                                    }
                                });
                            }
                        });
                    }
                });
            });

        });
    </script>
@endpush
