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

                    @canany(['penawaran_pembelian-create'])
                        <a href="{{ route('penawaran-pembelian.create') }}" class="btn btn-sm btn-primary">
                            <i class="ti ti-plus me-1"></i> Add Data
                        </a>
                    @endcanany
                    @canany(['penawaran_pembelian-trash'])
                        <a href="{{ route('penawaran-pembelian.trash') }}" class="btn btn-sm btn-secondary">
                            <i class="ti ti-trash me-1"></i> Trash Bin
                        </a>
                    @endcanany

                    @canany(['penawaran_pembelian-delete'])
                        <button id="deleteSelected" class="btn btn-danger btn-sm">
                            <i class="ti ti-trash me-1"></i> Delete Selected
                        </button>
                    @endcanany

                </div>
            </div>

        </div>
        <div class="card-datatable table-responsive p-3">
            <table class="table table-bordered" id="table">
                <thead class="border-top" style="background-color: #AEDEFC; ">
                    <tr>
                        <th>
                            <div class="form-check form-check-primary mt-3">
                                <input class="form-check-input" type="checkbox" value="" id="checkAll">
                            </div>
                        </th>
                        <th>#</th>
                        <th>ID Number</th>
                        <th>Date</th>
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
                ajax: '{{ route('penawaran-pembelian.index') }}',
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
                        data: 'code',
                    },
                    {
                        data: 'date',
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
        });
    </script>
@endpush
