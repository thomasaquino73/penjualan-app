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

    <div class="row">
        <div class="col-md-12">
            @include('partials.pengaturan.navbar_general')
            <div class="col-xl-12 mb-4">
                <div class="nav-align-top nav-tabs-shadow mb-4">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                                data-bs-target="#navs-top-cash" aria-controls="navs-top-cash" aria-selected="false"
                                tabindex="-1">
                                Cash & Bank
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                data-bs-target="#navs-top-currency" aria-controls="navs-top-currency" aria-selected="false"
                                tabindex="-1">
                                Currency
                            </button>
                        </li>

                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade active show" id="navs-top-cash" role="tabpanel">
                            @include('partials.general.cash_bank_table')

                        </div>
                        <div class="tab-pane fade" id="navs-top-currency" role="tabpanel">
                            @include('partials.general.currency_table')
                        </div>
                    </div>
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
    <script>
        < script >
    </script>
@endpush
