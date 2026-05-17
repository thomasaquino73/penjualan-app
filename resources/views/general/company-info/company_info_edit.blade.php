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
                    <form action="{{ route('company.update', $dataSistem->id) }}" id="postForm" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('put')
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
                                    value="{{ $dataSistem->nama_perusahaan }}">
                                <span class="text-danger error" id="nama_perusahaanError"></span>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Logo</label>
                                <input type="file" name="avatar" id="avatar" class="form-control">
                                <span class="text-danger error" id="avatarError"></span>
                            </div>
                            <div class="col-md-3 mb-3 ">
                                <label>Default Currency<small>*</small></label>
                                <select name="mata_uang_id" id="mata_uang_id" class="form-select select2"
                                    data-placeholder="Select Currency">
                                    <option></option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}"
                                            {{ $dataSistem->mata_uang_id == $currency->id ? 'selected' : '' }}>
                                            {{ $currency->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger error" id="mata_uang_idError"></span>
                            </div>
                            <div class="col-md-12 mb-3 ">
                                <label>Address<small>*</small></label>
                                <input type="text" name="alamat" id="alamat" class="form-control"
                                    value="{{ $dataSistem->alamat }}">
                                <span class="text-danger error" id="alamatError"></span>
                            </div>
                            <div class="col-md-6 mb-3 ">
                                <label>Postal Code<small>*</small></label>
                                <input type="text" name="kodepos" id="kodepos" class="form-control"
                                    value="{{ $dataSistem->kodepos }}">
                                <span class="text-danger error" id="kodeposError"></span>
                            </div>
                            <div class="col-md-6 mb-3 ">
                                <label>Country<small>*</small></label>
                                <input type="text" name="negara" id="negara" class="form-control"
                                    value="{{ $dataSistem->negara }}">
                                <span class="text-danger error" id="negaraError"></span>
                            </div>
                            <div class="col-md-3 mb-3 ">
                                <label>Phone Number<small>*</small></label>
                                <input type="text" name="nomor_telepon" id="nomor_telepon" class="form-control"
                                    value="{{ $dataSistem->nomor_telepon }}">
                                <span class="text-danger error" id="nomor_teleponError"></span>
                            </div>
                            <div class="col-md-3 mb-3 ">
                                <label>Email<small>*</small></label>
                                <input type="text" name="email" id="email" class="form-control"
                                    value="{{ $dataSistem->email }}">
                                <span class="text-danger error" id="emailError"></span>
                            </div>
                            <div class="col-md-6 mb-3 ">
                                <label>Website<small>*</small></label>
                                <input type="text" name="website" id="website" class="form-control"
                                    value="{{ $dataSistem->website }}">
                                <span class="text-danger error" id="websiteError"></span>
                            </div>
                        </div>

                        <div class="mt-3">
                            <a href="{{ route('company.info') }}" class="btn btn-secondary"> <i
                                    class="ti ti-chevron-left me-1"></i> Back </a>
                            <button class="btn btn-primary" id="savedata">
                                <i class="fa fa-save me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {

            $('#postForm').on('submit', function(e) {
                e.preventDefault();
                var form = this;
                $.ajax({
                    url: $(form).attr('action'),
                    method: $(form).attr('method'),
                    data: new FormData(form),
                    processData: false,
                    contentType: false,
                    datatype: 'json',
                    beforeSend: function(e) {
                        $('#savedata').html(
                            '<i class="fa fa-spin fa-spinner me-1"></i> Sending...');
                    },
                    complete: function(e) {
                        $('#savedata').html(' <i class="fa fa-save me-1"></i> Save');
                    },
                    success: function(response) {
                        window.location.href = response.redirect;
                        Swal.fire({
                            icon: 'success',
                            title: 'Change Successful',
                            text: response.message,
                            showClass: {
                                popup: 'animate__animated animate__bounceIn'
                            },
                            customClass: {
                                confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                            buttonsStyling: false
                        });

                    },
                    error: function(xhr) {
                        resetValidation();
                        Swal.fire({
                            icon: 'error',
                            title: 'Change Failed',
                            text: 'Please check your data again.',
                            showClass: {
                                popup: 'animate__animated animate__bounceIn'
                            },
                            customClass: {
                                confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                            buttonsStyling: false
                        });
                        let errors = xhr.responseJSON.errors;

                        $.each(errors, function(key, value) {
                            // For other fields, display individual field errors if any
                            displayFieldError(key, value[0]);
                        });
                    }
                });
            });
        });
    </script>
@endpush
