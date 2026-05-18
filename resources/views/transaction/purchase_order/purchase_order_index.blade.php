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

                    @canany(['purchase_order-create'])
                        <a href="{{ route('purchase-order.create') }}" class="btn btn-sm btn-primary">
                            <i class="ti ti-plus me-1"></i> Add Data
                        </a>
                    @endcanany
                    @canany(['purchase_order-trash'])
                        <a href="{{ route('purchase-order.trash') }}" class="btn btn-sm btn-secondary">
                            <i class="ti ti-trash me-1"></i> Trash Bin
                        </a>
                    @endcanany

                    @canany(['purchase_order-delete'])
                        <button id="deleteSelected" class="btn btn-danger btn-sm">
                            <i class="ti ti-trash me-1"></i> Delete Selected
                        </button>
                    @endcanany

                </div>
            </div>

        </div>
        <div class="card-datatable table-responsive p-3">
            <table class="table table-bordered" id="table">
                <thead class="border-top" style="background-color: #AEDEFC; ">
                    <tr>
                        <th>
                            <div class="form-check form-check-primary mt-3">
                                <input class="form-check-input" type="checkbox" value="" id="checkAll">
                            </div>
                        </th>
                        <th>#</th>
                        <th>PO Number</th>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Status</th>
                        <th>Expected Date</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Created</th>
                        <th>Updated</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            $('#checkAll').on('click', function() {
                $('.checkItem').prop('checked', this.checked);
            });

            // kalau salah satu di uncheck → header ikut off
            $(document).on('click', '.checkItem', function() {
                $('#checkAll').prop(
                    'checked',
                    $('.checkItem:checked').length === $('.checkItem').length
                );
            });
            var table = new DataTable('#table', {
                processing: true,
                serverSide: true,
                responsive: true,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All']
                ],
                ajax: '{{ route('purchase-order.index') }}',
                columns: [{
                        data: 'cekbok',
                        name: 'cekbok',
                        orderable: false,
                        searchable: false
                    }, {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'code',
                    },
                    {
                        data: 'date',
                    },

                    {
                        data: 'supplier',
                    },
                    {
                        data: 'status',
                    },
                    {
                        data: 'expected_date',
                    },
                    {
                        data: 'amount',
                    },
                    {
                        data: 'description',
                    },

                    {
                        data: 'created_at',
                    },
                    {
                        data: 'updated_at',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            $('body').on('click', '#delete', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let token = $("meta[name='csrf-token']").attr("content");

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
                            url: `/purchase-order/${id}`,
                            type: "DELETE",
                            cache: false,
                            data: {
                                _token: token
                            },
                            success: function(response) {
                                table.draw();
                                toastr.success('Deleted Data Successfully', '', {
                                    timeOut: 1500,
                                    progressBar: true,
                                    closeButton: false,
                                    positionClass: 'toast-top-right',
                                });
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
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
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
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
            });
            $('#deleteSelected').on('click', function() {

                let ids = [];

                $('.checkItem:checked').each(function() {
                    ids.push($(this).val());
                });

                if (ids.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'An error occurred. Please try again later.',
                        text: 'Please select data first!',
                        timer: 5000,
                        customClass: {
                            confirmButton: 'btn btn-primary waves-effect waves-light'
                        },
                        buttonsStyling: false
                    });
                    return;
                }

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Data will be deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
                        cancelButton: 'btn btn-label-secondary waves-effect waves-light'
                    },
                    buttonsStyling: false
                }).then((result) => {

                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/purchase-order/delete-multiple',
                            type: 'POST',
                            data: {
                                ids: ids,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                toastr.success('Deleted Data Successfully', '', {
                                    timeOut: 1500,
                                    progressBar: true,
                                    closeButton: false,
                                    positionClass: 'toast-top-right',
                                });
                                $('#table').DataTable().ajax.reload();
                            },
                            error: function() {
                                Swal.fire('Error!', 'Failed to delete data.', 'error');
                            }
                        });
                    }

                });

            });
            $(document).on('click', '.btn-submit-pr', function() {
                let id = $(this).data('id');
                let url = "{{ route('purchase-order.submit', ':id') }}".replace(':id', id);

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This Purchase Requisition will be submitted for approval!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, submit it!',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
                        cancelButton: 'btn btn-label-secondary waves-effect waves-light'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Menggunakan toastr untuk sukses sesuai style Anda
                                    toastr.success(response.message ||
                                        'Submitted Data Successfully', '', {
                                            timeOut: 1500,
                                            progressBar: true,
                                            closeButton: false,
                                            positionClass: 'toast-top-right',
                                        });

                                    // Ganti #table dengan ID DataTable Anda jika berbeda
                                    $('#table').DataTable().ajax.reload();
                                } else {
                                    Swal.fire({
                                        title: 'Warning!',
                                        text: response.message ||
                                            'Failed to submit data.',
                                        icon: 'warning',
                                        customClass: {
                                            confirmButton: 'btn btn-primary waves-effect waves-light'
                                        },
                                        buttonsStyling: false
                                    });
                                }
                            },
                            error: function(xhr) {
                                let errorMsg = xhr.responseJSON && xhr.responseJSON
                                    .message ?
                                    xhr.responseJSON.message :
                                    'Failed to submit data.';

                                Swal.fire({
                                    title: 'Error!',
                                    text: errorMsg,
                                    icon: 'error',
                                    customClass: {
                                        confirmButton: 'btn btn-primary waves-effect waves-light'
                                    },
                                    buttonsStyling: false
                                });
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.btn-approval-pr', function() {
                let id = $(this).data('id');
                let statusTarget = $(this).data('status'); // Expected: 'processing' or 'rejected'

                // Konfigurasi teks berdasarkan statusTarget
                let textKeterangan = statusTarget === 'processing' ? 'approve' : 'reject';
                let confirmBtnColor = statusTarget === 'processing' ? '#28a745' : '#dc3545';
                let confirmBtnText = statusTarget === 'processing' ? 'Yes, Approve!' : 'Yes, Reject!';
                let confirmBtnClass = statusTarget === 'processing' ?
                    'btn btn-success me-3 waves-effect waves-light' :
                    'btn btn-danger me-3 waves-effect waves-light';

                Swal.fire({
                    title: 'Are you sure?',
                    text: `You are about to ${textKeterangan} this Purchase Requisition document.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: confirmBtnColor,
                    confirmButtonText: confirmBtnText,
                    customClass: {
                        confirmButton: confirmBtnClass,
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/purchase-order/change-status/' +
                                id, // Match with your status update route
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                id: id,
                                status: statusTarget
                            },
                            success: function(response) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: response.message ||
                                        'The status has been updated successfully.',
                                    icon: 'success',
                                    showCancelButton: false,
                                    confirmButtonColor: '#28a745',
                                    confirmButtonText: 'OK',
                                    customClass: {
                                        confirmButton: 'btn btn-success'
                                    },
                                    buttonsStyling: false
                                });

                                // Reload table data (Pastikan ID table sesuai, contoh: #table atau #datatable)
                                if ($.fn.DataTable.isDataTable('#table')) {
                                    $('#table').DataTable().ajax.reload();
                                }
                            },
                            error: function(err) {
                                let errorMessage = 'Something went wrong.';
                                if (err.responseJSON && err.responseJSON.error) {
                                    errorMessage = err.responseJSON.error;
                                } else if (err.responseJSON && err.responseJSON
                                    .message) {
                                    errorMessage = err.responseJSON.message;
                                }

                                Swal.fire({
                                    title: 'Failed!',
                                    text: errorMessage,
                                    icon: 'error',
                                    showCancelButton: false,
                                    confirmButtonColor: '#3085d6',
                                    confirmButtonText: 'OK',
                                    customClass: {
                                        confirmButton: 'btn btn-primary'
                                    },
                                    buttonsStyling: false
                                });
                            }
                        });
                    }
                });
            });

            // filter
            $('#selectStatus').on('change', function() {
                table.ajax.reload();
            });
        });
    </script>
@endpush
