@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.bootstrap5.min.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/select/2.0.3/css/select.bootstrap5.min.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/rowreorder/1.5.0/css/rowReorder.bootstrap5.min.css">

    <style>
        /* CSS Tambahan opsional untuk menandakan baris bisa di-drag */
        #table tbody td:first-child {
            cursor: move;
        }
    </style>
    <script>
        window.routes = {
            getUnits: "{{ route('ajax.products.units', ':id') }}",
        };
    </script>
@endpush
