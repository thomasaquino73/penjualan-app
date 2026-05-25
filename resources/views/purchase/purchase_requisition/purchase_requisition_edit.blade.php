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
                <div class="d-flex flex-column flex-md-row gap-2 justify-content-start justify-content-lg-end">
                </div>
            </div>
        </div>

        <div class="card-body table-responsive p-3">
            <form
                action="{{ isset($model) ? route('permintaan-pembelian.update', $model->id) : route('permintaan-pembelian.store') }}"
                method="POST" id="postForm" enctype="multipart/form-data">
                @csrf
                @if (isset($model))
                    @method('PUT')
                @endif

                <input type="hidden" name="items_detail" id="items_detail">
                <input type="hidden" name="save_and_new" id="save_and_new" value="0">

                <div class="row mb-5">
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Request Number<small class="text-danger">*</small></label>
                                <input type="text" name="code" id="code" class="form-control"
                                    value="{{ isset($model) ? $model->code : $idNumber }}"
                                    {{ isset($model) ? 'readonly' : '' }}>
                                <span class="error text-danger" id="codeError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Request Date<small class="text-danger">*</small></label>
                                <input type="" name="date" id="date" class="form-control"
                                    value="{{ isset($model) ? $model->date : date('Y-m-d') }}">
                                <span class="error text-danger" id="dateError"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control">{{ isset($model) ? $model->description : '' }}</textarea>
                            <span class="error text-danger" id="descriptionError"></span>
                        </div>
                    </div>
                </div>

                <div class="divider divider-dashed">
                    <div class="divider-text">Purchase Requisition Detail</div>
                </div>

                <div class="row mt-3">
                    <table class="table display responsive nowrap" id="table">
                        <thead class="border-top" style="background-color: #AEDEFC;">
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Unit</th>
                                <th>Required Date</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                    </table>
                </div>

                <div class="card-footer d-flex justify-content-end gap-2 mt-4">
                    <button type="submit" id="savedata" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Update
                    </button>
                    <a href="{{ route('permintaan-pembelian.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalPrDetail">
        <div class="modal-dialog modal-xl">
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
                                    @foreach ($product as $p)
                                        <option value="{{ $p->id }}">{{ $p->nama_barang }}</option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="product_idError"></span>
                            </div>

                            <div class="col-3 mb-3">
                                <label class="form-label" for="quantity">Quantity</label>
                                <input type="number" id="quantity" name="quantity" class="form-control" placeholder="0"
                                    min="1">
                                <span class="error text-danger" id="quantityError"></span>
                            </div>
                            <div class="col-3 mb-3">
                                <label class="form-label" for="unit_id">Unit</label>
                                <select name="unit_id" id="unit_id" class="form-select select2-modal"
                                    data-placeholder="Select Unit">
                                    <option></option>
                                </select>
                                <span class="error text-danger" id="unit_idError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label" for="required_date">Required Date</label>
                                <input type="text" id="required_date" name="required_date" class="form-control"
                                    placeholder="Select Date">
                                <span class="error text-danger" id="required_dateError"></span>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label" for="notes">Notes</label>
                                <input type="text" id="notes" name="notes" class="form-control"
                                    placeholder="Enter notes">
                                <span class="error text-danger" id="notesError"></span>
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
        let prDetailsData = [
            @if (isset($model))
                @foreach ($model->details as $detail)
                    {
                        'product_id': '{{ $detail->product_id }}',
                        'data_produk': '{{ $detail->produkID ? $detail->produkID->nama_barang : 'Product Not Found' }}',
                        'quantity': '{{ $detail->qty }}',
                        'unit_id': '{{ $detail->unit_id }}',
                        'required_date': '{{ $detail->required_date }}',
                        'notes': '{{ $detail->notes }}',
                        'unit': '{{ $detail->unitID ? $detail->unitID->name ?? ($detail->unitID->detail ?? $detail->unitID->nama) : 'Unit' }}'
                    }
                    {{ !$loop->last ? ',' : '' }}
                @endforeach
            @endif
        ];

        // BACKUP: Copy initial data to a new variable using JSON parse/stringify to prevent reference binding (deep copy)
        const originalPrDetailsData = JSON.parse(JSON.stringify(prDetailsData));
        $(function() {

            const datePicker = flatpickr("#date", {
                enableTime: false,
                dateFormat: "d-m-Y",
                minDate: "today",
                defaultDate: "{{ \Carbon\Carbon::now()->format('d-m-Y') }}"
            });

            const expectedPicker = flatpickr("#required_date", {
                enableTime: false,
                dateFormat: "d-m-Y",
                minDate: "today",

                onChange: function(selectedDates, dateStr) {
                    if (selectedDates.length > 0) {
                        // set max date untuk PO Date
                        datePicker.set('maxDate', selectedDates[0]);

                        // ambil tanggal PO sekarang
                        let poDate = datePicker.selectedDates[0];

                        // kalau PO Date > Expected Date → reset
                        if (poDate && poDate > selectedDates[0]) {
                            datePicker.clear();
                        }
                    }
                }
            });

        });
        $(document).ready(function() {
            if ($.fn.select2) {
                $('.select2-modal').each(function() {
                    var $this = $(this);
                    $this.wrap('<div class="position-relative"></div>').select2({
                        placeholder: $this.attr('data-placeholder') || 'Select an option',
                        width: '100%',
                        dropdownParent: $(
                            '#modalPrDetail'
                        ) // Prevents scrolling & typing bugs in Bootstrap Modal
                    });
                });
            }

            let table = $('#table').DataTable({
                data: prDetailsData,
                dom: '<"d-flex justify-content-between align-items-center mb-3"B>t<"d-flex justify-content-between mt-3"ip>',
                select: {
                    style: 'single'
                },
                columns: [{
                        data: null,
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
                    {
                        data: 'required_date'
                    },
                    {
                        data: 'notes'
                    }
                ],
                buttons: [{
                        text: '<i class="ti ti-plus me-1"></i> New',
                        className: 'btn btn-primary btn-sm me-2',
                        action: function(e, dt, node, config) {
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

                            // Temporarily store the old unit ID in jQuery memory
                            $('#unit_id').data('pending-val', data.unit_id);
                            $('#product_id').val(data.product_id).trigger('change');
                            $('#required_date').val(data.required_date);
                            $('#notes').val(data.notes);

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
                                    // Remove item from local array based on index
                                    prDetailsData.splice(rowIndex, 1);

                                    // Refresh visual table view
                                    dt.clear().rows.add(prDetailsData).draw();

                                    toastr.success('Deleted Data Successfully', '', {
                                        timeOut: 1500,
                                        progressBar: true
                                    });
                                }
                            });
                        }
                    },
                    {
                        text: '<i class="ti ti-refresh me-1"></i> Refresh',
                        className: 'btn btn-secondary btn-sm',
                        action: function(e, dt, node, config) {
                            Swal.fire({
                                title: 'Reset Table?',
                                text: "All temporary changes will be discarded and the original data from the database will be restored!",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Yes, Reset!',
                                cancelButtonText: 'Cancel',
                                customClass: {
                                    confirmButton: 'btn btn-danger',
                                    cancelButton: 'btn btn-secondary'
                                },
                                buttonsStyling: false
                            }).then(function(result) {
                                if (result.isConfirmed) {
                                    // 1. Restore prDetailsData using original data backup
                                    prDetailsData = JSON.parse(JSON.stringify(
                                        originalPrDetailsData));

                                    // 2. Clear visual DataTables, populate with restored data, and redraw
                                    dt.clear().rows.add(prDetailsData).draw();

                                    // 3. Display success notification
                                    toastr.success(
                                        'Data successfully restored to original settings',
                                        '', {
                                            timeOut: 1500,
                                            progressBar: true
                                        });
                                }
                            });
                        }
                    }
                ]
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

                        // Lock unit ID if currently in EDIT mode process
                        let pendingUnitId = unitSelect.data('pending-val');
                        if (pendingUnitId) {
                            unitSelect.val(pendingUnitId).trigger('change');
                            unitSelect.removeData('pending-val');
                        }
                    },
                    error: function() {
                        console.error('Failed to fetch unit data.');
                        unitSelect.empty().append('<option></option>').prop('disabled', false)
                            .trigger('change');
                    }
                });
            });

            $('#formPrDetail').on('submit', function(e) {
                e.preventDefault();

                let productId = $('#product_id').val();
                let productName = $('#product_id option:selected').text();
                let quantity = $('#quantity').val();
                let unitId = $('#unit_id').val();
                let unitName = $('#unit_id option:selected').text();
                let detailId = $('#detail_id').val();
                let requiredDate = $('#required_date').val();
                let notes = $('#notes').val();

                if (!productId || !quantity || !unitId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please fill all fields!',
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });
                    return false;
                }

                // Check for duplicate products in local array
                let isDuplicate = false;
                if (prDetailsData && prDetailsData.length > 0) {
                    for (let i = 0; i < prDetailsData.length; i++) {
                        if (prDetailsData[i].product_id == productId) {
                            if (detailId === '') {
                                isDuplicate = true;
                                break;
                            } else if (detailId !== '' && i != detailId) {
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
                        html: `The product <b>"${productName}"</b> is already registered in the details list.`,
                        confirmButtonColor: '#3085d6',
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });
                    return false;
                }

                let itemData = {
                    'product_id': productId,
                    'data_produk': productName,
                    'quantity': quantity,
                    'unit_id': unitId,
                    'unit': unitName,
                    'required_date': requiredDate,
                    'notes': notes
                };

                if (detailId === '') {
                    prDetailsData.push(itemData);
                } else {
                    prDetailsData[detailId] = itemData;
                }

                table.clear().rows.add(prDetailsData).draw();
                $('#modalPrDetail').modal('hide');
            });


            $('#postForm').on('submit', function(e) {
                e.preventDefault();
                var form = this;
                if (prDetailsData.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Details Empty!',
                        text: 'You must add at least 1 product detail.',
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });
                    return false;
                }

                // Set flags JSON data array & action save type
                $('#items_detail').val(JSON.stringify(prDetailsData));
                $.ajax({
                    url: $(form).attr('action'),
                    method: $(form).attr('method'),
                    data: new FormData(form),
                    processData: false,
                    contentType: false,
                    dataType: 'json',

                    beforeSend: function() {
                        $('#savedata').html(
                            '<i class="fa fa-spin fa-spinner me-1"></i> Sending...');
                    },

                    complete: function() {
                        $('#savedata').html('<i class="fa fa-save me-1"></i> Save');
                    },

                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            showClass: {
                                popup: 'animate__animated animate__bounceIn'
                            },
                            customClass: {
                                confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                            buttonsStyling: false
                        });

                        window.location.href = response.redirect;
                    },

                    error: function(xhr) {
                        resetValidation();

                        Swal.fire({
                            icon: 'error',
                            title: 'Update Failed',
                            text: 'Please check your input data.',
                            showClass: {
                                popup: 'animate__animated animate__bounceIn'
                            },
                            customClass: {
                                confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                            buttonsStyling: false
                        });

                        let errors = xhr.responseJSON?.errors;

                        $.each(errors, function(key, value) {
                            displayFieldError(key, value[0]);
                        });
                    }
                });
            });
        });
    </script>
@endpush
