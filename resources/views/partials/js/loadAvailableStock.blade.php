@push('scripts')
    <script>
        $(document).ready(function() {
            function loadAvailableStock() {

                let productId = $('#product_id').val();
                let warehouseId = $('#warehouse_id').val();
                let unitId = $('#unit_id').val();

                if (!productId || !warehouseId || !unitId) {
                    $('#available_stok').val('');
                    return;
                }

                $.ajax({
                    url: "{{ route('warehouse.wh.get-stock') }}",
                    type: "GET",
                    data: {
                        product_id: productId,
                        warehouse_id: warehouseId,
                        unit_id: unitId
                    },
                    success: function(res) {
                        $('#available_stok').val(res.stock);
                    },
                    error: function(xhr) {
                        $('#available_stok').val(0);
                    }
                });
            }

            $(document).on('change', '#product_id, #warehouse_id, #unit_id', function() {
                loadAvailableStock();
            });
        });
    </script>
@endpush
