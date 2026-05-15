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



                </div>
            </div>

        </div>
        <div class="card-body table-responsive p-3">
            <form action="{{ route('user.store') }}" method="POST" id="postForm" enctype="multipart/form-data">
                @csrf
                <div class="row mb-5">

                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Order By</label>
                                    <select name="customer_id" id="customer_id" class="form-select select2"
                                        data-placeholder="Select Customer">
                                        <option></option>
                                        @foreach ($customer as $cust)
                                            <option value="{{ $cust->id }}">{{ $cust->nama }}</option>
                                        @endforeach
                                    </select>
                                    <span class="error text-danger" id="avatarError"></span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <textarea name="" id="" cols="" rows="5" class="form-control" disabled
                                    placeholder="Address"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                    </div>
                    <div class="col-md-3">
                        <div class="col-12 mb-3">
                            <label class="form-label">Request Number<small class="text-danger">*</small> </label>
                            <input type="text" name="code" id="code" class="form-control"
                                value="{{ $idNumber }}">
                            <span class="error text-danger" id="codeError"></span>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Request Date<small class="text-danger">*</small> </label>
                            <input type="date" name="date" id="date" class="form-control" value="">
                            <span class="error text-danger" id="dateError"></span>
                        </div>
                    </div>

                </div>
                <div class="divider divider-dashed">
                    <div class="divider-text">Purchase Requisition Detail</div>
                </div>
                {{-- <button class="btn btn-sm btn-primary"><i class="ti ti-plus me-1"></i>Add</button>
                <button class="btn btn-sm btn-primary"><i class="ti ti-edit me-1"></i>Edit</button>
                <button class="btn btn-sm btn-primary"><i class="ti ti-refresh me-1"></i>Refresh</button> --}}
                <div class="row mt-3">
                    <table class="table display responsive nowrap" id="table">
                        <thead class="border-top" style="background-color: #AEDEFC; ">
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                {{-- <th>Qty</th>
                                <th>Unit</th>
                                <th>Unit Price</th>
                                <th>Disc %</th>
                                <th>Tax</th>
                                <th>Amount</th> --}}
                            </tr>
                        </thead>

                    </table>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <button type="submit" id="savedata" class="btn btn-primary" data-save-and-new="false">
                        <i class="fa fa-upload me-1"></i> Save and Close
                    </button>

                    <button type="submit" id="savedatamore" class="btn btn-success" data-save-and-new="true">
                        <i class="fa fa-plus-circle me-1"></i> Save and Create New
                    </button>
                    <a href="{{ route('penawaran-pembelian.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="modalPrDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Create new entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formPrDetail">
                    @csrf
                    <input type="hidden" name="id" id="detail_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label" for="product_id">Product / Item</label>
                            <select name="product_id" id="product_id" class="form-select select2-modal">
                                <option value="">Select Product</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="qty">Quantity</label>
                            <input type="number" id="qty" name="qty" class="form-control" placeholder="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitModal">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/2.0.3/css/select.bootstrap5.css">
@endpush
@push('scripts')
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.js"></script>

    <script src="https://cdn.datatables.net/select/2.0.3/js/dataTables.select.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.3/js/select.bootstrap5.js"></script>
    <script>
        $(document).ready(function() {

            let table = new DataTable('#table', {
                processing: true,
                serverSide: true,
                responsive: true,
                select: true, // WAJIB TRUE agar fitur Edit/Delete berfungsi
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All']
                ],
                ajax: '{{ route('penawaran-pembelian.table_pr') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'data_produk',
                        name: 'data_produk'
                    }
                ],
                layout: {
                    topStart: {
                        buttons: [{
                                text: '<i class="ti ti-plus me-1"></i> New',
                                className: 'btn btn-primary btn-sm',
                                action: function(e, dt, node, config) {
                                    $('#formPrDetail')[0].reset();
                                    $('#detail_id').val('');
                                    $('#modalTitle').text('Create new entry');
                                    $('#btnSubmitModal').text('Create');
                                    $('#modalPrDetail').modal('show');
                                }
                            },
                            {
                                text: '<i class="ti ti-edit me-1"></i> Edit',
                                className: 'btn btn-warning btn-sm',
                                extend: 'selectedSingle', // Aktif hanya jika 1 baris dipilih
                                action: function(e, dt, node, config) {
                                    let data = dt.row({
                                        selected: true
                                    }).data();
                                    $('#detail_id').val(data.id);
                                    $('#product_id').val(data.product_id).trigger('change');
                                    $('#qty').val(data.qty);

                                    $('#modalTitle').text('Edit entry');
                                    $('#btnSubmitModal').text('Update');
                                    $('#modalPrDetail').modal('show');
                                }
                            },
                            // --- TAMBAHKAN TOMBOL DELETE DI SINI ---
                            {
                                text: '<i class="ti ti-trash me-1"></i> Delete',
                                className: 'btn btn-danger btn-sm',
                                extend: 'selected', // Aktif jika ada 1 atau lebih baris yang dipilih
                                action: function(e, dt, node, config) {
                                    // 1. Ambil data dari baris yang dipilih
                                    let rows = dt.rows({
                                        selected: true
                                    }).data().toArray();

                                    // Ambil ID dan Nama Produk dari baris pertama yang di-select
                                    let id = rows[0].id;
                                    let name = rows[0].data_produk ? rows[0].data_produk :
                                        ''; // Mengambil nama produk agar tidak eror

                                    Swal.fire({
                                        title: 'Are you sure?',
                                        text: "Want to delete data: " + name,
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonText: 'Yes, delete it!',
                                        cancelButtonText: 'Cancel',
                                        customClass: {
                                            confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
                                            cancelButton: 'btn btn-label-secondary waves-effect waves-light'
                                        },
                                        buttonsStyling: false
                                    }).then(function(result) {
                                        if (result.isConfirmed) {
                                            $.ajax({
                                                url: `/penawaran-pembelian/detail/delete/${id}`,
                                                type: "DELETE",
                                                cache: false,
                                                data: {
                                                    _token: '{{ csrf_token() }}' // Menggunakan token csrf Laravel langsung
                                                },
                                                success: function(response) {
                                                    // Memuat ulang data tabel via AJAX tanpa reload halaman penuh
                                                    dt.ajax.reload(null, false);

                                                    toastr.success(
                                                        'Deleted Data Successfully',
                                                        '', {
                                                            timeOut: 1500,
                                                            progressBar: true,
                                                            closeButton: false,
                                                            positionClass: 'toast-top-right',
                                                        }
                                                    );
                                                },
                                                error: function(jqXHR, textStatus,
                                                    errorThrown) {
                                                    Swal.fire({
                                                        icon: 'error',
                                                        title: 'Failed to delete',
                                                        text: 'An error occurred. Please try again later.',
                                                        timer: 5000,
                                                        customClass: {
                                                            confirmButton: 'btn btn-info waves-effect waves-light'
                                                        }
                                                    });
                                                }
                                            });
                                        } else if (result.dismiss === Swal.DismissReason
                                            .cancel) {
                                            Swal.fire({
                                                icon: 'info',
                                                title: 'Cancelled',
                                                text: 'Your data is safe.',
                                                customClass: {
                                                    confirmButton: 'btn btn-info waves-effect waves-light'
                                                }
                                            });
                                        }
                                    });
                                }
                            },
                            {
                                text: '<i class="ti ti-refresh me-1"></i> Refresh',
                                className: 'btn btn-secondary btn-sm',
                                action: function(e, dt, node, config) {
                                    dt.ajax.reload(null, false);
                                }
                            }
                        ]
                    }
                }
            });
        });
    </script>
@endpush
