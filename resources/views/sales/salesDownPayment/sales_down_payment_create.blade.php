@extends('layouts.app')
@section('title', 'Sales Invoice')
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
            <form action="{{ route('sales-down-payment.store') }}" method="POST" id="postForm" enctype="multipart/form-data">
                @csrf
                <div class="row mb-5">

                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Customer</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="ti ti-user"></i></span>
                                        <select name="customer_id" id="customer_id" class="form-select select2"
                                            data-placeholder="Select Customer">
                                            <option></option>
                                            @foreach ($customer as $cust)
                                                <option value="{{ $cust->id }}" data-alamat="{{ $cust->alamat }}">
                                                    [{{ $cust->id_customer }}] {{ $cust->nama_customer }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <span class="error text-danger" id="customer_idError"></span>
                                </div>

                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Number <small class="text-danger">*</small> </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti ti-barcode"></i></span>
                                    <input type="text" name="sales_downpayment_code" id="sales_downpayment_code"
                                        class="form-control" value="{{ $idNumber }}">
                                </div>
                                <span class="error text-danger" id="sales_invoice_codeError"></span>

                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Date<small class="text-danger">*</small> </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                                    <input type="text" name="sales_downpayment_date" id="sales_downpayment_date"
                                        class="form-control" value="">
                                </div>
                                <span class="error text-danger" id="sales_invoice_dateError"></span>
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
                                        <label class="form-label">Sales Order Number</label>
                                        <div class="input-group input-group-merge">
                                            <span class="input-group-text">
                                                <i class="ti ti-file-text"></i>
                                            </span>
                                            <select name="sales_order_id" id="sales_order_id" class="form-select select2"
                                                data-placeholder="Select Sales Order">
                                                <option></option>
                                            </select>
                                        </div>
                                        <span class="error text-danger" id="sales_order_idError"></span>
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
                                    <label class="form-label">PO Number<small class="text-danger">*</small>
                                    </label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="ti ti-file"></i></span>
                                        <input type="text" class="form-control">
                                    </div>
                                    <span class="error text-danger" id="sales_invoice_dateError"></span>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-left-profile" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3 row">
                                            <label class="col-md-4 col-form-label">Payment Term</label>
                                            <div class="col-md-8">
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-credit-card"></i>
                                                    </span>
                                                    <select name="payment_term_id" id="payment_term_id"
                                                        class="form-select select2" data-placeholder="Select Payment">
                                                        <option></option>
                                                        @foreach ($paymentTerm as $pay)
                                                            <option value="{{ $pay->id }}">{{ $pay->nama }}
                                                            </option>
                                                        @endforeach
                                                        <option></option>
                                                    </select>
                                                </div>
                                                <span class="error text-danger" id="payment_term_idError"></span>
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
                                                <span class="error text-danger" id="payment_term_idError"></span>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-md-4 col-form-label">Description</label>
                                            <div class="col-md-8">
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-file"></i>
                                                    </span>
                                                    <textarea name="" id="" class="form-control" cols="30" rows="10"></textarea>
                                                </div>
                                                <span class="error text-danger" id="payment_term_idError"></span>
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
                    <a href="{{ route('sales-down-payment.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(function() {
            const downPaymentDate = flatpickr("#sales_downpayment_date", {
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
            if (!value) {
                return 0;
            }

            return parseFloat(
                value
                .toString()
                .replace(/\./g, '')
                .replace(',', '.')
            ) || 0;
        }
        $(document).ready(function() {
            $('#customer_id').on('change', function() {
                let customerId = $(this).val();
                let contactDropdown = $('#customer_contact_id');
                contactDropdown.empty().append('<option>Loading...</option>');
                // kosongkan data pajak
                $('#taxpayer_data').val('');
                if (!customerId) {
                    contactDropdown.empty().append('<option value="">Pilih Kontak</option>');
                    return;
                }

                $.ajax({
                    url: '/sales-invoice/' + customerId + '/data',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('#address').val(data.address ?? '');

                    }
                });

                $.ajax({
                    url: "{{ url('sales-down-payment/ajax/customer-sales-order') }}/" + customerId,
                    type: "GET",
                    beforeSend: function() {
                        $('#sales_order_id')
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
                                    ${item.sales_order_code}
                                </option>
                            `;
                        });

                        $('#sales_order_id')
                            .prop('disabled', false)
                            .html(html)
                            .trigger('change');
                    }
                });

            });

            $('#sales_order_id').on('change', function() {

                let salesOrderId = $(this).val();

                // Reset
                $('#down_payment_percent').val('');
                $('#down_payment_amount').val('');

                if (!salesOrderId) {
                    $('#total_order')
                        .val('')
                        .attr('data-value', 0);

                    return;
                }

                $.ajax({
                    url: "{{ url('sales-down-payment/ajax/sales-order') }}/" +
                        salesOrderId +
                        "/down-payment",

                    type: "GET",
                    dataType: "json",

                    beforeSend: function() {
                        $('#down_payment_amount').val('Loading...');
                    },

                    success: function(data) {

                        let salesOrderAmount =
                            parseFloat(data.sales_order_amount) || 0;

                        let totalDP =
                            parseFloat(data.total_down_payment) || 0;

                        let remainingAmount =
                            parseFloat(data.remaining_amount) || 0;


                        // Total Sales Order
                        $('#total_order')
                            .val(formatRupiah(salesOrderAmount))
                            .attr('data-value', salesOrderAmount);


                        // Sisa DP
                        $('#down_payment_amount')
                            .val(formatRupiah(remainingAmount))
                            .attr('data-value', remainingAmount);


                        // Persentase sisa DP
                        let percent = 0;

                        if (salesOrderAmount > 0) {
                            percent =
                                (remainingAmount / salesOrderAmount) * 100;
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
                $('#sales_order_amount').val(
                    parseRupiah($('#sales_order_amount').val())
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
