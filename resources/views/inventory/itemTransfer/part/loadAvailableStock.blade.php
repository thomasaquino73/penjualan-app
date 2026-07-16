@push('scripts')
    <script>
        $(document).ready(function() {
            function loadAvailableStock() {

                let productId = $('#product_id').val();
                let warehouseId = $('#from_warehouse_id').val();
                let unitId = $('#unit_id').val();

                console.log({
                    productId,
                    warehouseId,
                    unitId
                });

                if (!productId || !warehouseId || !unitId) {
                    $('#available_stok').val('');
                    $('#modalTitle').text('Create new entry');
                    return;
                }

                $.ajax({
                    url: "{{ route('item-transfer.wh.get-stock') }}",
                    type: "GET",
                    data: {
                        product_id: productId,
                        from_warehouse_id: warehouseId,
                        unit_id: unitId
                    },
                    success: function(res) {

                        console.log('RESPONSE STOCK:', res);

                        $('#available_stok').val(res.stock);

                        $('#modalTitle').text(
                            `Create new entry `
                        );
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        $('#available_stok').val(0);
                    }
                });
            }

            $(document).on('change', '#product_id, #from_warehouse_id, #unit_id', function() {
                loadAvailableStock();
            });
        });
    </script>
@endpush
