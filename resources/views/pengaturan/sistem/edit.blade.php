@extends('layouts.app')
@section('konten')
    <div class="container-xxl flex-grow-1 container-p-y">

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

                <div class="card mb-4">

                    <h5 class="card-header">{{ $title }}</h5>

                    <div class="card-body">

                        <form action="{{ route('pengaturan.update', $dataSistem->id) }}" id="postForm" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('put')

                            <div class="divider divider-dashed">
                                <div class="divider-text">Fill in the data completely and correctly</div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Logo</label>
                                    <input type="file" name="avatar" id="avatar" class="form-control">
                                    <span class="text-danger error" id="avatarError"></span>
                                </div>
                                <div class="col-md-6 mb-3 ">
                                    <label>Application Name<small>*</small></label>
                                    <input type="text" name="nama_aplikasi" id="nama_aplikasi" class="form-control"
                                        value="{{ $dataSistem->nama_aplikasi }}">
                                    <span class="text-danger error" id="nama_aplikasiError"></span>
                                </div>
                                <div class="col-md-6 mb-3 ">
                                    <label>Institution Name<small>*</small></label>
                                    <input type="text" name="nama_instansi" id="nama_instansi" class="form-control"
                                        value="{{ $dataSistem->nama_instansi }}">
                                    <span class="text-danger error" id="nama_instansiError"></span>
                                </div>
                                <div class="col-md-6 mb-3 ">
                                    <label>Address<small>*</small></label>
                                    <input type="text" name="alamat" id="alamat" class="form-control"
                                        value="{{ $dataSistem->alamat }}">
                                    <span class="text-danger error" id="alamatError"></span>
                                </div>
                                <div class="col-md-6 mb-3 ">
                                    <label>Phone Number<small>*</small></label>
                                    <input type="text" name="telepon" id="telepon" class="form-control"
                                        value="{{ $dataSistem->telepon }}">
                                    <span class="text-danger error" id="teleponError"></span>
                                </div>
                                <div class="col-md-6 mb-3 ">
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
                                <div class="col-md-6 mb-3 ">
                                    <label>Description<small>*</small></label>
                                    <input type="text" name="deskripsi" id="deskripsi" class="form-control"
                                        value="{{ $dataSistem->deskripsi }}">
                                    <span class="text-danger error" id="deskripsiError"></span>
                                </div>
                                <div class="col-md-6 mb-3 ">
                                    <label>Foundation Year<small>*</small></label>
                                    <input type="text" name="tahun_berdiri" id="tahun_berdiri" class="form-control"
                                        value="{{ $dataSistem->tahun_berdiri }}">
                                    <span class="text-danger error" id="tahun_berdiriError"></span>
                                </div>

                            </div>

                            <div class="mt-3">
                                <a href="{{ route('pengaturan.sistem') }}" class="btn btn-secondary"> <i
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
