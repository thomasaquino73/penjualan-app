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

            </div>

        </div>
        <div class="card-body table-responsive p-3">
            <form action="{{ route('sales-quotation.update', $model->id) }}" method="POST" id="postForm"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
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
                                <label class="form-label">SQ Number <small class="text-danger">*</small> </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti ti-barcode"></i></span>
                                    <input type="text" name="sales_quotation_code" id="sales_quotation_code"
                                        class="form-control" value="{{ $model->sales_quotation_code ?? '' }}">
                                </div>
                                <span class="error text-danger" id="sales_quotation_codeError"></span>

                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row">

                            <div class="col-6 mb-3">
                                <label class="form-label">SQ Date<small class="text-danger">*</small> </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                                    <input type="text" name="sales_quotation_date" id="sales_quotation_date"
                                        class="form-control"
                                        value="{{ Carbon\Carbon::parse($model->sales_quotation_date)->format('d-m-Y') ?? '' }}">
                                </div>
                                <span class="error text-danger" id="sales_quotation_dateError"></span>

                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="salesman_id">Salesman</label>
                                <select name="salesman_id" id="salesman_id" class="form-select select2"
                                    data-placeholder="Select Salesman">
                                    <option></option>
                                    @foreach ($salesman as $salesman)
                                        <option value="{{ $salesman->id }}"
                                            {{ $model->salesman_id == $salesman->id ? 'selected' : '' }}>
                                            {{ $salesman->fullname }}
                                        </option>
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
                                @include('sales.salesQuotation.part.table_sales_quotation')

                            </div>
                            <div class="tab-pane fade" id="navs-pills-left-profile" role="tabpanel">
                                @include('sales.salesQuotation.part.info_sales_quotation_edit')

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
                                    placeholder="0" readonly value="{{ $model->sub_total ?? 0 }}">
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
                    </div>
                    <div class="col-2 mb-3" id="ppn_container" style="display:none;">
                        <div class="col-12 mb-3 ">
                            <label class="form-label" for="sub_total" id="taxes">Tax</label>
                            <div class="input-group input-group-merge">
                                <input type="text" name="tax_amount" id="tax_amount" class="form-control"
                                    value="{{ $model->tax_amount }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
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
                    <a href="{{ route('sales-quotation.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    @include('sales.salesQuotation.part.modal_sales_quotation')
    {{-- @include('sales.sales_quotation.part.modals.modalRequisitionDetail') --}}
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
            @if (isset($model) && $model->details)
                @foreach ($model->details as $detail)
                    {
                        'product_id': '{{ $detail->product_id }}',
                        'data_produk': '{{ $detail->produkID ? $detail->produkID->nama_barang : 'Product Not Found' }}',
                        'quantity': '{{ $detail->qty ?? 0 }}',
                        'unit_id': '{{ $detail->unit_id }}',
                        'unit': '{{ $detail->unitID ? $detail->unitID->name ?? ($detail->unitID->detail ?? ($detail->unitID->nama ?? 'Unit')) : 'Unit' }}',
                        'unit_price': '{{ $detail->unit_price ?? 0 }}',
                        'discount_percent': '{{ $detail['discount_percent'] }}',
                        'discount': '{{ $detail->discount ?? 0 }}',
                        'amount': '{{ $detail->amount ?? 0 }}',
                    }
                    {{ !$loop->last ? ',' : '' }}
                @endforeach
            @endif
        ];
        const originalPrDetailsData = JSON.parse(JSON.stringify(prDetailsData));
        $(function() {
            const datePicker = flatpickr("#sales_quotation_date", {
                enableTime: false,
                dateFormat: "d-m-Y",
            });
        });
        $(document).ready(function() {
            $(".select2-modal").each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: $this.attr("data-placeholder"),
                    width: "100%",
                    dropdownParent: $("#modalPrDetail"),
                });
            });

            $("#customer_contact_id").select2({
                placeholder: "Select Contact",
                width: "100%",
            });

            $("#payment_term_id").select2({
                placeholder: "Select Payment Term",
                width: "100%",
            });

            let table = new DataTable("#table", {
                processing: true,
                serverSide: false,
                responsive: true,
                select: true,
                searching: false,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"],
                ],
                data: prDetailsData,
                columns: [{
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        },
                    },
                    {
                        data: "data_produk",
                        render: function(data, type, row) {
                            // Menampilkan kode referensi PR di bawah nama produk jika ada
                            // if (row.requisition_code) {
                            //     return `<strong>${data}</strong><br><small class="text-primary">Ref: ${row.requisition_code}</small>`;
                            // }
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

                                    $("#total_price").val(data.amount || data.total_price || 0);

                                    $("#quantity").val(data.quantity);
                                    $("#unit_id").data("pending-val", data.unit_id);
                                    $("#product_id").val(data.product_id).trigger("change");
                                    $("#unit_price").val(data.unit_price);
                                    $("#discount_percent").val(data.discount_percent || 0);
                                    $("#discount").val(data.discount || 0);
                                    $("#tax").val(data.tax || 0);

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
                    url: '/get-kontak/' + customerId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {

                        // ==========================
                        // Kontak
                        // ==========================
                        contactDropdown.empty();
                        contactDropdown.append('<option value="">Pilih Kontak</option>');

                        $.each(response.kontak, function(key, value) {

                            contactDropdown.append(`
                    <option value="${value.id}">
                        ${value.sapaan} ${value.contact_person}
                        (${value.posisi_jabatan})
                    </option>
                `);

                        });

                        // ==========================
                        // Pajak
                        // ==========================
                        if (response.pajak) {
                            $('#taxpayer_data').val(response.pajak.tipe_id_pajak + ' :' +
                                response.pajak.nomor_wajib_pajak);
                        } else {
                            $('#taxpayer_data').val('');

                        }

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
                        "Silahkan pilih Customer terlebih dahulu pada form utama SQ!",
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
                        console.error("Gagal memuat list unit dari Controller.");
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
                    url: `/sales-quotation/sq/price-history?product_id=${productId}&customer_id=${customerId}`,
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
                                    "Belum ada riwayat SQ dengan customer ini. Silahkan isi harga manual.",
                                );
                            dropdownBtn.prop("disabled", true);
                            if (priceInput.val() === "") {
                                priceInput.val("0");
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error("Gagal mengambil data riwayat harga:", xhr);
                        helperText
                            .attr("class", "form-text text-danger")
                            .text("Gagal memuat riwayat harga.");
                    },
                });
            });

            // function calculateGrandTotal() {
            //     let grandSubTotal = 0;

            //     // 1. Iterasi/looping semua data amount yang ada di array lokal
            //     $.each(prDetailsData, function(index, item) {
            //         grandSubTotal += parseFloat(item.amount) || 0;
            //     });

            //     // 2. Masukkan hasil penjumlahan ke input field Sub Total
            //     $("#sub_total").val(Math.round(grandSubTotal));

            //     // 3. Hitung ulang diskon global secara otomatis saat isi tabel berubah
            //     let currentPercent = parseFloat($("#percent").val()) || 0;

            //     if (currentPercent > 0) {
            //         // Jika awalnya diisi persen, hitung ulang nominal Rupiahnya berdasarkan Sub Total baru
            //         let newDiscountNominal = grandSubTotal * (currentPercent / 100);
            //         $("#discount_all").val(Math.round(newDiscountNominal));
            //     } else {
            //         // Jika awalnya diisi nominal Rupiah, validasi agar tidak melebihi Sub Total baru
            //         let currentNominal = parseFloat($("#discount_all").val()) || 0;
            //         if (currentNominal > grandSubTotal) {
            //             currentNominal = grandSubTotal;
            //             $("#discount_all").val(Math.round(grandSubTotal));
            //         }
            //         // Set ulang nilai persen barunya
            //         let newPercent =
            //             grandSubTotal > 0 ? (currentNominal / grandSubTotal) * 100 : 0;
            //         $("#percent").val(
            //             newPercent % 1 === 0 ? newPercent : newPercent.toFixed(2),
            //         );
            //     }

            //     // 4. Update hasil akhir ke Total Order
            //     calculateTotalOrder();
            // }

            // function calculateTotalOrder() {
            //     // Ambil nilai dari input, jika kosong atau bukan angka, default ke 0
            //     let subTotal = parseFloat($("#sub_total").val()) || 0;
            //     let discount = parseFloat($("#discount_all").val()) || 0;

            //     // Rumus: Total Order = Sub Total - Discount
            //     let totalOrder = subTotal - discount;

            //     // Cegah nilai total order menjadi minus jika discount lebih besar dari subtotal
            //     if (totalOrder < 0) {
            //         totalOrder = 0;
            //     }

            //     // Masukkan hasil kalkulasi ke input Total Order
            //     $("#total_order").val(Math.round(totalOrder));
            // }

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

                if (
                    typeof prDetailsData === "undefined" ||
                    prDetailsData.length === 0
                ) {
                    Swal.fire({
                        icon: "warning",
                        title: "Empty Items",
                        text: "Please add at least one item detail to the table before saving.",
                        confirmButtonText: "OK",
                        customClass: {
                            confirmButton: "btn btn-primary waves-effect waves-light",
                        },
                        buttonsStyling: false,
                    }).then(() => {
                        // AFTER MODAL CLOSED
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
                    });

                    return false;
                }

                formData.append("save_and_new", saveAndNew ? 1 : 0);
                formData.append("items_detail", JSON.stringify(prDetailsData));

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
            $("#formPrDetail").on("submit", function(e) {
                e.preventDefault();

                let productId = $("#product_id").val();
                let productName = $("#product_id option:selected").text();
                let quantity = parseFloat($("#quantity").val()) || 0;
                let unitId = $("#unit_id").val();
                let unitName = $("#unit_id option:selected").text();
                let detailId = $("#detail_id")
                    .val(); // Ini adalah index row array (kosong jika barang baru)

                let unitPrice = parseFloat($("#unit_price").val()) || 0;
                let discountPercent = $("#discount_percent").val() || 0;
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
                    unit_price: unitPrice,
                    discount_percent: discountPercent,
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
                    // Properti bawaan PR seperti 'requisition_code' & 'purchase_requisition_detail_id' 
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

            var initialCustomerId = $('#customer_id').val();
            if (initialCustomerId) {
                // Panggil fungsi atau jalankan AJAX yang sama dengan yang ada di event change
                loadKontak(initialCustomerId);
            }

            function loadKontak(customerId) {
                var contactDropdown = $('#customer_contact_id');
                var selectedContactId =
                    "{{ $model->customer_contact_id ?? '' }}"; // Ambil ID kontak yang tersimpan

                $.ajax({
                    url: '/get-kontak/' + customerId,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        contactDropdown.empty();
                        contactDropdown.append('<option value="">Pilih Kontak</option>');

                        $.each(data, function(key, value) {
                            // Cek apakah ID kontak ini sama dengan yang tersimpan di database
                            var isSelected = (value.id == selectedContactId) ? 'selected' : '';

                            contactDropdown.append(
                                '<option value="' + value.id + '" ' + isSelected + '>' +
                                value.sapaan + ' ' + value.contact_person + ' (' + value
                                .posisi_jabatan + ')' +
                                '</option>'
                            );
                        });

                        // Jika Anda menggunakan Select2, jangan lupa trigger update
                        contactDropdown.trigger('change.select2');
                    }
                });
            }
        });
    </script>
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
            }

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

            // subtotal setelah diskon
            let subtotal = grandSubTotal - discount;

            if (subtotal < 0)
                subtotal = 0;

            let dpp = subtotal;
            let tax = 0;
            let totalOrder = subtotal;

            if (kenaPajak && taxPercent > 0) {

                $("#ppn_container").show();

                if (totalInclude) {

                    // ==================================
                    // TAX INCLUSIVE
                    // subtotal sudah termasuk pajak
                    // ==================================

                    dpp = subtotal / (1 + (taxPercent / 100));

                    tax = subtotal - dpp;

                    totalOrder = subtotal;

                } else {

                    // ==================================
                    // TAX EXCLUSIVE
                    // subtotal belum termasuk pajak
                    // ==================================

                    dpp = subtotal;

                    tax = dpp * taxPercent / 100;

                    totalOrder = dpp + tax;
                }

            } else {

                $("#ppn_container").hide();

                dpp = subtotal;
                tax = 0;
                totalOrder = subtotal;
            }

            // Label tax
            $("#taxes").text(
                taxPercent > 0 ?
                `Tax (${taxPercent}%)` :
                "Tax"
            );

            // ===================================================
            // SUB TOTAL TETAP DARI TABEL (JANGAN DPP)
            // ===================================================
            $("#sub_total").val(Math.round(subtotal));

            // Simpan DPP jika diperlukan
            $("#dpp_amount").val(Math.round(dpp));

            $("#tax_amount").val(Math.round(tax));

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
@endpush
