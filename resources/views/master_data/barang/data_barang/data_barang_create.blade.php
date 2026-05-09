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
        <div class="card-header d-flex justify-content-between">
            <h5 class="card-title mb-0">{{ $title }}</h5>
            <div class="col-12 col-lg-5 text-lg-end">
                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-lg-end">
                    <a href="{{ route('data-barang.index') }}" class="btn  btn-sm btn-secondary">
                        <i class="ti ti-chevron-left me-1"></i> Back
                    </a>

                </div>
            </div>
        </div>

        <div class="card table-responsive p-3">

        </div>
    </div>
@endsection
