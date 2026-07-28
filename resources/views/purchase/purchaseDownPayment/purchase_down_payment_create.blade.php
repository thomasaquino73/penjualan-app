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
            <form action="{{ route('purchase-down-payment.store') }}" method="POST" id="postForm"
                enctype="multipart/form-data">
                @csrf
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
                                            @foreach ($supplier as $cust)
                                                <option value="{{ $cust->id }}" data-alamat="{{ $cust->alamat }}">
                                                    [{{ $cust->id_supplier }}] {{ $cust->nama_supplier }}
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
                                        class="form-control" value="{{ $idNumber }}">
                                </div>
                                <span class="error text-danger" id="purchase_invoice_codeError"></span>

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
                                        class="form-control" value="">
                                </div>
                                <span class="error text-danger" id="purchase_invoice_dateError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Due Date<small class="text-danger">*</small> </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                                    <input type="text" name="due_date" id="due_date" class="form-control"
                                        value="">
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
                                        <label class="form-label">Purchase Order No.</label>
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
                                        <input type="text" name="total_order" id="total_order" class="form-control"
                                            readonly>
                                    </div>
                                    <span class="error text-danger" id="total_orderError"></span>
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
                                                    placeholder="0">
                                            </div>
                                        </div>


                                        <div class="col-lg-8">
                                            <div class="input-group input-group-merge">
                                                <span class="input-group-text">
                                                    {{ $company->currency?->symbol ?? 'Rp' }}
                                                </span>

                                                <input type="text" name="down_payment_amount" id="down_payment_amount"
                                                    class="form-control">
                                            </div>
                                            <span class="error text-danger" id="down_payment_amountError"></span>
                                        </div>

                                    </div>

                                </div>
                                <div class="col-lg-6 col-sm-12 mb-3">
                                    <label class="form-label">Invoice No<small class="text-danger">*</small>
                                    </label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="ti ti-file"></i></span>
                                        <input type="text" class="form-control" name="Invoice_number"
                                            id="Invoice_number">
                                    </div>
                                    <span class="error text-danger" id="Invoice_numberError"></span>
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
                                                        @foreach ($suppBank as $suppBank)
                                                            <option value="{{ $suppBank->id }}">
                                                                {{ $suppBank->nama_bank }}
                                                                [{{ $suppBank->nomor_rekening }}]
                                                            </option>
                                                        @endforeach
                                                        <option></option>
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
                                                    <textarea name="address" id="address" class="form-control"></textarea>
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
                                                    <textarea name="description" id="description" class="form-control" cols="30" rows="10"></textarea>
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
                        <i class="fa fa-upload me-1"></i> Save and Close
                    </button>

                    <button type="submit" id="savedatamore" class="btn btn-success" data-save-and-new="true">
                        <i class="fa fa-plus-circle me-1"></i> Save and Create New
                    </button>
                    <a href="{{ route('purchase-down-payment.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(function() {
            const downPaymentDate = flatpickr("#purchase_downpayment_date", {
                enableTime: false,
                dateFormat: "d-m-Y",
                defaultDate: "{{ \Carbon\Carbon::now()->format('d-m-Y') }}",
            });
            const dueDate = flatpickr("#due_date", {
                enableTime: false,
                dateFormat: "d-m-Y",
                defaultDate: "{{ \Carbon\Carbon::now()->format('d-m-Y') }}",
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

            // Format Indonesia: 1.381.090,50
            str = str
                .replace(/\./g, '')
                .replace(',', '.');

            const number = parseFloat(str);

            return isNaN(number) ? 0 : number;
        }
        $(document).ready(function() {
            $('#supplier_id').on('change', function() {

                let supplierId = $(this).val();
                $('#bank_id').empty().append('<option value="">Pilih Rekening</option>');
                if (!supplierId) {
                    return;
                }

                $.ajax({
                    url: '/purchase-order/' + supplierId + '/data',
                    type: 'GET',
                    success: function(response) {

                        // Isi rekening
                        $.each(response.rekening, function(index, item) {

                            $('#bank_id').append(`
                    <option value="${item.id}">
                        ${item.bank_name} - ${item.nomor_rekening}
                        (${item.nama_rekening})
                    </option>
                    `);

                        });

                        $('#bank_id').trigger('change');
                        if (response.supplier) {
                            let alamat = [];
                            if (response.supplier.alamat_pembayaran)
                                alamat.push(response.supplier.alamat_pembayaran);

                            $('#address').val(alamat.join('\n'));
                        }


                    }
                });

                $.ajax({
                    url: "{{ url('purchase-down-payment/ajax/supplier-purchase-order') }}/" +
                        supplierId,
                    type: "GET",
                    beforeSend: function() {
                        $('#purchase_order_id')
                            .prop('disabled', true)
                            .empty()
                            .append('<option value="">Loading...</option>')
                            .trigger('change');
                    },
                    success: function(data) {
                        let html = '<option value="">Select Sales Order</option>';
                        $.each(data, function(i, item) {

                            html += `
                                <option value="${item.id}" data-total="${item.grand_total}">
                                    ${item.code}
                                </option>
                            `;
                        });

                        $('#purchase_order_id')
                            .prop('disabled', false)
                            .html(html)
                            .trigger('change');
                    }
                });

            });

            $('#purchase_order_id').on('change', function() {

                let purchaseOrderId = $(this).val();

                // Reset
                $('#down_payment_percent').val('');
                $('#down_payment_amount').val('');

                if (!purchaseOrderId) {
                    $('#total_order')
                        .val('')
                        .attr('data-value', 0);

                    return;
                }

                $.ajax({
                    url: "{{ url('purchase-down-payment/ajax/purchase-order') }}/" +
                        purchaseOrderId +
                        "/down-payment",

                    type: "GET",
                    dataType: "json",

                    beforeSend: function() {
                        $('#down_payment_amount').val('Loading...');
                    },

                    success: function(data) {

                        let purchaseOrderAmount =
                            parseFloat(data.purchase_order_amount) || 0;

                        let totalDP =
                            parseFloat(data.total_down_payment) || 0;

                        let remainingAmount =
                            parseFloat(data.remaining_amount) || 0;


                        // Total Purchase Order
                        $('#total_order')
                            .val(formatRupiah(purchaseOrderAmount))
                            .attr('data-value', purchaseOrderAmount);


                        // Sisa DP
                        $('#down_payment_amount')
                            .val(formatRupiah(remainingAmount))
                            .attr('data-value', remainingAmount);


                        // Persentase sisa DP
                        let percent = 0;

                        if (purchaseOrderAmount > 0) {
                            percent =
                                (remainingAmount / purchaseOrderAmount) * 100;
                        }

                        $('#down_payment_percent')
                            .val(percent.toFixed(2));
                    },

                    error: function(xhr) {

                        $('#down_payment_amount').val('');

                    }
                });

            });

            // SIMPAN DATA SEMUA
            let saveAndNew = false;
            let activeBtn = null;

            $(document).on("click", '.card-footer button[type="submit"]', function() {
                saveAndNew = $(this).data("save-and-new");
                activeBtn = $(this);
            });


            $("#postForm").on("submit", function(e) {
                e.preventDefault();
                let form = this;
                $('#purchase_order_amount').val(
                    parseRupiah($('#purchase_order_amount').val())
                );

                $('#down_payment_amount').val(
                    parseRupiah($('#down_payment_amount').val())
                );

                $('#paid_amount').val(
                    parseRupiah($('#paid_amount').val())
                );

                $('#remaining_amount').val(
                    parseRupiah($('#remaining_amount').val())
                );
                let formData = new FormData(form);
                if (!activeBtn) {
                    activeBtn = $("#postForm").find(
                        'button[data-save-and-new="false"]',
                    );
                    saveAndNew = false;
                }
                // START LOADING
                activeBtn.html(
                    '<i class="fa fa-spin fa-spinner me-1"></i> Checking...',
                );
                $(".card-footer button").prop("disabled", true);
                formData.append("save_and_new", saveAndNew ? 1 : 0);

                $.ajax({
                    url: $(form).attr("action"),
                    method: $(form).attr("method"),
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: "json",
                    beforeSend: function() {
                        activeBtn.html(
                            '<i class="fa fa-spin fa-spinner me-1"></i> Sending...',
                        );
                        $(".card-footer button").prop("disabled", true);
                    },
                    complete: function() {
                        let closeBtn = $("#postForm").find(
                            'button[data-save-and-new="false"]',
                        );
                        let newBtn = $("#postForm").find(
                            'button[data-save-and-new="true"]',
                        );
                        closeBtn.html(
                            '<i class="fa fa-upload me-1"></i> Save and Close',
                        );
                        newBtn.html(
                            '<i class="fa fa-plus-circle me-1"></i> Save and Create New',
                        );
                        $(".card-footer button").prop("disabled", false);
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: "success",
                            title: "Data Created Successfully",
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
                        resetValidation();
                        let errors = xhr.responseJSON?.errors;
                        $.each(errors, function(key, value) {
                            displayFieldError(key, value[0]);
                        });
                        Swal.fire({
                            icon: "error",
                            title: "Failed to Create Data",
                            text: xhr.responseJSON.message ||
                                "Please check your data again.",
                            customClass: {
                                confirmButton: "btn btn-primary waves-effect waves-light",
                            },
                            buttonsStyling: false,
                        });
                    },
                });
            });
        });

        $('#down_payment_amount').on('input', function() {

            let amount = $(this).val()
                .replace(/[^\d]/g, '');

            amount = parseFloat(amount) || 0;

            let total = parseFloat(
                $('#total_order').attr('data-value')
            ) || 0;


            if (total > 0) {

                let percent = (amount / total) * 100;

                $('#down_payment_percent').val(
                    percent.toFixed(2)
                );

                // Grand Total = Down Payment Amount
                $('#grand_total').val(
                    formatRupiah(amount)
                );

                // Sub Total = Total Order
                $('#sub_total').val(
                    formatRupiah(total)
                );

            } else {

                $('#down_payment_percent').val(0);
                $('#grand_total').val('');
                $('#sub_total').val('');

            }
        });

        $('#down_payment_amount').on('blur', function() {

            let value = $(this).val()
                .replace(/\./g, '')
                .replace(',', '.');


            $(this).val(
                formatRupiah(parseFloat(value) || 0)
            );

        });

        $('#down_payment_percent').on('input', function() {

            let percent = parseFloat($(this).val()) || 0;

            let total = parseFloat(
                $('#total_order').attr('data-value')
            ) || 0;


            let amount = total * percent / 100;


            $('#down_payment_amount').val(
                formatRupiah(amount)
            );


        });
    </script>
@endpush
