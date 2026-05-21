@extends('layouts.app')
@section('konten')
    <h4>
        <span class="text-muted fw-light">
            @foreach ($breadcrumb as $key => $item)
                @if (!empty($item['url']))
                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                @else
                    {{ $item['label'] }}
                @endif
                @if (!$loop->last)
                    /
                @endif
            @endforeach
        </span>
    </h4>

    <div class="card">
        <form id="postForm" name="postForm" method="POST" action="{{ route('customer.store') }}">
            @csrf
            <div class="card-body table-responsive p-3">
                <div class="col-xl-12">
                    <div class="nav-align-top mb-4">
                        <ul class="nav nav-pills mb-3" role="tablist">
                            <li class="nav-item">
                                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-pills-top-general" aria-controls="navs-pills-top-general"
                                    aria-selected="true">
                                    General Information
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-pills-top-contact" aria-controls="navs-pills-top-contact"
                                    aria-selected="false">
                                    Contact Information
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-pills-top-term" aria-controls="navs-pills-top-term"
                                    aria-selected="false">
                                    Stock Information
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-pills-top-tax" aria-controls="navs-pills-top-tax"
                                    aria-selected="false">
                                    Other Information
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="navs-pills-top-general" role="tabpanel">
                                <div class="row">
                                    @include('master_data.barang.data_barang.part.data_umum')

                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-top-contact" role="tabpanel">
                                <div class="row">

                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-top-term" role="tabpanel">
                                <div class="row">
                                    @include('master_data.barang.data_barang.part.stock_table')
                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-top-tax" role="tabpanel">
                                <div class="row">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('customer.index') }}" type="button" class="btn btn-label-secondary waves-effect">
                        <i class="ti ti-chevron-left me-1"></i>
                        Back
                    </a>
                    <button type="submit" id="savedata" name="savedata" class="btn btn-primary me-sm-3 me-1">
                        <i class="fa fa-save me-1"></i>Save
                    </button>
                </div>
        </form>
    </div>

    <div class="modal fade" id="modalPrDetail">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Create new entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formPrDetailStock">
                    @csrf
                    <input type="hidden" name="id" id="detail_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label" for="warehouse_id">Warehouse</label>
                                <select name="warehouse_id" id="warehouse_id" class="form-select select2-warehouse "
                                    data-placeholder="Select Warehouse">
                                    <option></option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->nama_gudang }}</option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="warehouse_idError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label" for="date_stock">Date</label>
                                <input type="text" id="date_stock" name="date_stock" class="form-control ">
                                <span class="error text-danger" id="date_stockError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label" for="quantity">Quantity</label>
                                <input type="number" id="quantity" name="quantity" class="form-control"
                                    placeholder="0" min="0">
                                <span class="error text-danger" id="quantityError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label" for="unit_id_modals">Unit</label>
                                <select name="unit_id_modals" id="unit_id_modals" class="form-select select2-unit "
                                    data-placeholder="Select Unit">
                                    <option></option>
                                    @foreach ($unit as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->detail }}</option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="unit_id_modalsError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label" for="unit_price">Unit Price</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"> {{ $mataUangDefault ?? 'Rp' }}
                                    </span>
                                    <input type="number" id="unit_price" name="unit_price" class="form-control"
                                        placeholder="0" min="0">
                                </div>
                                <span class="error text-danger" id="unit_priceError"></span>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label" for="discount">Total Price</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"> {{ $mataUangDefault ?? 'Rp' }}
                                    </span>
                                    <input type="number" id="total_price" name="total_price" class="form-control"
                                        placeholder="0" min="0" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" id="btnSubmitModal">Create</button>
                        </div>
                </form>
            </div>
        </div>
    </div>
@endsection
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
        function hitungTotal() {
            let qty = parseFloat(document.getElementById('quantity').value) || 0;
            let price = parseFloat(document.getElementById('unit_price').value) || 0;

            let total = qty * price;

            document.getElementById('total_price').value = total;
        }

        // trigger saat input berubah
        document.getElementById('quantity').addEventListener('input', hitungTotal);
        document.getElementById('unit_price').addEventListener('input', hitungTotal);
    </script>
    <script>
        let prDetailsData = [];
        $(function() {
            $('#modalPrDetail').on('shown.bs.modal', function() {
                flatpickr("#date_stock", {
                    enableTime: false,
                    dateFormat: "d-m-Y",
                    minDate: "today",
                    defaultDate: new Date()
                });
            });

        });
        $('#unit_id').on('change', function() {
            let unitId = $(this).val();
            let unitText = $('#unit_id option:selected').text();

            $('.from_unit_text').val(unitText);

            // isi ke hidden input (buat backend)
            $('.from_unit_id').val(unitId);
            // 🔥 AKTIFKAN INPUT
            $('.qty').prop('disabled', false);
            $('.to_unit').prop('disabled', false);
        });
        $(document).ready(function() {

            $('.select2-warehouse').each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: $this.attr('data-placeholder'),
                    width: '100%',
                    dropdownParent: $('#modalPrDetail'),
                });
            });
            $('.select2-unit').each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: $this.attr('data-placeholder'),
                    width: '100%',
                    dropdownParent: $('#modalPrDetail'),
                });
            });
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
                        data: 'date_stock',
                    },

                    {
                        data: 'quantity'
                    },
                    {
                        data: 'unit'
                    },
                    {
                        data: 'unit_price'
                    },
                    {
                        data: 'data_warehouse'
                    },

                ],
                layout: {
                    topStart: {
                        buttons: [{
                                text: '<i class="ti ti-plus me-1"></i> New',
                                className: 'btn btn-primary btn-sm me-2',
                                action: function(e, dt, node, config) {

                                    $('#formPrDetailStock')[0].reset();
                                    $('#detail_id').val('');
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
                                    $('#tax').val(data.tax || 0); // Jika ada tax

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
                                    $('#percent').val(0); // Jika ada tax

                                }
                            }
                        ]
                    }
                }
            });
            $('#formPrDetailStock').on('submit', function(e) {
                e.preventDefault();
                let warehouseID = $('#warehouse_id').val();
                let warehouseName = $('#warehouse_id option:selected').text();
                let quantity = parseFloat($('#quantity').val()) || 0;
                let unitId = $('#unit_id_modals').val();
                let unitName = $('#unit_id_modals option:selected').text();
                let unitPrice = parseFloat($('#unit_price').val()) || 0;
                let dateStock = $('#date_stock').val() || '';
                let detailId = $('#detail_id').val();
                if (!warehouseID) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops...',
                        text: 'Warehouse must be selected!',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-warning'
                        },
                        buttonsStyling: false
                    });
                    return;
                }
                if (!quantity) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops...',
                        text: 'Quantity must be selected!',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-warning'
                        },
                        buttonsStyling: false
                    });
                    return;
                }

                if (!unitId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops...',
                        text: 'Unit must be selected!',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-warning'
                        },
                        buttonsStyling: false
                    });
                    return;
                }
                let itemData = {
                    'warehouse_id': warehouseID,
                    'data_warehouse': warehouseName,
                    'quantity': quantity,
                    'unit_id': unitId,
                    'unit': unitName,
                    'unit_price': unitPrice,
                    'date_stock': dateStock,
                };

                if (detailId === '') {
                    prDetailsData.push(itemData);
                } else {
                    prDetailsData[detailId] = itemData;
                }

                // Render ulang ke DataTable visual
                table.clear().rows.add(prDetailsData).draw();
                $('#modalPrDetail').modal('hide');
            });
        });
    </script>
@endpush
