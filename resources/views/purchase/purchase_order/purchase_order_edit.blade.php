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
                        <i class="ti ti-clipboard me-1"></i> REQUISITION
                    </button>

                </div>
            </div>

        </div>
        <div class="card-body table-responsive p-3">
            <form action="{{ route('purchase-order.update', $model->id) }}" method="POST" id="postForm"
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
                                                <th>Disc</th>
                                                <th>Amount</th>
                                                <th>Warehouse</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-left-profile" role="tabpanel">
                                @include('purchase.purchase_order.part.isi_tab.info_pesanan_edit')

                            </div>

                        </div>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-3"></div>
                    <div class="col-md-3">
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
                    <button type="submit" id="savedata" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Update
                    </button>
                    <a href="{{ route('purchase-order.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    @include('purchase.purchase_order.part.modals.modalPrDetail')
    @include('purchase.purchase_order.part.modals.modalRequisitionDetail')
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

        $("#btnAddShipping").click(function() {
            Swal.fire({
                title: "Add New Shipping",
                input: "text",
                inputLabel: "Shipping Name",
                inputPlaceholder: "Input shipping name...",

                showCancelButton: true,

                // confirmButtonColor: "#3085d6",
                // cancelButtonColor: "#d33",

                confirmButtonText: "Save",
                cancelButtonText: "Cancel",
                customClass: {
                    confirmButton: "btn btn-primary me-2",
                    cancelButton: "btn btn-danger",
                },
                buttonsStyling: false,
                inputValidator: (value) => {
                    if (!value) {
                        return "Shipping wajib diisi";
                    }
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('shipping.store') }}",
                        type: "POST",

                        data: {
                            nama: result.value,
                            _token: "{{ csrf_token() }}",
                        },

                        success: function(response) {
                            let option = new Option(
                                response.nama,
                                response.id,
                                true,
                                true,
                            );

                            $("#vehicle_id").append(option).trigger("change");

                            Swal.fire({
                                icon: "success",
                                title: "Success",
                                text: response.message,
                                customClass: {
                                    confirmButton: "btn btn-primary me-2",
                                },
                                buttonsStyling: false,
                            });
                        },

                        error: function(xhr) {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "Failed save shipping",
                                customClass: {
                                    confirmButton: "btn btn-info",
                                },
                                buttonsStyling: false,
                            });
                        },
                    });
                }
            });
        });
        $("#btnAddTerm").click(function() {
            Swal.fire({
                title: "Add New Payment Term",
                input: "text",
                theme: "bootstrap-5",
                inputLabel: "Payment Term Name",
                inputPlaceholder: "Input Payment Term name...",
                showCancelButton: true,
                confirmButtonText: "Save",
                cancelButtonText: "Cancel",
                customClass: {
                    confirmButton: "btn btn-primary me-2",
                    cancelButton: "btn btn-danger",
                },
                buttonsStyling: false,
                inputValidator: (value) => {
                    if (!value) {
                        return "Shipping wajib diisi";
                    }
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('syarat-pembayaran.store') }}",
                        type: "POST",

                        data: {
                            nama: result.value,
                            _token: "{{ csrf_token() }}",
                        },
                        success: function(response) {
                            let option = new Option(
                                response.nama,
                                response.id,
                                true,
                                true,
                            );
                            $("#payment_term").append(option).trigger("change");

                            Swal.fire({
                                icon: "success",
                                title: "Success",
                                text: response.message,
                                customClass: {
                                    confirmButton: "btn btn-primary me-2",
                                },
                                buttonsStyling: false,
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "Failed save payment term",
                                customClass: {
                                    confirmButton: "btn btn-info",
                                },
                                buttonsStyling: false,
                            });
                        },
                    });
                }
            });
        });

        $("#showModalpr").on("click", function(e) {
            e.preventDefault();

            let tbody = $("#requisitionTableBody");
            var supplierId = $("#supplier_id").val();

            // Validasi wajib pilih supplier dulu
            if (!supplierId || supplierId === "") {
                Swal.fire({
                    icon: "warning",
                    title: "Warning!",
                    text: "Please select Supplier first before adding new data.",
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
            $("#modalRequisitionDetail").modal("show");

            // Ambil data PR berstatus processing
            $.ajax({
                url: "{{ route('purchase-order.requisitions.processing') }}",
                type: "GET",
                dataType: "json",
                success: function(response) {
                    tbody.empty();

                    if (response && response.length > 0) {
                        $.each(response, function(key, item) {
                            let dateFormatted = new Date(item.created_at).toLocaleDateString(
                                "id-ID");

                            // Tambahkan baris PR ke tabel modal
                            tbody.append(`
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input checkItem" type="checkbox" value="${item.id}">
                                    </div>
                                </td>
                                <td><strong>${item.code}</strong></td>
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
            // Ambil nilai company_id yang sedang terpilih saat ini
            let companyId = $("#company_id").val("1");
            // Jalankan fungsi AJAX bawaanmu
            loadAddressHistory(companyId);
        });

        // Event ketika salah satu list alamat di dalam dropdown diklik
        $(document).on("click", ".select-address", function() {
            let chosenAddress = $(this).data("address");

            $("#shipping_address").val(chosenAddress);
        });

        function loadAddressHistory(companyId) {
            if (!companyId) return;

            $.ajax({
                url: `/purchase-order/get-company-addresses/${companyId}`,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    let dropdownMenu = $("#address-dropdown-menu");
                    dropdownMenu.empty();

                    if (response.success && response.data.length > 0) {
                        response.data.forEach(function(item) {
                            // Ditambahkan class p-2, text-dark, dan w-100 agar warna tulisan muncul dan areanya lebar
                            let listItem = `
                        <li class="w-100">
                            <a class="dropdown-item select-address p-2 d-block text-dark" href="javascript:void(0);" data-address="${item.address}" style="white-space: normal;">
                                <strong class="text-dark d-block mb-1">${item.address_name}</strong>
                                <span class="text-muted small d-block">${item.address}</span>
                            </a>
                        </li>
                    `;
                            dropdownMenu.append(listItem);
                        });
                    } else {
                        dropdownMenu.append(
                            '<li><span class="dropdown-item text-muted p-2">No address history found</span></li>',
                        );
                    }
                },
                error: function(xhr) {
                    console.error("Gagal memuat alamat:", xhr.responseText);
                },
            });
        }
    </script>
    <script>
        let prDetailsData = [
            @if (isset($jsonDetails))
                @foreach ($jsonDetails as $detail)
                    {
                        'id': '{{ $detail['id'] }}',
                        'purchase_order_id': '{{ $detail['purchase_order_id'] }}',
                        'purchase_requisition_detail_id': '{{ $detail['purchase_requisition_detail_id'] }}',
                        'requisition_code': '{{ $detail['requisition_code'] ?? '' }}', // Ini yang Anda cari
                        'product_id': '{{ $detail['product_id'] }}',
                        'data_produk': '{{ $detail['data_produk'] }}',
                        'quantity': '{{ $detail['quantity'] }}',
                        'unit_id': '{{ $detail['unit_id'] }}',
                        'unit': '{{ $detail['unit'] }}',
                        'warehouse_id': '{{ $detail['warehouse_id'] }}',
                        'warehouse': '{{ $detail['warehouse'] }}',
                        'unit_price': '{{ $detail['unit_price'] }}',
                        'discount': '{{ $detail['discount'] }}',
                        'amount': '{{ $detail['amount'] }}',
                        'sisa_pr': '{{ $detail['sisa_pr'] }}',
                        'kuota_asli': '{{ $detail['kuota_asli'] }}',
                        'total_diambil_lainnya': '{{ $detail['total_diambil_lainnya'] }}'
                    }
                    {{ !$loop->last ? ',' : '' }}
                @endforeach
            @endif
        ];

        // Cek status PO global (Optional jika ingin mematikan tombol "Requisition" di pojok kanan atas saat edit)
        let poIsFromPR = {{ $isFromPR ? 'true' : 'false' }};
        if (poIsFromPR) {
            // Jika PO ini dari PR, mungkin kamu mau mendisable tombol "REQUISITION" di atas agar user tidak tambah PR lain
            $(".btn-success").html('<i class="ti ti-link"></i> Linked to PR').prop('disabled', true);
        }
        // let prDetailsData = [
        //     @if (isset($model) && isset($model->details))
        //         @foreach ($model->details as $detail)
        //             {
        //                 'product_id': '{{ $detail->product_id }}',
        //                 'data_produk': '{{ $detail->produkID ? $detail->produkID->nama_barang : 'Product Not Found' }}',
        //                 'quantity': '{{ $detail->qty }}',
        //                 'unit_id': '{{ $detail->unit_id }}',
        //                 'unit': '{{ $detail->unitID ? $detail->unitID->name ?? ($detail->unitID->detail ?? $detail->unitID->nama) : 'Unit' }}',
        //                 'unit_price': '{{ $detail->unit_price }}',
        //                 'discount': '{{ $detail->discount ?? 0 }}',
        //                 'amount': '{{ $detail->amount }}',
        //             }
        //             {{ !$loop->last ? ',' : '' }}
        //         @endforeach
        //     @endif
        // ];

        $(document).ready(function() {
            $(".select2-modal").each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: $this.attr("data-placeholder"),
                    width: "100%",
                    dropdownParent: $("#modalPrDetail"),
                });
            });
            // MENAMPILKAN TABEL BARANG
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
                            if (row.requisition_code) {
                                return `<strong>${data}</strong><br><small class="text-primary">Ref: ${row.requisition_code}</small>`;
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
                                    var supplierId = $("#supplier_id").val();

                                    if (!supplierId || supplierId === "") {
                                        // ... (SweetAlert warning code tetap sama)
                                        return false;
                                    }

                                    $("#formPrDetail")[0].reset();
                                    $("#detail_id").val(""); // Kosongkan index menandakan data baru

                                    // === BERSIHKAN ATRIBUT VALDASI PR AGAR TIDAK MENGUNCI DATA BARU ===
                                    $("#quantity").removeAttr("data-max-allowed");
                                    $("#quantity").removeAttr("data-qty-lama");

                                    // Reset status asal source di modal saat buat data baru
                                    $("#badgeSource").removeClass("bg-primary").addClass(
                                        "bg-secondary").text("Source: Manual / Tanpa PR");
                                    $("#product_id").prop("disabled",
                                        false); // Pastikan bisa dipilih kembali

                                    if ($.fn.select2) {
                                        $("#product_id").val("").trigger("change");
                                        $("#unit_id").val("").trigger("change");
                                    }

                                    $("#modalTitle").text("Create new entry");
                                    $("#btnSubmitModal").text("Create");
                                    $("#modalPrDetail").modal("show");
                                }
                            },
                            {
                                text: '<i class="ti ti-edit me-1"></i> Edit',
                                className: "btn btn-warning btn-sm me-2",
                                extend: "selectedSingle",
                                action: function(e, dt, node, config) {
                                    let rowSelected = dt.row({
                                        selected: true
                                    });
                                    let data = rowSelected.data();
                                    let rowIndex = rowSelected.index();

                                    // 1. TANDAI BAHWA SEDANG PROSES EDIT
                                    window.isPopulating = true;
                                    window.isEditingMode = true;

                                    // 2. Setup Data Dasar
                                    $("#detail_id").val(rowIndex);

                                    // Ambil data untuk kalkulasi
                                    let kuotaAwalPr = parseFloat(data.kuota_asli || 0);
                                    let sisaPr = parseFloat(data.sisa_pr || 0);
                                    let totalLain = parseFloat(data.total_diambil_lainnya ||
                                        0); // Data baru dari backend
                                    let qtySekarang = parseFloat(data.quantity || 0);

                                    // 3. Update Title & UI Modal dengan informasi total serapan lain
                                    $("#modalTitle").html(`
                                            Edit Entry |
                                            <span class="badge bg-primary">PR Awal: ${kuotaAwalPr}</span>
                                            <span class="badge bg-warning text-dark">Sudah diambil PO lain: ${totalLain}</span>
                                        `);

                                    // 4. Bersihkan Form (Kecuali Hidden Fields)
                                    $("#formPrDetail").find("input, select, textarea").not(
                                        '[type="hidden"]').val('');

                                    // 5. Isi Data ke Input Form
                                    $("#quantity").val(qtySekarang);
                                    $("#modal_purchase_requisition_detail_id").val(data
                                        .purchase_requisition_detail_id || "");
                                    $("#unit_price").val(parseFloat(data.unit_price || 0));
                                    $("#discount").val(parseFloat(data.discount || 0));
                                    $("#tax").val(parseFloat(data.tax || 0));

                                    // 6. Set Validasi Maksimal di Input
                                    // Logika: Sisa PR (outstanding) + Qty PO ini sendiri (karena qty lama akan di-overwrite)
                                    let maxAllowed = sisaPr + qtySekarang;
                                    $("#quantity").attr("data-max-allowed", maxAllowed);
                                    $("#quantity").attr("placeholder", "Maksimal: " + maxAllowed);

                                    // 7. Penanganan Select2 (Product & Unit)
                                    $('#unit_id').prop("disabled", false);
                                    $('#product_id').prop("disabled", false);

                                    // Trigger Product untuk memuat daftar unit via AJAX
                                    $("#product_id").val(data.product_id).trigger("change.select2");
                                    $("#warehouse_id").val(data.warehouse_id).trigger(
                                        "change.select2");

                                    // Delay untuk menunggu respons AJAX produk selesai
                                    setTimeout(function() {
                                        let $unitSelect = $('#unit_id');
                                        $unitSelect.empty();

                                        if (data.unit_id) {
                                            let newOption = new Option(data.unit || "Unit",
                                                data.unit_id, true, true);
                                            $unitSelect.append(newOption).trigger(
                                                'change.select2');
                                        } else {
                                            $unitSelect.val('').trigger('change.select2');
                                        }

                                        // Lepaskan flag setelah semua proses selesai
                                        window.isPopulating = false;
                                    }, 500);

                                    // 8. Tampilkan Modal
                                    $("#btnSubmitModal").text("Update");
                                    $("#modalPrDetail").modal("show");
                                }
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
                    tax: tax,
                    amount: (qtyInput * unitPrice) - discount,
                    purchase_requisition_detail_id: prDetailId // Ini yang akan dikirim ke controller
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

            //==============================+++++++++++

            // 5. Event Handler: Submit Form Modal Detail (Sekarang baris prDetailsData PASTI terbaca)
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

            // Jalankan fungsi setiap kali user mengetik sesuatu di Sub Total atau Discount
            $("#sub_total, #discount_all").on("input", function() {
                calculateTotalOrder();
            });

            function calculateGrandTotal() {
                let grandSubTotal = 0;

                // 1. Iterasi/looping semua data amount yang ada di array lokal
                $.each(prDetailsData, function(index, item) {
                    grandSubTotal += parseFloat(item.amount) || 0;
                });

                // 2. Masukkan hasil penjumlahan ke input field Sub Total
                $("#sub_total").val(Math.round(grandSubTotal));

                // 3. Hitung ulang diskon global secara otomatis saat isi tabel berubah
                let currentPercent = parseFloat($("#percent").val()) || 0;

                if (currentPercent > 0) {
                    // Jika awalnya diisi persen, hitung ulang nominal Rupiahnya berdasarkan Sub Total baru
                    let newDiscountNominal = grandSubTotal * (currentPercent / 100);
                    $("#discount_all").val(Math.round(newDiscountNominal));
                } else {
                    // Jika awalnya diisi nominal Rupiah, validasi agar tidak melebihi Sub Total baru
                    let currentNominal = parseFloat($("#discount_all").val()) || 0;
                    if (currentNominal > grandSubTotal) {
                        currentNominal = grandSubTotal;
                        $("#discount_all").val(Math.round(grandSubTotal));
                    }
                    // Set ulang nilai persen barunya
                    let newPercent =
                        grandSubTotal > 0 ? (currentNominal / grandSubTotal) * 100 : 0;
                    $("#percent").val(
                        newPercent % 1 === 0 ? newPercent : newPercent.toFixed(2),
                    );
                }

                // 4. Update hasil akhir ke Total Order
                calculateTotalOrder();
            }

            function calculateTotalOrder() {
                // Ambil nilai dari input, jika kosong atau bukan angka, default ke 0
                let subTotal = parseFloat($("#sub_total").val()) || 0;
                let discount = parseFloat($("#discount_all").val()) || 0;

                // Rumus: Total Order = Sub Total - Discount
                let totalOrder = subTotal - discount;

                // Cegah nilai total order menjadi minus jika discount lebih besar dari subtotal
                if (totalOrder < 0) {
                    totalOrder = 0;
                }

                // Masukkan hasil kalkulasi ke input Total Order
                $("#total_order").val(Math.round(totalOrder));
            }

            // A. Jika User Mengetik di Kolom PERSEN (%)
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
                                    "Belum ada riwayat PO dengan supplier ini. Silahkan isi harga manual.",
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

            $("#vehicle_id").select2({
                placeholder: "Select Shipping",
                width: "100%",
            });



            $("#btnSubmitSelected").on("click", function() {
                let checkedBoxes = $(".checkItem:checked");

                // 1. Validasi jika tidak ada PR yang dicentang
                if (checkedBoxes.length === 0) {
                    Swal.fire({
                        icon: "warning",
                        title: "Peringatan",
                        text: "Silakan pilih minimal satu data requisition!",
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
                            url: "{{ route('purchase-order.get-requisition-detail') }}",
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
                                        let qtyAwal = parseFloat(item.qty || 0);
                                        let sudahPO = parseFloat(item.po_qty ||
                                            0);
                                        let sisaPr = qtyAwal -
                                            sudahPO; // Batas maksimal kuantitas PR

                                        if (sisaPr <= 0) {
                                            return; // Jika sisa PR habis, jangan masukkan ke list
                                        }

                                        let unitPrice = 0;
                                        let discount = 0;
                                        let amount = sisaPr * unitPrice;

                                        prDetailsData.push({
                                            detail_id: item
                                                .id, // ID Detail PR tersimpan di sini
                                            product_id: item.product_id,
                                            data_produk: item
                                                .product_name,
                                            quantity: sisaPr,
                                            sisa_pr: sisaPr, // <--- TAMBAHKAN INI: Sebagai acuan validasi batas maksimal
                                            unit_id: item.unit_id,
                                            unit: item.unit_name,
                                            unit_price: unitPrice,
                                            discount: discount,
                                            amount: amount,
                                            requisition_code: item
                                                .requisition_code,
                                            required_date: item
                                                .required_date,
                                            notes: item.notes
                                        });
                                    });

                                    // 6. Refresh dan gambar ulang DataTables kamu
                                    $('#table').DataTable()
                                        .clear()
                                        .rows.add(prDetailsData)
                                        .draw();

                                    // 7. Hitung ulang total matematika PO
                                    if (typeof calculateGrandTotal === "function") {
                                        calculateGrandTotal();
                                    }
                                    if (typeof calculateTotalOrder === "function") {
                                        calculateTotalOrder();
                                    }

                                    // 8. Tutup Modal Requisition
                                    $("#modalRequisitionDetail").modal("hide");

                                    // 9. Beri feedback sukses ke user
                                    Swal.fire({
                                        icon: "success",
                                        title: "Success",
                                        text: "Data requisition berhasil dimasukkan.",
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
        });
    </script>
@endpush
