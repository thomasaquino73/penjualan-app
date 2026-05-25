@extends('layouts.app')
@section('konten')
    <h4>
        <span class="text-muted fw-light">
            @foreach ($breadcrumb as $item)
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

    {{-- @include('partials.pengaturan.navbar_general') --}}
    <div class="col-xl-12">
        <div class="nav-align-top mb-4">
            <ul class="nav nav-pills mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                        data-bs-target="#navs-pills-top-bank" aria-controls="navs-pills-top-bank" aria-selected="true">
                        Cash & Bank
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                        data-bs-target="#navs-pills-top-currency" aria-controls="navs-pills-top-currency"
                        aria-selected="false" tabindex="-1">
                        Currency
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                        data-bs-target="#navs-pills-top-exchange" aria-controls="navs-pills-top-exchange"
                        aria-selected="false" tabindex="-1">
                        Exchange Rates
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                        data-bs-target="#navs-pills-top-delivery" aria-controls="navs-pills-top-delivery"
                        aria-selected="false" tabindex="-1">
                        Company Delivery Address
                    </button>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="navs-pills-top-bank" role="tabpanel">
                    @include('partials.general.cash_bank_table')
                </div>
                <div class="tab-pane fade" id="navs-pills-top-currency" role="tabpanel">
                    @include('partials.general.currency_table')
                </div>
                <div class="tab-pane fade" id="navs-pills-top-exchange" role="tabpanel">
                    @include('partials.general.exchange_rate_table')
                </div>
                <div class="tab-pane fade" id="navs-pills-top-delivery" role="tabpanel">
                    @include('partials.general.company_delivery_table')
                </div>
            </div>
        </div>
    </div>
@endsection
@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/2.0.3/css/select.bootstrap5.css">
@endpush
@push('scripts')
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.js"></script>

    <script src="https://cdn.datatables.net/select/3.1.3/js/dataTables.select.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.3/js/select.bootstrap5.js"></script>
@endpush
