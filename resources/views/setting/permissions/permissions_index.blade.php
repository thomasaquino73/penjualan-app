@extends('layouts.app')
@section('konten')
    <div class="row g-4">
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
        <!-- Users List Table -->
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">{{ $title }}</h5>
                    <div class="card-header-elements ms-auto">
                        {{-- @if (auth()->user()->can('role-create')) --}}
                        {{-- <a href="{{ route('permissions.create') }}" type="button"
                                class="btn btn-md btn-primary waves-effect waves-light">
                                <span class="tf-icon ti ti-plus ti-md me-1"></span>{{ __('mainpage.added') }}
                            </a> --}}
                        {{-- @endif --}}

                    </div>
                </div>
                <div class="card-datatable table-responsive" style="padding: 20px">
                    <table class="table table-bordered" id="table">
                        <thead class="border-top" style="background-color: #AEDEFC; ">
                            <tr>
                                <th>#</th>
                                <th>Roles Name</th>
                                <th>Created</th>
                                <th>Updated</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
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
                ajax: '{{ route('permissions.index') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },

                    {
                        data: 'name',
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
                            url: `permissions/${id}`,
                            type: "DELETE",
                            cache: false,
                            data: {
                                _token: token
                            },
                            success: function(response) {
                                table.draw();
                                toastr.success('Data Berhasil dihapus', '', {
                                    timeOut: 1500,
                                    progressBar: true,
                                    closeButton: false,
                                    positionClass: 'toast-top-right',
                                });
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                let message = 'Terjadi kesalahan.';

                                if (jqXHR.status === 403 && jqXHR.responseJSON?.error) {
                                    message = jqXHR.responseJSON.error;
                                } else if (jqXHR.status === 422 && jqXHR.responseJSON
                                    ?.errors) {
                                    message = Object.values(jqXHR.responseJSON.errors)
                                        .flat().join(', ');
                                } else if (jqXHR.responseJSON?.message) {
                                    message = jqXHR.responseJSON.message;
                                }

                                Swal.fire({
                                    icon: 'error',
                                    title: '',
                                    text: message,
                                    showClass: {
                                        popup: 'animate__animated animate__bounceIn'
                                    },
                                    customClass: {
                                        confirmButton: 'btn btn-primary waves-effect waves-light'
                                    },
                                    buttonsStyling: false
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
