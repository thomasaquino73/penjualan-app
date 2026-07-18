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

                    @canany(['penjualan_toko-create'])
                        <a href="{{ route('penjualan-toko.create') }}" class="btn btn-sm btn-primary">
                            <i class="ti ti-plus me-1"></i> Add Data
                        </a>
                    @endcanany

                </div>
            </div>

        </div>
        <div class="card-datatable table-responsive p-3">
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="input-group input-group-merge">
                        <span class="input-group-text" id=""><i class="fa fa-filter me-1"></i>Filter</span>
                        <select class="form-select " id="selectStatus" data-placeholder="Choose status...">
                            <option value="" selected hidden>Select Status</option>
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                </div>
            </div>

            <table class="table table-binvoiceed" id="table">
                <thead class="binvoice-top" style="background-color: #AEDEFC; ">
                    <tr>
                        <th>#</th>
                        <th>Number</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Notes</th>
                        <th>Status</th>
                        <th>Total</th>
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
            $('#selectStatus').on('change', function() {
                table.ajax.reload();
            });
            var table = new DataTable('#table', {
                processing: true,
                serverSide: true,
                responsive: true,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All']
                ],
                ajax: {
                    url: '{{ route('penjualan-toko.index') }}',
                    data: function(d) {
                        d.status = $('#selectStatus').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'store_sales_code',
                    },
                    {
                        data: 'store_sales_date',
                    },
                    {
                        data: 'customer_name',
                    },
                    {
                        data: 'notes',
                    },
                    {
                        data: 'status',
                    },
                    {
                        data: 'total',
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
