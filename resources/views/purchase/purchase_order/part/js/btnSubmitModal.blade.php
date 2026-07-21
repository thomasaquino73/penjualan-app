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
                url: "{{ route('purchase-order.requisitions.processing') }}",
                type: "GET",
                success: function(response) {

                    let option = '<option value="">Select Quotation</option>';

                    $.each(response, function(i, item) {
                        option += `<option value="${item.id}">
                                ${item.code}
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
                url: "{{ route('purchase-order.getQuotationDetail') }}",
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

                                    data-purchase_requisition_id="${item.purchase_requisition_id}"
                                    data-purchase_requisition_code="${item.purchase_requisition_code}"
                                >
                            </div>
                        </td>

                        <td>${item.nama_barang}</td>
                        <td class="text-end">${parseFloat(item.outstanding_qty)}</td>
                        <td>${item.unit_name}</td>

                    </tr>`;
                    });

                    $("#checkAll").prop("checked", false);
                    $("#quotationTableBody").html(html);

                }
            });

        });
    </script>
@endpush
