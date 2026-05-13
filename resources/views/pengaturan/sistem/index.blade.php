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
            @include('partials.pengaturan.navbar_pengaturan')

            <div class="card mb-4">

                <h5 class="card-header">{{ $title }}</h5>

                <div class="card-body">

                    <div class="divider divider-dashed">
                        <div class="divider-text">Application System Information</div>
                    </div>
                    <div class="row">

                        <div class="col-md-6 mb-3 ">
                            <label>Application Name<small>*</small></label>
                            <input type="text" name="nama_aplikasi" id="nama_aplikasi"
                                class="form-control text-uppercase" value="{{ $dataSistem->nama_aplikasi }}" disabled>
                            <span class="text-danger error" id="nama_aplikasiError"></span>
                        </div>
                        <div class="col-md-6 mb-3 ">
                            <label>System Name<small>*</small></label>
                            <input type="text" name="nama_sistem" id="nama_sistem" class="form-control"
                                value="{{ $dataSistem->nama_sistem }}" disabled>
                            <span class="text-danger error" id="nama_sistemError"></span>
                        </div>

                    </div>
                    <div class="mt-3">
                        <a href="{{ route('pengaturan.edit', $dataSistem->id) }}" class="btn btn-primary" id="savedata">
                            <i class="fa fa-save me-1"></i> Change Data
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
