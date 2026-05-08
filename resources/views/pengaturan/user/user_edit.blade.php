@extends('layouts.app')
@section('title', $title)
@section('konten')

    <div class="container-xxl flex-grow-1 container-p-y">

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

        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">

                    <h5 class="card-header">{{ $title }}</h5>

                    <div class="card-body p-4">

                        <form action="{{ route('user.update', $account->id) }}" id="postForm" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">

                                <div class="divider divider-dashed">
                                    <div class="divider-text">PERSONAL DATA</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Avatar</label>
                                    <input type="file" class="form-control" name="avatar">
                                    <span class="error text-danger" id="avatarError"></span>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">ID Number <small class="text-danger">*</small></label>
                                    <input type="text" class="form-control" name="no_ID"
                                        value="{{ old('no_ID', $account->no_ID) }}">
                                    <span class="error text-danger" id="no_IDError"></span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name <small class="text-danger">*</small></label>
                                    <input type="text" class="form-control" name="fullname"
                                        value="{{ old('fullname', $account->fullname) }}">
                                    <span class="error text-danger" id="fullnameError"></span>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nickname <small class="text-danger">*</small></label>
                                    <input type="text" class="form-control" name="nickname"
                                        value="{{ old('nickname', $account->nickname) }}">
                                    <span class="error text-danger" id="nicknameError"></span>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Gender <small class="text-danger">*</small></label>
                                    <select name="gender" class="form-select select2" id="gender">
                                        <option value="Male" {{ $account->gender == 'Male' ? 'selected' : '' }}>Male
                                        </option>
                                        <option value="Female" {{ $account->gender == 'Female' ? 'selected' : '' }}>
                                            Female
                                        </option>
                                    </select>
                                    <span class="error text-danger" id="genderError"></span>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Email <small class="text-danger">*</small></label>
                                    <input type="text" class="form-control" name="email"
                                        value="{{ old('email', $account->email) }}">
                                    <span class="error text-danger" id="emailError"></span>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone Number <small class="text-danger">*</small></label>
                                    <input type="text" class="form-control" name="phone"
                                        value="{{ old('phone', $account->phone) }}">
                                    <span class="error text-danger" id="phoneError"></span>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Address</label>
                                    <input type="text" class="form-control" name="address"
                                        value="{{ old('address', $account->address) }}">
                                    <span class="error text-danger" id="addressError"></span>
                                </div>

                            </div>

                            <div class="divider divider-dashed">
                                <div class="divider-text">ACCOUNT DATA</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input class="form-control" type="text" name="username"
                                    value="{{ old('username', $account->username) }}">
                                <span class="error text-danger" id="usernameError"></span>
                            </div>

                            <div class="row">

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Password</label>
                                    <input class="form-control" type="password" name="password">
                                    <small>Minimum 6 characters</small>
                                    <span class="error text-danger" id="passwordError"></span>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Confirm Password</label>
                                    <input class="form-control" type="password" name="confirm_password">
                                    <small>Minimum 6 characters</small>
                                    <span class="error text-danger" id="confirm_passwordError"></span>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="Active" {{ $account->status == 'Active' ? 'selected' : '' }}>Active
                                        </option>
                                        <option value="Inactive" {{ $account->status == 'Inactive' ? 'selected' : '' }}>
                                            Inactive</option>
                                    </select>
                                    <span class="error text-danger" id="statusError"></span>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Roles</label>
                                    <select name="roles[]" id="roles" class="form-select select2">
                                        <option value="">--Select Roles--</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}"
                                                {{ in_array($role->name, $userRoles) ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="error text-danger" id="rolesError"></span>
                                </div>

                            </div>

                            <div class="card-footer mt-3">
                                <a href="{{ route('user.index') }}" class="btn btn-secondary">
                                    Back
                                </a>

                                <button type="submit" id="savedata" class="btn btn-primary">
                                    <i class="fa fa-save me-1"></i> Update
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
                            '<i class="fa fa-spin fa-spinner me-1"></i> Sending...');
                    },

                    complete: function() {
                        $('#savedata').html('<i class="fa fa-save me-1"></i> Save');
                    },

                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
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
                            title: 'Update Failed',
                            text: 'Please check your input data.',
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
