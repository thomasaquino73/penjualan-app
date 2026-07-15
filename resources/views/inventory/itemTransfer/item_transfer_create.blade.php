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
        <div
            class="card-header d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center">

            <h5 class="card-title mb-2 mb-lg-0">{{ $title }}</h5>

            <div class="col-12 col-lg-5">
                <div
                    class="d-flex flex-column flex-md-row gap-2
                    justify-content-start justify-content-lg-end">
                </div>
            </div>

        </div>
        <div class="card-body table-responsive p-3">
            <form action="{{ route('item-transfer.store') }}" method="POST" id="postForm" enctype="multipart/form-data">
                @csrf
                <div class="row mb-5">
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Number<small class="text-danger">*</small> </label>
                                <input type="text" name="transfer_code" id="transfer_code" class="form-control"
                                    value="{{ $idNumber }}">
                                <span class="error text-danger" id="transfer_codeError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Date<small class="text-danger">*</small> </label>
                                <input type="text" name="transfer_date" id="transfer_date" class="form-control">
                                <span class="error text-danger" id="transfer_dateError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">From Warehouse<small class="text-danger">*</small> </label>
                                <select name="from_warehouse_id" id="from_warehouse_id" class="from-select select2"
                                    data-placeholder="From Warehouse">
                                    <option></option>
                                    @foreach ($fromWarehouse as $fw)
                                        <option value="{{ $fw->id }}">{{ $fw->nama_gudang }}</option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="from_warehouse_idError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">To Warehouse<small class="text-danger">*</small> </label>
                                <select name="to_warehouse_id" id="to_warehouse_id" class="from-select select2"
                                    data-placeholder="To Warehouse">
                                    <option></option>
                                    @foreach ($toWarehouse as $tw)
                                        <option value="{{ $tw->id }}">{{ $tw->nama_gudang }}</option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="to_warehouse_idError"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">

                            <label class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control"></textarea>
                            <span class="error text-danger" id="descriptionError"></span>
                        </div>
                    </div>

                </div>
                <div class="divider divider-dashed">
                    <div class="divider-text">Item Transfer Detail</div>
                </div>

                <div class="row mt-3">
                    <table class="table display responsive nowrap" id="table">
                        <thead class="border-top" style="background-color: #AEDEFC; ">
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Unit</th>
                            </tr>
                        </thead>

                    </table>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <button type="submit" id="savedata" class="btn btn-primary" data-save-and-new="false">
                        <i class="fa fa-upload me-1"></i> Save and Close
                    </button>

                    <button type="submit" id="savedata" class="btn btn-success" data-save-and-new="true">
                        <i class="fa fa-plus-circle me-1"></i> Save and Create New
                    </button>
                    <a href="{{ route('permintaan-pembelian.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="modalPrDetail">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Create new entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formPrDetail">
                    @csrf
                    <input type="hidden" name="id" id="detail_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label" for="product_id">Product / Item</label>
                                <select name="product_id" id="product_id" class="form-select select2-modal"
                                    data-placeholder="Select Product">
                                    <option></option>
                                    @foreach ($product as $product)
                                        <option value="{{ $product->id }}">{{ $product->nama_barang }}</option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="product_idError"></span>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-3">
                                <label class="form-label" for="quantity">Quantity</label>
                                <input type="number" id="quantity" name="quantity" class="form-control"
                                    placeholder="0">
                                <span class="error text-danger" id="quantityError"></span>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-3">
                                <label class="form-label" for="unit_id">Unit</label>
                                <select name="unit_id" id="unit_id" class="form-select select2-modal "
                                    data-placeholder="Select Unit">
                                    <option></option>
                                </select>
                                <span class="error text-danger" id="unit_idError"></span>
                            </div>
                            <div class="col-md-6 col-sm-12 mb-3">
                                <label class="form-label" for="available_stok">Available Stock</label>
                                <input type="number" id="available_stok" name="available_stok" class="form-control"
                                    readonly>
                                <span class="error text-danger" id="available_stokError"></span>
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
        $(function() {

            const datePicker = flatpickr("#transfer_date", {
                enableTime: false,
                dateFormat: "d-m-Y",
                // minDate: "today",
                // defaultDate: "{{ \Carbon\Carbon::now()->format('d-m-Y') }}"
            });
        });
        $(document).ready(function() {

            $('.select2-modal').each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: $this.attr('data-placeholder'),
                    width: '100%',
                    dropdownParent: $('#modalPrDetail')
                });
            });

            // function loadAvailableStock() {

            //     let productId = $('#product_id').val();
            //     let warehouseId = $('#from_warehouse_id').val();
            //     let unitId = $('#unit_id').val();

            //     console.log({
            //         productId,
            //         warehouseId,
            //         unitId
            //     });

            //     if (!productId || !warehouseId || !unitId) {
            //         $('#available_stok').val('');
            //         return;
            //     }

            //     $.ajax({
            //         url: "{{ route('item-transfer.wh.get-stock') }}",
            //         type: "GET",
            //         data: {
            //             product_id: productId,
            //             warehouse_id: warehouseId,
            //             unit_id: unitId
            //         },
            //         success: function(res) {
            //             $('#available_stok').val(res.stock);
            //             console.log('RESPONSE STOCK:', res.stock);

            //             $('#modalTitle').text(
            //                 `Create new entry `
            //             );
            //         }
            //     });
            // }

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
                data: prDetailsData,
                columns: [{
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: 'data_produk'
                    },
                    {
                        data: 'quantity'
                    },
                    {
                        data: 'unit'
                    },
                ],
                layout: {
                    topStart: {
                        buttons: [{
                                text: '<i class="ti ti-plus me-1"></i> New',
                                className: 'btn btn-primary btn-sm me-2',
                                action: function(e, dt, node, config) {
                                    var fromWarehouseId = $("#from_warehouse_id").val();
                                    var toWarehouseId = $("#to_warehouse_id").val();

                                    if (!fromWarehouseId || !toWarehouseId) {
                                        Swal.fire({
                                            icon: "warning",
                                            title: "Warning!",
                                            text: "Please select Warehouse first.",
                                            confirmButtonColor: "#3085d6",
                                            confirmButtonText: "OK",
                                            customClass: {
                                                confirmButton: "btn btn-danger",
                                            },
                                            buttonsStyling: false,
                                        });
                                        return false;
                                    }

                                    // Validasi warehouse tidak boleh sama
                                    if (fromWarehouseId === toWarehouseId) {
                                        Swal.fire({
                                            icon: "warning",
                                            title: "Warning!",
                                            text: "From Warehouse and To Warehouse cannot be the same.",
                                            confirmButtonColor: "#3085d6",
                                            confirmButtonText: "OK",
                                            customClass: {
                                                confirmButton: "btn btn-danger",
                                            },
                                            buttonsStyling: false,
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

                                    $('#detail_id').val(rowIndex);
                                    $('#quantity').val(data.quantity);
                                    $('#required_date').val(data.required_date);
                                    $('#notes').val(data.notes);

                                    // Simpan ID unit secara temporer untuk trigger di AJAX product change
                                    $('#unit_id').data('pending-val', data.unit_id);
                                    $('#product_id').val(data.product_id).trigger('change');

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
                                }
                            }
                        ]
                    }
                }
            });

            $(document).on('change', '#product_id', function() {
                let productId = $(this).val();
                let unitSelect = $('#unit_id');

                if (!productId) {
                    unitSelect.empty().append('<option></option>').trigger('change');
                    return;
                }

                $.ajax({
                    url: `/get-units-by-product/${productId}`,
                    type: 'GET',
                    dataType: 'json',
                    beforeSend: function() {
                        unitSelect.html('<option>Loading units...</option>').prop('disabled',
                            true);
                    },
                    success: function(response) {
                        unitSelect.empty().append('<option></option>').prop('disabled', false);

                        if (response && response.length > 0) {
                            $.each(response, function(key, item) {
                                unitSelect.append(
                                    `<option value="${item.id}">${item.name}</option>`
                                );
                            });
                        } else {
                            unitSelect.append('<option value="">No unit available</option>');
                        }

                        unitSelect.trigger('change');

                        // Jika dalam mode EDIT, pasang kembali unit yang terpilih sebelumnya
                        let pendingUnitId = unitSelect.data('pending-val');
                        if (pendingUnitId) {
                            unitSelect.val(pendingUnitId).trigger('change');
                            unitSelect.removeData('pending-val');
                        }
                    },
                    error: function() {
                        toastr.error('Failed to fetch units data.');
                        unitSelect.empty().append('<option></option>').prop('disabled', false)
                            .trigger('change');
                    }
                });
            });

            $('#formPrDetail').on('submit', function(e) {
                e.preventDefault();

                let productId = $('#product_id').val();
                let productName = $('#product_id option:selected').text();
                let unitId = $('#unit_id').val();
                let unitName = $('#unit_id option:selected').text();
                let quantity = parseFloat($('#quantity').val()) || 0;
                let availableStock = parseFloat($('#available_stok').val()) || 0;
                let requiredDate = $('#required_date').val();
                let notes = $('#notes').val();
                let detailId = $('#detail_id').val();

                // Validasi input wajib
                if (!productId || quantity <= 0 || !unitId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please fill all required fields! (Product, Quantity, Unit)',
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });
                    return false;
                }

                // Validasi stok tersedia
                if (quantity > availableStock) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Insufficient Stock!',
                        html: `
                Requested quantity <b>${quantity}</b> exceeds
                available stock <b>${availableStock}</b>.
                `,
                        customClass: {
                            confirmButton: 'btn btn-warning'
                        },
                        buttonsStyling: false
                    });

                    $('#quantity').focus();
                    return false;
                }

                // Validasi Duplikasi Produk
                let isDuplicate = false;

                if (prDetailsData && prDetailsData.length > 0) {
                    for (let i = 0; i < prDetailsData.length; i++) {

                        if (prDetailsData[i].product_id == productId) {

                            // Mode Create
                            if (detailId === '') {
                                isDuplicate = true;
                                break;
                            }

                            // Mode Edit
                            if (detailId !== '' && parseInt(i) !== parseInt(detailId)) {
                                isDuplicate = true;
                                break;
                            }
                        }
                    }
                }

                if (isDuplicate) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Product Already Exists!',
                        html: `The product <b>"${productName}"</b> is already registered in the list.`,
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });
                    return false;
                }

                // Susun Object Data
                let itemData = {
                    product_id: productId,
                    data_produk: productName,
                    quantity: quantity,
                    unit_id: unitId,
                    unit: unitName,
                    required_date: requiredDate,
                    notes: notes
                };

                // Create / Update
                if (detailId === '') {
                    prDetailsData.push(itemData);
                } else {
                    prDetailsData[detailId] = itemData;
                }

                // Refresh DataTable
                table.clear();
                table.rows.add(prDetailsData);
                table.draw();

                // Reset Form
                $('#formPrDetail')[0].reset();
                $('#detail_id').val('');
                $('#product_id').val(null).trigger('change');
                $('#unit_id').empty().trigger('change');
                $('#available_stok').val('');

                // Tutup Modal
                $('#modalPrDetail').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: detailId === '' ?
                        'Item added successfully.' : 'Item updated successfully.',
                    timer: 1500,
                    showConfirmButton: false
                });
            });

            let saveAndNew = false;
            let activeBtn = null;

            $(document).on("click", '.card-footer button[type="submit"]', function() {
                saveAndNew = $(this).data("save-and-new");
                activeBtn = $(this);
            });


            $("#postForm").on("submit", function(e) {
                e.preventDefault();

                // 1. Tangkap tombol yang ditekan (lebih akurat)
                let submitter = e.originalEvent.submitter;
                let saveAndNew = $(submitter).data("save-and-new") === true;
                let btnTextOriginal = $(submitter).html();

                // 2. Validasi awal (Frontend)
                if (typeof prDetailsData === "undefined" || prDetailsData.length === 0) {
                    Swal.fire({
                        icon: "warning",
                        title: "Empty Items",
                        text: "Please add at least one item detail to the table before saving.",
                        confirmButtonText: "OK",
                        customClass: {
                            confirmButton: "btn btn-primary waves-effect waves-light",
                        },
                        buttonsStyling: false,
                    });
                    return false;
                }

                // Pindahkan pengecekan warehouse ke sini!
                if ($("#from_warehouse_id").val() === $("#to_warehouse_id").val()) {
                    Swal.fire({
                        icon: "warning",
                        title: "Warning!",
                        text: "Warehouse cannot be the same.",
                        customClass: {
                            confirmButton: "btn btn-primary waves-effect waves-light",
                        },
                        buttonsStyling: false,
                    });
                    return false;
                }

                let formData = new FormData(this);
                formData.append("save_and_new", saveAndNew ? 1 : 0);
                formData.append("items_detail", JSON.stringify(prDetailsData));

                $.ajax({
                    url: $(this).attr("action"),
                    method: $(this).attr("method"),
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: "json",
                    beforeSend: function() {
                        $(submitter).html(
                                '<i class="fa fa-spin fa-spinner me-1"></i> Processing...')
                            .prop("disabled", true);
                        $(".card-footer button").prop("disabled", true);
                    },
                    complete: function() {
                        // Kembalikan teks asli ke tombol yang diklik
                        // $(submitter).html(btnTextOriginal).prop("disabled", false);
                        $(".card-footer button").prop("disabled", false);
                    },
                    success: function(response) {
                        Swal.fire({
                                icon: "success",
                                title: "Success",
                                text: response.message,
                                customClass: {
                                    confirmButton: "btn btn-primary waves-effect waves-light",
                                },
                                buttonsStyling: false,
                            })
                            .then(() => {
                                window.location.href = response.redirect;
                            });
                    },
                    error: function(xhr) {
                        resetValidation();
                        let errors = xhr.responseJSON?.errors;
                        $.each(errors, function(key, value) {
                            displayFieldError(key, value[0]);
                        });
                        Swal.fire({
                            icon: "error",
                            title: "Failed",
                            text: xhr.responseJSON?.message || "Error",
                            customClass: {
                                confirmButton: "btn btn-primary waves-effect waves-light",
                            },
                            buttonsStyling: false,
                        });

                    }
                });
            });


        });
    </script>
@endpush
