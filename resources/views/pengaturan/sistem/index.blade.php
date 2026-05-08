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
                        <div class="col-md-12 mb-3">
                            <label>Logo</label>
                            <figure class=" mr-2"><img id="preview"
                                    src="{{ $dataSistem->logo ? asset($dataSistem->logo) : asset('image/no-images.jpg') }}"
                                    width=10%></figure>
                        </div>
                        <div class="col-md-6 mb-3 ">
                            <label>Application Name<small>*</small></label>
                            <input type="text" name="nama_aplikasi" id="nama_aplikasi" class="form-control"
                                value="{{ $dataSistem->nama_aplikasi }}" disabled>
                            <span class="text-danger error" id="nama_aplikasiError"></span>
                        </div>
                        <div class="col-md-6 mb-3 ">
                            <label>Institution Name<small>*</small></label>
                            <input type="text" name="nama_instansi" id="nama_instansi" class="form-control"
                                value="{{ $dataSistem->nama_instansi }}" disabled>
                            <span class="text-danger error" id="nama_instansiError"></span>
                        </div>
                        <div class="col-md-6 mb-3 ">
                            <label>Address<small>*</small></label>
                            <input type="text" name="alamat" id="alamat" class="form-control"
                                value="{{ $dataSistem->alamat }}" disabled>
                            <span class="text-danger error" id="alamatError"></span>
                        </div>
                        <div class="col-md-6 mb-3 ">
                            <label>Phone Number<small>*</small></label>
                            <input type="text" name="telepon" id="telepon" class="form-control"
                                value="{{ $dataSistem->telepon }}" disabled>
                            <span class="text-danger error" id="teleponError"></span>
                        </div>
                        <div class="col-md-6 mb-3 ">
                            <label>Email<small>*</small></label>
                            <input type="text" name="email" id="email" class="form-control"
                                value="{{ $dataSistem->email }}" disabled>
                            <span class="text-danger error" id="emailError"></span>
                        </div>
                        <div class="col-md-6 mb-3 ">
                            <label>Website<small>*</small></label>
                            <input type="text" name="website" id="website" class="form-control"
                                value="{{ $dataSistem->website }}" disabled>
                            <span class="text-danger error" id="websiteError"></span>
                        </div>
                        <div class="col-md-6 mb-3 ">
                            <label>Description<small>*</small></label>
                            <input type="text" name="deskripsi" id="deskripsi" class="form-control"
                                value="{{ $dataSistem->deskripsi }}" disabled>
                            <span class="text-danger error" id="deskripsiError"></span>
                        </div>
                        <div class="col-md-6 mb-3 ">
                            <label>Foundation Year<small>*</small></label>
                            <input type="text" name="tahun_berdiri" id="tahun_berdiri" class="form-control"
                                value="{{ $dataSistem->tahun_berdiri }}" disabled>
                            <span class="text-danger error" id="tahun_berdiriError"></span>
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
