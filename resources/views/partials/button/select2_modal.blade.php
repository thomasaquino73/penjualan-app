@push('scripts')
    <script>
        $(document).ready(function() {
            $(".select2-modal").each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: $this.attr("data-placeholder"),
                    width: "100%",
                    dropdownParent: $("#modalPrDetail"),
                });
            });
            $(".select2-modal2").each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: $this.attr("data-placeholder"),
                    width: "100%",
                    dropdownParent: $("#modalRequisitionDetail"),
                });
            });
            $(".select2-modaldp").each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: $this.attr("data-placeholder"),
                    width: "100%",
                    dropdownParent: $("#modalDownPayment"),
                });
            });

        });
    </script>
@endpush
