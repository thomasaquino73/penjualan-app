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

                    @canany(['purchase_invoice-create'])
                        <a href="{{ route('purchase-invoice.create') }}" class="btn btn-sm btn-primary">
                            <i class="ti ti-plus me-1"></i> Add Data
                        </a>
                    @endcanany
                    @canany(['purchase_invoice-trash'])
                        <a href="{{ route('purchase-invoice.trash') }}" class="btn btn-sm btn-secondary">
                            <i class="ti ti-trash me-1"></i> Trash Bin
                        </a>
                    @endcanany
                </div>
            </div>

        </div>
        <div class="card-datatable table-responsive p-3">
            <table class="table table-binvoiceed" id="table">
                <thead class="binvoice-top" style="background-color: #AEDEFC; ">
                    <tr>
                        <th>#</th>
                        <th>Number</th>
                        <th>Invoice Number</th>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Age (day)</th>
                        <th>Grand Total</th>
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
                ajax: '{{ route('purchase-invoice.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'code',
                    },
                    {
                        data: 'no_faktur',
                    },
                    {
                        data: 'date',
                    },

                    {
                        data: 'supplier',
                    },
                    {
                        data: 'description',
                    },
                    {
                        data: 'status',
                    },
                    {
                        data: 'amount',
                    },
                    {
                        data: 'amount',
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

            $(document).on('click', '.btn-processing', function() {
                let id = $(this).data('id');
                let url = "{{ route('purchase-invoice.process', ':id') }}".replace(':id', id);

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This Purchase Invoice will be submitted for processing!",
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
                            url: `/purchase-invoice/${id}`,
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

            // filter
            $('#selectStatus').on('change', function() {
                table.ajax.reload();
            });
        });
    </script>
@endpush
