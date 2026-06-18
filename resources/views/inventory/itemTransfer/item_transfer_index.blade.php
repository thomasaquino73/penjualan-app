@extends('layouts.app')
@section('title', $title)
@section('konten')
    <h4><span class="text-muted fw-light">
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

                    @canany(['item_transfer-create'])
                        <a href="{{ route('item-transfer.create') }}" class="btn  btn-sm btn-primary">
                            <i class="ti ti-plus me-1"></i> Add Data
                        </a>
                    @endcanany
                    @canany(['item_transfer-delete'])
                        <button id="deleteSelected" class="btn btn-danger btn-sm">
                            <i class="ti ti-trash me-1"></i> Delete Selected
                        </button>
                    @endcanany

                </div>
            </div>

        </div>

        <div class="card-datatable table-responsive" style="padding: 20px">
            <table class="table" id="table">
                <thead style="background-color: #AEDEFC; ">
                    <tr>
                        <th>
                            <div class="form-check form-check-primary mt-3">
                                <input class="form-check-input" type="checkbox" value="" id="checkAll">
                            </div>
                        </th>
                        <th>#</th>
                        <th>Number</th>
                        <th>Date</th>
                        <th>Reference Warehouse</th>
                        <th>Warehouse</th>
                        <th>Description</th>
                        <th>Status</th>
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
                ajax: '{{ route('item-transfer.index') }}',
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
                        data: 'transfer_code',
                    },
                    {
                        data: 'transfer_date',
                    },
                    {
                        data: 'to_warehouse',
                    },
                    {
                        data: 'from_warehouse',
                    },
                    {
                        data: 'description',
                    },
                    {
                        data: 'status',
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
            $(document).on('click', '.btn-submit', function() {
                let id = $(this).data('id');
                let url = "{{ route('item-transfer.submit', ':id') }}".replace(':id', id);

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This Item Transfer will be submitted for processing!",
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
                                    table.draw();
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
            $(document).on('click', '.btn-approval', function() {
                let id = $(this).data('id');
                let statusTarget = $(this).data('status'); // Nilai dari HTML: 'approved' atau 'rejected'

                // =========================================================================
                // PERBAIKAN: Mengubah acuan kata 'processing' menjadi 'approved'
                // =========================================================================
                let isApprove = statusTarget === 'approved';
                let textKeterangan = isApprove ? 'approve' : 'reject';
                let confirmBtnColor = isApprove ? '#28a745' : '#dc3545';
                let confirmBtnText = isApprove ? 'Yes, Approve!' : 'Yes, Reject!';
                let confirmBtnClass = isApprove ?
                    'btn btn-success me-3 waves-effect waves-light' :
                    'btn btn-danger me-3 waves-effect waves-light';

                Swal.fire({
                    title: 'Are you sure?',
                    // PERBAIKAN: Mengubah teks berkas menjadi Item Transfer
                    text: `You are about to ${textKeterangan} this Item Transfer document.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: confirmBtnColor,
                    confirmButtonText: confirmBtnText,
                    customClass: {
                        confirmButton: confirmBtnClass,
                        cancelButton: 'btn btn-label-secondary waves-effect waves-light'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/item-transfer/change-status/' + id,
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

                                // Reload table tanpa melompat ke page 1 lagi
                                if ($.fn.DataTable.isDataTable('#table')) {
                                    $('#table').DataTable().ajax.reload(null, false);
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

        });
    </script>
@endpush
