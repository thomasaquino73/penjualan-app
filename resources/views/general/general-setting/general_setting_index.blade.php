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

            <div class="card mb-4">

                <div class="card-header bg-white">
                    <div class="row">
                        <div class="col-12 col-lg-6">
                            <h5 class="mb-0">{{ $title }}</h5>
                        </div>
                        <div class="col-12 col-lg-6 text-lg-end">

                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="card-datatable table-responsive" style="padding: 20px">
                        </div>
                    </div>
                    <div class="mt-3">

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script></script>
@endpush
