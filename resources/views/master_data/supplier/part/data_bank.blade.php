<div class="row mt-3">
    <table class="table display responsive nowrap" id="table">
        <thead class="border-top" style="background-color: #AEDEFC; ">
            <tr>
                <th>#</th>
                <th>Account Number</th>
                <th>Account Name</th>
                <th>Bank</th>
            </tr>
        </thead>
    </table>
</div>

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/2.0.3/css/select.bootstrap5.css">
@endpush
@push('scripts')
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.js"></script>

    <script src="https://cdn.datatables.net/select/3.1.3/js/dataTables.select.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.3/js/select.bootstrap5.js"></script>

    <script>
        $(document).ready(function() {
            let prDetailsData = [];
            let table = new DataTable('#table', {
                processing: true,
                serverSide: false,
                responsive: true,
                select: true,
                searching: false,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All']
                ],
                data: prDetailsData, // Mengarah ke array di atas
                columns: [{
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: 'nama_bank'
                    },
                    {
                        data: 'nomor_rekening'
                    },
                    {
                        data: 'nama_rekening'
                    },

                ],
                layout: {
                    topStart: {
                        buttons: [{
                                text: '<i class="ti ti-plus me-1"></i> New',
                                className: 'btn btn-primary btn-sm me-2',
                                action: function(e, dt, node, config) {
                                    var supplierId = $('#nama_supplier').val();

                                    if (!supplierId || supplierId === '') {
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Warning!',
                                            text: 'Please enter Supplier Name first before adding new data.',
                                            confirmButtonColor: '#3085d6',
                                            confirmButtonText: 'OK',
                                            customClass: {
                                                confirmButton: 'btn btn-danger'
                                            },
                                            buttonsStyling: false
                                        });
                                        return false;
                                    }

                                    $('#formPrDetail')[0].reset();
                                    $('#detail_id').val('');

                                    if ($.fn.select2) {
                                        $('#product_id').val('').trigger('change');
                                        $('#unit_id').val('').trigger('change');
                                    }

                                    $('#modalTitle').text('Create new entry');
                                    $('#btnSubmitModal').text('Create');
                                    $('#modalPrDetail').modal('show');
                                }
                            },
                            {
                                text: '<i class="ti ti-edit me-1"></i> Edit',
                                className: 'btn btn-warning btn-sm me-2',
                                extend: 'selectedSingle',
                                action: function(e, dt, node, config) {
                                    let data = dt.row({
                                        selected: true
                                    }).data();
                                    let rowIndex = dt.row({
                                        selected: true
                                    }).index();

                                    // 1. Set penanda bahwa ini adalah mode EDIT
                                    window.isEditingMode = true;

                                    $('#detail_id').val(rowIndex);
                                    $('#quantity').val(data.quantity);
                                    $('#unit_id').data('pending-val', data.unit_id);

                                    // 2. Set value produk dan trigger change
                                    $('#product_id').val(data.product_id).trigger('change');

                                    // 3. Set harga unit price asli dari tabel data
                                    $('#unit_price').val(data.unit_price);
                                    $('#discount').val(data.discount || 0); // Jika ada diskon
                                    $('#modalTitle').text('Edit entry');
                                    $('#btnSubmitModal').text('Update');
                                    $('#modalPrDetail').modal('show');
                                }
                            },
                            {
                                text: '<i class="ti ti-trash me-1"></i> Delete',
                                className: 'btn btn-danger btn-sm me-2',
                                extend: 'selected',
                                action: function(e, dt, node, config) {
                                    let rowIndex = dt.row({
                                        selected: true
                                    }).index();
                                    let data = dt.row({
                                        selected: true
                                    }).data();
                                    let name = data.data_produk ? data.data_produk : '';

                                    Swal.fire({
                                        title: 'Are you sure?',
                                        text: "Want to delete data: " + name,
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonText: 'Yes, delete it!',
                                        cancelButtonText: 'Cancel',
                                        customClass: {
                                            confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
                                            cancelButton: 'btn btn-label-secondary waves-effect waves-light'
                                        },
                                        buttonsStyling: false
                                    }).then(function(result) {
                                        if (result.isConfirmed) {
                                            prDetailsData.splice(rowIndex, 1);
                                            dt.clear().rows.add(prDetailsData).draw();
                                            calculateGrandTotal();
                                            calculateTotalOrder()
                                            toastr.success('Deleted Data Successfully',
                                                '', {
                                                    timeOut: 1500,
                                                    progressBar: true
                                                });
                                        }
                                    });
                                }
                            },
                            {
                                text: '<i class="ti ti-refresh me-1"></i> Clear All',
                                className: 'btn btn-secondary btn-sm',
                                action: function(e, dt, node, config) {
                                    prDetailsData = [];
                                    dt.clear().draw();
                                    calculateGrandTotal();
                                    calculateTotalOrder()
                                    $('#percent').val(0);

                                }
                            }
                        ]
                    }
                }
            });

            $('.select2-modal').each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: $this.attr('data-placeholder'),
                    width: '100%',
                    dropdownParent: $('#modalPrDetail')
                });
            });
            $('#showModalpr').on('click', function(e) {
                e.preventDefault();

                let tbody = $('#requisitionTableBody');

                // Reset checkbox 'Check All' menjadi tidak tercentang saat modal dibuka
                $('#checkAll').prop('checked', false);

                tbody.html(
                    '<tr><td colspan="3" class="text-center"><i class="fa fa-spin fa-spinner me-1"></i> Loading data...</td></tr>'
                );
                $('#modalRequisitionDetail').modal('show');

                $.ajax({
                    url: "{{ route('purchase-order.requisitions.processing') }}",
                    type: "GET",
                    dataType: "json",
                    success: function(response) {
                        tbody.empty();

                        if (response && response.length > 0) {
                            $.each(response, function(key, item) {
                                let dateFormatted = new Date(item.created_at)
                                    .toLocaleDateString('id-ID');

                                // Tambahkan checkbox dengan class 'checkItem' dan value berupa ID data
                                tbody.append(`
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input checkItem" type="checkbox" value="${item.id}">
                                    </div>
                                </td>
                                <td><strong>${item.code}</strong></td>
                                <td>${dateFormatted}</td>
                            </tr>
                        `);
                            });
                        } else {
                            tbody.html(
                                '<tr><td colspan="3" class="text-center text-muted">No processing data found.</td></tr>'
                            );
                        }
                    },
                    error: function(xhr) {
                        tbody.html(
                            '<tr><td colspan="3" class="text-center text-danger">Failed to fetch data.</td></tr>'
                        );
                    }
                });
            });

        });
    </script>
@endpush
