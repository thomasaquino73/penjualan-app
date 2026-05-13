@extends('layouts.guest')
@push('style')
    <style>
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
                    <!-- Logo -->
                    {{-- <div class="app-brand justify-content-center mb-4 mt-2">
                        <a href="index.html" class="app-brand-link gap-2">
                            <span class="app-brand-logo demo">
                                <svg width="32" height="22" viewBox="0 0 32 22" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z"
                                        fill="#7367F0" />
                                    <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd"
                                        d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z"
                                        fill="#161616" />
                                    <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd"
                                        d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z"
                                        fill="#161616" />
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z"
                                        fill="#7367F0" />
                                </svg>
                            </span>
                            <span class="app-brand-text demo text-body fw-bold ms-1">Vuexy</span>
                        </a>
                    </div> --}}
                    <!-- /Logo -->
                    <h4 class="mb-1 pt-2 text-center">{{ $aplikasi }}</h4>
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

                    {{-- <p class="text-center">
                        <span>New on our platform?</span>
                        <a href="auth-register-basic.html">
                            <span>Create an account</span>
                        </a>
                    </p>

                    <div class="divider my-4">
                        <div class="divider-text">or</div>
                    </div>

                    <div class="d-flex justify-content-center">
                        <a href="javascript:;" class="btn btn-icon btn-label-facebook me-3">
                            <i class="tf-icons fa-brands fa-facebook-f fs-5"></i>
                        </a>

                        <a href="javascript:;" class="btn btn-icon btn-label-google-plus me-3">
                            <i class="tf-icons fa-brands fa-google fs-5"></i>
                        </a>

                        <a href="javascript:;" class="btn btn-icon btn-label-twitter">
                            <i class="tf-icons fa-brands fa-twitter fs-5"></i>
                        </a>
                    </div> --}}
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
                            toastr.success('Login successful! Redirecting to dashboard...', '', {
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
                            title: 'Login Gagal',
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
                    title: 'Email Belum Diverifikasi',
                    text: message,

                    showConfirmButton: true, // ✅ pastikan ini jelas
                    confirmButtonText: 'Kirim Ulang Link Verifikasi',

                    showCancelButton: true,
                    cancelButtonText: 'Batal',

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
                            title: 'Mengirim...',
                            text: 'Mohon tunggu sebentar.',
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
                            title: 'Berhasil!',
                            text: response.message,
                            showConfirmButton: false,
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: xhr.responseJSON?.message ?? 'Gagal mengirim verifikasi.',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false
                        });
                    }
                });
            }

            // ==== LOGIN DENGAN SIDIK JARI (PASSKEY) ====
            $('#login-passkey').on('click', async function() {
                try {
                    // 1. Ambil challenge dari server
                    const options = await fetch('/webauthn/login', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json'
                        }
                    }).then(r => r.json());

                    // Convert base64 → array buffer
                    options.publicKey.challenge = Uint8Array.from(atob(options.publicKey.challenge),
                        c => c.charCodeAt(0));

                    if (options.publicKey.allowCredentials) {
                        options.publicKey.allowCredentials = options.publicKey.allowCredentials.map(c =>
                            ({
                                ...c,
                                id: Uint8Array.from(atob(c.id), x => x.charCodeAt(0))
                            }));
                    }

                    // 2. Jalankan fingerprint / passkey
                    const assertion = await navigator.credentials.get(options);

                    // 3. Kirim ke server
                    const verify = await fetch('/webauthn/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(assertion)
                    }).then(r => r.json());

                    if (verify.authenticated) {
                        toastr.success('Login dengan sidik jari berhasil!');

                        setTimeout(() => {
                            window.location.href = verify.redirect ?? '/dashboard';
                        }, 800);
                    }

                } catch (err) {
                    console.error(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Gagal',
                        text: 'Tidak dapat menggunakan sidik jari pada perangkat ini.',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    });
                }
            });


        });
    </script>
@endpush
