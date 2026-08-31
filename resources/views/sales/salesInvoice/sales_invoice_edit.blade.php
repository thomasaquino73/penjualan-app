@extends('layouts.app')
@section('title', 'Sales Quotation')
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
                            <li><button class="dropdown-item btn-info btn-sm " id="showModaldv">
                                    <i class="ti ti-clipboard me-1"></i>DELIVERY
                                </button></li>
                            <li><button class="dropdown-item btn-primary btn-sm " id="showModaldp">
                                    <i class="ti ti-clipboard me-1"></i>DOWN PAYMENT
                                </button></li>
                            {{-- <li><button class="dropdown-item btn-success btn-sm " id="showModalso">
                                    <i class="ti ti-clipboard me-1"></i>SALES ORDER
                                </button></li> --}}
                        </ul>
                    </div>
                </div>
            </div>

        </div>
        <div class="card-body table-responsive p-3">
            <form action="{{ route('sales-invoice.update', $model->id) }}" method="POST" id="postForm"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="sales_order_id" name="sales_order_id">

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
                                                <option value="{{ $cust->id }}" data-alamat="{{ $cust->alamat }}"
                                                    {{ $model->customer_id == $cust->id ? 'selected' : '' }}>
                                                    [{{ $cust->id_customer }}] {{ $cust->nama_customer }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <span class="error text-danger" id="customer_idError"></span>
                                </div>

                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">SI Number <small class="text-danger">*</small> </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti ti-barcode"></i></span>
                                    <input type="text" name="sales_invoice_code" id="sales_invoice_code"
                                        class="form-control" value="{{ $model->sales_invoice_code }}">
                                </div>
                                <span class="error text-danger" id="sales_invoice_codeError"></span>

                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Tax Invoice Number<small class="text-danger">*</small> </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti ti-barcode"></i></span>
                                    <input type="text" name="no_faktur_pajak" id="no_faktur_pajak" class="form-control"
                                        value="{{ $model->no_faktur_pajak }}">
                                </div>
                                <span class="error text-danger" id="no_faktur_pajakError"></span>

                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row">

                            <div class="col-6 mb-3">
                                <label class="form-label">Date<small class="text-danger">*</small> </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                                    <input type="text" name="sales_invoice_date" id="sales_invoice_date"
                                        class="form-control"
                                        value="{{ Carbon\Carbon::parse($model->sales_invoice_date)->format('d-m-Y') }}">
                                </div>
                                <span class="error text-danger" id="sales_invoice_dateError"></span>

                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="salesman_id">Salesman</label>
                                <select name="salesman_id" id="salesman_id" class="form-select select2"
                                    data-placeholder="Select Salesman">
                                    <option></option>
                                    @foreach ($salesman as $salesman)
                                        <option value="{{ $salesman->id }}"
                                            {{ $model->salesman_id == $salesman->id ? 'selected' : '' }}>
                                            {{ $salesman->fullname }}</option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="salesman_idError"></span>
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
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                    id='tabIndo' data-bs-target="#navs-pills-left-profile"
                                    aria-controls="navs-pills-left-profile" aria-selected="false" tabindex="-1">
                                    <i class="ti ti-info-circle"></i>
                                </button>
                            </li>

                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active show" id="navs-pills-left-home" role="tabpanel">
                                @include('sales.salesInvoice.part.table_sales_invoice')

                            </div>
                            <div class="tab-pane fade" id="navs-pills-left-profile" role="tabpanel">
                                @include('sales.salesInvoice.part.info_sales_invoice_edit')

                            </div>

                        </div>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-7"></div>
                    <div class="col-md-5">
                        <div class="col-12 mb-3 ">
                            <label class="form-label" for="sub_total">Sub Total</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>
                                <input type="number" id="sub_total" name="sub_total" class="form-control"
                                    placeholder="0" value="{{ $model->sub_total ?? 0 }}" readonly>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label" for="discount_all">Discount</label>
                            <div class="row">
                                <div class="col-4">
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text">%</span>
                                        <input type="number" id="percent" name="percent" min="0"
                                            step="any" class="form-control" placeholder="0"
                                            value="{{ $model->disc_percent ?? 0 }}">
                                        <span class="text-danger" id="discountError"></span>
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>
                                        <input type="number" id="discount_all" name="discount_all" class="form-control"
                                            placeholder="0" min='0' value="{{ $model->disc_nominal ?? 0 }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-3 " id="ppn_container" style="display:none;">
                            <label class="form-label" for="sub_total" id="taxes">Tax</label>
                            <div class="input-group input-group-merge">
                                <input type="text" name="tax_amount" id="tax_amount" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label" for="biaya_lain"> <strong>Biaya Lain-lain</strong></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>
                                <input type="number" id="biaya_lain" name="biaya_lain" class="form-control"
                                    placeholder="0" value="{{ $model->biaya_lain ?? 0 }}">
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label" for="total_order"> <strong>Total Order</strong></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>
                                <input type="number" id="total_order" name="total_order" class="form-control"
                                    placeholder="0" readonly value="{{ $model->grand_total ?? 0 }}">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <button type="submit" id="savedata" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Update
                    </button>
                    <a href="{{ route('sales-invoice.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    @include('sales.salesInvoice.part.modal_sales_invoice')
    @include('sales.salesInvoice.part.modalDeliveryOrder')
    @include('sales.salesInvoice.part.modalDownPayment')
@endsection
@include('partials.tabel.css')
@include('partials.tabel.js')
@include('partials.button.btn_addshipping')
@include('partials.button.btn_addpayment')
@include('partials.button.btn_submitform')
@include('partials.button.select2_modal')
@include('partials.js.calculate_total')
@include('partials.js.loadAvailableStock')
@include('sales.salesInvoice.part.loadCustomerAddress')
@include('sales.salesInvoice.part.js')
@push('scripts')
    <script>
        const paymentType = @json($model->payment_type);
        const proformaId = @json($model->proforma_id);
        const downPaymentId = @json($model->sales_down_payment_id);
        const totalDP = @json($model->total_dp);
    </script>
    <script>
        $(function() {

            if (totalDP) {
                $("#total_dp").val(formatRupiah(totalDP));
            }

            if (paymentType) {

                $("input[name='payment_type'][value='" + paymentType + "']")
                    .prop("checked", true);

                if (paymentType === "proforma") {

                    $("#proforma_id").prop("disabled", false);
                    $("#pelunasan_id").prop("disabled", true);

                    loadReference(proformaId);

                } else if (paymentType === "pelunasan") {

                    $("#proforma_id").prop("disabled", true);
                    $("#pelunasan_id").prop("disabled", false);

                    loadReference(downPaymentId);

                } else if (paymentType === "no_down_payment") {

                    $("#proforma_id").prop("disabled", true);
                    $("#pelunasan_id").prop("disabled", true);

                    $("#total_dp").val("");
                }
            }

        });
        $(function() {
            const datePicker = flatpickr("#sales_invoice_date", {
                enableTime: false,
                dateFormat: "d-m-Y",
            });
        });
        // Show Modal Proforma
        $("#showModaldv").on("click", function(e) {
            e.preventDefault();

            var customerId = $("#customer_id").val();
            $("#sq_number")
                .empty()
                .append('<option value="">Select Quotation</option>')
                .val(null)
                .trigger("change");
            if (!customerId) {
                Swal.fire({
                    icon: "warning",
                    title: "Warning!",
                    text: "Please select Customer first before adding new data.",
                    confirmButtonColor: "#3085d6",
                    confirmButtonText: "OK",
                    customClass: {
                        confirmButton: "btn btn-danger",
                    },
                    buttonsStyling: false,
                });
                return;
            }

            $.ajax({
                url: "/sales-invoice/get-delivery/" + customerId,
                type: "GET",
                success: function(response) {

                    let option = '<option value="">Select Delivery</option>';

                    $.each(response, function(i, item) {
                        option += `<option value="${item.id}">
                                ${item.delivery_order_code}
                           </option>`;
                    });

                    $("#sq_number").html(option);

                    $("#modalDeliveryDetail").modal("show");
                }
            });
        });

        $('#sq_number').on('change', function() {

            let quotationIds = $(this).val();

            if (!quotationIds || quotationIds.length === 0) {
                $('#orderTableBody').html('');
                return;
            }

            $.ajax({
                url: "{{ route('sales-invoice.getDeliveryDetail') }}",
                type: "POST",
                data: {
                    quotation_ids: quotationIds,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    let html = '';

                    if (response.success && response.data.length > 0) {
                        $.each(response.data, function(index, item) {
                            let safeProductName = (item.product_name || '').replace(/"/g,
                                '&quot;');

                            // Konversi ke angka yang valid sebelum toLocaleString
                            let price = parseFloat(item.unit_price) || 0;
                            let discount = parseFloat(item.discount) || 0;
                            let amount = parseFloat(item.amount) || 0;

                            html += `
                                <tr>
                                    <td>
                                        <input class="form-check-input checkItem" type="checkbox"
                                            data-id="${item.id}"
                                            data-product_id="${item.product_id}"
                                            data-product_name="${safeProductName}"
                                            data-qty="${item.qty}"
                                            data-outstanding_qty="${item.outstanding_qty}"
                                            data-unit_id="${item.unit_id}"
                                            data-unit_name="${item.unit_name}"
                                            data-warehouse_id="${item.warehouse_id}"
                                            data-warehouse_name="${item.warehouse_name}"
                                            data-unit_price="${price}"
                                            data-discount="${discount}"
                                            data-amount="${amount}"
                                            data-delivery_order_code="${item.order_code}"
                                        >
                                    </td>
                                    <td>${item.product_name}</td>
                                    <td class="text-end">${item.qty}</td>
                                    <td>${item.unit_name}</td>
                                    <td class="text-end">${price.toLocaleString('id-ID')}</td>
                                    <td class="text-end">${discount.toLocaleString('id-ID')}</td>
                                    <td class="text-end">${amount.toLocaleString('id-ID')}</td>
                                </tr>`;
                        });
                    } else {
                        html =
                            '<tr><td colspan="7" class="text-center">Tidak ada data ditemukan</td></tr>';
                    }

                    $("#orderTableBody").html(html);
                }
            });

        });

        $("#showModaldp").on("click", function(e) {
            e.preventDefault();

            var customerId = $("#customer_id").val();
            $("#dp_number")
                .empty()
                .append('<option value="">Select Down Payment</option>')
                .val(null)
                .trigger("change");
            if (!customerId) {
                Swal.fire({
                    icon: "warning",
                    title: "Warning!",
                    text: "Please select Customer first before adding new data.",
                    confirmButtonColor: "#3085d6",
                    confirmButtonText: "OK",
                    customClass: {
                        confirmButton: "btn btn-danger",
                    },
                    buttonsStyling: false,
                });
                return;
            }

            $.ajax({
                url: "/sales-invoice/get-down-payment/" + customerId,
                type: "GET",
                success: function(response) {

                    let option = '<option value="">Select Down Payment</option>';

                    $.each(response, function(i, item) {
                        option += `<option value="${item.id}">
                                ${item.sales_downpayment_code}
                           </option>`;
                    });

                    $("#dp_number").html(option);

                    $("#modalDownPayment").modal("show");
                }
            });
        });
        //  LOGIC LOCK: CHECK ALL / UNCHECK ALL
        $("#checkAll").on("change", function() {
            // Jika checkAll dicentang, semua .checkItem ikut dicentang, begitu sebaliknya
            $(".checkItem").prop("checked", $(this).prop("checked"));
        });

        // Jika salah satu item diuncheck secara manual, matikan checkAll di atas head tabel
        $(document).on("change", ".checkItem", function() {
            if ($(".checkItem:checked").length === $(".checkItem").length) {
                $("#checkAll").prop("checked", true);
            } else {
                $("#checkAll").prop("checked", false);
            }
        });

        $(document).ready(function() {

            // 🔥 SET STATE AWAL checkbox
            if ($("#kena_pajak").is(":checked")) {
                $("#tax_container").show();
                $("#ppn_container").show();
            } else {
                $("#tax_container").hide();
                $("#ppn_container").hide();
            }

            // 🔥 kalau sudah ada tax_id dari DB, jangan override default
            let existingTaxId = $("#tax_id").val();

            if ($("#kena_pajak").is(":checked")) {
                if (!existingTaxId && DEFAULT_TAX_ID) {
                    $("#tax_id").val(numeral(DEFAULT_TAX_ID).format('0,0.00'));
                }
            }

            // 🔥 WAJIB: hitung ulang saat pertama load
            calculateTotalOrder();
            calculateGrandTotal();
        });
    </script>
    <script>
        let prDetailsData = [
            @if (isset($jsonDetails))
                @foreach ($jsonDetails as $detail)
                    {
                        id: @json($detail['id'] ?? null),
                        sales_invoice_id: @json($detail['sales_invoice_id'] ?? null),
                        urutan: @json($detail['urutan'] ?? 0),
                        sales_order_id: @json($detail['sales_order_id'] ?? null),
                        sales_order_detail_id: @json($detail['sales_order_detail_id'] ?? null),
                        detail_id: @json($detail['sales_order_detail_id'] ?? null),
                        order_code: @json($detail['order_code'] ?? null),
                        // product_id: @json($detail['product_id'] ?? null),
                        product_id: @json($detail['product_id'] ?? null),
                        data_produk: @json($detail['data_produk'] ?? ($detail['product_name'] ?? 'Product Not Found')),
                        // data_produk: @json($detail['data_produk'] ?? null),
                        quantity: @json($detail['quantity'] ?? 0),

                        // =====================================================
                        // UNIT
                        // =====================================================
                        unit_id: @json($detail['unit_id'] ?? null),

                        unit: @json($detail['unit'] ?? null),

                        // =====================================================
                        // WAREHOUSE
                        // =====================================================
                        warehouse_id: @json($detail['warehouse_id'] ?? null),

                        warehouse: @json($detail['warehouse'] ?? null),

                        // =====================================================
                        // PRICE
                        // =====================================================
                        unit_price: @json($detail['unit_price'] ?? 0),

                        // =====================================================
                        // DISCOUNT
                        // =====================================================
                        discount_percent: @json($detail['discount_percent'] ?? 0),

                        discount: @json($detail['discount'] ?? 0),

                        // =====================================================
                        // AMOUNT
                        // =====================================================
                        amount: @json($detail['amount'] ?? 0),

                        // =====================================================
                        // TAX
                        // =====================================================
                        tax: @json($detail['tax'] ?? 0),

                        // =====================================================
                        // SALES ORDER QUOTA
                        // =====================================================
                        sisa_so: @json($detail['sisa_so'] ?? null),

                        kuota_asli_so: @json($detail['kuota_asli_so'] ?? null),

                        total_diambil_lainnya: @json($detail['total_diambil_lainnya'] ?? 0)
                    }
                    {{ !$loop->last ? ',' : '' }}
                @endforeach
            @endif
        ];
        const originalPrDetailsData = JSON.parse(JSON.stringify(prDetailsData));

        $(function() {
            const datePicker = flatpickr("#sales_invoice_date", {
                enableTime: false,
                dateFormat: "d-m-Y",
            });
        });


        //  LOGIC LOCK: CHECK ALL / UNCHECK ALL
        $("#checkAll").on("change", function() {
            // Jika checkAll dicentang, semua .checkItem ikut dicentang, begitu sebaliknya
            $(".checkItem").prop("checked", $(this).prop("checked"));
        });

        // Jika salah satu item diuncheck secara manual, matikan checkAll di atas head tabel
        $(document).on("change", ".checkItem", function() {
            if ($(".checkItem:checked").length === $(".checkItem").length) {
                $("#checkAll").prop("checked", true);
            } else {
                $("#checkAll").prop("checked", false);
            }
        });

        function loadReference(selectedId = null) {
            let customerId = $("#customer_id").val();
            let type = $("input[name='payment_type']:checked").val();

            console.log("LOAD REFERENCE:", {
                customerId: customerId,
                type: type,
                selectedId: selectedId
            });

            if (!customerId || !type) {
                return;
            }

            $.ajax({
                url: `/sales-invoice/get-reference/${customerId}/${type}`,
                type: "GET",
                dataType: "json",

                success: function(response) {
                    console.log("REFERENCE RESPONSE:", response);

                    let select = type === "proforma" ?
                        $("#proforma_id") :
                        $("#pelunasan_id");

                    select.empty();

                    select.append(
                        new Option("Pilih Referensi", "", false, false)
                    );

                    $.each(response, function(i, item) {
                        let option = new Option(
                            `${item.code} - ${item.date} (Rp ${formatRupiah(item.amount)})`,
                            item.id,
                            false,
                            false
                        );

                        $(option)
                            .attr("data-amount", item.amount)
                            .attr("data-sales-order-id", item.sales_order_id);

                        select.append(option);
                    });

                    let valueToSelect = null;

                    if (
                        selectedId !== null &&
                        selectedId !== undefined &&
                        selectedId !== ""
                    ) {
                        valueToSelect = String(selectedId);
                    } else if (response.length === 1) {
                        valueToSelect = String(response[0].id);
                    }

                    if (valueToSelect !== null) {
                        select.val(valueToSelect);
                    } else {
                        select.val("");
                    }

                    select.trigger("change");

                    console.log("SELECTED VALUE:", select.val());
                },

                error: function(xhr) {
                    console.error(
                        "GET REFERENCE ERROR:",
                        xhr.status,
                        xhr.responseText
                    );
                }
            });
        }
    </script>
@endpush
