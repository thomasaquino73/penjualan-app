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
                            <li><button class="dropdown-item btn-success btn-sm " id="showModalpr">
                                    <i class="ti ti-clipboard me-1"></i>QUOTATION
                                </button></li>
                            {{-- <li><button class="dropdown-item btn-info btn-sm " id="showModalproforma">
                                    <i class="ti ti-clipboard me-1"></i>Proforma Invoice
                                </button></li> --}}
                        </ul>
                    </div>
                    {{-- <button class="btn btn-success btn-sm " id="showModalpr">
                        <i class="ti ti-clipboard me-1"></i>QUOTATION
                    </button> --}}

                </div>
            </div>

        </div>
        <div class="card-body table-responsive p-3">
            <form action="{{ route('sales-order.store') }}" method="POST" id="postForm" enctype="multipart/form-data">
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
                                <label class="form-label">SO Number <small class="text-danger">*</small> </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti ti-barcode"></i></span>
                                    <input type="text" name="sales_order_code" id="sales_order_code" class="form-control"
                                        value="{{ $idNumber }}">
                                </div>
                                <span class="error text-danger" id="sales_order_codeError"></span>

                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row">

                            <div class="col-6 mb-3">
                                <label class="form-label">Date<small class="text-danger">*</small> </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                                    <input type="text" name="sales_order_date" id="sales_order_date" class="form-control"
                                        value="">
                                </div>
                                <span class="error text-danger" id="sales_order_dateError"></span>

                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="salesman_id">Salesman</label>
                                <select name="salesman_id" id="salesman_id" class="form-select select2"
                                    data-placeholder="Select Salesman">
                                    <option></option>
                                    @foreach ($salesman as $salesman)
                                        <option value="{{ $salesman->id }}">{{ $salesman->fullname }}</option>
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
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" id='tabIndo'
                                    data-bs-target="#navs-pills-left-profile" aria-controls="navs-pills-left-profile"
                                    aria-selected="false" tabindex="-1">
                                    <i class="ti ti-info-circle"></i>
                                </button>
                            </li>

                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active show" id="navs-pills-left-home" role="tabpanel">
                                @include('sales.salesOrder.part.table_sales_order')

                            </div>
                            <div class="tab-pane fade" id="navs-pills-left-profile" role="tabpanel">
                                @include('sales.salesOrder.part.info_sales_order')

                            </div>

                        </div>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-2"></div>
                    <div class="col-md-2">
                        <div class="col-12 mb-3 ">
                            <label class="form-label" for="sub_total">Sub Total</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>
                                <input type="number" id="sub_total" name="sub_total" class="form-control"
                                    placeholder="0" readonly>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="col-12 mb-3">
                            <label class="form-label" for="discount_all">Discount</label>
                            <div class="row">
                                <div class="col-4">
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text">%</span>
                                        <input type="number" id="percent" name="percent" min="0"
                                            step="any" class="form-control" placeholder="0">
                                        <span class="text-danger" id="discountError"></span>
                                    </div>
                                </div>
                                <div class="col-8">
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>
                                        <input type="number" id="discount_all" name="discount_all" class="form-control"
                                            placeholder="0" min='0'>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-2 mb-3" id="ppn_container" style="display:none;">
                        <div class="col-12 mb-3 ">
                            <label class="form-label" for="sub_total" id="taxes">Tax</label>
                            <div class="input-group input-group-merge">
                                <input type="text" name="tax_amount" id="tax_amount" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="col-12 mb-3">
                            <label class="form-label" for="total_order"> <strong>Total Order</strong></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $company->currency?->symbol ?? 'Rp' }}</span>
                                <input type="number" id="total_order" name="total_order" class="form-control"
                                    placeholder="0" readonly>
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
                    <a href="{{ route('sales-order.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    @include('sales.salesOrder.part.modal_sales_order')
    @include('sales.salesOrder.part.modalQuotationDetail')
@endsection
@include('partials.tabel.css')
@include('partials.tabel.js')
@include('partials.button.btn_addshipping')
@include('partials.button.btn_addpayment')
@include('partials.button.btn_submitform')
@include('partials.button.select2_modal')
@include('partials.js.calculate_total')

@push('scripts')
    <script>
        let prDetailsData = [];
        $(function() {
            const datePicker = flatpickr("#sales_order_date", {
                enableTime: false,
                dateFormat: "d-m-Y",
                defaultDate: "{{ \Carbon\Carbon::now()->format('d-m-Y') }}",
            });
        });

        $("#showModalpr").on("click", function(e) {
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
                url: "/sales-order/get-quotation/" + customerId,
                type: "GET",
                success: function(response) {

                    let option = '<option value="">Select Quotation</option>';

                    $.each(response, function(i, item) {
                        option += `<option value="${item.id}">
                                ${item.sales_quotation_code}
                           </option>`;
                    });

                    $("#sq_number").html(option);

                    $("#modalQuotationDetail").modal("show");
                }
            });
        });

        $('#sq_number').on('change', function() {

            let quotationIds = $(this).val();

            if (!quotationIds || quotationIds.length === 0) {
                $('#quotationTableBody').html('');
                return;
            }

            $.ajax({
                url: "{{ route('sales-order.getQuotationDetail') }}",
                type: "POST",
                data: {
                    quotation_ids: quotationIds,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {

                    let html = '';

                    $.each(response, function(index, item) {

                        html += `
                    <tr>
                        <td>
                            <div class="form-check form-check-primary">
                                <input
                                    class="form-check-input checkItem"
                                    type="checkbox"

                                    data-id="${item.id}"
                                    data-product_id="${item.product_id}"
                                    data-product_name="${item.nama_barang}"
                                    data-qty="${item.qty}"
                                    data-unit_id="${item.unit_id}"
                                    data-unit_name="${item.unit_name}"
                                    data-unit_price="${item.unit_price}"
                                    data-discount="${item.discount}"
                                    data-amount="${item.amount}"
                                    data-quotation_id="${item.sales_quotation_id}"
                                >
                            </div>
                        </td>

                        <td>${item.nama_barang}</td>
                        <td class="text-end">${item.qty}</td>
                        <td>${item.unit_name}</td>
                        <td class="text-end">${parseFloat(item.unit_price).toLocaleString()}</td>
                        <td class="text-end">${parseFloat(item.discount).toLocaleString()}</td>
                        <td class="text-end">${parseFloat(item.amount).toLocaleString()}</td>
                    </tr>`;
                    });

                    $("#checkAll").prop("checked", false);
                    $("#quotationTableBody").html(html);

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
                            // Menampilkan kode referensi PR di bawah nama produk jika ada
                            if (row.quotation_code) {
                                return `<strong>${data}</strong><br><small class="text-primary">Ref: ${row.quotation_code}</small>`;
                            }
                            return `<strong>${data}</strong>`;
                        }
                    },
                    {
                        data: "quantity",
                        className: "text-end", // Rata kanan untuk angka
                        render: function(data) {
                            return parseFloat(data).toLocaleString('id-ID');
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
                        data: "warehouse",
                        className: "text-center"
                    },
                ],
                layout: {
                    topStart: {
                        buttons: [{
                                text: '<i class="ti ti-plus me-1"></i> New',
                                className: "btn btn-primary btn-sm me-2 AddNew",
                                action: function(e, dt, node, config) {
                                    var customerId = $("#customer_id").val();

                                    if (!customerId || customerId === "") {
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
                                        return false;
                                    }

                                    $("#formPrDetail")[0].reset();
                                    $("#warehouse_id").val("").trigger("change");
                                    $("#detail_id").val("");

                                    if ($.fn.select2) {
                                        $("#product_id").val("").trigger("change");
                                        $("#unit_id").val("").trigger("change");
                                    }

                                    $("#modalTitle").text("Create new entry");
                                    $("#btnSubmitModal").text("Create");
                                    $("#modalPrDetail").modal("show");

                                },
                            },
                            {
                                text: '<i class="ti ti-edit me-1"></i> Edit',
                                className: "btn btn-warning btn-sm me-2",
                                extend: "selectedSingle",
                                action: function(e, dt, node, config) {
                                    let data = dt.row({
                                        selected: true
                                    }).data();
                                    let rowIndex = dt.row({
                                        selected: true
                                    }).index();

                                    window.isEditingMode = true;

                                    // Menyimpan index baris array untuk penanda update
                                    $("#detail_id").val(rowIndex);

                                    // --- AMANKAN DATA ID RELASI DI SINI ---
                                    $("#modal_purchase_requisition_detail_id").val(data.detail_id ||
                                        data.purchase_requisition_detail_id || "");
                                    $("#modal_requisition_code").val(data.requisition_code || "");

                                    // Simpan nilai sisa_pr ke attribute input modal quantity agar bisa divalidasi
                                    if (data.sisa_pr !== undefined && data.sisa_pr !== null) {
                                        $("#quantity").attr("data-sisa-pr", data.sisa_pr);
                                    } else {
                                        $("#quantity").removeAttr(
                                            "data-sisa-pr"); // Jika PO bebas, hapus batasannya
                                    }
                                    // --------------------------------------

                                    $("#quantity").val(data.quantity);
                                    $("#unit_id").data("pending-val", data.unit_id);
                                    $("#warehouse_id").val(data.warehouse_id).trigger("change");
                                    $("#product_id").val(data.product_id).trigger("change");
                                    $("#unit_price").val(data.unit_price);
                                    $("#discount").val(data.discount || 0);
                                    $("#discount_percent").val(data.discount_percent || 0);
                                    $("#tax").val(data.tax || 0);
                                    $("#total_price").val(data.amount || 0);

                                    $("#modalTitle").text("Edit entry");
                                    $("#btnSubmitModal").text("Update");
                                    $("#modalPrDetail").modal("show");
                                },
                            },
                            {
                                text: '<i class="ti ti-trash me-1"></i> Delete',
                                className: "btn btn-danger btn-sm me-2",
                                extend: "selected",
                                action: function(e, dt, node, config) {
                                    let rowIndex = dt.row({
                                        selected: true
                                    }).index();
                                    let data = dt.row({
                                        selected: true
                                    }).data();
                                    let name = data.data_produk ? data.data_produk : "";

                                    Swal.fire({
                                        title: "Are you sure?",
                                        text: "Want to delete data: " + name,
                                        icon: "warning",
                                        showCancelButton: true,
                                        confirmButtonText: "Yes, delete it!",
                                        cancelButtonText: "Cancel",
                                        customClass: {
                                            confirmButton: "btn btn-primary me-3 waves-effect waves-light",
                                            cancelButton: "btn btn-label-secondary waves-effect waves-light",
                                        },
                                        buttonsStyling: false,
                                    }).then(function(result) {
                                        if (result.isConfirmed) {
                                            prDetailsData.splice(rowIndex, 1);
                                            dt.clear().rows.add(prDetailsData).draw();
                                            calculateGrandTotal();
                                            calculateTotalOrder();
                                            toastr.success(
                                                "Deleted Data Successfully",
                                                "", {
                                                    timeOut: 1500,
                                                    progressBar: true,
                                                },
                                            );
                                        }
                                    });
                                },
                            },
                            {
                                text: '<i class="ti ti-refresh me-1"></i> Clear All',
                                className: "btn btn-secondary btn-sm",
                                action: function(e, dt, node, config) {
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

                console.log("Urutan prDetailsData terkunci permanen:", prDetailsData);
            });

            function loadAvailableStock() {

                let productId = $('#product_id').val();
                let warehouseId = $('#warehouse_id').val();
                let unitId = $('#unit_id').val();



                if (!productId || !warehouseId || !unitId) {
                    $('#available_stok').val('');
                    return;
                }

                $.ajax({
                    url: "{{ route('sales-order.wh.get-stock') }}",
                    type: "GET",
                    data: {
                        product_id: productId,
                        warehouse_id: warehouseId,
                        unit_id: unitId
                    },
                    success: function(res) {
                        $('#available_stok').val(res.stock);

                        $('#modalTitle').text(
                            `Create new entry (Available Stock: ${res.stock} ${res.unit})`
                        );
                    }
                });
            }

            $(document).on('change', '#product_id, #warehouse_id', function() {
                loadAvailableStock();
            });

            $(document).on('change select2:select', '#unit_id', function() {
                loadAvailableStock();
            });

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
                    url: '/sales-order/' + customerId + '/data',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {

                        // ======================
                        // Kontak
                        // ======================

                        contactDropdown.empty();
                        contactDropdown.append('<option value="">Pilih Kontak</option>');

                        $.each(data.kontak, function(key, value) {

                            contactDropdown.append(
                                `<option value="${value.id}">
                        ${value.sapaan} ${value.contact_person}
                        (${value.posisi_jabatan})
                    </option>`
                            );

                        });

                        // ======================
                        // Pajak
                        // ======================

                        if (data.pajak) {
                            $('#taxpayer_data').val(data.pajak.tipe_id_pajak + ' :' + data
                                .pajak.nomor_wajib_pajak);
                        } else {
                            $('#taxpayer_data').val('');
                        }

                        // ======================
                        // Alamat
                        // ======================

                        $('#address').val(data.address ?? '');

                    }
                });

            });

            $(document).on("change", "#product_id", function() {
                let productId = $(this).val();
                let unitSelect = $("#unit_id");
                let priceInput = $("#unit_price");
                let dropdownBtn = $("#btn-history-po");
                let dropdownMenu = $("#po-price-dropdown-menu");
                let helperText = $("#po-history-helper");

                // Pastikan ID selector ini sesuai dengan ID Select Customer di form utama kamu
                let customerId = $("#customer_id").val();

                if (!productId) {
                    unitSelect.empty().append("<option></option>").trigger("change");
                    priceInput.val("");
                    dropdownBtn.prop("disabled", true);
                    dropdownMenu.empty();
                    helperText.text("Pilih produk untuk melacak riwayat harga beli.");
                    return;
                }

                // Tambahan Validasi: Ingatkan user jika customer belum dipilih
                if (!customerId) {
                    alert(
                        "Silahkan pilih Customer terlebih dahulu pada form utama SO!",
                    );
                    $(this).val("").trigger("change"); // Reset pilihan produk
                    return;
                }

                // ==========================================
                // 1. AJAX List Unit (Sesuai Kode Bawaanmu)
                // ==========================================
                $.ajax({
                    url: window.routes.getUnits.replace(':id', productId),
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

                        // FIX: Akses properti 'units' dari objek response
                        let units = response.units;

                        if (units && units.length > 0) {
                            $.each(units, function(key, item) {
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

                        // Gunakan response.default_price
                        // priceInput.val(response.default_price || 0);
                        if (!window.isEditingMode) {
                            priceInput.val(response.default_price || 0);
                        }
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
                    url: `/sales-order/so/price-history?product_id=${productId}&customer_id=${customerId}`,
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
                                    calculateTotal();
                                });

                                li.append(a);
                                dropdownMenu.append(li);
                            });
                        } else {
                            helperText
                                .attr("class", "form-text text-muted")
                                .text(
                                    "Belum ada riwayat harga dengan customer ini. Silahkan isi harga manual.",
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


            $("#btnSubmitModal").on("click", function(e) {
                let qtyInput = $("#quantity");
                let currentQty = parseFloat(qtyInput.val()) || 0;

                // Ambil batas sisa PR dari atribut input modal
                let maxPrLimit = qtyInput.attr("data-sisa-pr");

                // JIKA BERDASARKAN PR (maxPrLimit terdefinisi dan tidak kosong)
                if (maxPrLimit !== undefined && maxPrLimit !== null && maxPrLimit !== '') {
                    maxPrLimit = parseFloat(maxPrLimit);

                    if (currentQty > maxPrLimit) {
                        e.preventDefault(); // Hentikan proses simpan/update ke array

                        Swal.fire({
                            icon: "warning",
                            title: "Melebihi Sisa PR",
                            text: `Kuantitas item ini tidak boleh melebihi sisa PR (Maksimal sisa: ${maxPrLimit}).`,
                            customClass: {
                                confirmButton: "btn btn-warning"
                            },
                            buttonsStyling: false
                        });

                        qtyInput.val(maxPrLimit); // Otomatis reset input ke angka maksimal
                        return false;
                    }
                }

                // JIKA PO BEBAS (maxPrLimit tidak ada), AKAN LOLOS TANPA VALIDASI MAKSIMAL
            });

            $("#formPrDetail").on("submit", function(e) {
                e.preventDefault();

                let productId = $("#product_id").val();
                let productName = $("#product_id option:selected").text();
                let quantity = parseFloat($("#quantity").val()) || 0;
                let unitId = $("#unit_id").val();
                let unitName = $("#unit_id option:selected").text();
                let warehouseId = $("#warehouse_id").val();
                let warehouseName = $("#warehouse_id option:selected").text();
                let detailId = $("#detail_id")
                    .val(); // Ini adalah index row array (kosong jika barang baru)

                let unitPrice = parseFloat($("#unit_price").val()) || 0;
                let discount = parseFloat($("#discount").val()) || 0;
                let tax = parseFloat($("#tax").val()) || 0;

                let requiredDate = $("#required_date").val() || "";

                // 1. Validasi Input Wajib
                if (!productId || quantity <= 0 || !unitId) {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: "Please fill all required fields! (Product, Valid Quantity, and Unit)",
                        customClass: {
                            confirmButton: "btn btn-danger",
                        },
                        buttonsStyling: false,
                    });
                    return false;
                }

                // 2. Validasi Duplikasi Produk
                let isDuplicate = false;
                if (prDetailsData && prDetailsData.length > 0) {
                    for (let i = 0; i < prDetailsData.length; i++) {
                        if (prDetailsData[i].product_id == productId) {
                            if (detailId === "") {
                                // Jika tambah baru dan produk sudah ada di tabel
                                isDuplicate = true;
                                break;
                            } else if (detailId !== "" && i != detailId) {
                                // Jika sedang edit, tapi produk diubah ke produk lain yang sudah ada di tabel
                                isDuplicate = true;
                                break;
                            }
                        }
                    }
                }

                if (isDuplicate) {
                    Swal.fire({
                        icon: "error",
                        title: "Product Already Exists!",
                        html: `The product <b>"${productName}"</b> is already registered.<br>Please edit the item if you want to change it.`,
                        customClass: {
                            confirmButton: "btn btn-danger",
                        },
                        buttonsStyling: false,
                    });
                    return false;
                }

                // 3. Matematika Kalkulasi Amount (Tax dalam persen)
                let subTotal = quantity * unitPrice;
                let totalDiscount = discount; // Diskon nominal tetap
                let setelahDiskon = subTotal - totalDiscount;
                let totalTax = setelahDiskon * (tax / 100);
                let amount = setelahDiskon + totalTax;

                // 4. Menyusun Object Data Baru / Hasil Editan Form
                let itemData = {
                    product_id: productId,
                    data_produk: productName,
                    quantity: quantity,
                    unit_id: unitId,
                    unit: unitName,
                    warehouse_id: warehouseId,
                    warehouse: warehouseName,
                    unit_price: unitPrice,
                    discount: discount,
                    tax: tax,
                    amount: amount,
                    required_date: requiredDate,
                };

                // 5. Logika Penyimpanan Berdasarkan 2 Cara Pengisian PO
                if (detailId === "") {
                    // --- CARA A: PO ISI SENDIRI (TAMBAH BARU MANUAL) ---
                    prDetailsData.push(itemData);
                } else {
                    // --- CARA B: AMBIL DARI PR & EDIT DATA ---
                    // Kita gabungkan data lama di dalam array dengan data yang baru diinput.
                    // Properti bawaan PR seperti 'quotation_code' & 'purchase_quotation_detail_id'
                    // akan otomatis aman dan dipertahankan.
                    prDetailsData[detailId] = {
                        ...prDetailsData[detailId], // Pertahankan data lama (Ref PR)
                        ...itemData // Update dengan data baru dari form modal
                    };
                }

                // 6. Refresh Tampilan & Hitung Total Akhir PO
                table.clear().rows.add(prDetailsData).draw();

                // Panggil fungsi hitung total keseluruhan halaman PO kamu
                if (typeof calculateGrandTotal === "function") calculateGrandTotal();
                if (typeof calculateTotalOrder === "function") calculateTotalOrder();

                // Tutup Modal Form Detail
                $("#modalPrDetail").modal("hide");
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


            $("#btnSubmitSelected").on("click", function() {

                let checkedBoxes = $(".checkItem:checked");

                if (checkedBoxes.length === 0) {
                    Swal.fire({
                        icon: "warning",
                        title: "Warning",
                        text: "Silakan pilih minimal satu item quotation.",
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
                        quantity: parseFloat($(this).data("qty")),
                        sisa_pr: parseFloat($(this).data("qty")),
                        unit_id: $(this).data("unit_id"),
                        unit: $(this).data("unit_name"),
                        warehouse_id: null,
                        warehouse: null,
                        unit_price: parseFloat($(this).data("unit_price")),
                        discount: parseFloat($(this).data("discount")),
                        amount: parseFloat($(this).data("amount")),
                        quotation_code: $(this).data("quotation_code"),
                    };

                    // Hindari data ganda
                    let exists = prDetailsData.some(x => x.detail_id == item.detail_id);

                    if (!exists) {
                        prDetailsData.push(item);
                    }
                });

                table.clear().rows.add(prDetailsData).draw();

                calculateGrandTotal();
                calculateTotalOrder();

                $("#modalQuotationDetail").modal("hide");

                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: "Data quotation berhasil dimasukkan.",
                    customClass: {
                        confirmButton: "btn btn-primary",
                    },
                    buttonsStyling: false,
                });

            });
        });
    </script>
@endpush
