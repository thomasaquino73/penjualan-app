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
                    @canany(['kategori_customer-delete'])
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
                        <th>Name</th>
                        <th>Description</th>
                        <th>Created</th>
                        <th>Updated</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection
@include('partials.tabel.css')
@include('partials.tabel.js')
@push('scripts')
    <div class="modal fade" id="modals" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered1 modal-simple ">
            <div class="modal-content p-3 p-md-5">
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center mb-4">
                        <h3 class="mb-2" id="modal-title"></h3>
                    </div>
                    <form id="postForm" name="postForm" method="POST" action="{{ route('kategori-customer.store') }}">
                        @csrf
                        <input type="text" name="id" id="id" hidden>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label w-100" for="detail">Categories Name</label>
                                <div class="input-group input-group-merge">
                                    <input id="detail" name="detail" class="form-control credit-card-mask"
                                        type="text" placeholder="Enter Categories Name" />
                                </div>
                                <span class="error text-danger" id="detailError"></span>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label w-100" for="description">Description</label>
                                <div class="input-group input-group-merge">
                                    <input id="description" name="description" class="form-control credit-card-mask"
                                        type="text" placeholder="Enter Description" />
                                </div>
                                <span class="error text-danger" id="descriptionError"></span>
                            </div>
                        </div>
                        <div class="col-12 text-center">
                            <button type="submit" id="savedata" name="savedata" class="btn btn-primary me-sm-3 me-1">
                            </button>
                    </form>
                    <button type="reset" class="btn btn-label-secondary btn-reset" data-bs-dismiss="modal"
                        aria-label="Close">
                        Cancel
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div>
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

            // ✅ Siapkan tombol dulu (DI LUAR DataTable)
            let buttons = [];

            // ✅ Button CREATE (pakai permission)
            @canany(['kategori_customer-create'])
                buttons.push({
                    text: '<i class="ti ti-plus me-1"></i> New',
                    className: "btn btn-primary btn-sm me-2 AddNew",
                    action: function(e, dt, node, config) {
                        $('#modals').modal('show');
                        $('#savedata').html('<i class="fa fa-save me-1"></i>Save changes');
                        $('#modal-title').html('Add Categories');
                        $('#postForm').trigger('reset');
                        $('#id').val('');
                        resetValidation();
                    }
                });
            @endcanany


            // ✅ Button EDIT
            @canany(['kategori_customer-edit'])
                buttons.push({
                    text: '<i class="ti ti-edit me-1"></i> Edit',
                    className: "btn btn-warning btn-sm me-2",
                    extend: "selectedSingle",
                    action: function(e, dt, node, config) {

                        let row = dt.row({
                            selected: true
                        });

                        // ❗ Pastikan ada row terpilih
                        if (!row.any()) {
                            toastr.warning("Pilih data terlebih dahulu!");
                            return;
                        }

                        let rowData = row.data();
                        let id = rowData.id; // ✅ Ambil ID dari row terpilih


                        window.isEditingMode = true;
                        resetValidation();

                        // Tampilkan loading/spinner jika diperlukan, atau langsung jalankan AJAX
                        $.ajax({
                            type: "GET",
                            url: "{{ url('kategori-customer') }}/" + id + "/edit",
                            data: {
                                id: id
                            },
                            dataType: 'json',
                            success: function(data) {
                                // 1. Ubah teks UI Modal
                                $('#modal-title').html('Edit Categories');
                                $('#savedata').html(
                                    'Save changes'
                                ); // Menjaga konsistensi text tombol submit

                                // 2. Isi nilai input form berdasarkan data dari server
                                $('#id').val(data.id);
                                $('#detail').val(data.detail);
                                $('#description').val(data.description);

                                // 3. Bersihkan sisa-sisa error validasi lawas
                                resetValidation();

                                // 4. 🔥 TAMPILKAN MODAL KE LAYAR
                                $('#modals').modal('show');
                            },
                            error: function(xhr) {
                                let errorMessage = xhr.responseJSON && xhr.responseJSON
                                    .message ?
                                    xhr.responseJSON.message :
                                    "Gagal mengambil data dari server.";
                                toastr.error(errorMessage);
                            }
                        });
                    }
                });
            @endcanany


            // ✅ Button DELETE
            @canany(['kategori_customer-delete'])
                buttons.push({
                    text: '<i class="ti ti-trash me-1"></i> Delete',
                    className: "btn btn-danger btn-sm me-2",
                    extend: "selected",
                    action: function(e, dt, node, config) {

                        let row = dt.row({
                            selected: true
                        });

                        // ❗ Pastikan ada row yang terpilih sebelum mengeksekusi hapus
                        if (!row.any()) {
                            toastr.warning("Pilih data terlebih dahulu!");
                            return;
                        }

                        let rowIndex = row.index();
                        let data = row.data();

                        // ✅ PERBAIKAN: Ambil langsung dari objek data milik DataTables, bukan dari $(this)
                        let id = data.id;
                        let name = data.detail || data.name ||
                            'Data'; // Sesuaikan dengan key nama/kategori di database Anda
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
                                    url: `/kategori-customer/${id}`,
                                    type: "DELETE",
                                    cache: false,
                                    data: {
                                        _token: token
                                    },
                                    success: function(response) {
                                        // Refresh data tabel setelah sukses menghapus
                                        table.draw();

                                        toastr.success('Deleted Data Successfully',
                                            '', {
                                                timeOut: 1500,
                                                progressBar: true,
                                                closeButton: false,
                                                positionClass: 'toast-top-right',
                                            });
                                    },
                                    error: function(jqXHR) {
                                        let message =
                                            "Something went wrong"; // Fallback jika respon kosong

                                        if (jqXHR.responseJSON && jqXHR.responseJSON
                                            .message) {
                                            // Mengambil pesan spesifik dari Controller Anda
                                            message = jqXHR.responseJSON.message;
                                        }

                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Failed to delete',
                                            text: message, // Pesan otomatis berubah sesuai kondisi di Controller
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
                    }
                });
            @endcanany
            var table = new DataTable('#table', {
                processing: true,
                serverSide: true,
                responsive: true,
                select: true,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All']
                ],
                ajax: '{{ route('kategori-customer.index') }}',
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
                        data: 'detail',
                    },
                    {
                        data: 'description',
                    },
                    {
                        data: 'created_at',
                    },
                    {
                        data: 'updated_at',
                    },

                ],
                layout: {
                    topStart: {
                        buttons: buttons
                    }
                }
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
                        $('#savedata').html(' <i class="fa fa-save me-1"></i> Save changes');
                    },
                    success: function(response) {
                        $('#modals').modal('hide');
                        table.draw();
                        Swal.fire({
                            icon: 'success',
                            title: response.action === 'create' ?
                                'Created Data Successfully' :
                                'Updated Data Successfully',
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
                            text: 'Please check your data again.',
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
                            url: '/kategori-customer/delete-multiple',
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
                    }

                });

            });
        });
    </script>
@endpush
