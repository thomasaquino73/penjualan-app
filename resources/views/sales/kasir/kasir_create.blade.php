@extends('layouts.app')
@section('konten')
    <!-- HEADER -->
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">

            <!-- Kiri -->
            <h4 class="fw-bold mb-0">🛒 Store Sales (POS)</h4>

            <!-- Kanan -->
            <div class="d-flex gap-2">
                <div class="mb-3">
                    <label class="form-label">Cashier</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-user"></i></span>
                        <input type="text" value="{{ Auth()->user()->fullname }}" class="form-control" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Date</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                        <input type="text" value="" id="sales_date" name="sales_date" class="form-control"
                            readonly>
                    </div>
                </div>

            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-2 mb-lg-0"><i class="ti ti-news me-1"></i>Transaction</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Transaction Number <small class="text-danger">*</small> </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"> <i class="ti ti-barcode"></i>
                                </span>
                                <input type="text" name="id_barang" id="id_barang" class="form-control"
                                    value="{{ $idNumber }}">
                            </div>
                            <span class="error text-danger" id="id_barangError"></span>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Customer<small class="text-danger">*</small> </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"> <i class="ti ti-user"></i>
                                </span>
                                <select name="" id="" class="form-select select2"
                                    data-placeholder="Select Customer">
                                    <option></option>
                                    @foreach ($customer as $cust)
                                        <option value="{{ $cust->id }}">{{ $cust->nama_customer }}</option>
                                    @endforeach
                                </select>
                                <span class="input-group-text"> <button class="btn btn-sm btn-info"> <i
                                            class="ti ti-plus"></i></button></span>
                            </div>
                            <span class="error text-danger" id="id_barangError"></span>
                        </div>

                    </div>
                    {{-- <div class="col-md-12">
                        <label class="form-label">Barcode / Product Name<small class="text-danger">*</small> </label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="ti ti-qrcode"></i></i>
                            </span>
                            <input type="text" name="id_barang" id="id_barang" class="form-control" value="">
                        </div>
                        <span class="error text-danger" id="id_barangError"></span>
                    </div> --}}

                    <div class="mt-5">
                        <h6 class="card-title mb-2 mb-lg-0">Product List</h6>
                        <table class="table display responsive nowrap" id="table">
                            <thead class="border-top" style="background-color: #AEDEFC; ">
                                <tr>
                                    <th>NO</th>
                                    <th>PRODUCT</th>
                                    <th>QTY</th>
                                    <th>UNIT</th>
                                    <th>Price</th>
                                    <th>DISC</th>
                                    <th>AMOUNT</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0 fw-bold"><i class="ti ti-credit-card me-1"></i>Payment Summary</h6>
                </div>

                <div class="card-body">

                    <!-- Subtotal -->
                    <div class="d-flex justify-content-between mb-2">
                        <div class="col-6 text-muted">Sub Total</div>
                        <div class="col-6">
                            <div class="input-group input-group-merge">
                                <span class="input-group-text border-0">{{ $mataUangDefault->symbol }}</span>
                                <input type="number" id="sub_total" name="sub_total" class="form-control border-0"
                                    placeholder="0" min="0" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Discount -->
                    <div class="d-flex justify-content-between mb-2">
                        <div class="col-6 text-muted">Discount</div>
                        <div class="col-6">
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $mataUangDefault->symbol }}</span>
                                <input type="number" id="discount_all" name="discount_all" class="form-control"
                                    placeholder="0" min="0">
                            </div>
                        </div>
                    </div>

                    <!-- Tax -->
                    <div class="d-flex justify-content-between mb-3">
                        <div class="col-6 text-muted" id="taxes">Tax (11%)</div>
                        <div class="col-6">
                            <div class="input-group input-group-merge">
                                <span class="input-group-text border-0">{{ $mataUangDefault->symbol }}</span>
                                <input type="text" name="tax_amount" id="tax_amount" class="form-control border-0"
                                    readonly>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Total -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="col-6 fw-bold">Total</div>

                        <div class="col-6">
                            <div class="input-group input-group-merge">
                                <span class="input-group-text border-0 bg-transparent fw-bold text-primary fs-5">
                                    {{ $mataUangDefault->symbol }}
                                </span>

                                <input type="number" id="total_order" name="total_order"
                                    class="form-control border-0 bg-transparent shadow-none text-end fw-bold text-primary fs-5"
                                    placeholder="0" readonly>
                            </div>
                        </div>
                    </div>


                    <!-- Cash Received -->
                    <div class="mb-3">
                        <label class="form-label">Amount Received</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text">{{ $mataUangDefault->symbol }}</span>
                            <input type="number" id="amount_receive" name="amount_receive" class="form-control"
                                placeholder="0" min="0">
                        </div>
                    </div>

                    <!-- Change -->
                    <div class="mb-3">
                        <label class="form-label">Change</label>
                        <input type="text" class="form-control text-success fw-bold text-end bg-light" value=""
                            readonly>
                    </div>
                    <!-- Payment Method -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Method</label>
                        <select name="" id="" class="form-select select2"
                            data-placeholder="Select Payment">
                            <option></option>
                            <option value="Cash">Tunai</option>
                            <option value="Qris">Qris</option>
                            <option value="Transfer">Transfer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Shipping Method</label>
                        <select name="" id="" class="form-select select2"
                            data-placeholder="Select Shipping">
                            <option></option>
                            <option value="Pick Up">Diambil</option>
                            <option value="Delivery">Dikirim</option>
                        </select>
                    </div>
                    <!-- Notes -->
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" rows="2" placeholder="Add notes..."></textarea>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary w-50">Cancel</button>
                        <button class="btn btn-success w-50 fw-bold">✔ Save & Pay</button>
                    </div>

                </div>
            </div>

        </div>
    </div>
    </div>
    @include('sales.kasir.part.modal_kasir')
@endsection
@include('partials.tabel.css')
@include('partials.tabel.js')
{{-- @include('partials.js.calculate_total') --}}
@push('scripts')
    <script>
        let prDetailsData = [];
        $(function() {
            const datePicker = flatpickr("#sales_date", {
                enableTime: false,
                dateFormat: "d-m-Y",
                defaultDate: "{{ \Carbon\Carbon::now()->format('d-m-Y') }}",
            });
        });

        $("#showModalpr").on("click", function(e) {
            e.preventDefault();

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

                    $("#modals").modal("show");
                }
            });
        });


        $(document).ready(function() {
            $(".select2-modal").each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: $this.attr("data-placeholder"),
                    width: "100%",
                    dropdownParent: $("#modals"),
                });
            });
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
                ],
                layout: {
                    topStart: {
                        buttons: [{
                                text: '<i class="ti ti-plus me-1"></i> New',
                                className: "btn btn-primary btn-sm me-2 AddNew",
                                action: function(e, dt, node, config) {
                                    $("#formPrDetail")[0].reset();
                                    $("#detail_id").val("");

                                    if ($.fn.select2) {
                                        $("#product_id").val("").trigger("change");
                                        $("#unit_id").val("").trigger("change");
                                    }

                                    $("#modalTitle").text("Create new entry");
                                    $("#btnSubmitModal").text("Create");
                                    $("#modals").modal("show");

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
                                    $("#unit_price").val(data.unit_price);
                                    $("#discount").val(data.discount || 0);
                                    $("#discount_percent").val(data.discount_percent || 0);
                                    $("#tax").val(data.tax || 0);
                                    $("#total_price").val(data.amount || 0);

                                    $("#modalTitle").text("Edit entry");
                                    $("#btnSubmitModal").text("Update");
                                    $("#modals").modal("show");
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





            $(document).on("change", "#product_id", function() {
                let productId = $(this).val();
                let unitSelect = $("#unit_id");
                let priceInput = $("#unit_price");
                let dropdownBtn = $("#btn-history-po");
                let dropdownMenu = $("#po-price-dropdown-menu");

                // Pastikan ID selector ini sesuai dengan ID Select Customer di form utama kamu
                if (!productId) {
                    unitSelect.empty().append("<option></option>").trigger("change");
                    priceInput.val("");
                    dropdownBtn.prop("disabled", true);
                    dropdownMenu.empty();
                    return;
                }

                // Tambahan Validasi: Ingatkan user jika customer belum dipilih



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
                $("#modals").modal("hide");
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
            document.getElementById('total_price').value = remaining.toFixed(2);
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
            document.getElementById('total_price').value = total.toFixed(2);

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
        const TAXES = @json($taxes);

        function calculateTotalOrder() {

            // Sub Total dari semua item
            let subTotal = getGrandSubTotal();

            // Discount
            let discount = parseFloat($("#discount_all").val()) || 0;

            // Nilai setelah diskon
            let taxableAmount = subTotal - discount;

            if (taxableAmount < 0) {
                taxableAmount = 0;
            }

            // Pajak tetap 11%
            let tax = taxableAmount * 0.11;

            // Total
            let totalOrder = taxableAmount + tax;

            // Tampilkan
            $("#sub_total").val(Math.round(subTotal));
            $("#tax_amount").val(Math.round(tax));
            $("#total_order").val(Math.round(totalOrder));
        }
        $("#discount_all").on("input", calculateTotalOrder);
        $("#tax_id").on("change", calculateTotalOrder);
    </script>
@endpush
