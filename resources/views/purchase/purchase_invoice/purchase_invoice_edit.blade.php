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
                    <button class="btn btn-success btn-sm " id="showModalpr">
                        <i class="ti ti-clipboard me-1"></i> RECEIVE ITEM
                    </button>

                </div>
            </div>

        </div>
        <div class="card-body table-responsive p-3">
            <form action="{{ route('purchase-invoice.update', $model->id) }}" method="POST" id="postForm"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row mb-5">

                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-md-12">
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
                                        <span class="error text-danger" id="supplier_idError"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">PO Number <small class="text-danger">*</small> </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti ti-barcode"></i></span>
                                    <input type="text" name="code" id="code" class="form-control"
                                        value="{{ $model->code }}" readonly>
                                    <span class="error text-danger" id="codeError"></span>
                                </div>

                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">PO Date<small class="text-danger">*</small> </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                                    <input type="text" name="datePO" id="datePO" class="form-control"
                                        value="{{ Carbon\Carbon::parse($model->datePO)->format('d-m-Y') }}">
                                    <span class="error text-danger" id="datePOError"></span>
                                </div>
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
                                <div class="row mt-3">
                                    <table class="table display responsive nowrap" id="table">
                                        <thead class="border-top" style="background-color: #AEDEFC; ">
                                            <tr>
                                                <th>#</th>
                                                <th>Item</th>
                                                <th>Qty</th>
                                                <th>Unit</th>
                                                <th>Unit Price</th>
                                                {{-- <th>DiscPercent</th> --}}
                                                <th>Disc</th>
                                                <th>Amount</th>
                                                <th>Warehouse</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-left-profile" role="tabpanel">
                                @include('purchase.purchase_invoice.part.isi_tab.info_pesanan_edit')

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
                                    placeholder="0">
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
                    <a href="{{ route('purchase-invoice.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    @include('purchase.purchase_invoice.part.modals.modalPrDetail')
    @include('purchase.purchase_invoice.part.modals.modalRequisitionDetail')
@endsection
@include('partials.tabel.css')
@include('partials.tabel.js')
@include('partials.button.btn_addshipping')
@include('partials.button.btn_addpayment')
@include('partials.button.btn_submitform')
@include('partials.button.select2_modal')
{{-- @include('partials.js.calculate_total') --}}
@include('purchase.purchase_invoice.part.js.btnSubmitModal')


@push('scripts')
    <script>
        function calculateTotal() {
            let qty = parseFloat(document.getElementById('quantity').value) || 0;
            let price = parseFloat(document.getElementById('unit_price').value) || 0;
            let discountInput = document.getElementById('discount_percent').value;

            let subtotal = qty * price;

            let remaining = subtotal;
            let totalDiscount = 0;

            if (discountInput) {
                // Ambil semua angka dari input seperti "10+5+5"
                let discounts = discountInput.split('+');

                discounts.forEach(d => {
                    let percent = parseFloat(d.trim()) || 0;

                    let discValue = remaining * (percent / 100);
                    totalDiscount += discValue;

                    remaining -= discValue;
                });
            }

            // Set hasil ke input discount (nominal)
            document.getElementById('discount').value = totalDiscount.toFixed(2);

            // Set total price
            document.getElementById('amount').value = remaining.toFixed(2);
        }

        document.getElementById('discount').addEventListener('input', function() {

            let qty = parseFloat(document.getElementById('quantity').value) || 0;
            let price = parseFloat(document.getElementById('unit_price').value) || 0;
            let discountNominal = parseFloat(this.value) || 0;

            let subtotal = qty * price;

            if (discountNominal > subtotal) {
                discountNominal = subtotal;
                this.value = subtotal;
            }

            // Hitung discount %
            let discountPercent = 0;
            if (subtotal > 0) {
                discountPercent = (discountNominal / subtotal) * 100;
            }

            document.getElementById('discount_percent').value = discountPercent.toFixed(2);

            // Hitung total
            let total = subtotal - discountNominal;
            document.getElementById('amount').value = total.toFixed(2);

        });

        document.getElementById('quantity').addEventListener('input', calculateTotal);
        document.getElementById('unit_price').addEventListener('input', calculateTotal);
        document.getElementById('discount_percent').addEventListener('input', calculateTotal);
    </script>
    <script>
        $("#sub_total, #discount_all").on("input", function() {
            calculateTotalOrder();
        });

        // ===============================
        // Ambil Grand Total dari Detail
        // ===============================
        function getGrandSubTotal() {

            let total = 0;

            $.each(prDetailsData, function(index, item) {
                total += parseFloat(item.amount) || 0;
            });

            return total;
        }

        // ===============================
        // Hitung Grand Total
        // ===============================
        function calculateGrandTotal() {

            let grandSubTotal = getGrandSubTotal();

            let currentPercent = parseFloat($("#percent").val()) || 0;

            if (currentPercent > 0) {

                let nominalDiscount = grandSubTotal * currentPercent / 100;

                $("#discount_all").val(Math.round(nominalDiscount));

            } else {

                let nominalDiscount = parseFloat($("#discount_all").val()) || 0;

                if (nominalDiscount > grandSubTotal) {
                    nominalDiscount = grandSubTotal;
                    $("#discount_all").val(Math.round(nominalDiscount));
                }

                let percent = grandSubTotal > 0 ?
                    (nominalDiscount / grandSubTotal) * 100 :
                    0;

                $("#percent").val(
                    percent % 1 === 0 ? percent : percent.toFixed(2)
                );
            }

            calculateTotalOrder();
        }

        const TAXES = @json($taxes);
        const DEFAULT_TAX_ID = {{ $defaultTax->id ?? 'null' }};

        // ===============================
        // Hitung Total Order
        // ===============================
        function calculateTotalOrder() {

            // Selalu hitung subtotal dari tabel
            let grandSubTotal = getGrandSubTotal();

            let discount = parseFloat($("#discount_all").val()) || 0;

            // Ambil biaya lain-lain
            let biayaLain = parseFloat($("#biaya_lain").val()) || 0;

            let kenaPajak = $("#kena_pajak").is(":checked");
            let totalInclude = $("#total_termasuk_pajak").is(":checked");

            let selectedTaxId = $("#tax_id").val();

            let taxPercent = 0;

            if (selectedTaxId) {
                let selectedTax = TAXES.find(t => t.id == selectedTaxId);

                if (selectedTax) {
                    taxPercent = parseFloat(selectedTax.percentage) || 0;
                }
            }

            // Subtotal dari tabel
            let subtotal = grandSubTotal;

            // Subtotal setelah discount
            let subtotalAfterDiscount = subtotal - discount;

            if (subtotalAfterDiscount < 0) {
                subtotalAfterDiscount = 0;
            }

            let dpp = subtotalAfterDiscount;
            let tax = 0;
            let totalOrder = subtotalAfterDiscount;

            if (kenaPajak && taxPercent > 0) {

                $("#ppn_container").show();

                if (totalInclude) {

                    // Harga SUDAH termasuk pajak
                    dpp = subtotalAfterDiscount / (1 + (taxPercent / 100));

                    tax = subtotalAfterDiscount - dpp;

                    totalOrder = subtotalAfterDiscount;

                } else {

                    // Harga BELUM termasuk pajak
                    dpp = subtotalAfterDiscount;

                    tax = dpp * taxPercent / 100;

                    totalOrder = dpp + tax;
                }

            } else {

                $("#ppn_container").hide();

                dpp = subtotalAfterDiscount;
                tax = 0;
                totalOrder = subtotalAfterDiscount;
            }

            // Label tax
            $("#taxes").text(
                taxPercent > 0 ?
                `Tax (${taxPercent}%)` :
                "Tax"
            );

            // ===================================================
            // SUB TOTAL
            // ===================================================
            $("#sub_total").val(Math.round(subtotal));

            // DPP
            $("#dpp_amount").val(Math.round(dpp));

            // TAX
            $("#tax_amount").val(Math.round(tax));

            // ===================================================
            // TAMBAHKAN BIAYA LAIN-LAIN
            // ===================================================
            totalOrder += biayaLain;

            // TOTAL ORDER
            $("#total_order").val(Math.round(totalOrder));
        }

        // ===============================
        // EVENT
        // ===============================

        $("#kena_pajak").on("change", function() {

            if ($(this).is(":checked")) {

                $("#tax_container").show();

                if (DEFAULT_TAX_ID) {
                    $("#tax_id").val(DEFAULT_TAX_ID);
                }

            } else {

                $("#tax_container").hide();

                $("#tax_id").val("");

                $("#total_termasuk_pajak").prop("checked", false);

            }

            calculateTotalOrder();
        });

        $("#tax_id").on("change", function() {

            calculateTotalOrder();

        });

        $("#total_termasuk_pajak").on("change", function() {

            if ($(this).is(":checked")) {

                $("#kena_pajak").prop("checked", true);

                if ($("#tax_id").val() == "" && DEFAULT_TAX_ID) {
                    $("#tax_id").val(DEFAULT_TAX_ID);
                }
                $("#tax_container").hide();

            } else {
                $("#tax_container").show();
            }

            calculateTotalOrder();

        });

        $("#discount_all").on("input", function() {

            calculateTotalOrder();

        });

        $("#percent").on("input", function() {

            let subtotal = getGrandSubTotal();

            let percent = parseFloat($(this).val()) || 0;

            let nominal = subtotal * percent / 100;

            $("#discount_all").val(Math.round(nominal));

            calculateTotalOrder();

        });
    </script>
    <script>
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
        $("#vehicle_id").on("select2:select", function(e) {
            let data = e.params.data;

            if (data.newTag) {
                Swal.fire({
                    title: "Save New Shipping?",
                    text: "Shipping belum ada, simpan data baru?",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Yes, Save",
                    cancelButtonText: "Cancel",
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('shipping.store') }}",
                            type: "POST",

                            data: {
                                nama: data.text,
                                _token: "{{ csrf_token() }}",
                            },

                            success: function(response) {
                                $(
                                    '#vehicle_id option[value="' + data.id + '"]',
                                ).remove();

                                let newOption = new Option(
                                    response.nama,
                                    response.id,
                                    true,
                                    true,
                                );

                                $("#vehicle_id").append(newOption).trigger("change");

                                Swal.fire({
                                    icon: "success",
                                    title: "Success",
                                    text: response.message,
                                });
                            },

                            error: function() {
                                Swal.fire({
                                    icon: "error",
                                    title: "Error",
                                    text: "Failed save shipping",
                                });
                            },
                        });
                    } else {
                        $("#vehicle_id").val(null).trigger("change");
                    }
                });
            }
        });

        // $("#showModalpr").on("click", function(e) {
        //     e.preventDefault();

        //     var supplierID = $("#supplier_id").val();
        //     $("#sq_number")
        //         .empty()
        //         .append('<option value="">Select Requisition</option>')
        //         .val(null)
        //         .trigger("change");
        //     if (!supplierID) {
        //         Swal.fire({
        //             icon: "warning",
        //             title: "Warning!",
        //             text: "Please select Supplier first before adding new data.",
        //             confirmButtonColor: "#3085d6",
        //             confirmButtonText: "OK",
        //             customClass: {
        //                 confirmButton: "btn btn-danger",
        //             },
        //             buttonsStyling: false,
        //         });
        //         return;
        //     }

        //     $.ajax({
        //         url: "{{ route('purchase-order.requisitions.processing') }}",
        //         type: "GET",
        //         success: function(response) {

        //             let option = '<option value="">Select Quotation</option>';

        //             $.each(response, function(i, item) {
        //                 option += `<option value="${item.id}">
    //                         ${item.code}
    //                    </option>`;
        //             });

        //             $("#sq_number").html(option);

        //             $("#modalRequisitionDetail").modal("show");
        //         }
        //     });
        // });

        // $('#sq_number').on('change', function() {

        //     let quotationIds = $(this).val();

        //     if (!quotationIds || quotationIds.length === 0) {
        //         $('#quotationTableBody').html('');
        //         return;
        //     }

        //     $.ajax({
        //         url: "{{ route('purchase-order.getQuotationDetail') }}",
        //         type: "POST",
        //         data: {
        //             quotation_ids: quotationIds,
        //             _token: "{{ csrf_token() }}"
        //         },
        //         success: function(response) {
        //             let html = '';
        //             $.each(response, function(index, item) {
        //                 let safeProductName = item.nama_barang.replace(/"/g,
        //                     '&quot;');
        //                 html += `
    //             <tr>
    //                 <td>
    //                     <div class="form-check form-check-primary">
    //                         <input
    //                             class="form-check-input checkItem"
    //                             type="checkbox"

    //                             data-id="${item.id}"
    //                             data-product_id="${item.product_id}"
    //                             data-product_name="${safeProductName}"
    //                             data-outstanding_qty="${item.outstanding_qty}"
    //                             data-unit_id="${item.unit_id}"
    //                             data-unit_name="${item.unit_name}"

    //                             data-quotation_id="${item.purchase_requisition_id}"
    //                         >
    //                     </div>
    //                 </td>

    //                 <td>${item.nama_barang}</td>
    //                 <td class="text-end">${parseFloat(item.outstanding_qty)}</td>
    //                 <td>${item.unit_name}</td>

    //             </tr>`;
        //             });

        //             $("#checkAll").prop("checked", false);
        //             $("#quotationTableBody").html(html);

        //         }
        //     });

        // });
        //  LOGIC LOCK: CHECK ALL / UNCHECK ALL
        $("#checkAll").on("change", function() {
            // Jika checkAll dicentang, semua .checkItem ikut dicentang, begitu sebaliknya
            $(".checkItem").prop("checked", $(this).prop("checked"));
        });

        $(document).on("change", ".checkItem", function() {
            if ($(".checkItem:checked").length === $(".checkItem").length) {
                $("#checkAll").prop("checked", true);
            } else {
                $("#checkAll").prop("checked", false);
            }
        });

        $(function() {
            const datePicker = flatpickr("#datePO", {
                enableTime: false,
                dateFormat: "d-m-Y",
                // minDate: "today",
                defaultDate: "{{ \Carbon\Carbon::now()->format('d-m-Y') }}",
            });

            const expectedPicker = flatpickr("#tanggal_kirim", {
                enableTime: false,
                dateFormat: "d-m-Y",
                // minDate: "today",

                onChange: function(selectedDates, dateStr) {
                    if (selectedDates.length > 0) {
                        // set max date untuk PO Date
                        datePicker.set("maxDate", selectedDates[0]);

                        // ambil tanggal PO sekarang
                        let poDate = datePicker.selectedDates[0];

                        // kalau PO Date > Expected Date → reset
                        if (poDate && poDate > selectedDates[0]) {
                            datePicker.clear();
                        }
                    }
                },
            });
        });

        $("#vehicle_id").select2({
            placeholder: "Select Shipping",
            tags: true,
            width: "100%",
            allowClear: true,

            language: {
                noResults: function(params) {
                    let term = $.trim(params.term);

                    if (term === "") {
                        return "No results found";
                    }

                    return 'Press ENTER to add "' + term + '"';
                },
            },

            escapeMarkup: function(markup) {
                return markup;
            },

            createTag: function(params) {
                let term = $.trim(params.term);

                if (term === "") {
                    return null;
                }

                return {
                    id: term,
                    text: term,
                    newTag: true,
                };
            },
        });

        // ENTER KEY FIX
        $(document).on("keypress", ".select2-search__field", function(e) {
            if (e.which == 13) {
                e.preventDefault();

                let value = $(this).val();

                if (value.trim() != "") {
                    let option = new Option(value, value, true, true);

                    $("#vehicle_id").append(option).trigger("change");

                    $("#vehicle_id").trigger({
                        type: "select2:select",
                        params: {
                            data: {
                                id: value,
                                text: value,
                                newTag: true,
                            },
                        },
                    });
                }
            }
        });

        // Ketika tombol map/history alamat diklik
        $(document).on("click", "#btn-history-address", function() {
            let supplierId = $("#supplier_id").val();
            loadSupplierAddress(supplierId);
        });
        $(document).on("click", ".select-address", function() {
            $("#shipping_address").val($(this).data("address"));
        });

        function loadSupplierAddress(supplierId) {
            if (!supplierId) return;
            $.ajax({
                url: `/purchase-order/get-supplier-address/${supplierId}`,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    let dropdownMenu = $("#address-dropdown-menu");
                    dropdownMenu.empty();
                    if (response.success) {
                        let item = response.data;
                        dropdownMenu.append(`
                    <li class="w-100">
                        <a class="dropdown-item select-address p-2 d-block text-dark"
                           href="javascript:void(0);"
                           data-address="${item.address.replace(/\n/g,'&#10;')}"
                           style="white-space: normal;">
                            <strong class="d-block">${item.address_name}</strong>
                            <span class="text-muted small" style="white-space: pre-line;">
                                ${item.address}
                            </span>
                        </a>
                    </li>
                `);
                    } else {
                        dropdownMenu.append(`
                    <li>
                        <span class="dropdown-item text-muted">
                            Tidak ada alamat.
                        </span>
                    </li>
                `);
                    }
                }
            });

        }
    </script>
    <script>
        let prDetailsData = [
            @if (isset($jsonDetails))
                @foreach ($jsonDetails as $detail)
                    {
                        id: @json($detail['id']),
                        purchase_invoice_id: @json($detail['purchase_invoice_id']),
                        receive_item_detail_id: @json($detail['receive_item_detail_id']),
                        receive_item_code: @json($detail['receive_item_code']),
                        product_id: @json($detail['product_id']),
                        data_produk: @json($detail['data_produk']),
                        quantity: @json($detail['quantity']),
                        unit_id: @json($detail['unit_id']),
                        unit: @json($detail['unit']),
                        warehouse_id: @json($detail['warehouse_id']),
                        warehouse: @json($detail['warehouse']),
                        unit_price: @json($detail['unit_price']),
                        discount: @json($detail['discount']),
                        discount_percent: @json($detail['discount_percent']),
                        amount: @json($detail['amount']),
                        received_qty: @json($detail['received_qty']),
                        outstanding_qty: @json($detail['outstanding_qty']),
                        total_invoice_lainnya: @json($detail['total_invoice_lainnya']),
                        purchase_order_id: @json($detail['purchase_order_id'])
                    }
                    {{ !$loop->last ? ',' : '' }}
                @endforeach
            @endif
        ];

        // Cek status PO global (Optional jika ingin mematikan tombol "Requisition" di pojok kanan atas saat edit)
        let isFromReceiveItem = {{ $isFromReceiveItem ? 'true' : 'false' }};
        if (isFromReceiveItem) {
            // Jika PO ini dari PR, mungkin kamu mau mendisable tombol "REQUISITION" di atas agar user tidak tambah PR lain
            $(".btn-success").html('<i class="ti ti-link"></i> Linked to PR').prop('disabled', true);
        }


        $(document).ready(function() {
            // MENAMPILKAN TABEL BARANG
            // ========================================================
            // 🛠️ LANGKAH UTAMA: SUNTIKKAN PROPERTI URUTAN KE DATA ASAL
            // ========================================================
            function refreshDataIndices() {
                if (Array.isArray(prDetailsData)) {
                    prDetailsData.forEach((item, index) => {
                        item.urutan_lokal = index; // Membuat nomor ID unik lokal berbasis index array
                    });
                }
            }
            // Jalankan fungsi sebelum tabel diinisialisasi
            refreshDataIndices();

            let table = new DataTable("#table", {
                processing: true,
                serverSide: false,
                responsive: true,
                select: true,
                searching: false,

                // 1. Tambahkan indeks pengurutan awal ke kolom pertama [0] agar engine rowReorder aktif
                order: [
                    [0, 'asc']
                ],

                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"],
                ],

                // 2. Hubungkan fungsi pencarian indeks dinamis berdasarkan data objek array langsung
                rowReorder: {
                    selector: 'td:first-child',
                    dataSrc: function(row) {
                        return prDetailsData.indexOf(row);
                    }
                },

                data: prDetailsData,
                columns: [{
                        // 3. Menggunakan data: null agar aman dari error unknown parameter
                        data: null,
                        orderable: true, // Wajib TRUE agar baris bisa digeser
                        className: "text-center reorder-pointer",
                        searchable: false,
                        render: function(data, type, row, meta) {
                            // Memberikan angka visual statis sesuai baris di layar saat ini
                            if (type === 'display') {
                                return meta.row + 1;
                            }
                            // Kembalikan indeks array murni ke internal DataTables agar kalkulasi drag & drop berjalan
                            return prDetailsData.indexOf(row);
                        },
                    },
                    {
                        data: "data_produk",
                        render: function(data, type, row) {

                            if (row.receive_item_code) {
                                return `
                                        <strong>${data}</strong>
                                        <br>
                                        <small class="text-primary">
                                            RI : ${row.receive_item_code}
                                        </small>
                                    `;
                            }

                            return `<strong>${data}</strong>`;
                        }
                    },
                    {
                        data: "quantity",
                        className: "text-end",
                        render: function(data) {
                            return parseFloat(data ?? 0).toLocaleString('id-ID');
                        }
                    },
                    {
                        data: "unit",
                        className: "text-center"
                    },
                    {
                        data: "unit_price",
                        className: "text-end",
                        render: function(data) {
                            return parseFloat(data ?? 0).toLocaleString('id-ID', {
                                minimumFractionDigits: 0
                            });
                        }
                    },
                    {
                        data: "discount",
                        className: "text-end",
                        render: function(data) {
                            return parseFloat(data ?? 0).toLocaleString('id-ID', {
                                minimumFractionDigits: 0
                            });
                        }
                    },
                    {
                        data: "amount",
                        className: "text-end",
                        render: function(data) {
                            return `<strong>${parseFloat(data ?? 0).toLocaleString('id-ID', { minimumFractionDigits: 0 })}</strong>`;
                        }
                    },
                    {
                        data: 'warehouse',
                        render: function(data) {
                            return data ? data : '-';
                        }
                    },
                    {
                        data: 'purchase_order_id',
                        render: function(data) {
                            return data ? data : '-';
                        }
                    },
                ],
                layout: {
                    topStart: {
                        buttons: [
                            // =======================
                            // ➕ ADD
                            // =======================
                            {
                                text: '<i class="ti ti-plus me-1"></i> New',
                                className: "btn btn-primary btn-sm me-2 AddNew",
                                action: function() {
                                    let supplierId = $("#supplier_id").val();

                                    if (!supplierId) {
                                        Swal.fire({
                                            icon: "warning",
                                            title: "Warning!",
                                            text: "Please select Supplier first before adding new data.",
                                            confirmButtonText: "OK",
                                            customClass: {
                                                confirmButton: "btn btn-danger",
                                            },
                                            buttonsStyling: false,
                                        });
                                        return;
                                    }

                                    window.isEditingMode = false;

                                    $("#formPrDetail")[0].reset();

                                    $("#detail_id").val("");
                                    $("#modal_receive_item_detail_id").val("");
                                    $("#modal_receive_item_code").val("");

                                    $("#warehouse_id").val("").trigger("change");

                                    if ($.fn.select2) {
                                        $("#product_id").val("").trigger("change");
                                        $("#unit_id").val("").trigger("change");
                                    }

                                    $("#quantity").removeAttr("data-outstanding");

                                    $("#modalTitle").text("Create new entry");
                                    $("#btnSubmitModal").text("Create");

                                    $("#modalPrDetail").modal("show");
                                },
                            },

                            // =======================
                            // ✏️ EDIT
                            // =======================
                            {
                                text: '<i class="ti ti-edit me-1"></i> Edit',
                                className: "btn btn-warning btn-sm me-2",
                                extend: "selectedSingle",
                                action: function(e, dt, node, config) {

                                    const row = dt.row({
                                        selected: true
                                    });

                                    if (!row.any()) {
                                        Swal.fire({
                                            icon: "warning",
                                            title: "Warning",
                                            text: "Please select one data first."
                                        });
                                        return;
                                    }

                                    const data = row.data();
                                    const rowIndex = row.index();

                                    window.isEditingMode = true;

                                    // Reset error
                                    $("#formPrDetail .error").html("");

                                    // ==========================
                                    // HEADER
                                    // ==========================
                                    $("#modalTitle").text("Edit entry");
                                    $("#btnSubmitModal").text("Update");

                                    // ==========================
                                    // HIDDEN FIELDS (PERBAIKAN DI SINI)
                                    // ==========================

                                    // Simpan indeks baris untuk kebutuhan update array nantinya (jika diperlukan untuk referensi posisi)
                                    $("#detail_id").val(rowIndex);

                                    // Pastikan product_id dan field penting lainnya ikut dimasukkan ke input form/hidden yang sesuai!
                                    $("#product_id").val(data
                                        .product_id
                                    ); // <-- Pastikan ini ada agar product_id ikut naik/terbawa

                                    $("#modal_purchase_requisition_detail_id").val(
                                        data.detail_id ??
                                        data.purchase_requisition_detail_id ??
                                        ""
                                    );

                                    $("#modal_requisition_code").val(
                                        data.requisition_code ?? ""
                                    );

                                    // ==========================
                                    // TEXTBOX
                                    // ==========================
                                    $("#quantity").val(data.quantity ?? "");
                                    $("#unit_price").val(data.unit_price ?? 0);
                                    $("#discount").val(data.discount ?? 0);
                                    $("#discount_percent").val(data.discount_percent ?? 0);
                                    $("#tax").val(data.tax ?? 0);
                                    $("#amount").val(data.amount ?? 0);
                                    $("#available_stok").val(data.available_stok ?? "");

                                    // ==========================
                                    // ATTRIBUTE
                                    // ==========================
                                    if (data.sisa_pr != null) {
                                        $("#quantity").attr("data-sisa-pr", data.sisa_pr);
                                    } else {
                                        $("#quantity").removeAttr("data-sisa-pr");
                                    }

                                    // ==========================
                                    // SELECT
                                    // ==========================
                                    $("#warehouse_id")
                                        .val(data.warehouse_id)
                                        .trigger("change.select2");

                                    // simpan unit yg dipilih
                                    $("#unit_id").data("pending-val", data.unit_id);

                                    // simpan harga lama
                                    $("#unit_price").data("pending-price", data.unit_price);

                                    // buka modal dulu
                                    $("#modalPrDetail").modal("show");

                                    // terakhir trigger product agar Select2 membaca value product_id yang baru
                                    setTimeout(function() {
                                        $("#product_id")
                                            .val(data.product_id)
                                            .trigger("change");
                                    }, 150);
                                }
                            },

                            // =======================
                            // 🗑 DELETE
                            // =======================
                            {
                                text: '<i class="ti ti-trash me-1"></i> Delete',
                                className: "btn btn-danger btn-sm me-2",
                                extend: "selected",
                                action: function(e, dt) {
                                    let data = dt.row({
                                        selected: true
                                    }).data();
                                    if (!data) return;

                                    let name = data.data_produk || "";

                                    Swal.fire({
                                        title: "Are you sure?",
                                        text: "Want to delete data: " + name,
                                        icon: "warning",
                                        showCancelButton: true,
                                        confirmButtonText: "Yes, delete it!",
                                        cancelButtonText: "Cancel",
                                        customClass: {
                                            confirmButton: "btn btn-primary me-3",
                                            cancelButton: "btn btn-label-secondary",
                                        },
                                        buttonsStyling: false,
                                    }).then(function(result) {
                                        if (result.isConfirmed) {
                                            prDetailsData = prDetailsData.filter(item =>
                                                item.id !== data.id
                                            );

                                            dt.clear().rows.add(prDetailsData).draw();

                                            calculateGrandTotal();
                                            calculateTotalOrder();

                                            toastr.success("Deleted Data Successfully",
                                                "", {
                                                    timeOut: 1500,
                                                    progressBar: true,
                                                });
                                        }
                                    });
                                },
                            },

                            // =======================
                            // 🔄 CLEAR ALL
                            // =======================
                            {
                                text: '<i class="ti ti-refresh me-1"></i> Clear All',
                                className: "btn btn-secondary btn-sm",
                                action: function(e, dt) {
                                    prDetailsData = [];
                                    dt.clear().draw();

                                    calculateGrandTotal();
                                    calculateTotalOrder();

                                    $("#percent").val(0);
                                },
                            },
                        ],
                    },
                },
            });

            // ========================================================
            // 🔄 EVENT SINKRONISASI COCOK UNTUK STRUKTUR JAVASCRIPT ARRAY
            // ========================================================
            table.on('row-reorder', function(e, diff, edit) {
                // Jika tidak ada perubahan posisi penyeretan, abaikan proses
                if (diff.length === 0) return;

                // Lakukan loop manipulasi urutan elemen array asli di javascript menggunakan splice
                diff.forEach(function(change) {
                    let movedRowData = table.row(change.node).data();
                    let oldIndex = prDetailsData.indexOf(movedRowData);

                    if (oldIndex !== -1) {
                        // Hapus dari posisi lama
                        prDetailsData.splice(oldIndex, 1);
                        // Masukkan tepat ke indeks baris baru hasil geser visual
                        prDetailsData.splice(change.newPosition, 0, movedRowData);
                    }
                });

                // Perbarui cache internal instan tanpa memicu re-render / draw agresif yang merusak urutan baru
                table.rows().invalidate();

            });

            $("#btnSubmitModal").on("click", function(e) {
                e.preventDefault();

                // 1. Ambil data dari input form
                let productId = $("#product_id").val();
                let productName = $("#product_id option:selected").text();
                let qtyInput = parseFloat($("#quantity").val() || 0);
                let unitId = $("#unit_id").val();
                let unitName = $("#unit_id option:selected").text();
                let warehouseId = $("#warehouse_id").val();
                let warehouseName = $("#warehouse_id option:selected").text();
                let unitPrice = parseFloat($("#unit_price").val() || 0);
                let discount = parseFloat($("#discount").val() || 0);
                let discountPercent = $("#discount_percent").val();
                let tax = parseFloat($("#tax").val() || 0);

                // 2. Ambil ID dan PR Detail ID
                let rowIndex = $("#detail_id").val();
                let prDetailId = $("#modal_purchase_requisition_detail_id").val();

                // Validasi dasar
                if (!productId || qtyInput <= 0 || !unitId) {
                    Swal.fire("Error", "Mohon lengkapi produk, quantity, dan unit.", "error");
                    return;
                }

                // 3. Normalisasi PR Detail ID agar benar-benar null jika tidak ada
                prDetailId = (prDetailId && prDetailId !== "null" && prDetailId !== "") ? parseInt(
                    prDetailId) : null;

                // 4. Validasi Kuota PR (jika ini adalah item dari PR)
                let batasMaksimal = parseFloat($("#quantity").attr("data-max-allowed") || 0);
                if (prDetailId !== null && batasMaksimal > 0 && qtyInput > batasMaksimal) {
                    Swal.fire({
                        title: "Melebihi Kuota PR",
                        text: `Kuantitas tidak boleh melebihi kuota PR. (Maksimal: ${batasMaksimal})`,
                        icon: "error"
                    });
                    return;
                }

                // 5. Buat objek data
                let rowData = {
                    product_id: productId,
                    data_produk: productName,
                    quantity: qtyInput,
                    unit_id: unitId,
                    unit: unitName,
                    warehouse_id: warehouseId,
                    warehouse: warehouseName,
                    unit_price: unitPrice,
                    discount: discount,
                    discount_percent: discountPercent,
                    tax: tax,
                    amount: (qtyInput * unitPrice) - discount,
                    receive_item_detail_id: prDetailId // Ini yang akan dikirim ke controller
                };

                // 6. Update ke array global prDetailsData
                if (rowIndex !== "" && rowIndex !== null && rowIndex !== undefined && prDetailsData[
                        rowIndex]) {
                    // Mode Edit: Update data pada index yang ada
                    prDetailsData[rowIndex] = {
                        ...prDetailsData[rowIndex],
                        ...rowData
                    };
                } else {
                    // Mode Create: Push data baru
                    prDetailsData.push(rowData);
                }

                // 7. Refresh DataTable
                table.clear().rows.add(prDetailsData).draw();

                // 8. Bersihkan Modal
                $("#modalPrDetail").modal("hide");
                $("#formPrDetail")[0].reset();
                $("#detail_id").val("");
                $("#modal_purchase_requisition_detail_id").val(
                    ""); // PENTING: Reset agar tidak terbawa ke item berikutnya

                // Reset select2 jika digunakan
                if ($.fn.select2) {
                    $("#product_id, #unit_id").val("").trigger("change.select2");
                }

                // Update total perhitungan
                if (typeof calculateGrandTotal === "function") {
                    calculateGrandTotal();
                }
                if (typeof calculateTotalOrder === "function") {
                    calculateTotalOrder();
                }
            });


            $("#percent").on("input", function() {
                let subTotal = parseFloat($("#sub_total").val()) || 0;
                let percent = parseFloat($(this).val()) || 0;

                // Batasi agar persen tidak minus atau lebih dari 100
                if (percent < 0) {
                    percent = 0;
                    $(this).val(0);
                }
                if (percent > 100) {
                    percent = 100;
                    $(this).val(100);
                }

                // Hitung nominal Rupiahnya
                let discountNominal = subTotal * (percent / 100);

                // Masukkan hasil ke kolom Rupiah (discount_all)
                $("#discount_all").val(Math.round(discountNominal));

                // Hitung ulang Grand Total Akhir (Memanggil fungsi yang benar)
                calculateTotalOrder();
            });

            // B. Jika User Mengetik di Kolom NOMINAL (Rp)
            $("#discount_all").on("input", function() {
                let subTotal = parseFloat($("#sub_total").val()) || 0;
                let discountNominal = parseFloat($(this).val()) || 0;

                // Batasi agar nominal diskon tidak melebihi subtotal
                if (discountNominal < 0) {
                    discountNominal = 0;
                    $(this).val(0);
                }
                if (discountNominal > subTotal) {
                    discountNominal = subTotal;
                    $(this).val(subTotal);
                }

                // Hitung Persentasenya
                let percent = 0;
                if (subTotal > 0) {
                    percent = (discountNominal / subTotal) * 100;
                }

                // Masukkan hasil ke kolom persen (ambil 2 angka di belakang koma agar presisi)
                $("#percent").val(percent % 1 === 0 ? percent : percent.toFixed(2));

                // Hitung ulang Grand Total Akhir (Memanggil fungsi yang benar)
                calculateTotalOrder();
            });

            $("#biaya_lain").on("input", function() {
                calculateTotalOrder();
            });

            $(document).on("change", "#product_id", function() {
                let productId = $(this).val();
                let unitSelect = $("#unit_id");
                let priceInput = $("#unit_price");
                let dropdownBtn = $("#btn-history-po");
                let dropdownMenu = $("#po-price-dropdown-menu");
                let helperText = $("#po-history-helper");

                // Pastikan ID selector ini sesuai dengan ID Select Supplier di form utama kamu
                let supplierId = $("#supplier_id").val();

                if (!productId) {
                    unitSelect.empty().append("<option></option>").trigger("change");
                    priceInput.val("");
                    dropdownBtn.prop("disabled", true);
                    dropdownMenu.empty();
                    helperText.text("Pilih produk untuk melacak riwayat harga beli.");
                    return;
                }

                // Tambahan Validasi: Ingatkan user jika supplier belum dipilih
                if (!supplierId) {
                    alert(
                        "Silahkan pilih Supplier terlebih dahulu pada form utama PO!",
                    );
                    $(this).val("").trigger("change"); // Reset pilihan produk
                    return;
                }

                // ==========================================
                // 1. AJAX List Unit (Sesuai Kode Bawaanmu)
                // ==========================================
                $.ajax({
                    url: `/get-units-by-product/${productId}`,
                    type: "GET",
                    dataType: "json",
                    beforeSend: function() {
                        unitSelect
                            .html("<option>Loading units...</option>")
                            .prop("disabled", true);
                    },
                    success: function(response) {
                        unitSelect
                            .empty()
                            .append("<option></option>")
                            .prop("disabled", false);

                        if (response && response.length > 0) {
                            $.each(response, function(key, item) {
                                unitSelect.append(
                                    `<option value="${item.id}">${item.name}</option>`,
                                );
                            });
                        } else {
                            unitSelect.append(
                                '<option value="">No unit available</option>',
                            );
                        }

                        unitSelect.trigger("change");

                        let pendingUnitId = unitSelect.data("pending-val");
                        if (pendingUnitId) {
                            unitSelect.val(pendingUnitId).trigger("change");
                            unitSelect.removeData("pending-val");
                        }
                    },
                    error: function() {
                        unitSelect
                            .empty()
                            .append("<option></option>")
                            .prop("disabled", false)
                            .trigger("change");
                    },
                });

                // ==========================================
                // 2. AJAX History PO + Fallback Harga Master
                // ==========================================
                $.ajax({
                    url: `/purchase-order/po/price-history?product_id=${productId}&supplier_id=${supplierId}`,
                    type: "GET",
                    dataType: "json",
                    beforeSend: function() {
                        // Jangan hapus isi textbox jika sudah ada nilainya (misal saat mode EDIT)
                        if (priceInput.val() === "" || priceInput.val() == "0") {
                            priceInput.val("0");
                        }
                        dropdownBtn.prop("disabled", true);
                        dropdownMenu.empty();
                        helperText.text("Mencari riwayat harga...");
                    },
                    success: function(response) {
                        if (response.success && response.history.length > 0) {
                            dropdownBtn.prop("disabled", false);
                            helperText
                                .attr("class", "form-text text-success")
                                .text(
                                    "Riwayat ditemukan. Klik icon untuk ganti harga lama.",
                                );

                            // Render ulang list dropdown menu
                            $.each(response.history, function(index, item) {
                                // 1. Ambil nilai harga dan tanggal dari objek item
                                let harga = item.harga;
                                let tanggalMentah = item.tanggal;

                                // 2. Format Tanggal (Contoh Hasil: 23-05-2026 14:30)
                                let formattedDate = "-";
                                if (tanggalMentah) {
                                    let d = new Date(tanggalMentah);
                                    let tgl = String(d.getDate()).padStart(2, "0");
                                    let bln = String(d.getMonth() + 1).padStart(2,
                                        "0"); // Bulan dimulai dari 0
                                    let thn = d.getFullYear();
                                    let jam = String(d.getHours()).padStart(2, "0");
                                    let mnt = String(d.getMinutes()).padStart(2, "0");

                                    formattedDate =
                                        `${tgl}-${bln}-${thn} ${jam}:${mnt}`;
                                }

                                // 3. Format Tampilan Harga Ke Rupiah
                                let formattedPrice =
                                    `Rp ${Number(harga).toLocaleString("id-ID")}`;

                                // 4. Susun konten teks menu dropdown (Harga di kiri, Tanggal & Badge di kanan)
                                let badgeTerakhir =
                                    index === 0 ?
                                    `<span class="badge bg-label-success text-xs ms-1">Terakhir</span>` :
                                    "";

                                let itemContent = `
                                        <div class="d-flex flex-column w-100">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span><strong>${formattedPrice}</strong></span>
                                                ${badgeTerakhir}
                                            </div>
                                            <small class="text-muted" style="font-size: 11px;">
                                                <i class="ti ti-calendar text-xs me-1"></i>${formattedDate}
                                            </small>
                                        </div>
                                    `;

                                let li = $("<li></li>");
                                let a = $(
                                    `<a class="dropdown-item d-flex align-items-center py-2" href="#" style="min-width: 220px;">${itemContent}</a>`,
                                );

                                // Ketika item di klik, harga dimasukkan ke textbox
                                a.on("click", function(e) {
                                    e.preventDefault();
                                    priceInput.val(harga);
                                });

                                li.append(a);
                                dropdownMenu.append(li);
                            });
                        } else {
                            helperText
                                .attr("class", "form-text text-muted")
                                .text(
                                    "Belum ada riwayat harga dengan supplier ini. Silahkan isi harga manual.",
                                );
                            dropdownBtn.prop("disabled", true);
                            if (priceInput.val() === "") {
                                priceInput.val("0");
                            }
                        }
                    },
                    error: function(xhr) {
                        helperText
                            .attr("class", "form-text text-danger")
                            .text("Gagal memuat riwayat harga.");
                    },
                });
            });

            $("#vehicle_id").select2({
                placeholder: "Select Shipping",
                width: "100%",
            });



            $("#btnSubmitSelected").on("click", function() {
                let checkedBoxes = $(".checkItem:checked");
                if (checkedBoxes.length === 0) {
                    Swal.fire({
                        icon: "warning",
                        title: "Warning",
                        text: "Silakan pilih minimal satu item requisition.",
                        customClass: {
                            confirmButton: "btn btn-danger",
                        },
                        buttonsStyling: false,
                    });
                    return;
                }

                if (typeof prDetailsData === "undefined") {
                    window.prDetailsData = [];
                }

                checkedBoxes.each(function() {

                    let item = {
                        detail_id: $(this).data("id"),
                        product_id: $(this).data("product_id"),
                        data_produk: $(this).data("product_name"),
                        quantity: parseFloat($(this).data("outstanding_qty")),
                        sisa_pr: parseFloat($(this).data("qty")),
                        unit_id: $(this).data("unit_id"),
                        unit: $(this).data("unit_name"),
                        warehouse_id: null,
                        warehouse: null,
                        unit_price: 0,
                        discount: 0,
                        amount: 0,
                        order_code: $(this).data("code"),
                    };

                    // Hindari data ganda
                    let exists = prDetailsData.some(x => x.detail_id == item.detail_id);

                    if (!exists) {
                        prDetailsData.push(item);
                    }
                });

                table.clear().rows.add(prDetailsData).draw();

                $("#modalRequisitionDetail").modal("hide");

                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: "Data requisition berhasil dimasukkan.",
                    customClass: {
                        confirmButton: "btn btn-primary",
                    },
                    buttonsStyling: false,
                });

            });

            $(document).on('input change', '.input-qty', function() {
                let inputField = $(this);
                let currentQty = parseFloat(inputField.val()) || 0;

                // Ambil data batasan PR jika ada (jika input manual / bukan PR, nilainya akan undefined)
                let maxPrLimit = inputField.data('sisa-pr');

                // JIKA AMBIL DATA DARI PR (maxPrLimit memiliki nilai)
                if (maxPrLimit !== undefined && maxPrLimit !== null && maxPrLimit !== '') {
                    maxPrLimit = parseFloat(maxPrLimit);

                    if (currentQty > maxPrLimit) {
                        Swal.fire({
                            icon: "warning",
                            title: "Melebihi Sisa PR",
                            text: `Kuantitas tidak boleh melebihi sisa permintaan PR (Maksimal: ${maxPrLimit}).`,
                            customClass: {
                                confirmButton: "btn btn-warning"
                            },
                            buttonsStyling: false
                        });

                        // Kembalikan otomatis nilainya ke batas maksimal sisa PR
                        inputField.val(maxPrLimit).trigger('change');
                        return;
                    }
                }

                // JIKA PO BEBAS / INPUT MANUAL
                // Kode di bawah ini tetap berjalan bebas tanpa interupsi batas maksimal...
            });

            $('#supplier_id').on('change', function() {

                let supplierId = $(this).val();

                $('#bank_id').empty().append('<option value="">Pilih Rekening</option>');
                $('#taxpayer_data').val('');

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

                        // Isi NPWP
                        if (response.pajak) {
                            $('#taxpayer_data').val(response.pajak.tipe_id_pajak + ' :' +
                                response.pajak.nomor_wajib_pajak);
                        }
                        if (response.supplier) {
                            let alamat = [];
                            if (response.supplier.alamat_pembayaran)
                                alamat.push(response.supplier.alamat_pembayaran);
                            let kotaProvinsi = [];
                            if (response.supplier.kota)
                                kotaProvinsi.push(response.supplier.kota);
                            if (response.supplier.provinsi)
                                kotaProvinsi.push(response.supplier.provinsi);
                            if (response.supplier.kodepos)
                                kotaProvinsi.push(response.supplier.kodepos);
                            if (kotaProvinsi.length > 0)
                                alamat.push(kotaProvinsi.join(', '));
                            if (response.supplier.negara)
                                alamat.push(response.supplier.negara);
                            $('#shipping_address').val(alamat.join('\n'));
                        }

                    }
                });

            });
        });
    </script>
@endpush
