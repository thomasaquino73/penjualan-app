@extends('layouts.app')
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

    {{-- <div class="row"> --}}
    <div class="card ">
        <div class="card-header">
            <div class="row w-100">

                <!-- Title -->
                <div class="col-12 col-lg-7">
                    <h5 class="card-title mb-3 mb-lg-0"> <i class="ti ti-trash me-2 "></i>{{ $title }}</h5>
                </div>

                <!-- Buttons -->
                <div class="col-12 col-lg-5">
                    <div
                        class="d-flex flex-column flex-md-row gap-2
                        justify-content-start justify-content-lg-end">

                        <a href="{{ route('company-delivery.index') }}" class="btn btn-secondary btn-sm ">
                            <i class="ti ti-chevron-left me-1"></i> Back
                        </a>

                        @canany(['shipping-restore'])
                            <button id="restoreSelected" class="btn btn-success btn-sm ">
                                <i class="ti ti-refresh me-1"></i> Restore Selected
                            </button>
                        @endcanany

                    </div>
                </div>

            </div>
        </div>

        <div class="card-datatable table-responsive" style="padding: 20px">
            <table class="datatables-ajax table" id="table">
                <thead class="border-top" style="background-color: #FFEF9F; ">
                    <tr>
                        <th>
                            <div class="form-check form-check-primary mt-3">
                                <input class="form-check-input" type="checkbox" value="" id="checkAll">
                            </div>
                        </th>
                        <th>#</th>
                        <th>Name</th>
                        <th>PIC</th>
                        <th>Phone Number</th>
                        <th>Address</th>
                        <th>Created</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <div class="modal fade" id="modals" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="mb-2" id="modal-title"></h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="postForm" name="postForm" method="POST" action="{{ route('company-delivery.store') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="text" name="id" id="id" hidden>
                        <div class="row">
                            <div class="col-md-12 col-sm-12 mb-3">
                                <label for="nama" class="form-label">Name<small>*</small></label>
                                <input type="text" id="nama" name="nama" class="form-control"
                                    placeholder="Enter Name">
                                <span class="error text-danger" id="namaError"></span>
                            </div>

                            <div class="col-md-12 col-sm-12 mb-3">
                                <label for="pic" class="form-label">PIC<small>*</small></label>
                                <input type="text" id="pic" name="pic" class="form-control"
                                    placeholder="Enter PIC">
                                <span class="error text-danger" id="picError"></span>
                            </div>

                            <div class="col-md-12 col-sm-12 mb-3">
                                <label for="phone" class="form-label">Phone Number<small>*</small></label>
                                <input type="text" id="phone" name="phone" class="form-control"
                                    placeholder="Enter Phone Number">
                                <span class="error text-danger" id="phoneError"></span>
                            </div>
                            <div class="col-md-12 col-sm-12 mb-3">
                                <label for="address" class="form-label">Address<small>*</small></label>
                                <textarea type="text" id="address" name="address" class="form-control" placeholder="Enter Address"></textarea>
                                <span class="error text-danger" id="addressError"></span>
                            </div>

                            <div class="col-md-6 col-sm-12 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="" selected hidden>Select Status</option>
                                    <option value="1">Active</option>
                                    <option value="2">Inactive</option>
                                </select>
                                <span class="error text-danger" id="statusError"></span>
                            </div>
                        </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary waves-effect" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="submit" id="savedata" name="savedata" class="btn btn-primary me-sm-3 me-1">
                        Save
                    </button>
                </div>
                </form>

            </div>
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
                ajax: '{{ route('company-delivery.trash') }}',
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
                        data: 'nama',
                    },
                    {
                        data: 'pic',
                    },
                    {
                        data: 'phone',
                    },
                    {
                        data: 'address',
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
                    title: 'Restore this shipping?',
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
                            url: `/company-delivery/restore/${id}`,
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
                                let errMsg = 'Error restoring company-delivery';
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
            $('#restoreSelected').on('click', function() {

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
                    text: "Data will be restored!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, restore it!',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
                        cancelButton: 'btn btn-label-secondary waves-effect waves-light'
                    },
                    buttonsStyling: false
                }).then((result) => {

                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/company-delivery/restore-multiple',
                            type: 'POST',
                            data: {
                                ids: ids,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                toastr.success('Restored Data Successfully', '', {
                                    timeOut: 1500,
                                    progressBar: true,
                                    closeButton: false,
                                    positionClass: 'toast-top-right',
                                });
                                $('#table').DataTable().ajax.reload();
                            },
                            error: function() {
                                Swal.fire('Error!', 'Failed to restore data.', 'error');
                            }
                        });
                    }

                });

            });
        });
    </script>
@endpush
