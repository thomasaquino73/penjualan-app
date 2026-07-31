@push('scripts')
    <script>
        $("#showModalpr").on("click", function(e) {
            e.preventDefault();

            var supplierID = $("#supplier_id").val();
            $("#sq_number")
                .empty()
                .append('<option value="">Select Requisition</option>')
                .val(null)
                .trigger("change");
            if (!supplierID) {
                Swal.fire({
                    icon: "warning",
                    title: "Warning!",
                    text: "Please select Supplier first before adding new data.",
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
                url: "{{ route('purchase-invoice.receive.processing') }}",
                type: "GET",
                success: function(response) {

                    let option = '<option value="">Select Receive Item</option>';

                    $.each(response, function(i, item) {
                        option += `<option value="${item.id}">
                                ${item.receive_item_code}
                           </option>`;
                    });

                    $("#sq_number").html(option);

                    $("#modalRequisitionDetail").modal("show");
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
                url: "{{ route('purchase-invoice.getReceiveDetail') }}",
                type: "POST",
                data: {
                    quotation_ids: quotationIds,
                    _token: "{{ csrf_token() }}"
                },

                success: function(response) {

                    console.log('Response:', response);

                    let html = '';

                    if (!response.success || !response.data || response.data.length === 0) {
                        $('#quotationTableBody').html(`
                    <tr>
                        <td colspan="6" class="text-center">
                            Tidak ada data Receive Item
                        </td>
                    </tr>
                `);
                        return;
                    }

                    $.each(response.data, function(index, item) {

                        let safeProductName = (item.product_name ?? '-')
                            .replace(/"/g, '&quot;');

                        html += `
                    <tr>
                        <td>
                            <div class="form-check form-check-primary">
                                <input
                                    class="form-check-input checkItem"
                                    type="checkbox"

                                    data-id="${item.id}"
                                    data-receive_item_id="${item.receive_item_id}"
                                    data-purchase_order_id="${item.purchase_order_id ?? ''}"

                                    data-product_id="${item.product_id}"
                                    data-product_name="${safeProductName}"

                                    data-qty="${item.qty}"
                                    data-unit_id="${item.unit_id}"
                                    data-unit_name="${item.unit_name}"

                                    data-warehouse_id="${item.warehouse_id}"
                                    data-warehouse_name="${item.warehouse_name}"

                                    data-unit_price="${item.unit_price}"
                                    data-discount="${item.discount}"
                                    data-amount="${item.amount}"
                                     data-order_code="${item.order_code ?? ''}"
                                >
                            </div>
                        </td>

                        <td>${item.product_name ?? '-'}</td>

                        <td class="text-end">
                            ${parseFloat(item.qty || 0)}
                        </td>

                        <td>
                            ${item.unit_name ?? '-'}
                        </td>

                        <td>
                            ${item.warehouse_name ?? '-'}
                        </td>

                        <td class="text-end">
                            ${new Intl.NumberFormat('id-ID').format(
                                parseFloat(item.unit_price || 0)
                            )}
                        </td>

                    </tr>
                `;
                    });

                    $("#checkAll").prop("checked", false);
                    $("#quotationTableBody").html(html);
                },

                error: function(xhr) {
                    console.log('Error:', xhr.responseText);

                    $('#quotationTableBody').html(`
                <tr>
                    <td colspan="6" class="text-center text-danger">
                        Gagal mengambil data Receive Item
                    </td>
                </tr>
            `);
                }
            });

        });
    </script>
@endpush
