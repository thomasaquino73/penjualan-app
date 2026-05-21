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
        <form id="postForm" name="postForm" method="POST" action="{{ route('customer.store') }}">
            @csrf
            <div class="card-body table-responsive p-3">
                <div class="col-xl-12">
                    <div class="nav-align-top mb-4">
                        <ul class="nav nav-pills mb-3" role="tablist">
                            <li class="nav-item">
                                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-pills-top-general" aria-controls="navs-pills-top-general"
                                    aria-selected="true">
                                    General Information
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-pills-top-contact" aria-controls="navs-pills-top-contact"
                                    aria-selected="false">
                                    Contact Information
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-pills-top-term" aria-controls="navs-pills-top-term"
                                    aria-selected="false">
                                    Stock Information
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-pills-top-tax" aria-controls="navs-pills-top-tax"
                                    aria-selected="false">
                                    Other Information
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="navs-pills-top-general" role="tabpanel">
                                <div class="row">

                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-top-contact" role="tabpanel">
                                <div class="row">

                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-top-term" role="tabpanel">
                                <div class="row">

                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-top-tax" role="tabpanel">
                                <div class="row">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('customer.index') }}" type="button" class="btn btn-label-secondary waves-effect">
                        <i class="ti ti-chevron-left me-1"></i>
                        Back
                    </a>
                    <button type="submit" id="savedata" name="savedata" class="btn btn-primary me-sm-3 me-1">
                        <i class="fa fa-save me-1"></i>Save
                    </button>
                </div>
        </form>
    </div>

    </div>
@endsection
