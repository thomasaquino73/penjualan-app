@push('scripts')
    <script>
        $(document).ready(function() {

            $("#customer_contact_id").select2({
                placeholder: "Select Contact",
                width: "100%",
            });

            $("#payment_term_id").select2({
                placeholder: "Select Payment Term",
                width: "100%",
            });
            $("#jenis_pengiriman").select2({
                placeholder: "Select Shipping",
                width: "100%",
            });
        });
    </script>
@endpush
