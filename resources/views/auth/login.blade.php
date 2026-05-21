@extends('layouts.guest')
@push('style')
    <style>
        .app-brand-logo {
            width: 32px;
            height: 32px;
            overflow: hidden;
        }

        .bg-slider {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .bg-slide {
            position: absolute;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 1.5s ease-in-out;
        }

        .bg-slide.active {
            opacity: 1;
        }

        /* overlay agar login card jelas */
        .bg-slider::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
        }
    </style>
@endpush
@section('konten')
    <div class="bg-slider">
        @foreach ($backgrounds as $bg)
            <div class="bg-slide" style="background-image:url('{{ asset('image/login_background/' . $bg->gambar) }}')">
            </div>
        @endforeach
    </div>

    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-4">
            <!-- Login -->
            <div class="card">
                <div class="card-body">
                    @if (session('logout_message'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            {{ session('logout_message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('google_error'))
                        <div class="alert alert-danger alert-dismissible d-flex align-items-center" role="alert">
                            <i class="ti ti-ban ti-xs me-2"></i>

                            {{ session('google_error') }}

                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    <!-- Logo -->
                    <div class="app-brand justify-content-center mb-4 mt-2">
                        <a href="{{ route('login') }}" class="app-brand-link gap-2">
                            <span class="app-brand-logo demo" style="display:flex; align-items:center;">
                                <img src="{{ asset($favicon) }}" alt="Logo"
                                    style="height: 50px; width: auto; object-fit: contain;">
                            </span>
                            <span class="app-brand-text demo text-body fw-bold ms-1">{{ $aplikasi }}</span>
                        </a>
                    </div>
                    <!-- /Logo -->
                    {{-- <h4 class="mb-1 pt-2 text-center">{{ $aplikasi }}</h4> --}}
                    {{-- <p class="mb-4">Please sign-in to your account and start the adventure</p> --}}
                    <div class="divider my-4">
                        <div class="divider-text">Please Login</div>
                    </div>
                    <form id="loginForm" method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label>Email / Username</label>
                            <input type="text" id="username" name="username" class="form-control" autocomplete="off"
                                placeholder="Masukkan email atau username" value="{{ old('username') }}" autofocus>
                            <span class="error text-danger" id="usernameError"></span>
                        </div>
                        <div class="mb-3 form-password-toggle">
                            <div class="d-flex justify-content-between">
                                <label>Password</label>
                                <a href="{{ route('password.request') }}"><small>Forgot Password?</small></a>
                            </div>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" name="password" class="form-control"
                                    placeholder="•••••••••" autocomplete="new-password">
                                <span class="input-group-text cursor-pointer">
                                    <i class="ti ti-eye-off"></i>
                                </span>
                            </div>
                            <span class="error text-danger" id="passwordError"></span>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember-me" />
                                <label class="form-check-label" for="remember-me"> Remember Me </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <button id="savedata" type="submit" class="btn btn-primary w-100">Sign In</button>
                        </div>
                    </form>
                    <div class="divider my-4">
                        <div class="divider-text">or Login with</div>
                    </div>

                    <div class="d-flex justify-content-center">

                        <a href="{{ url('/auth/google') }}" class="btn btn-icon btn-label-secondary waves-effect me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
                                <path fill="#EA4335"
                                    d="M12.24 10.285V14.4h6.887c-.275 1.565-1.88 4.604-6.887 4.604-4.33 0-7.866-3.577-7.866-8s3.536-8 7.866-8c2.46 0 4.105 1.025 5.047 1.926l3.227-3.107C18.282 2.101 15.54 1 12.24 1 5.48 1 0 6.48 0 13s5.48 12 12.24 12c7.06 0 11.75-4.962 11.75-11.95 0-.804-.087-1.417-.19-1.765H12.24z" />
                                <path fill="#4285F4"
                                    d="M23.8 11.235H12.24V14.4h6.887c-.12.68-.53 1.3-1.12 1.7l2.67 2.07c1.56-1.44 2.47-3.56 2.47-5.93 0-.36-.03-.7-.09-1.005z" />
                                <path fill="#FBBC05"
                                    d="M17.74 18.105l-2.67-2.07c-.74.5-1.69.8-2.83.8-2.18 0-4.03-1.45-4.69-3.48l-2.76 2.13C6.18 20.15 8.95 22 12.24 22c2.09 0 3.96-.69 5.5-1.9z" />
                                <path fill="#34A853"
                                    d="M12.24 5.4c1.48 0 2.82.51 3.87 1.51l2.9-2.9C17.25 2.44 14.94 1.6 12.24 1.6c-3.29 0-6.06 1.85-7.44 4.56l2.76 2.14c.66-2.03 2.51-3.48 4.68-3.48z" />
                            </svg>
                        </a>

                    </div>
                </div>
            </div>
            <!-- /Register -->
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            let slides = document.querySelectorAll('.bg-slide');
            let index = 0;

            function showSlide() {

                slides.forEach(slide => {
                    slide.classList.remove('active');
                });

                slides[index].classList.add('active');

                index++;

                if (index >= slides.length) {
                    index = 0;
                }

            }

            showSlide();
            setInterval(showSlide, 5000);

        });
    </script>
    <script>
        $(document).ready(function() {

            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                var form = this;

                resetValidation();

                $.ajax({
                    url: $(form).attr('action'),
                    method: $(form).attr('method'),
                    data: new FormData(form),
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    beforeSend: function() {
                        $('#savedata').html(
                                '<i class="fa fa-spin fa-spinner me-1"></i> Sending...')
                            .prop('disabled', true);
                    },
                    complete: function() {
                        $('#savedata').html('Masuk').prop('disabled', false);
                    },
                    success: function(response) {
                        if (response.redirect) {
                            toastr.success('Login successful! Redirecting to dashboard...',
                                '', {
                                    timeOut: 1500,
                                    progressBar: true,
                                    positionClass: 'toast-top-right',
                                    onHidden: function() {
                                        window.location.href = response.redirect;
                                    }
                                });
                            return;
                        }

                        if (response.status_code === 'unverified_email') {
                            showUnverifiedAlert(response.message, $('#username').val());
                            return;
                        }

                        toastr.success('Login berhasil!');
                    },
                    error: function(xhr) {
                        resetValidation();

                        if (xhr.responseJSON?.status_code === 'unverified_email') {
                            showUnverifiedAlert(xhr.responseJSON.message, $('#username').val());
                            return;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Login Fail',
                            text: xhr.responseJSON?.message ?? 'Terjadi kesalahan.',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false
                        });

                        if (xhr.responseJSON?.errors) {
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                displayFieldError(key, value[0]);
                            });
                        }
                    }
                });
            });

            function showUnverifiedAlert(message, email) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Email Not Verified',
                    text: message,

                    showConfirmButton: true,
                    confirmButtonText: 'Resend Verification Link',

                    showCancelButton: true,
                    cancelButtonText: 'Cancel',

                    reverseButtons: true,
                    allowOutsideClick: false,

                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-outline-secondary ms-2'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        resendVerificationEmail(email);
                    }
                });
            }

            function resendVerificationEmail(email) {
                $.ajax({
                    url: '/send-verification',
                    method: 'POST',
                    data: {
                        email: email,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Sending...',
                            text: 'Please wait a moment.',
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false,
                            didOpen: () => Swal.showLoading()
                        });
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            showConfirmButton: true,
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed!',
                            text: xhr.responseJSON?.message ??
                                'Failed to send verification email.',
                            showConfirmButton: true,
                            confirmButtonText: 'Try Again',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false
                        });
                    }
                });
            }


        });
    </script>
@endpush
