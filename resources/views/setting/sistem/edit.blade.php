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
                                <div class="col-md-6 mb-3 ">
                                    <label>Application Name<small>*</small></label>
                                    <input type="text" name="nama_aplikasi" id="nama_aplikasi"
                                        class="form-control text-uppercase" value="{{ $dataSistem->nama_aplikasi }}">
                                    <span class="text-danger error" id="nama_aplikasiError"></span>
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
