<script src="{{ asset('') }}assets/vendor/libs/jquery/jquery.js"></script>
<script src="{{ asset('') }}assets/vendor/libs/popper/popper.js"></script>
<script src="{{ asset('') }}assets/vendor/js/bootstrap.js"></script>
<script src="{{ asset('') }}assets/vendor/libs/node-waves/node-waves.js"></script>
<script src="{{ asset('') }}assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="{{ asset('') }}assets/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('') }}assets/vendor/libs/i18n/i18n.js"></script>
<script src="{{ asset('') }}assets/vendor/libs/typeahead-js/typeahead.js"></script>
<script src="{{ asset('') }}assets/vendor/js/menu.js"></script>

<!-- endbuild -->

<!-- Vendors JS -->
{{-- <script src="{{ asset('') }}assets/vendor/libs/@form-validation/popular.js"></script>
<script src="{{ asset('') }}assets/vendor/libs/@form-validation/bootstrap5.js"></script>
<script src="{{ asset('') }}assets/vendor/libs/@form-validation/auto-focus.js"></script> --}}

<!-- Main JS -->
<script src="{{ asset('') }}assets/js/main.js"></script>
<script src="{{ asset('assets/vendor/libs/tagify/tagify.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
<!-- Page JS -->
<script src="{{ asset('') }}assets/js/pages-auth.js"></script>
<script src="{{ asset('') }}assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
<script src="{{ asset('') }}assets/js/extended-ui-sweetalert2.js"></script>
<script src="{{ asset('') }}assets/vendor/libs/toastr/toastr.js"></script>
<script src="{{ asset('') }}assets/js/ui-toasts.js"></script>
<script src="//cdn.datatables.net/2.3.7/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.8/js/dataTables.responsive.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.8/js/responsive.dataTables.js"></script>


<script>
    function resetValidation() {
        $('.error').text('');
        $('input, select').removeClass('is-invalid');
    }

    function displayFieldError(fieldId, errorMessage) {
        if (!$('#' + fieldId + 'Error').length) {
            $('#' + fieldId).after(
                `<div id="${fieldId}Error" class="invalid-feedback">${errorMessage}</div>`);
        } else {
            $('#' + fieldId + 'Error').text(errorMessage);
        }
        $('#' + fieldId).addClass('is-invalid');
    }
</script>
@stack('scripts')
<!-- Modal Koneksi Terputus -->
<div class="modal fade" id="offlineModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4">
            <div class="modal-body">
                <img src="{{ asset('image/wifi-off.svg') }}" alt="Logo" width="100">
                <h5 class="mb-3 text-danger">
                    Connection Lost
                </h5>
                <p>Please check your internet connection...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Koneksi Pulih -->
<div class="modal fade" id="onlineModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4">
            <div class="modal-body">
                <img src="{{ asset('image/wifi.svg') }}" alt="Logo" width="100">
                <h5 class="mb-3 text-success">
                    Connection Recovered
                </h5>
                <p>Internet is back to normal.</p>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const offlineModal = new bootstrap.Modal(document.getElementById('offlineModal'));
        const onlineModal = new bootstrap.Modal(document.getElementById('onlineModal'));

        // Jika saat load sudah offline
        if (!navigator.onLine) {
            offlineModal.show();
        }

        window.addEventListener('offline', () => {
            offlineModal.show();
        });

        window.addEventListener('online', () => {
            offlineModal.hide();
            onlineModal.show();

            // auto-hide setelah 2 detik
            setTimeout(() => onlineModal.hide(), 2000);
        });
    });
</script>
<script>
    window.addEventListener("load", function() {
        let preloader = document.getElementById("preloader");
        preloader.style.opacity = "0";
        setTimeout(() => {
            preloader.style.display = "none";
        }, 500); // hilang setelah 0.5 detik
    });

    function initTooltip() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    }

    function initPopovers() {
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
        var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl)
        })
    }
</script>
<script>
    // window.addEventListener("load", function() {
    //     const preloader = document.getElementById("preloader");

    //     setTimeout(() => {
    //         preloader.classList.add("hidden");

    //         // benar-benar remove dari DOM (optional)
    //         setTimeout(() => {
    //             preloader.remove();
    //         }, 600);

    //     }, 800); // delay biar smooth
    // });
</script>
