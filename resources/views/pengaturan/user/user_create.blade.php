@extends('layouts.app')
@section('title', $title)

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
                    <div class="card-body p-10">
                        <form action="{{ route('user.store') }}" method="POST" id="postForm">
                            @csrf
                            <div class="row">
                                <div class="divider divider-dashed">
                                    <div class="divider-text">PERSONAL DATA</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Avatar :</label>
                                        <input type="file" class="form-control" id="avatar" name="avatar" />
                                        <span class="error text-danger" id="avatarError"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label me-1">ID Number</label><small class="text-danger">*
                                            required</small>
                                        <input type="text" class="form-control" id="no_ID" name="no_ID"
                                            placeholder="Enter ID number" />
                                        <span class="error text-danger" id="no_IDError"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label me-1">Fullname</label><small class="text-danger">*
                                            required</small>
                                        <input type="text" class="form-control" id="fullname" name="fullname"
                                            placeholder="Enter full name" />
                                        <span class="error text-danger" id="fullnameError"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label me-1">Nickname</label><small class="text-danger">*
                                            required</small>
                                        <input type="text" class="form-control" id="nickname" name="nickname"
                                            placeholder="Enter nickname" />
                                        <span class="error text-danger" id="nicknameError"></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label me-1">Gender</label><small class="text-danger">*
                                            required</small>
                                        <select id="gender" name="gender" style="width:100%" class="select2 form-select"
                                            aria-label="Default select example" data-placeholder="Select Gender...">
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                        <span class="error text-danger" id="genderError"></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Email</label><small class="text-danger">*
                                            required</small>
                                        <input type="text" class="form-control" id="email" name="email"
                                            placeholder="ex: 123@example.com" />
                                        <span class="error text-danger" id="emailError"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label me-1">Phone Number</label><small class="text-danger">*
                                            required</small>
                                        <input type="text" class="form-control" id="phone" name="phone"
                                            placeholder="ex: 0812***" />
                                        <span class="error text-danger" id="phoneError"></span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Address</label>
                                        <input type="text" class="form-control" id="address" name="address"
                                            placeholder="Enter street name and house number" />
                                        <span class="error text-danger" id="addressError"></span>
                                    </div>
                                </div>

                            </div>
                            <div class="divider divider-dashed">
                                <div class="divider-text">ACCOUNT DATA</div>
                            </div>
                            <div class="mb-3">
                                <label for="" class="form-label">Username:</label><small class="text-danger">*
                                    required</small>
                                <input class="form-control" type="text" id="username" name="username"
                                    placeholder="Enter your username..." />
                                <span class="error text-danger" id="usernameError"></span>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="" class="form-label">Password:</label><small
                                            class="text-danger">*
                                            required</small>
                                        <input class="form-control" type="password" id="password" name="password" />
                                        <small>*Minimal 6 character</small>
                                        <span class="error text-danger" id="passwordError"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="" class="form-label">Confirm Password:</label><small
                                            class="text-danger">*
                                            required</small>
                                        <input class="form-control" type="password" id="confirm_password"
                                            name="confirm_password" />
                                        <small>*Minimal 6 character</small>
                                        <span class="error text-danger" id="confirm_passwordError"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="exampleFormControlSelect1" class="form-label">Status:</label><small
                                            class="text-danger">*
                                            required</small>
                                        <select name="status" id="status" class="form-select select2"
                                            data-placeholder="Select Status">
                                            <option value="" selected hidden>Select Status...</option>
                                            <option value="Active">Active</option>
                                            <option value="Not Active">Not Active</option>
                                        </select>
                                        <span class="error text-danger" id="statusError"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="exampleFormControlSelect1" class="form-label ">Roles:</label><small
                                            class="text-danger">*
                                            required</small>
                                        <select name="roles" id="roles" class="form-select select2">
                                            <option value="">-- Select Role --</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="error text-danger" id="rolesError"></span>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <a href="{{ route('user.index') }}" class="btn btn-secondary"><i
                                            class="fas fa-chevron-left me-1"></i>
                                        <b>{{ __('Back') }}</b></a>
                                    <button type="submit" id="savedata" name="savedata" class="btn btn-primary">
                                        <i class="fa fa-save me-1"></i>{{ __('Save') }}
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
                    dataType: 'json',

                    beforeSend: function() {
                        $('#savedata').html(
                            '<i class="fa fa-spin fa-spinner me-1"></i> Sending...'
                        );
                    },

                    complete: function() {
                        $('#savedata').html(
                            '<i class="fa fa-save me-1"></i> Save'
                        );
                    },

                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Saved Successfully',
                            text: response.message,
                            showClass: {
                                popup: 'animate__animated animate__bounceIn'
                            },
                            customClass: {
                                confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                            buttonsStyling: false
                        });

                        window.location.href = response.redirect;
                    },

                    error: function(xhr) {
                        resetValidation();

                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to Update Data',
                            text: 'Please check your input and try again.',
                            showClass: {
                                popup: 'animate__animated animate__bounceIn'
                            },
                            customClass: {
                                confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                            buttonsStyling: false
                        });

                        let errors = xhr.responseJSON?.errors;

                        $.each(errors, function(key, value) {
                            displayFieldError(key, value[0]);
                        });
                    }
                });
            });
        });
    </script>
@endpush
