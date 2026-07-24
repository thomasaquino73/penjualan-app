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
                <div
                    class="d-flex flex-column flex-md-row gap-2
                    justify-content-start justify-content-lg-end">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary dropdown-toggle waves-effect waves-light"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Get Form
                        </button>
                        <ul class="dropdown-menu">
                            <li><button class="dropdown-item btn-info btn-sm " id="showModalpr">
                                    <i class="ti ti-clipboard me-1"></i>DELIVERY
                                </button></li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
        <div class="card-body table-responsive p-3">
            <form action="{{ route('sales-down-payment.store') }}" method="POST" id="postForm"
                enctype="multipart/form-data">
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
                                    <input type="text" name="sales_invoice_code" id="sales_invoice_code"
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
                                <span class="error text-danger" id="sales_invoice_dateError"></span>
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
                                <div class="col-6 mb-3">
                                    <label class="form-label">Total Order<small class="text-danger">*</small>
                                    </label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>
                                        <input type="text" name="total_order" id="total_order" class="form-control"
                                            readonly>
                                    </div>
                                    <span class="error text-danger" id="sales_invoice_dateError"></span>
                                </div>
                                <div class="col-6 mb-3">
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
                                        </div>

                                    </div>

                                </div>
                                <div class="col-6 mb-3">
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

                <div class="row mb-5">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <div class="row">
                            <div class="col-6 mb-3 ">
                                <label class="form-label" for="sub_total">Sub Total</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>
                                    <input type="number" id="sub_total" name="sub_total" class="form-control"
                                        placeholder="0" readonly>
                                </div>
                            </div>
                            <div class="col-6 mb-3 ">
                                <label class="form-label" for="total">Total</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>
                                    <input type="number" id="total" name="total" class="form-control"
                                        placeholder="0" readonly>
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

                let total = $(this)
                    .find(':selected')
                    .data('total');


                if (total) {

                    // tampilkan format rupiah
                    $('#total_order')
                        .val(formatRupiah(total))
                        .attr('data-value', total);


                } else {

                    $('#total_order')
                        .val('')
                        .attr('data-value', 0);

                }

            });
        });

        $('#down_payment_amount').on('input', function() {

            let amount = $(this).val()
                .replace(/\./g, '')
                .replace(/,/g, '.');


            amount = parseFloat(amount) || 0;


            let total = parseFloat(
                $('#total_order').attr('data-value')
            ) || 0;


            if (total > 0) {

                let percent = (amount / total) * 100;


                $('#down_payment_percent').val(
                    percent.toFixed(2)
                );

            } else {

                $('#down_payment_percent').val(0);

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
