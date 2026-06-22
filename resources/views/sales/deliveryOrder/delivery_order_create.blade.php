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
                    <button class="btn btn-success btn-sm " id="showModalpr">
                        <i class="ti ti-clipboard me-1"></i>SALES ORDER
                    </button>

                </div>
            </div>

        </div>
        <div class="card-body table-responsive p-3">
            <form action="{{ route('delivery-order.store') }}" method="POST" id="postForm" enctype="multipart/form-data">
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
                                <label class="form-label">DO Number <small class="text-danger">*</small> </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti ti-barcode"></i></span>
                                    <input type="text" name="delivery_order_code" id="delivery_order_code"
                                        class="form-control" value="{{ $idNumber }}">
                                </div>
                                <span class="error text-danger" id="delivery_order_codeError"></span>

                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row">

                            <div class="col-6 mb-3">
                                <label class="form-label">Date<small class="text-danger">*</small> </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                                    <input type="text" name="delivery_order_date" id="delivery_order_date"
                                        class="form-control" value="">
                                </div>
                                <span class="error text-danger" id="delivery_order_dateError"></span>

                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="shipping_id">Shipping</label>
                                <select name="shipping_id" id="shipping_id" class="form-select select2"
                                    data-placeholder="Select Shipping">
                                    <option></option>
                                    @foreach ($shipping as $shipping)
                                        <option value="{{ $shipping->id }}">{{ $shipping->nama }}</option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="shipping_idError"></span>
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
                                @include('sales.deliveryOrder.part.table_delivery_order')

                            </div>
                            <div class="tab-pane fade" id="navs-pills-left-profile" role="tabpanel">
                                @include('sales.deliveryOrder.part.info_delivery_order')

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
    @include('sales.deliveryOrder.part.modal_delivery_order')
    @include('sales.deliveryOrder.part.modalOrderDetail')
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
        let prDetailsData = [];
        $(function() {
            const datePicker = flatpickr("#delivery_order_date", {
                enableTime: false,
                dateFormat: "d-m-Y",
                // minDate: "today",
                defaultDate: "{{ \Carbon\Carbon::now()->format('d-m-Y') }}",
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


            $("#shipping").select2({
                placeholder: "Select Shipping",
                width: "100%",
            });

            function loadAvailableStock() {

                let productId = $('#product_id').val();
                let warehouseId = $('#warehouse_id').val();

                if (!productId || !warehouseId) {
                    $('#available_stok').val('');
                    return;
                }

                $.ajax({
                    url: "{{ route('item-transfer.wh.get-stock') }}",
                    type: "GET",
                    data: {
                        product_id: productId,
                        warehouse_id: warehouseId,
                    },
                    success: function(res) {
                        let stock = Number(res.stock);
                        $('#available_stok').val(stock);

                        $('#modalTitle').text(
                            `Create new entry (Available Stock: ${stock} ${res.unit})`
                        );
                    }
                });
            }

            $('#product_id').on('change', loadAvailableStock);
            $('#warehouse_id').on('change', loadAvailableStock);
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
                    // {
                    //     data: "warehouse_id",
                    //     className: "text-center"
                    // },

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
                                    $("#modal_purchase_quotation_detail_id").val(data.detail_id ||
                                        data.purchase_quotation_detail_id || "");
                                    $("#modal_quotation_code").val(data.quotation_code || "");

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
                                    $("#product_id").val(data.product_id).trigger("change");
                                    $("#unit_price").val(data.unit_price);
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

            $('#formPrDetail').on('submit', function(e) {
                e.preventDefault();

                let productId = $('#product_id').val();
                let productName = $('#product_id option:selected').text();
                let unitId = $('#unit_id').val();
                let unitName = $('#unit_id option:selected').text();
                let quantity = parseFloat($('#quantity').val()) || 0;
                let availableStock = parseFloat($('#available_stok').val()) || 0;
                let warehouseID = $('#warehouse_id').val();
                let detailId = $('#detail_id').val();

                // Validasi input wajib
                if (!productId || quantity <= 0 || !unitId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please fill all required fields! (Product, Quantity, Unit)',
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });
                    return false;
                }

                // Validasi stok tersedia
                if (quantity > availableStock) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Insufficient Stock!',
                        html: `
                Requested quantity <b>${quantity}</b> exceeds
                available stock <b>${availableStock}</b>.
                `,
                        customClass: {
                            confirmButton: 'btn btn-warning'
                        },
                        buttonsStyling: false
                    });

                    $('#quantity').focus();
                    return false;
                }

                // Validasi Duplikasi Produk
                let isDuplicate = false;

                if (prDetailsData && prDetailsData.length > 0) {
                    for (let i = 0; i < prDetailsData.length; i++) {

                        if (prDetailsData[i].product_id == productId) {

                            // Mode Create
                            if (detailId === '') {
                                isDuplicate = true;
                                break;
                            }

                            // Mode Edit
                            if (detailId !== '' && parseInt(i) !== parseInt(detailId)) {
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
                        html: `The product <b>"${productName}"</b> is already registered in the list.`,
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });
                    return false;
                }

                // Susun Object Data
                let itemData = {
                    product_id: productId,
                    data_produk: productName,
                    quantity: quantity,
                    unit_id: unitId,
                    unit: unitName,
                    warehouse_id: warehouseID
                };

                // Create / Update
                if (detailId === '') {
                    prDetailsData.push(itemData);
                } else {
                    prDetailsData[detailId] = itemData;
                }

                // Refresh DataTable
                table.clear();
                table.rows.add(prDetailsData);
                table.draw();

                // Reset Form
                $('#formPrDetail')[0].reset();
                $('#detail_id').val('');
                $('#product_id').val(null).trigger('change');
                $('#unit_id').empty().trigger('change');
                $('#available_stok').val('');

                // Tutup Modal
                $('#modalPrDetail').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: detailId === '' ?
                        'Item added successfully.' : 'Item updated successfully.',
                    timer: 1500,
                    showConfirmButton: false
                });
            });

            $('#customer_id').on('change', function() {
                var customerId = $(this).val();
                var contactDropdown = $('#customer_contact_id');

                // Reset dropdown
                contactDropdown.empty().append('<option>Loading...</option>');

                if (customerId) {
                    $.ajax({
                        url: '/get-kontak/' + customerId,
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            contactDropdown.empty();
                            contactDropdown.append('<option value="">Pilih Kontak</option>');

                            $.each(data, function(key, value) {
                                contactDropdown.append(
                                    '<option value="' + value.id + '">' + value
                                    .sapaan + ' ' +
                                    value.contact_person + ' (' + value
                                    .posisi_jabatan + ')' +
                                    '</option>'
                                );
                            });
                        }
                    });
                } else {
                    contactDropdown.empty().append('<option></option>');
                }
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
                        "Silahkan pilih Customer terlebih dahulu pada form utama DO!",
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


            });

            let saveAndNew = false;
            let activeBtn = null;

            $(document).on("click", '.card-footer button[type="submit"]', function() {
                saveAndNew = $(this).data("save-and-new");
                activeBtn = $(this);
            });


            $("#postForm").on("submit", function(e) {
                e.preventDefault();

                // 1. Tangkap tombol yang ditekan (lebih akurat)
                let submitter = e.originalEvent.submitter;
                let saveAndNew = $(submitter).data("save-and-new") === true;
                let btnTextOriginal = $(submitter).html();

                // 2. Validasi awal (Frontend)
                if (typeof prDetailsData === "undefined" || prDetailsData.length === 0) {
                    Swal.fire({
                        icon: "warning",
                        title: "Empty Items",
                        text: "Please add at least one item detail to the table before saving.",
                        confirmButtonText: "OK",
                        customClass: {
                            confirmButton: "btn btn-primary waves-effect waves-light",
                        },
                        buttonsStyling: false,
                    });
                    return false;
                }

                let formData = new FormData(this);
                formData.append("save_and_new", saveAndNew ? 1 : 0);
                formData.append("items_detail", JSON.stringify(prDetailsData));

                $.ajax({
                    url: $(this).attr("action"),
                    method: $(this).attr("method"),
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: "json",
                    beforeSend: function() {
                        $(submitter).html(
                                '<i class="fa fa-spin fa-spinner me-1"></i> Processing...')
                            .prop("disabled", true);
                        $(".card-footer button").prop("disabled", true);
                    },
                    complete: function() {
                        // Kembalikan teks asli ke tombol yang diklik
                        // $(submitter).html(btnTextOriginal).prop("disabled", false);
                        $(".card-footer button").prop("disabled", false);
                    },
                    success: function(response) {
                        Swal.fire({
                                icon: "success",
                                title: "Success",
                                text: response.message,
                                customClass: {
                                    confirmButton: "btn btn-primary waves-effect waves-light",
                                },
                                buttonsStyling: false,
                            })
                            .then(() => {
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
                            title: "Failed",
                            text: xhr.responseJSON?.message || "Error",
                            customClass: {
                                confirmButton: "btn btn-primary waves-effect waves-light",
                            },
                            buttonsStyling: false,
                        });

                    }
                });
            });

            // ==========================================
            // 1. EVENT KLIK UNTUK MEMBUKA MODAL PR
            // ==========================================
            $("#showModalpr").on("click", function(e) {
                e.preventDefault();

                let tbody = $("#requisitionTableBody");
                var customerId = $("#customer_id").val();

                // Validasi wajib pilih customer dulu
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

                // Reset checkbox 'Check All' menjadi tidak tercentang saat modal dibuka
                $("#checkAll").prop("checked", false);

                tbody.html(
                    '<tr><td colspan="3" class="text-center"><i class="fa fa-spin fa-spinner me-1"></i> Loading data...</td></tr>',
                );
                $("#modalOrderDetail").modal("show");

                // Ambil data PR berstatus processing
                $.ajax({
                    url: "{{ route('delivery-order.so.processing') }}",
                    type: "GET",
                    dataType: "json",
                    data: {
                        customer_id: customerId
                    },
                    success: function(response) {
                        tbody.empty();

                        if (response && response.length > 0) {
                            $.each(response, function(key, item) {
                                let dateFormatted = new Date(item.created_at)
                                    .toLocaleDateString(
                                        "id-ID");

                                // Tambahkan baris PR ke tabel modal
                                tbody.append(`
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input checkItem" type="checkbox" value="${item.id}">
                                    </div>
                                </td>
                                <td><strong>${item.sales_order_code}</strong></td>
                                <td>${dateFormatted}</td>
                            </tr>
                        `);
                            });
                        } else {
                            tbody.html(
                                '<tr><td colspan="3" class="text-center text-muted">No processing data found.</td></tr>',
                            );
                        }
                    },
                    error: function(xhr) {
                        tbody.html(
                            '<tr><td colspan="3" class="text-center text-danger">Failed to fetch data.</td></tr>',
                        );
                    },
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

            $("#btnSubmitSelected").on("click", function() {
                let checkedBoxes = $(".checkItem:checked");

                // 1. Validasi jika tidak ada PR yang dicentang
                if (checkedBoxes.length === 0) {
                    Swal.fire({
                        icon: "warning",
                        title: "Peringatan",
                        text: "Silakan pilih minimal satu data po!",
                        customClass: {
                            confirmButton: "btn btn-danger",
                        },
                        buttonsStyling: false,
                    });
                    return;
                }

                // 2. Ambil ID requisition yang dicentang
                let ids = [];
                checkedBoxes.each(function() {
                    ids.push($(this).val());
                });

                // 3. Tampilkan konfirmasi SweetAlert sebelum memproses
                Swal.fire({
                    title: "Proses data terpilih?",
                    text: `Anda memilih ${checkedBoxes.length} data untuk dimasukkan ke tabel.`,
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Ya, Masukkan!",
                    cancelButtonText: "Batal",
                    customClass: {
                        confirmButton: "btn btn-primary",
                        cancelButton: "btn btn-secondary",
                    },
                    buttonsStyling: false,
                }).then((result) => {
                    if (result.isConfirmed) {

                        // 4. Kirim request AJAX ke backend
                        $.ajax({
                            url: "{{ route('receive-item.get-order-detail') }}",
                            type: "POST",
                            data: {
                                ids: ids,
                                _token: "{{ csrf_token() }}"
                            },
                            beforeSend: function() {
                                $("#btnSubmitSelected")
                                    .html(
                                        '<i class="fa fa-spinner fa-spin me-1"></i> Processing...'
                                    )
                                    .prop("disabled", true);
                            },
                            success: function(response) {
                                if (response.success) {

                                    // Bersihkan atau siapkan array penampung global jika belum didefinisikan sebelumnya
                                    if (typeof prDetailsData === 'undefined') {
                                        window.prDetailsData = [];
                                    }

                                    // 5. Looping data response backend untuk dimasukkan ke array DataTables
                                    response.data.forEach(function(item) {
                                        // let qtyAwal = parseFloat(item.qty || 0);
                                        // let sudahPO = parseFloat(item
                                        //     .received_qty || 0);
                                        let sisaPr = parseFloat(item.quantity ||
                                            0);

                                        if (sisaPr <= 0) {
                                            return; // Jika sisa habis, jangan masukkan ke list
                                        }

                                        prDetailsData.push({
                                            detail_id: item.id,
                                            product_id: item.product_id,
                                            data_produk: item
                                                .product_name,
                                            // Pastikan key ini ada agar render di DataTable tidak error
                                            requisition_code: item
                                                .order_code,
                                            // quantity: sisaPr,
                                            quantity: sisaPr,
                                            sisa_pr: sisaPr,
                                            unit_id: item.unit_id,
                                            unit: item.unit_name,
                                            warehouse_id: null,
                                            warehouse: '-', // Sesuaikan dengan controller Anda

                                            quotation_code: item
                                                .quotation_code
                                        });
                                    });

                                    // Refresh Tabel
                                    let table = $('#table').DataTable();
                                    table.clear().rows.add(prDetailsData).draw();

                                    // 7. Hitung ulang total matematika PO
                                    // if (typeof calculateGrandTotal === "function") {
                                    //     calculateGrandTotal();
                                    // }
                                    // if (typeof calculateTotalOrder === "function") {
                                    //     calculateTotalOrder();
                                    // }

                                    // 8. Tutup Modal Requisition
                                    $("#modalOrderDetail").modal("hide");

                                    // 9. Beri feedback sukses ke user
                                    Swal.fire({
                                        icon: "success",
                                        title: "Success",
                                        text: "Data order berhasil dimasukkan.",
                                        customClass: {
                                            confirmButton: "btn btn-primary",
                                        },
                                        buttonsStyling: false,
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: "error",
                                    title: "Error",
                                    text: "Terjadi kesalahan saat mengambil data.",
                                });
                            },
                            complete: function() {
                                // Kembalikan kondisi tombol submit ke semula
                                $("#btnSubmitSelected")
                                    .html(
                                        '<i class="ti ti-check me-1"></i> Process Selected'
                                    )
                                    .prop("disabled", false);
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
