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

                <h5 class="card-header">{{ $title }}</h5>

                <div class="card-body">

                    <div class="divider divider-dashed">
                        <div class="divider-text">Company Information</div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label>Logo</label>
                            <figure class=" mr-2"><img id="preview"
                                    src="{{ $dataSistem->logo ? asset($dataSistem->logo) : asset('image/no-images.jpg') }}"
                                    width=10%></figure>
                        </div>
                        <div class="col-md-6 mb-3 ">
                            <label>Company Name<small>*</small></label>
                            <input type="text" name="nama_perusahaan" id="nama_perusahaan" class="form-control"
                                value="{{ $dataSistem->nama_perusahaan }}" disabled>
                            <span class="text-danger error" id="nama_perusahaanError"></span>
                        </div>
                        <div class="col-md-6 mb-3 ">
                            <label>Default Currency<small>*</small></label>
                            <input type="text" name="mata_uang" id="mata_uang" class="form-control"
                                value="{{ $dataSistem->currency->name ?? '' }}" disabled>
                            <span class="text-danger error" id="mata_uangError"></span>
                        </div>
                        <div class="col-md-12 mb-3 ">
                            <label>Address<small>*</small></label>
                            <input type="text" name="alamat" id="alamat" class="form-control"
                                value="{{ $dataSistem->alamat }}" disabled>
                            <span class="text-danger error" id="alamatError"></span>
                        </div>
                        <div class="col-md-6 mb-3 ">
                            <label>Postal Code<small>*</small></label>
                            <input type="text" name="kodepos" id="kodepos" class="form-control"
                                value="{{ $dataSistem->kodepos }}" disabled>
                            <span class="text-danger error" id="kodeposError"></span>
                        </div>
                        <div class="col-md-6 mb-3 ">
                            <label>Country<small>*</small></label>
                            <input type="text" name="negara" id="negara" class="form-control"
                                value="{{ $dataSistem->negara }}" disabled>
                            <span class="text-danger error" id="negaraError"></span>
                        </div>
                        <div class="col-md-3 mb-3 ">
                            <label>Phone Number<small>*</small></label>
                            <input type="text" name="nomor_telepon" id="nomor_telepon" class="form-control"
                                value="{{ $dataSistem->nomor_telepon }}" disabled>
                            <span class="text-danger error" id="nomor_teleponError"></span>
                        </div>
                        <div class="col-md-3 mb-3 ">
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

                    </div>
                    @if (auth()->user()->can('company-edit'))
                        <div class="mt-3">
                            <a href="{{ route('company.edit', $dataSistem->id) }}" class="btn btn-primary" id="savedata">
                                <i class="fa fa-save me-1"></i> Change Data
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
@endpush
