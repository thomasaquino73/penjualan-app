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

                    <a href="{{ route('purchase-down-payment.index') }}" class="btn btn-secondary btn-sm ">
                        <i class="ti ti-chevron-left me-1"></i> Back
                    </a>

                </div>
            </div>

        </div>
        <div class="card-datatable table-responsive p-3">
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="input-group input-group-merge">
                        <span class="input-group-text" id=""><i class="fa fa-filter me-1"></i>Filter</span>
                        <select class="form-select " id="selectStatus" data-placeholder="Choose status...">
                            <option value="" selected hidden>Select Status</option>
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="processing">Processing</option>
                            <option value="paid">Paid</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                </div>
            </div>

            <table class="table table-binvoiceed" id="table">
                <thead class="border-top" style="background-color: #FFEF9F; ">
                    <tr>

                        <th>#</th>
                        <th>Number</th>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Age</th>
                        <th>Total</th>
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
                ajax: {
                    url: '{{ route('purchase-down-payment.trash') }}',
                    data: function(d) {
                        d.status = $('#selectStatus').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'purchase_downpayment_code',
                    },
                    {
                        data: 'purchase_downpayment_date',
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
                        data: 'age',
                    },
                    {
                        data: 'total',
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

            $('body').on('click', '.restore', function() {
                let id = $(this).data('id');
                let token = $("meta[name='csrf-token']").attr("content");
                Swal.fire({
                    title: 'Restore this purchase down payment?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, restore!',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        confirmButton: 'btn btn-success me-3 waves-effect waves-light',
                        cancelButton: 'btn btn-secondary waves-effect waves-light'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/purchase-down-payment/restore/${id}`,
                            type: 'PUT',
                            data: {
                                _token: token
                            },
                            success: function(response) {
                                table.draw();
                                toastr.success(response.message, '', {
                                    timeOut: 2000,
                                    progressBar: true,
                                    positionClass: 'toast-top-right'
                                });

                            },
                            error: function(xhr) {
                                let errMsg = 'Error restoring purchaseman';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errMsg = xhr.responseJSON.message;
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Failed',
                                    text: errMsg,
                                    timer: 5000,
                                    customClass: {
                                        confirmButton: 'btn btn-info waves-effect waves-light'
                                    }
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
