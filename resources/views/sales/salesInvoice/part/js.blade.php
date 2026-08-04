@push('scripts')
    <script>
        function formatRupiah(angka) {
            if (!angka) return "0";

            angka = parseFloat(angka);

            return angka.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }
    </script>
@endpush
