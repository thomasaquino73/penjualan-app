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

                    @canany(['barang-create'])
                        <a href="{{ route('data-barang.create') }}" class="btn btn-sm btn-primary ">
                            <i class="ti ti-plus me-1"></i> Add Data
                        </a>
                    @endcanany

                    @canany(['barang-trash'])
                        <a href="{{ route('data-barang.trash') }}" class="btn btn-sm btn-secondary ">
                            <i class="ti ti-trash me-1"></i>Trash Bin
                        </a>
                    @endcanany

                    @canany(['barang-delete'])
                        <button id="deleteSelected" class="btn btn-sm btn-danger ">
                            <i class="ti ti-trash me-1"></i> Delete Selected
                        </button>
                    @endcanany
                    <button type="button" class="btn btn-sm btn-info" id="btnPrintAll">
                        <i class="ti ti-printer me-1"></i> Print All
                    </button>
                    {{-- <a href="{{ route('data-barang.print_all') }}" target="_blank" class="btn btn-sm btn-info ">
                        <i class="ti ti-printer me-1"></i> Print All
                    </a> --}}

                </div>
            </div>

        </div>

        <div class="card-datatable table-responsive p-3">
            <div class="col-12 col-lg-6">
                <div class="row g-2 align-items-center">

                    <!-- Status -->
                    <div class="col-md">
                        <select class="form-select select2" id="selectFilter" data-placeholder="Choose category...">
                            <option></option>
                            @foreach ($kategori as $item)
                                <option value="{{ $item->id }}">{{ $item->detail }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Reset button -->
                    <div class="col-md-auto">
                        <button class="btn btn-outline-secondary w-100" id="resetFilter">
                            <i class="ti ti-refresh me-1"></i> Reset
                        </button>
                    </div>

                </div>
            </div>
            <table class="display responsive nowrap" id="table">
                <thead class="border-top" style="background-color: #AEDEFC; ">
                    <tr>
                        <th>
                            <div class="form-check form-check-primary mt-3">
                                <input class="form-check-input" type="checkbox" value="" id="checkAll">
                            </div>
                        </th>
                        <th>#</th>
                        {{-- <th>Picture</th>
                        <th>Barcode</th> --}}
                        <th>Product Code</th>
                        <th>Category</th>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Updated</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <div class="modal fade" id="modalstok" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <form id="formPrintStock" method="GET" action="{{ route('data-barang.print_all') }}">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            Print Semua Barang
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal">
                        </button>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">
                            <label>Tanggal Awal</label>

                            <input type="text" class="form-control" name="start_date" id="start_date"
                                value="{{ date('Y-m-01') }}" required>
                        </div>

                        <div class="mb-3">
                            <label>Tanggal Akhir</label>

                            <input type="text" class="form-control" name="end_date" id="end_date"
                                value="{{ date('Y-m-d') }}" required>
                        </div>

                    </div>

                    <div class="modal-footer">

                        <button class="btn btn-primary">
                            Print
                        </button>

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>

                    </div>

                </form>

            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(function() {

            const startDate = flatpickr("#start_date", {
                enableTime: false,
                dateFormat: "Y-m-d", // dikirim ke server
                altInput: true,
                altFormat: "d-m-Y", // ditampilkan ke user
                defaultDate: "{{ now()->format('Y-m-d') }}",
                onChange: function(selectedDates) {
                    endDate.set('minDate', selectedDates[0]);
                }
            });

            const endDate = flatpickr("#end_date", {
                enableTime: false,
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d-m-Y",
                defaultDate: "{{ now()->format('Y-m-d') }}",
                onChange: function(selectedDates) {
                    startDate.set('maxDate', selectedDates[0]);
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
            $('#btnPrintAll').click(function() {
                $('#modalstok').modal('show');
            });
            var groupColumn = 3;
            var table = new DataTable('#table', {
                processing: true,
                serverSide: true,
                responsive: true,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All']
                ],
                columnDefs: [{
                    visible: false,
                    targets: groupColumn
                }],
                order: [
                    [groupColumn, 'asc']
                ],
                drawCallback: function(settings) {
                    var api = this.api();
                    var rows = api.rows({
                        page: 'current'
                    }).nodes();
                    var last = null;

                    api.column(groupColumn, {
                            page: 'current'
                        })
                        .data()
                        .each(function(group, i) {
                            if (last !== group) {
                                $(rows)
                                    .eq(i)
                                    .before(
                                        '<tr class="group"><td colspan="11">' +
                                        group +
                                        '</td></tr>'
                                    );

                                last = group;
                            }
                        });
                },
                // ajax: '{{ route('data-barang.index') }}',
                ajax: {
                    url: '{{ route('data-barang.index') }}',
                    data: function(d) {
                        d.kategori_id = $('#selectFilter').val();
                        d.brand_id = $('#selectBrand').val();
                    }
                },
                columns: [{
                        data: 'cekbok',
                        name: 'cekbok',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },

                    {
                        data: 'id_barang',
                    },
                    {
                        data: 'kategori',
                    },
                    {
                        data: 'nama_barang',
                    },
                    {
                        data: 'harga',
                    },
                    {
                        data: 'stok',
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
            $('#selectFilter').on('change', function() {
                table.ajax.reload();
            });
            $('#selectBrand').on('change', function() {
                table.ajax.reload();
            });
            $('#resetFilter').on('click', function() {
                $('#selectFilter').val(null).trigger('change');
                $('#selectBrand').val(null).trigger('change');
                table.ajax.reload(); // reload datatable
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
                            url: '/data-barang/delete-multiple',
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

            $("#formPrintStock").on("submit", function(e) {

                if ($("#barang_id_stock").val() == "") {
                    e.preventDefault();

                    Swal.fire({
                        icon: "warning",
                        title: "Peringatan",
                        text: "Silahkan pilih barang terlebih dahulu.",
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });

                    return false;
                }

                $(this).attr("target", "_blank");

                $("#modalstok").modal("hide");
            });
        });
    </script>
@endpush
