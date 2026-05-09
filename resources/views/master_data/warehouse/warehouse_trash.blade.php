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
        <div class="card-header d-flex justify-content-between">
            <h5 class="card-title mb-0">{{ $title }}</h5>
            <div class="col-12 col-lg-5 text-lg-end">
                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-lg-end">
                    <a href="{{ route('warehouse.index') }}" class="btn btn-secondary">
                        <i class="ti ti-chevron-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <div class="card-datatable table-responsive p-3">
            <table class="table table-bordered" id="table">
                <thead class="border-top" style="background-color: #FFEF9F; ">
                    <tr style=";font-color:white;">
                        <th>#</th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Description</th>
                        <th>Responsible Person</th>
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
            var table = new DataTable('#table', {
                processing: true,
                serverSide: true,
                responsive: true,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All']
                ],
                ajax: '{{ route('warehouse.trash') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'id_gudang',
                    },
                    {
                        data: 'nama_gudang',
                    },
                    {
                        data: 'alamat',
                    },
                    {
                        data: 'keterangan',
                    },
                    {
                        data: 'penanggung_jawab',
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
                    title: 'Restore this warehouse?',
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
                            url: `/warehouse/restore/${id}`,
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
                                let errMsg = 'Error restoring warehouse';
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
