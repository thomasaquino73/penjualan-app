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
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="rejected">Rejected</option>
                            <option value="sent">Sent</option>
                            <option value="partially_received">Partially Received</option>
                            <option value="completed">Completed</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                </div>
            </div>

            <table class="table table-binvoiceed" id="table">
                <thead class="binvoice-top" style="background-color: #AEDEFC; ">
                    <tr>
                        <th>
                            <div class="form-check form-check-primary mt-3">
                                <input class="form-check-input" type="checkbox" value="" id="checkAll">
                            </div>
                        </th>
                        <th>#</th>
                        <th>Number</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Description</th>
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
