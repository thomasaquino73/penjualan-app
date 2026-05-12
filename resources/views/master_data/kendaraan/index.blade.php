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

    {{-- <div class="row"> --}}
    <div class="card ">
        <div
            class="card-header d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center">

            <h5 class="card-title mb-2 mb-lg-0">{{ $title }}</h5>

            <div class="col-12 col-lg-5">
                <div
                    class="d-flex flex-column flex-md-row gap-2 
                    justify-content-start justify-content-lg-end">

                    @canany(['kendaraan-create'])
                        <button id="create" class="btn  btn-sm btn-primary">
                            <i class="ti ti-plus me-1"></i> Add Data
                        </button>
                    @endcanany
                    @canany(['kendaraan-trash'])
                        <a href="{{ route('daftar-kendaraan.trash') }}" class="btn  btn-sm btn-secondary">
                            <i class="ti ti-trash"></i> Trash Bin
                        </a>
                    @endcanany
                    @canany(['kendaraan-delete'])
                        <button id="deleteSelected" class="btn btn-sm btn-danger ">
                            <i class="ti ti-trash me-1"></i> Delete Selected
                        </button>
                    @endcanany


                </div>
            </div>

        </div>

        <div class="card-datatable table-responsive" style="padding: 20px">
            <table class="datatables-ajax table" id="table">
                <thead style="background-color: #AEDEFC; ">
                    <tr>
                        <th>
                            <div class="form-check form-check-primary mt-3">
                                <input class="form-check-input" type="checkbox" value="" id="checkAll">
                            </div>
                        </th>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Brand</th>
                        <th>Type</th>
                        <th>License Plate</th>
                        <th>Color</th>
                        <th>Owner</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    {{-- </div> --}}
@endsection
@push('style')
    <style>
        input[name^="plat_"] {
            text-transform: uppercase;
            font-weight: bold;
            text-align: center;
            letter-spacing: 2px;
            font-size: 1.1rem;
        }
    </style>
@endpush
@push('scripts')
    <div class="modal fade" id="modals" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="mb-2" id="modal-title"></h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="postForm" name="postForm" method="POST" action="{{ route('daftar-kendaraan.store') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="text" name="id" id="id" hidden>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="foto" class="form-label">Photo<small>*</small></label>
                                <input type="file" id="foto" name="foto" class="form-control"
                                    placeholder="Upload Photo">
                                <span class="error text-danger" id="fotoError"></span>
                            </div>

                            <div class="col-6 mb-3">
                                <label for="merk" class="form-label">Brand<small>*</small></label>
                                <input type="text" id="merk" name="merk" class="form-control"
                                    placeholder="Enter Brand">
                                <span class="error text-danger" id="merkError"></span>
                            </div>

                            <div class="col-6 mb-3">
                                <label for="tipe" class="form-label">Type<small>*</small></label>
                                <input type="text" id="tipe" name="tipe" class="form-control"
                                    placeholder="Enter Type">
                                <span class="error text-danger" id="tipeError"></span>
                            </div>

                            <div class="col-6 mb-3">
                                <label class="form-label">License Plate<small>*</small></label>
                                <div class="row g-2">
                                    <div class="col-md-2">
                                        <input type="text" name="plat_depan" id="plat_depan" class="form-control"
                                            maxlength="2" placeholder="B">
                                        <span class="error text-danger" id="plat_depanError"></span>
                                    </div>

                                    <div class="col-md-4">
                                        <input type="text" name="plat_tengah" id="plat_tengah" class="form-control"
                                            maxlength="4" placeholder="1234">
                                        <span class="error text-danger" id="plat_tengahError"></span>
                                    </div>

                                    <div class="col-md-2">
                                        <input type="text" name="plat_belakang" id="plat_belakang"
                                            class="form-control" maxlength="3" placeholder="XYZ">
                                        <span class="error text-danger" id="plat_belakangError"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-3 mb-3">
                                <label for="warna" class="form-label">Color<small>*</small></label>
                                <input type="text" id="warna" name="warna" class="form-control"
                                    placeholder="Enter Color">
                                <span class="error text-danger" id="warnaError"></span>
                            </div>

                            <div class="col-3 mb-3">
                                <label for="pemilik" class="form-label">Owner<small>*</small></label>
                                <input type="text" id="pemilik" name="pemilik" class="form-control"
                                    placeholder="Enter Owner Name">
                                <span class="error text-danger" id="pemilikError"></span>
                            </div>

                            <div class="col-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">Select Status</option>
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
    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Vehicle Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center">

                    <img id="detail_gambar" src="" class="img-fluid rounded shadow" style="max-height:400px;">

                    <div class="mt-3">
                        <span id="detail_alias" class="fw-bold"></span>
                    </div>

                </div>

            </div>
        </div>
    </div>
    <script>
        document.querySelectorAll('input[name^="plat_"]').forEach((input, index, arr) => {
            input.addEventListener('input', function() {
                if (this.value.length == this.maxLength) {
                    if (index < arr.length - 1) arr[index + 1].focus();
                }
            });
        });
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
                ajax: '{{ route('daftar-kendaraan.data') }}',
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
                        data: 'foto',
                    },
                    {
                        data: 'merk',
                    },
                    {
                        data: 'tipe',
                    },
                    {
                        data: 'plat_nomor',
                    },
                    {
                        data: 'warna',
                    },
                    {
                        data: 'pemilik',
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

            $('#create').click(function() {
                let kodeOtomatis = 'IUR' + Date.now();
                $('#modals').modal('show');
                $('#modal-title').html('Tambah Vehicle');
                $('#savedata').html('<i class="fa fa-save me-1"></i> Save');
                $('#postForm').trigger('reset');
                $('#id').val('');
                $('#kode').val(kodeOtomatis);
                resetValidation();
            });
            $('#postForm').on('submit', function(e) {
                e.preventDefault();
                var form = this;
                $.ajax({
                    url: $(form).attr('action'),
                    method: $(form).attr('method'),
                    data: new FormData(form),
                    processData: false,
                    contentType: false,
                    datatype: 'json',
                    beforeSend: function(e) {
                        $('#savedata').html(
                            '<i class="fa fa-spin fa-spinner me-1"></i> Sending...');
                    },
                    complete: function(e) {
                        $('#savedata').html(' <i class="fa fa-save me-1"></i>Save');
                    },
                    success: function(response) {
                        $('#modals').modal('hide');
                        table.draw();
                        Swal.fire({
                            icon: 'success',
                            title: response.title,
                            text: response.message,
                            showClass: {
                                popup: 'animate__animated animate__bounceIn'
                            },
                            customClass: {
                                confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                            buttonsStyling: false
                        });

                    },
                    error: function(xhr) {
                        resetValidation();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Periksa kembali data Anda.',
                            showClass: {
                                popup: 'animate__animated animate__bounceIn'
                            },
                            customClass: {
                                confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                            buttonsStyling: false
                        });
                        let errors = xhr.responseJSON.errors;

                        $.each(errors, function(key, value) {
                            // For other fields, display individual field errors if any
                            displayFieldError(key, value[0]);
                        });
                    }
                });


            });
            $('body').on('click', '.editPost', function(a) {
                $('#modals').modal('show');
                $('#savedata').html('<i class="fa fa-save me-1"></i>Save');
                resetValidation();

                var id = $(this).data('id');

                $.ajax({
                    type: "GET",
                    url: "/daftar-kendaraan/" + id + "/edit",
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    success: function(data) {
                        console.log(data);
                        $('#modal-title').html('Edit Vehicle');
                        $('#id').val(data.id);
                        $('#merk').val(data.merk);
                        $('#tipe').val(data.tipe);
                        $('#warna').val(data.warna);
                        $('#pemilik').val(data.pemilik);
                        $('#status').val(data.status).trigger('change');
                        $('#deskripsi').val(data.deskripsi);

                        // Set plat nomor terpisah
                        $('#plat_depan').val(data.plat_depan);
                        $('#plat_tengah').val(data.plat_tengah);
                        $('#plat_belakang').val(data.plat_belakang);

                        resetValidation();
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
                            url: `/daftar-kendaraan/${id}`,
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

        $('body').on('click', '.detail', function() {

            let gambar = $(this).data('gambar');
            let alias = $(this).data('alias');

            $('#detail_gambar').attr('src', gambar);
            $('#detail_alias').text(alias);

            $('#modalDetail').modal('show');

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
                        url: '/daftar-kendaraan/delete-multiple',
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
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Failed to delete data.',
                                timer: 5000,
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
    </script>
@endpush
