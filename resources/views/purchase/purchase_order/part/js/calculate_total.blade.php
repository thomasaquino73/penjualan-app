@push('scripts')
    <script>
        function calculateTotal() {
            let qty = parseFloat(document.getElementById('quantity').value) || 0;
            let price = parseFloat(document.getElementById('unit_price').value) || 0;
            let discountInput = document.getElementById('discount_percent').value;
            let subtotal = qty * price;
            let remaining = subtotal;
            let totalDiscount = 0;
            if (discountInput) {
                // Ambil semua angka dari input seperti "10+5+5"
                let discounts = discountInput.split('+');
                discounts.forEach(d => {
                    let percent = parseFloat(d.trim()) || 0;
                    let discValue = remaining * (percent / 100);
                    totalDiscount += discValue;
                    remaining -= discValue;
                });
            }
            // Set hasil ke input discount (nominal)
            document.getElementById('discount').value = totalDiscount.toFixed(2);
            // Set total price
            document.getElementById('amount').value = remaining.toFixed(2);
        }
        document.getElementById('discount').addEventListener('input', function() {
            let qty = parseFloat(document.getElementById('quantity').value) || 0;
            let price = parseFloat(document.getElementById('unit_price').value) || 0;
            let discountNominal = parseFloat(this.value) || 0;
            let subtotal = qty * price;
            if (discountNominal > subtotal) {
                discountNominal = subtotal;
            }
            let total = subtotal - discountNominal;
            document.getElementById('amount').value = total.toFixed(2);
        });
        document.getElementById('quantity').addEventListener('input', calculateTotal);
        document.getElementById('unit_price').addEventListener('input', calculateTotal);
        document.getElementById('discount_percent').addEventListener('input', calculateTotal);
    </script>
    <script>
        $("#sub_total, #discount_all").on("input", function() {
            calculateTotalOrder();
        });
        // ===============================
        // Ambil Grand Total dari Detail
        // ===============================
        function getGrandSubTotal() {
            let total = 0;
            $.each(prDetailsData, function(index, item) {
                total += parseFloat(item.amount) || 0;
            });
            return total;
        }
        // ===============================
        // Hitung Grand Total
        // ===============================
        function calculateGrandTotal() {
            let grandSubTotal = getGrandSubTotal();
            let currentPercent = parseFloat($("#percent").val()) || 0;
            if (currentPercent > 0) {
                let nominalDiscount = grandSubTotal * currentPercent / 100;
                $("#discount_all").val(Math.round(nominalDiscount));
            } else {
                let nominalDiscount = parseFloat($("#discount_all").val()) || 0;
                if (nominalDiscount > grandSubTotal) {
                    nominalDiscount = grandSubTotal;
                    $("#discount_all").val(Math.round(nominalDiscount));
                }
                let percent = grandSubTotal > 0 ?
                    (nominalDiscount / grandSubTotal) * 100 :
                    0;
                $("#percent").val(
                    percent % 1 === 0 ? percent : percent.toFixed(2)
                );
            }
            calculateTotalOrder();
        }

        const TAXES = @json($taxes);
        const DEFAULT_TAX_ID = {{ $defaultTax->id ?? 'null' }};
        // ===============================
        // Hitung Total Order
        // ===============================
        function calculateTotalOrder() {
            // Selalu hitung subtotal dari tabel
            let grandSubTotal = getGrandSubTotal();
            let discount = parseFloat($("#discount_all").val()) || 0;
            let kenaPajak = $("#kena_pajak").is(":checked");
            let totalInclude = $("#total_termasuk_pajak").is(":checked");
            let selectedTaxId = $("#tax_id").val();
            let taxPercent = 0;
            if (selectedTaxId) {
                let selectedTax = TAXES.find(t => t.id == selectedTaxId);
                if (selectedTax) {
                    taxPercent = parseFloat(selectedTax.percentage) || 0;
                }
            }
            // subtotal setelah diskon
            let subtotal = grandSubTotal - discount;

            if (subtotal < 0)
                subtotal = 0;

            let dpp = subtotal;
            let tax = 0;
            let totalOrder = subtotal;

            if (kenaPajak && taxPercent > 0) {

                $("#ppn_container").show();

                if (totalInclude) {

                    // ==================================
                    // TAX INCLUSIVE
                    // subtotal sudah termasuk pajak
                    // ==================================

                    dpp = subtotal / (1 + (taxPercent / 100));

                    tax = subtotal - dpp;

                    totalOrder = subtotal;

                } else {

                    // ==================================
                    // TAX EXCLUSIVE
                    // subtotal belum termasuk pajak
                    // ==================================

                    dpp = subtotal;

                    tax = dpp * taxPercent / 100;

                    totalOrder = dpp + tax;
                }

            } else {

                $("#ppn_container").hide();

                dpp = subtotal;
                tax = 0;
                totalOrder = subtotal;
            }

            // Label tax
            $("#taxes").text(
                taxPercent > 0 ?
                `Tax (${taxPercent}%)` :
                "Tax"
            );

            // ===================================================
            // SUB TOTAL TETAP DARI TABEL (JANGAN DPP)
            // ===================================================
            $("#sub_total").val(Math.round(subtotal));

            // Simpan DPP jika diperlukan
            $("#dpp_amount").val(Math.round(dpp));

            $("#tax_amount").val(Math.round(tax));

            $("#total_order").val(Math.round(totalOrder));
        }

        // ===============================
        // EVENT
        // ===============================

        $("#kena_pajak").on("change", function() {

            if ($(this).is(":checked")) {

                $("#tax_container").show();

                if (DEFAULT_TAX_ID) {
                    $("#tax_id").val(DEFAULT_TAX_ID);
                }

            } else {

                $("#tax_container").hide();

                $("#tax_id").val("");

                $("#total_termasuk_pajak").prop("checked", false);

            }

            calculateTotalOrder();
        });

        $("#tax_id").on("change", function() {

            calculateTotalOrder();

        });

        $("#total_termasuk_pajak").on("change", function() {

            if ($(this).is(":checked")) {

                $("#kena_pajak").prop("checked", true);

                if ($("#tax_id").val() == "" && DEFAULT_TAX_ID) {
                    $("#tax_id").val(DEFAULT_TAX_ID);
                }
                $("#tax_container").hide();

            } else {
                $("#tax_container").show();
            }

            calculateTotalOrder();

        });

        $("#discount_all").on("input", function() {

            calculateTotalOrder();

        });

        $("#percent").on("input", function() {

            let subtotal = getGrandSubTotal();

            let percent = parseFloat($(this).val()) || 0;

            let nominal = subtotal * percent / 100;

            $("#discount_all").val(Math.round(nominal));

            calculateTotalOrder();

        });
    </script>
@endpush
