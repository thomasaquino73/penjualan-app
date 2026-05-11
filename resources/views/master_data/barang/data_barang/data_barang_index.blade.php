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
                    @canany(['barang-create'])
                        <a href="{{ route('data-barang.create') }}" class="btn  btn-sm btn-primary">
                            <i class="ti ti-plus me-1"></i> Add Data
                        </a>
                    @endcanany
                    @canany(['barang-trash'])
                        <a href="{{ route('data-barang.trash') }}" class="btn btn-secondary">
                            <i class="ti ti-trash me-1"></i>
                        </a>
                    @endcanany
                </div>
            </div>
        </div>

        <div class="card-datatable table-responsive p-3">
            <table class="table table-bordered" id="table">
                <thead class="border-top" style="background-color: #AEDEFC; ">
                    <tr>
                        <th>#</th>
                        <th>Picture</th>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Product Type</th>
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
                ajax: '{{ route('data-barang.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'fotoProduk',
                    },
                    {
                        data: 'id_barang',
                    },
                    {
                        data: 'nama_barang',
                    },
                    {
                        data: 'price',
                    },

                    {
                        data: 'productType',
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
                            url: `/data-barang/${id}`,
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
        });
    </script>
@endpush
