<!doctype html>

<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr"
    data-theme="theme-default" data-assets-path="../../assets/" data-template="vertical-menu-template">

<head>
    @include('components.header')

</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <x-sidebar />

            <!-- Layout container -->
            <div class="layout-page">
                @include('components.navbar')

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->

                    <div class="container-xxl flex-grow-1 container-p-y">
                        @yield('konten')
                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    <footer class="content-footer footer bg-footer-theme">
                        <div class="container-xxl">
                            <div
                                class="footer-container d-flex align-items-center justify-content-between py-2 flex-md-row flex-column">
                                <div>
                                    ©
                                    <script>
                                        document.write(new Date().getFullYear());
                                    </script>
                                    , {{ $aplikasi }}, by
                                    <a href="https://www.thomasaquino.my.id" target="_blank"
                                        class="footer-link text-primary fw-medium">Thomas Aquino</a>
                                </div>
                                <div class="d-none d-lg-inline-block">
                                    Version 1.0.0
                                </div>
                            </div>
                        </div>
                    </footer>
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>

        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->
    <!-- Idle Lock Screen Modal -->
    <div class="modal fade" id="idleLockModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static"
        data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content p-2 text-center">
                <div class="modal-body">
                    <img src="{{ Auth::user()->avatar ? asset(Auth::user()->avatar) : asset('image/foto_user/avatar_user_default.png') }}"
                        class="rounded-circle mb-1" alt="user avatar" width="80" height="80">
                    <h5 class="mb-1">{{ Auth::user()->fullname }}</h5>
                    <p class="text-muted">Your session has been locked due to inactivity</p>

                    <form id="idleLogoutForm" action="{{ route('logout') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <input type="password" id="idlePassword" class="form-control text-center" autocomplete="off"
                                placeholder="Enter your password" required>
                            <!-- dummy agar browser tidak auto-suggest -->
                            <input type="text" style="display:none">
                            <input type="password" style="display:none">
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <button type="button" class="btn btn-outline-danger" id="idleLogoutBtn">
                                <i data-feather="log-out"></i> Logout
                            </button>
                            <button type="button" id="btnUnlock" class="btn btn-primary">
                                <i data-feather="unlock"></i> Unlock
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @include('components.footer')
</body>

</html>

<script>
    let idleTime = 0;
    let idleInterval;
    // const idleLimit = 60*60; // 1 jam
    const idleLimit = 3600; // 1 jam
    // const idleLimit = 10; // untuk testing

    function resetIdleTime() {
        idleTime = 0;
        // console.log("IdleTime:", idleTime);
        if (idleInterval) {
            clearInterval(idleInterval);
        }
        startIdleTimer();
    }

    function startIdleTimer() {
        if (idleInterval) clearInterval(idleInterval);

        idleInterval = setInterval(() => {
            idleTime++;
            // console.log untuk debug
            // console.log("Idle:", idleTime);

            if (idleTime >= idleLimit) {
                clearInterval(idleInterval);
                $('#idleLockModal').modal({
                    backdrop: 'static',
                    keyboard: false
                }).modal('show');

                $.ajax({
                    url: "{{ route('token.expire') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        console.log("Token expired:", res);
                    }
                });
            }
        }, 1000);
    }

    window.onload = () => {
        document.onmousemove = resetIdleTime;
        document.onkeypress = resetIdleTime;
        document.onclick = resetIdleTime;
        document.onscroll = resetIdleTime;

        startIdleTimer();
    };

    // Submit form untuk login kembali
    $('#idleUnlockForm').on('submit', function(e) {
        e.preventDefault();
        const password = $('#idlePassword').val();

        $.ajax({
            url: "{{ route('token.unlock') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                password: password
            },
            success: function(res) {
                if (res.success) {
                    $('#idleLockModal').modal('hide');
                    resetIdleTime();
                    startIdleTimer();
                    $('#idlePassword').val('');
                } else {
                    toastr.error('Password salah, coba lagi!');
                }
            }
        });
    });

    // Tombol logout
    $('#idleLogoutBtn').on('click', function() {
        // window.location.href = "{{ route('logout') }}";
        $('#idleLogoutForm').submit();
    });

    // Pastikan modal tidak bisa ditutup manual
    $('#idleLockModal').on('hide.bs.modal', function(e) {
        if (!$('#idleLockModal').data('forceClose')) {
            e.preventDefault();
        }
    });

    $(document).ready(function() {
        $.ajax({
            url: "{{ route('token.check') }}",
            type: "GET",
            success: function(res) {
                if (res.expired == 1) {
                    $('#idleLockModal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                    $('#idleLockModal').modal('show');
                }
            }
        });

        // Klik tombol "Login Kembali"
        $('#btnUnlock').click(function() {
            var password = $('#idlePassword').val();

            $.ajax({
                url: "{{ route('token.unlock') }}",
                type: "POST",
                data: {
                    password: password,
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    if (res.success) {
                        // tutup modal idleLockModal
                        $('#idleLockModal').data('forceClose', true);
                        $('#idleLockModal').modal('hide');
                        $('#idleLockModal').removeData('forceClose'); // reset lagi

                        resetIdleTime();
                        startIdleTimer();
                        $('#idlePassword').val('');

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Login berhasil, sesi dilanjutkan!',
                            confirmButtonText: 'OK',
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: res.message ||
                                "Password salah, silakan coba lagi.",
                            confirmButtonText: 'OK',
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        });
                    }
                },
                error: function(xhr) {
                    console.error("AJAX error:", xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: "Terjadi kesalahan server, coba lagi nanti.",
                        confirmButtonText: 'OK',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                }
            });
        });

    });
</script>

<script>
    function lockBrowser() {
        // Disable refresh (F5 & Ctrl+R)
        document.onkeydown = function(e) {
            if (e.key === "F5" || (e.ctrlKey && e.key === "r")) {
                e.preventDefault();
                return false;
            }
            // Cegah back (Alt+ArrowLeft / Backspace)
            if (e.key === "Backspace" && e.target.tagName !== "INPUT" && e.target.tagName !== "TEXTAREA") {
                e.preventDefault();
                return false;
            }
        };

        // Disable klik kanan
        document.oncontextmenu = function(e) {
            e.preventDefault();
            return false;
        };

        // Disable tombol back browser
        history.pushState(null, null, location.href);
        window.onpopstate = function() {
            history.go(1);
        };
    }

    function unlockBrowser() {
        // Balikin normal
        document.onkeydown = null;
        document.oncontextmenu = null;
        window.onpopstate = null;
    }

    // Aktifkan lock ketika modal muncul
    $('#idleLockModal').on('shown.bs.modal', function() {
        lockBrowser();
    });

    // Lepaskan lock ketika modal hilang (misal setelah login ulang)
    $('#idleLockModal').on('hidden.bs.modal', function() {
        unlockBrowser();
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Saat modal ditampilkan
        $('#idleLockModal').on('shown.bs.modal', function() {
            // Fokus otomatis ke input password
            $('#idlePassword').trigger('focus');

            // Reset value agar kosong lagi
            $('#idlePassword').val('');
        });

        // Enter otomatis klik btnUnlock
        $('#idlePassword').on('keypress', function(e) {
            if (e.which === 13) { // 13 = Enter
                e.preventDefault(); // cegah form submit default
                $('#btnUnlock').click(); // trigger klik tombol Unlock
            }
        });
    });
</script>
