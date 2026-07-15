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

            <h5 class="card-title mb-3 mb-lg-0"><i class="ti ti-trash me-1"></i>{{ $title }}</h5>

            <div class="col-12 col-lg-5">
                <div
                    class="d-flex flex-column flex-md-row gap-2 
                        justify-content-start justify-content-lg-end">

                    <a href="{{ route('item-transfer.index') }}" class="btn btn-secondary btn-sm ">
                        <i class="ti ti-chevron-left me-1"></i> Back
                    </a>

                </div>
            </div>

        </div>

        <div class="card-datatable table-responsive" style="padding: 20px">
            <table class="table" id="table">
                <thead class="border-top" style="background-color: #FFEF9F; ">
                    <tr>
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
                ajax: '{{ route('item-transfer.trash') }}',
                columns: [{
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
            $('body').on('click', '.restore', function() {
                let id = $(this).data('id');
                let token = $("meta[name='csrf-token']").attr("content");
                Swal.fire({
                    title: 'Restore this Item?',
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
                            url: `/item-transfer/restore/${id}`,
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
                                let errMsg = 'Error restoring salesman';
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


        });
    </script>
@endpush
