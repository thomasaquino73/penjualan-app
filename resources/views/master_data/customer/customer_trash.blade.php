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
                    <a href="{{ route('customer.index') }}" class="btn btn-secondary">
                        <i class="ti ti-chevron-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <div class="card-datatable table-responsive p-3">
            <table class="table table-bordered" id="table">
                <thead class="border-top" style="background-color: rgba(168, 35, 35, 0.664); ">
                    <tr style=";font-color:white;">
                        <th>#</th>
                        <th>Customer ID</th>
                        <th>Customer Name</th>
                        <th>Customer Email</th>
                        <th>Customer Phone</th>
                        <th>Customer Address</th>
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
    <div class="modal fade" id="modals" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="mb-2" id="modal-title"></h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="postForm" name="postForm" method="POST" action="{{ route('customer.store') }}">
                        @csrf
                        <input type="text" name="id" id="id" hidden>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="id_pelanggan" class="form-label">Customer ID<small>*</small></label>
                                <input type="text" id="id_pelanggan" name="id_pelanggan" class="form-control"
                                    placeholder="Enter Customer ID">
                                <span class="error text-danger" id="id_pelangganError"></span>

                            </div>
                            <div class="col-6 mb-3">
                                <label for="nama" class="form-label">Customer Name<small>*</small></label>
                                <input type="text" id="nama" name="nama" class="form-control"
                                    placeholder="Enter Customer Name">
                                <span class="error text-danger" id="namaError"></span>

                            </div>
                            <div class="col-12 mb-3">
                                <label for="alamat" class="form-label">Address<small>*</small></label>
                                <input type="text" id="alamat" name="alamat" class="form-control"
                                    placeholder="Enter Customer Address">
                                <span class="error text-danger" id="alamatError"></span>
                            </div>
                            <div class="col-12 mb-3">
                                <label for="alamat_pajak" class="form-label">Tax Address</label>
                                <input type="text" id="alamat_pajak" name="alamat_pajak" class="form-control"
                                    placeholder="Enter Customer Tax Address">
                                <span class="error text-danger" id="alamat_pajakError"></span>
                            </div>

                            <div class="col-3 mb-3">
                                <label for="kodepos" class="form-label">Postal Code</label>
                                <input type="text" id="kodepos" name="kodepos" class="form-control"
                                    placeholder="Enter Postal Code">
                                <span class="error text-danger" id="kodeposError"></span>
                            </div>
                            <div class="col-3 mb-3">
                                <label for="negara" class="form-label">Country<small>*</small></label>
                                <input type="text" id="negara" name="negara" class="form-control"
                                    placeholder="Enter Country">
                                <span class="error text-danger" id="negaraError"></span>
                            </div>
                            <div class="col-3 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="text" id="email" name="email" class="form-control"
                                    placeholder="Enter Email">
                                <span class="error text-danger" id="emailError"></span>
                            </div>
                            <div class="col-3 mb-3">
                                <label for="website" class="form-label">Website</label>
                                <input type="text" id="website" name="website" class="form-control"
                                    placeholder="Enter Website">
                                <span class="error text-danger" id="websiteError"></span>
                            </div>
                            <div class="col-3 mb-3">
                                <label for="telepon" class="form-label">Phone Number<small>*</small></label>
                                <input type="text" id="telepon" name="telepon" class="form-control"
                                    placeholder="Enter Phone Number">
                                <span class="error text-danger" id="teleponError"></span>
                            </div>
                            <div class="col-3 mb-3">
                                <label for="personal_kontak" class="form-label">Contact Person</label>
                                <input type="text" id="personal_kontak" name="personal_kontak" class="form-control"
                                    placeholder="Enter Contact Person">
                                <span class="error text-danger" id="personal_kontakError"></span>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Status<small>*</small></label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">Select Status</option>
                                    <option value="1">Active</option>
                                    <option value="2">Not Active</option>
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
                    </button>
                </div>
                </form>

            </div>
        </div>
    </div>
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
                ajax: '{{ route('customer.trash') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'id_pelanggan',
                    },
                    {
                        data: 'nama',
                    },
                    {
                        data: 'email',
                    },
                    {
                        data: 'telepon',
                    },
                    {
                        data: 'alamat',
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
                    title: 'Restore this customer?',
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
                            url: `/customer/restore/${id}`,
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
                                let errMsg = 'Error restoring customer';
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
