@push('scripts')
    <script>
        $(document).on("click", "#btn-history-address", function() {
            let customerId = $("#customer_id").val();
            loadCustomerAddress(customerId);
        });
        $(document).on("click", ".select-address", function() {
            let address = $(this).data("address");
            $("#address").val(address);
        });

        function loadCustomerAddress(customerId) {
            if (!customerId) return;

            $.ajax({
                url: `/sales-invoice/get-address-list/${customerId}`,
                type: "GET",
                dataType: "json",
                success: function(response) {

                    let dropdownMenu = $("#address-dropdown-menu");
                    dropdownMenu.empty();

                    if (!response.success) {
                        dropdownMenu.append(`
                    <li>
                        <span class="dropdown-item text-muted">
                            Tidak ada alamat.
                        </span>
                    </li>
                `);
                        return;
                    }

                    // ================= Billing ==================
                    dropdownMenu.append(`
                <li>
                    <h6 class="dropdown-header text-primary fw-bold">
                        Billing Address
                    </h6>
                </li>
            `);

                    dropdownMenu.append(`
                <li>
                    <a href="javascript:void(0)"
                       class="dropdown-item select-address"
                       data-address="${response.billing.address.replace(/\n/g,'&#10;')}"
                       style="white-space:normal;">
                        <span style="white-space:pre-line;">${response.billing.address}</span>
                    </a>
                </li>
            `);

                    // Divider
                    if (response.delivery.length) {
                        dropdownMenu.append(`
                    <li><hr class="dropdown-divider"></li>
                `);
                    }

                    // ================ Delivery ==================
                    response.delivery.forEach(function(item) {

                        let badge = item.default == 1 ?
                            '<span class="badge bg-success ms-1">Default</span>' :
                            '';

                        dropdownMenu.append(`
                    <li>
                        <h6 class="dropdown-header text-success fw-bold">
                            Delivery Address ${badge}
                        </h6>
                    </li>

                    <li>
                        <a href="javascript:void(0)"
                           class="dropdown-item select-address"
                           data-address="${item.address.replace(/\n/g,'&#10;')}"
                           style="white-space:normal;">

                            <strong>${item.receiver ?? ''}</strong><br>

                            ${item.phone
                                ? `<small class="text-muted">${item.phone}</small><br>`
                                : ''
                            }

                            <span style="white-space:pre-line;">
                                ${item.address}
                            </span>

                        </a>
                    </li>

                    <li><hr class="dropdown-divider"></li>
                `);

                    });

                }
            });
        }
    </script>
@endpush
