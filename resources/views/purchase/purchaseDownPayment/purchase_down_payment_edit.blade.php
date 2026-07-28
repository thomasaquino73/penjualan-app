@extends('layouts.app')
@section('title', 'Purchase Down Payment')
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

            </div>

        </div>
        <div class="card-body table-responsive p-3">
            <form action="{{ route('purchase-down-payment.update', $model->id) }}" method="POST" id="postForm"
                enctype="multipart/form-data">
                @csrf
                @method('put')
                <div class="row mb-5">

                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Supplier</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="ti ti-user"></i></span>
                                        <select name="supplier_id" id="supplier_id" class="form-select select2"
                                            data-placeholder="Select Supplier">
                                            <option></option>
                                            @foreach ($supplier as $supp)
                                                <option value="{{ $supp->id }}" data-alamat="{{ $supp->alamat }}"
                                                    {{ $supp->id == $model->supplier_id ? 'selected' : '' }}>
                                                    [{{ $supp->id_supplier }}] {{ $supp->nama_supplier }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <span class="error text-danger" id="supplier_idError"></span>
                                </div>

                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Number <small class="text-danger">*</small> </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti ti-barcode"></i></span>
                                    <input type="text" name="purchase_downpayment_code" id="purchase_downpayment_code"
                                        class="form-control" value="{{ $model->purchase_downpayment_code }}">
                                </div>
                                <span class="error text-danger" id="purchase_downpayment_codeError"></span>

                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Date<small class="text-danger">*</small> </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                                    <input type="text" name="purchase_downpayment_date" id="purchase_downpayment_date"
                                        class="form-control"
                                        value="{{ Carbon\Carbon::parse($model->purchase_downpayment_date)->format('d-m-Y') }}">
                                </div>
                                <span class="error text-danger" id="purchase_downpayment_dateError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Due Date<small class="text-danger">*</small> </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                                    <input type="text" name="due_date" id="due_date" class="form-control"
                                        value="{{ $model->due_date ? Carbon\Carbon::parse($model->due_date)->format('d-m-Y') : '' }}">
                                </div>
                                <span class="error text-danger" id="due_dateError"></span>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-xl-12">
                    <div class="nav-align-left mb-4">
                        <ul class="nav nav-pills me-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-pills-left-home" aria-controls="navs-pills-left-home"
                                    aria-selected="false" tabindex="-1">
                                    <i class="ti ti-clipboard-text"></i>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" id='tabIndo'
                                    data-bs-target="#navs-pills-left-profile" aria-controls="navs-pills-left-profile"
                                    aria-selected="false" tabindex="-1">
                                    <i class="ti ti-info-circle"></i>
                                </button>
                            </li>

                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active show" id="navs-pills-left-home" role="tabpanel">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Purchase Order Number</label>
                                        <div class="input-group input-group-merge">
                                            <span class="input-group-text">
                                                <i class="ti ti-file-text"></i>
                                            </span>
                                            <select name="purchase_order_id" id="purchase_order_id"
                                                class="form-select select2" data-placeholder="Select Purchase Order">
                                                <option></option>
                                            </select>
                                        </div>
                                        <span class="error text-danger" id="purchase_order_idError"></span>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-12 mb-3">
                                    <label class="form-label">Total Order<small class="text-danger">*</small>
                                    </label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>
                                        <input type="text" name="purchase_order_amount" id="total_order"
                                            class="form-control"
                                            value="{{ format_rupiah($model->purchase_order_amount, 2) }}" readonly>
                                    </div>
                                    <span class="error text-danger" id="purchase_order_amountError"></span>
                                </div>
                                <div class="col-lg-6 col-sm-12 mb-3">
                                    <label class="form-label">
                                        Down Payment<small class="text-danger">*</small>
                                    </label>

                                    <div class="row">

                                        <div class="col-lg-4">
                                            <div class="input-group input-group-merge">
                                                <span class="input-group-text">
                                                    <i class="ti ti-percentage"></i>
                                                </span>
                                                <input type="number" name="down_payment_percent"
                                                    id="down_payment_percent" class="form-control" step="0.01"
                                                    max="100" placeholder="0"
                                                    value="{{ $model->down_payment_percent }}">
                                            </div>
                                            <span class="error text-danger" id="down_payment_percentError"></span>
                                        </div>


                                        <div class="col-lg-8">
                                            <div class="input-group input-group-merge">
                                                <span class="input-group-text">
                                                    {{ $company->currency?->symbol ?? 'Rp' }}
                                                </span>

                                                <input type="text" name="down_payment_amount" id="down_payment_amount"
                                                    class="form-control"
                                                    value="{{ format_rupiah($model->down_payment_amount, 2) }}">
                                            </div>
                                            <span class="error text-danger" id="down_payment_amountError"></span>
                                        </div>

                                    </div>

                                </div>
                                <div class="col-lg-6 col-sm-12 mb-3">
                                    <label class="form-label">Invoice Number<small class="text-danger">*</small>
                                    </label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="ti ti-file"></i></span>
                                        <input type="text" class="form-control" name="invoice_number"
                                            id="invoice_number" value="{{ $model->invoice_number }}">
                                    </div>
                                    <span class="error text-danger" id="invoice_numberError"></span>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-left-profile" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3 row">
                                            <label class="col-md-4 col-form-label">Bank Account</label>
                                            <div class="col-md-8">
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-credit-card"></i>
                                                    </span>
                                                    <select name="bank_id" id="bank_id" class="form-select select2"
                                                        data-placeholder="Select Bank Account">
                                                        <option></option>
                                                        @foreach ($suppBank as $bank)
                                                            <option value="{{ $bank->id }}"
                                                                {{ $bank->id == $model->bank_id ? 'selected' : '' }}>
                                                                {{ $bank->nama_bank }}
                                                                [{{ $bank->nomor_rekening }}]
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <span class="error text-danger" id="bank_idError"></span>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-md-4 col-form-label">Address</label>
                                            <div class="col-md-8">
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-map"></i>
                                                    </span>
                                                    <textarea name="address" id="address" class="form-control">{{ $model->address }}</textarea>
                                                </div>
                                                <span class="error text-danger" id="addressError"></span>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-md-4 col-form-label">Description</label>
                                            <div class="col-md-8">
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-file"></i>
                                                    </span>
                                                    <textarea name="description" id="description" class="form-control" cols="30" rows="10">{{ $model->description }}</textarea>
                                                </div>
                                                <span class="error text-danger" id="descriptionError"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="card-footer d-flex justify-content-end gap-2">

                    <button type="submit" id="savedata" class="btn btn-primary" data-save-and-new="false">
                        <i class="fa fa-save me-1"></i> update
                    </button>
                    <a href="{{ route('purchase-down-payment.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        const existingSupplierId = @json($model->supplier_id);
        const existingPurchaseOrderId = @json($model->purchase_order_id);
    </script>
    <script>
        $(function() {
            flatpickr("#purchase_downpayment_date", {
                enableTime: false,
                dateFormat: "d-m-Y",
            });
            flatpickr("#due_date", {
                enableTime: false,
                dateFormat: "d-m-Y",
            });
        });

        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(number);
        }

        function parseRupiah(value) {
            if (value === null || value === undefined || value === '') {
                return 0;
            }
            let str = value.toString().trim();
            str = str.replace(/\./g, '').replace(',', '.');
            const number = parseFloat(str);
            return isNaN(number) ? 0 : number;
        }

        // ==========================================
        // FUNGSI LOAD DETAIL PURCHASE ORDER
        // ==========================================
        function loadPurchaseOrderData(purchaseOrderId, isEdit = false) {
            if (!purchaseOrderId) return;

            const selectedOption = $('#purchase_order_id').find('option:selected');
            let totalOrder = selectedOption.data('total');

            if (totalOrder) {
                $('#total_order').val(formatRupiah(totalOrder)).attr('data-value', totalOrder);
            } else {
                let modelTotal = @json($model->purchase_order_amount ?? 0);
                $('#total_order').val(formatRupiah(modelTotal)).attr('data-value', modelTotal);
            }
        }

        $(document).ready(function() {

            function loadPurchaseOrders(supplierId, selectedPurchaseOrderId = null) {
                const dropdown = $('#purchase_order_id');

                if (!supplierId) {
                    dropdown.prop('disabled', true)
                        .empty()
                        .append('<option value="">Select Purchase Order</option>')
                        .trigger('change');
                    return;
                }

                $.ajax({
                    url: "{{ url('purchase-down-payment/ajax/edit-supplier-purchase-order') }}/" +
                        supplierId,
                    type: "GET",
                    dataType: "json",
                    beforeSend: function() {
                        dropdown.prop('disabled', true)
                            .empty()
                            .append('<option value="">Loading...</option>')
                            .trigger('change');
                    },
                    success: function(data) {
                        let html = '<option value="">Select Purchase Order</option>';

                        $.each(data, function(i, item) {
                            const selected = String(item.id) === String(
                                selectedPurchaseOrderId) ? 'selected' : '';
                            html += `
                                <option value="${item.id}" data-total="${item.grand_total}" ${selected}>
                                    ${item.code}
                                </option>
                            `;
                        });

                        dropdown.prop('disabled', false)
                            .html(html)
                            .trigger('change');

                        if (selectedPurchaseOrderId) {
                            loadPurchaseOrderData(selectedPurchaseOrderId, true);
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        dropdown.prop('disabled', false)
                            .empty()
                            .append('<option value="">Select Purchase Order</option>')
                            .trigger('change');
                    }
                });
            }

            $('#supplier_id').on('change', function() {
                const supplierId = $(this).val();

                if (!supplierId) {
                    $('#purchase_order_id').prop('disabled', true).empty().append(
                        '<option value="">Select Purchase Order</option>').trigger('change');
                    $('#bank_id').empty().append('<option value="">Select Bank Account</option>').trigger(
                        'change');
                    $('#address').val('');
                    return;
                }

                loadPurchaseOrders(supplierId);

                // Load Bank & Address
                $.ajax({
                    url: '/purchase-order/' + supplierId + '/data',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        let bankHtml = '<option value="">Select Bank Account</option>';
                        $.each(response.rekening || [], function(index, item) {
                            bankHtml += `
                                <option value="${item.id}">
                                    ${item.bank_name} - ${item.nomor_rekening}
                                    ${item.nama_rekening ? '(' + item.nama_rekening + ')' : ''}
                                </option>
                            `;
                        });
                        $('#bank_id').html(bankHtml).trigger('change');

                        if (response.supplier) {
                            let alamat = [];
                            if (response.supplier.alamat_pembayaran) {
                                alamat.push(response.supplier.alamat_pembayaran);
                            }
                            $('#address').val(alamat.join('\n'));
                        }
                    }
                });
            });

            $('#purchase_order_id').on('change', function() {
                const purchaseOrderId = $(this).val();
                if (!purchaseOrderId) {
                    $('#total_order').val('').attr('data-value', 0);
                    return;
                }
                loadPurchaseOrderData(purchaseOrderId, false);
            });

            // Trigger saat halaman pertama kali dimuat jika ada data existing
            if (existingSupplierId) {
                $('#total_order').attr('data-value', @json($model->purchase_order_amount ?? 0));
                loadPurchaseOrders(existingSupplierId, existingPurchaseOrderId);
            }

            // Form Submit Handler
            let saveAndNew = false;
            let activeBtn = null;

            $(document).on("click", '.card-footer button[type="submit"]', function() {
                saveAndNew = $(this).data("save-and-new");
                activeBtn = $(this);
            });

            $("#postForm").on("submit", function(e) {
                e.preventDefault();
                let form = this;
                let formData = new FormData(form);

                if (!activeBtn) {
                    activeBtn = $("#postForm").find('button[data-save-and-new="false"]');
                    saveAndNew = false;
                }

                $.ajax({
                    url: $(form).attr("action"),
                    method: $(form).attr("method"),
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: "json",
                    beforeSend: function() {
                        activeBtn.html('<i class="fa fa-spin fa-spinner me-1"></i> Sending...');
                        $(".card-footer button").prop("disabled", true);
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: "success",
                            title: "Data Updated Successfully",
                            text: response.message,
                            customClass: {
                                confirmButton: "btn btn-primary waves-effect waves-light",
                            },
                            buttonsStyling: false,
                        }).then(() => {
                            window.location.href = response.redirect;
                        });
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors;
                        if (errors) {
                            $.each(errors, function(key, value) {
                                if (typeof displayFieldError === 'function') {
                                    displayFieldError(key, value[0]);
                                }
                            });
                        }
                        Swal.fire({
                            icon: "error",
                            title: "Failed to Update Data",
                            text: xhr.responseJSON?.message ||
                                "Please check your data again.",
                            customClass: {
                                confirmButton: "btn btn-primary waves-effect waves-light",
                            },
                            buttonsStyling: false,
                        });
                        $(".card-footer button").prop("disabled", false);
                        activeBtn.html('<i class="fa fa-save me-1"></i> update');
                    }
                });
            });

        });

        // Kalkulasi Down Payment
        $('#down_payment_amount').on('input', function() {
            let amount = parseFloat($(this).val().replace(/[^\d]/g, '')) || 0;
            let total = parseFloat($('#total_order').attr('data-value')) || 0;

            if (total > 0) {
                let percent = (amount / total) * 100;
                $('#down_payment_percent').val(percent.toFixed(2));
            } else {
                $('#down_payment_percent').val(0);
            }
        });

        $('#down_payment_percent').on('input', function() {
            let percent = parseFloat($(this).val()) || 0;
            let total = parseFloat($('#total_order').attr('data-value')) || 0;
            let amount = total * percent / 100;

            $('#down_payment_amount').val(formatRupiah(amount));
        });
    </script>
@endpush
