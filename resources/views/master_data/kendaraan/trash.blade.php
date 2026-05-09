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

    <div class="row">
        <div class="card p-10">
            <div class="card-header  mt-2">
                <div class="row">
                    <div class="col-12 col-lg-6 d-flex align-items-center">
                        <i class="ti ti-trash me-2 "></i>
                        <h5 class="mb-0 ">{{ $title }}</h5>
                    </div>
                    <div class="col-12 col-lg-6 text-lg-end">

                        <a href="{{ route('daftar-kendaraan.index') }}" class="btn btn-secondary">
                            <i class="ti ti-chevron-left me-1"></i> Back
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-datatable text-nowrap ">
                <table class="table table-bordered" id="table">
                    <thead class="border-top" style="background-color: #FFEF9F; ">
                        <tr>
                            <th>#</th>
                            <th>Foto</th>
                            <th>Merk</th>
                            <th>Tipe</th>
                            <th>plat_nomor</th>
                            <th>warna</th>
                            <th>pemilik</th>
                            <th>Dibuat oleh</th>
                            <th>Diubah oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
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

        .bg-custom-red {
            background-color: rgba(168, 35, 35, 0.664) !important;
        }
    </style>
@endpush
@push('scripts')
    <script>
        document.querySelectorAll('input[name^="plat_"]').forEach((input, index, arr) => {
            input.addEventListener('input', function() {
                if (this.value.length == this.maxLength) {
                    if (index < arr.length - 1) arr[index + 1].focus();
                }
            });
        });
        $(document).ready(function() {
            var table = new DataTable('#table', {
                processing: true,
                serverSide: true,
                responsive: true,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All']
                ],
                ajax: '{{ route('daftar-kendaraan.trash') }}',
                columns: [{
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
                let row = $(this).closest('tr');
                Swal.fire({
                    title: 'Restore this data?',
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
                            url: "{{ route('daftar-kendaraan.restore', ':id') }}".replace(
                                ':id',
                                id),
                            type: 'PUT',
                            data: {
                                _token: token
                            },
                            success: function(response) {
                                table.draw();
                                if (response.redirect) {
                                    toastr.success(response.message, '', {
                                        timeOut: 2000,
                                        progressBar: true,
                                        positionClass: 'toast-top-right'
                                    });

                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Failed',
                                        text: response.message ||
                                            'Error restoring user'
                                    });
                                }
                            },
                            error: function(xhr) {
                                let errMsg = 'Error restoring user';
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
