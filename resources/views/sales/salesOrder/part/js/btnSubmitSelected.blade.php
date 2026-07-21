@push('scripts')
    <script>
        $("#showModalpr").on("click", function(e) {
            e.preventDefault();

            var customerId = $("#customer_id").val();
            $("#sq_number")
                .empty()
                .append('<option value="">Select Quotation</option>')
                .val(null)
                .trigger("change");
            if (!customerId) {
                Swal.fire({
                    icon: "warning",
                    title: "Warning!",
                    text: "Please select Customer first before adding new data.",
                    confirmButtonColor: "#3085d6",
                    confirmButtonText: "OK",
                    customClass: {
                        confirmButton: "btn btn-danger",
                    },
                    buttonsStyling: false,
                });
                return;
            }

            $.ajax({
                url: "/sales-order/get-quotation/" + customerId,
                type: "GET",
                success: function(response) {

                    let option = '<option value="">Select Quotation</option>';

                    $.each(response, function(i, item) {
                        option += `<option value="${item.id}">
                            ${item.sales_quotation_code}
                       </option>`;
                    });

                    $("#sq_number").html(option);

                    $("#modalQuotationDetail").modal("show");
                }
            });
        });

        $('#sq_number').on('change', function() {

            let quotationIds = $(this).val();

            if (!quotationIds || quotationIds.length === 0) {
                $('#quotationTableBody').html('');
                return;
            }

            $.ajax({
                url: "{{ route('sales-order.getQuotationDetail') }}",
                type: "POST",
                data: {
                    quotation_ids: quotationIds,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    let html = '';
                    $.each(response, function(index, item) {
                        let safeProductName = item.nama_barang.replace(/"/g,
                            '&quot;');
                        html += `
                <tr>
                    <td>
                        <div class="form-check form-check-primary">
                            <input
                                class="form-check-input checkItem"
                                type="checkbox"

                                data-id="${item.id}"
                                data-product_id="${item.product_id}"
                                data-product_name="${safeProductName}"
                                data-outstanding_qty="${item.outstanding_qty}"
                                data-unit_id="${item.unit_id}"
                                data-unit_name="${item.unit_name}"
                                data-unit_price="${item.unit_price}"
                                data-discount="${item.discount}"
                                data-amount="${item.amount}"
                                data-quotation_id="${item.sales_quotation_id}"
                                data-quotation_code="${item.sales_quotation_code}"
                            >
                        </div>
                    </td>

                    <td>${item.nama_barang}</td>
                    <td class="text-end">${parseFloat(item.outstanding_qty)}</td>
                    <td>${item.unit_name}</td>
                    <td class="text-end">${parseFloat(item.unit_price).toLocaleString()}</td>
                    <td class="text-end">${parseFloat(item.discount).toLocaleString()}</td>
                    <td class="text-end">${parseFloat(item.amount).toLocaleString()}</td>
                </tr>`;
                    });

                    $("#checkAll").prop("checked", false);
                    $("#quotationTableBody").html(html);

                }
            });

        });
    </script>
@endpush
