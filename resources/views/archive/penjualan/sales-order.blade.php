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
            </div>
        </div>
        <div class="card-datatable table-responsive" style="padding: 20px">
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="input-group input-group-merge">
                        <span class="input-group-text">
                            <i class="fa fa-filter me-1"></i>
                        </span>

                        <select name="year" id="year" class="form-select select2" data-placeholder="Pilih Tahun"
                            style="width:100%;">
                            <option value=""></option>

                            @for ($year = 2026; $year <= date('Y'); $year++)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </select>

                        <button type="button" class="btn btn-primary" id="resetButton">
                            <i class="fa fa-refresh me-1"></i>
                        </button>
                    </div>
                </div>
            </div>
            <table class="table" id="table">
                <thead style="background-color: #AEDEFC; ">
                    <tr>
                        <th>#</th>
                        <th>PO Number</th>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Status</th>
                        <th>Shipping Date</th>
                        <th>Grand Total</th>
                        <th>Description</th>
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
                // Agar saat halaman pertama dibuka tidak langsung mengambil data
                deferLoading: 0,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All']
                ],
                ajax: {
                    url: "{{ route('archive.sales-order.datatable') }}",
                    data: function(d) {
                        d.year = $('#year').val();
                    }
                },
                columns: [{
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
                        data: 'supplier',
                    },
                    {
                        data: 'status',
                    },
                    {
                        data: 'tanggal_kirim',
                    },
                    {
                        data: 'amount',
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
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
            // Ketika tahun dipilih
            $('#year').on('change', function() {

                if ($(this).val() !== '') {
                    table.ajax.reload();
                } else {
                    table.clear().draw();
                }

            });

            // Tombol reset
            $('#resetButton').on('click', function() {

                $('#year').val(null).trigger('change');

                table.clear().draw();

            });
        });
    </script>
@endpush
