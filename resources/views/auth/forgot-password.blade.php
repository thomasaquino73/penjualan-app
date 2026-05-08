@extends('layouts.guest')

@section('konten')
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-4">
                <div class="card">
                    <div class="card-body">
                        <div class="app-brand justify-content-center mb-4 mt-2">
                            <a href="{{ url('/') }}" class="app-brand-link gap-2">
                                <span class="app-brand-text demo text-body fw-bold">{{ $aplikasi }}</span>
                            </a>
                        </div>

                        <h4 class="mb-1 pt-2">Lupa Password? 🔒</h4>
                        <p class="mb-4">
                            Masukkan email Anda dan kami akan mengirimkan instruksi untuk mengatur ulang kata sandi Anda.
                        </p>

                        <form id="forgotForm" method="POST" action="{{ route('password.email') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="Masukkan Email Anda" autofocus autocomplete="email" />
                            </div>
                            <button id="savedata" type="submit" class="btn btn-primary d-grid w-100">
                                Kirim
                            </button>
                            <hr>
                            <a href="{{ route('login') }}" type="button" class="btn btn-sm btn-secondary"> Back to Login
                                Page</a>
                        </form>
                        <div class="text-center mt-3">
                            <small>{{ $aplikasi }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            $('#forgotForm').on('submit', function(e) {
                e.preventDefault();
                var form = this;

                resetValidation();

                $.ajax({
                    url: $(form).attr('action'),
                    method: $(form).attr('method'),
                    data: new FormData(form),
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    headers: {
                        'Accept': 'application/json'
                    },
                    beforeSend: function() {
                        $('#savedata').prop('disabled', true).html(
                            '<i class="fa fa-spin fa-spinner me-1"></i> Sedang diproses...');
                    },
                    complete: function() {
                        $('#savedata').prop('disabled', false).html('Kirim');
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message ||
                                'Instruksi pengaturan ulang kata sandi telah dikirim ke email Anda.',
                            showClass: {
                                popup: 'animate__animated animate__bounceIn'
                            },
                            customClass: {
                                confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                            buttonsStyling: false,
                        }).then(() => {
                            window.location.href = "{{ route('login') }}";
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: xhr.responseJSON?.message ||
                                'Terjadi kesalahan, silakan coba lagi.',
                            showClass: {
                                popup: 'animate__animated animate__bounceIn'
                            },
                            customClass: {
                                confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                            buttonsStyling: false
                        }).then(() => {
                            // Redirect jika email belum diverifikasi
                            if (xhr.responseJSON?.redirect) {
                                window.location.href = xhr.responseJSON.redirect;
                            }
                        });

                        // Tampilkan error validation field
                        if (xhr.responseJSON?.errors) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                displayFieldError(key, value[0]);
                            });
                        }
                    }
                });
            });

            function resetValidation() {
                $('.invalid-feedback').remove();
                $('input, select').removeClass('is-invalid');
            }

            function displayFieldError(fieldId, errorMessage) {
                if (!$('#' + fieldId + 'Error').length) {
                    $('#' + fieldId).after(
                        `<div id="${fieldId}Error" class="invalid-feedback">${errorMessage}</div>`
                    );
                } else {
                    $('#' + fieldId + 'Error').text(errorMessage);
                }
                $('#' + fieldId).addClass('is-invalid');
            }
        });
    </script>
@endpush
