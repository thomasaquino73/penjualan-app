    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width" />

    <title></title>

    <meta name="description" content="" />
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ $favicon }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
        rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/fonts/fontawesome.css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/fonts/tabler-icons.css" />
    {{-- <link rel="stylesheet" href="{{ asset('') }}assets/vendor/fonts/flag-icons.css" /> --}}

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/css/rtl/core.css"
        class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/css/rtl/theme-default.css"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/node-waves/node-waves.css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/typeahead-js/typeahead.css" />
    <!-- Vendor -->
    <link rel="stylesheet"
        href="{{ asset('') }}assets/vendor/libs/@form-validation/form-validation.css" />

    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/css/pages/page-auth.css" />

    <!-- Helpers -->
    <script src="{{ asset('') }}assets/vendor/js/helpers.js"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    <script src="{{ asset('') }}assets/vendor/js/template-customizer.js"></script>
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('') }}assets/js/config.js"></script>

    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/flatpickr/flatpickr.css" />

    {{-- <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/apex-charts/apex-charts.css" /> --}}
    {{-- <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/swiper/swiper.css" /> --}}
    {{-- <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" /> --}}
    {{-- <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" /> --}}
    {{-- <link rel="stylesheet"
  href="{{ asset('') }}assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css" /> --}}
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/tagify/tagify.css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/toastr/toastr.css" />
    <link rel="stylesheet" href="{{ asset('') }}assets/vendor/libs/sweetalert2/sweetalert2.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="//cdn.datatables.net/2.3.7/css/dataTables.dataTables.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <link rel="stylesheet" href="//cdn.datatables.net/2.3.7/css/dataTables.dataTables.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/preloader.css') }}">
    <div id="preloader">
    <div class="preloader-content">

        @if (!empty($logo))
            <img src="{{ asset($logo) }}" class="preloader-logo" />
        @else
            @php
                $initial = strtoupper(substr($aplikasi ?? 'APP', 0, 2));
            @endphp
            <div class="default-logo">{{ $initial }}</div> @endif

        <div class="loader-ring">
    </div>

    <!-- TEXT TAMBAHAN -->
    <h4 class="loading-text">Loading...</h4>
    <p class="loading-subtext">Please wait a moment</p>

    </div>
    </div>
    </div>
    {{-- <div id="preloader">
        <div class="preloader-content">

            <!-- SVG Medical -->
            <svg class="medical-loader" viewBox="0 0 100 100" width="90" height="90">

                <!-- Lingkaran animasi -->
                <circle class="circle-bg" cx="50" cy="50" r="40"></circle>
                <circle class="circle-loader" cx="50" cy="50" r="40"></circle>

                <!-- Heartbeat -->
                <polyline class="heartbeat" points="20,55 35,55 42,40 50,70 60,45 70,55 80,55">
                </polyline>

            </svg>

            <p class="loading-text">Memuat Sistem {{ $aplikasi }}...</p>

        </div>
    </div> --}}

    @stack('style')
