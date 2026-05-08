@extends('layouts.app')
@section('title', $title)
@section('konten')

    <div class="container-xxl flex-grow-1 container-p-y">
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

        <!-- Header -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="user-profile-header-banner">
                        <img src="{{ asset('') }}assets/img/pages/profile-banner.png" alt="Banner image"
                            class="rounded-top" />
                    </div>
                    <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4">
                        <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                            <img src="{{ $user->avatar ? asset($user->avatar) : asset('image/foto_user/avatar_user_default.png') }}"
                                alt="{{ $user->fullname ?? 'User Avatar' }}"
                                class="d-block h-auto ms-0 ms-sm-4 rounded user-profile-img" />
                        </div>
                        <div class="flex-grow-1 mt-3 mt-sm-5">
                            <div
                                class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
                                <div class="user-profile-info">
                                    <h4>{{ $user->fullname }}</h4>
                                    <ul
                                        class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2">
                                        <li class="list-inline-item d-flex gap-1">
                                            <i class="ti ti-color-swatch"></i>
                                            @foreach ($user->getRoleNames() as $role)
                                                {{ $role }}
                                                @if (!$loop->last)
                                                    |
                                                @endif
                                            @endforeach
                                        </li>
                                        <li class="list-inline-item d-flex gap-1">
                                            <i class="ti ti-calendar"></i>{{ Carbon\Carbon::now()->format('d M Y') }}
                                        </li>
                                    </ul>
                                </div>
                                <a href="{{ url()->previous() }}" class="btn btn-secondary">
                                    <i class="ti ti-rotate-clockwise-2 me-1"></i> Back
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Header -->



        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="card-title mb-0">{{ $title }}</h5>
            </div>
            <div class="card-datatable table-responsive" style="padding: 20px">
                <div class="row">
                    <form action="{{ route('ganti.password') }}" id="postForm" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('put')
                        <div class="row">
                            <div class="col-md-6 col-sm-6 mb-3">
                                <label for="" class="form-label">Avatar:</label>
                                <input class="form-control" type="file" id="avatar" name="avatar" />
                                <span class="error text-danger" id="avatarError"></span>
                            </div>
                            <div class="col-md-6 col-sm-6 mb-3">
                                <label for="" class="form-label">Username:</label>
                                <input class="form-control" type="text" id="username" name="username"
                                    value="{{ old($user->username, $user->username) }}" readonly />
                                <span class="error text-danger" id="usernameError"></span>
                            </div>
                            <div class=" col-md-6  col-sm-6 mb-3">
                                <label for="" class="form-label">Email:</label>
                                <input class="form-control" type="text" id="email" name="email"
                                    value="{{ old($user->email, $user->email) }}" />
                                <span class="error text-danger" id="emailError"></span>
                            </div>
                            <div class="mb-3 col-lg-6 col-md-12 col-sm-12 form-password-toggle">
                                <label class="form-label" for="password">Current Password</label>
                                <div class="input-group input-group-merge">
                                    <input class="form-control" type="password" id="current_password"
                                        name="current_password"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                                    <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                                </div>
                                <span class="error text-danger" id="current_passwordError"></span>

                            </div>
                            <div class="mb-3 col-lg-6 col-md-12 col-sm-12 form-password-toggle">
                                <label class="form-label" for="password">New Password</label>
                                <div class="input-group input-group-merge">
                                    <input class="form-control" type="password" id="password" name="password"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                                    <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                                </div>
                                <span class="error text-danger" id="passwordError"></span>

                            </div>

                            <div class="mb-3 col-lg-6 col-md-12 col-sm-12 form-password-toggle">
                                <label class="form-label" for="password_confirmation">Confirm New Password</label>
                                <div class="input-group input-group-merge">
                                    <input class="form-control" type="password" name="password_confirmation"
                                        id="password_confirmation"
                                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                                    <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                                </div>
                                <span class="error text-danger" id="password_confirmationError"></span>
                            </div>
                        </div>

                        <div>
                            <button id="savedata" name="savedata" type="submit" class="btn btn-primary me-2"> <i
                                    class="fa fa-save me-1"></i> Change
                                Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('style')
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/css/pages/page-profile.css" />
@endpush
@push('scripts')
    <script src="{{ asset('') }}assets/js/pages-profile.js"></script>
    <script>
        $('#postForm').on('submit', function(e) {
            e.preventDefault();
            var form = this;

            $.ajax({
                url: $(form).attr('action'),
                method: $(form).attr('method'),
                data: new FormData(form),
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function() {
                    $('#savedata').html(
                        '<i class="fa fa-spin fa-spinner me-1"></i> Sending...');
                    resetValidation();
                },
                complete: function() {
                    $('#savedata').html(
                        '<i class="fa fa-save me-1"></i> Change Password');
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        showClass: {
                            popup: 'animate__animated animate__bounceIn'
                        },
                        customClass: {
                            confirmButton: 'btn btn-primary waves-effect waves-light'
                        },
                        buttonsStyling: false
                    }).then(() => {
                        window.location.href = response.redirect;
                    });
                },
                error: function(xhr) {
                    resetValidation();
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Periksa kembali data Anda.',
                        showClass: {
                            popup: 'animate__animated animate__bounceIn'
                        },
                        customClass: {
                            confirmButton: 'btn btn-primary waves-effect waves-light'
                        },
                        buttonsStyling: false
                    });

                    let errors = xhr.responseJSON.errors;

                    if (errors) {
                        $.each(errors, function(key, value) {
                            displayFieldError(key, value[0]);
                        });
                    }
                }
            });
        });
    </script>
@endpush
