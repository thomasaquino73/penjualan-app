@extends('layouts.app')
@section('title', $title)
@section('konten')

    <div class="container-xxl flex-grow-1 container-p-y">

        <h4 class="py-3 mb-4">
            <span class="text-muted fw-light">Permissions /</span> {{ $title }}
        </h4>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ $title }}</h5>
            </div>

            <div class="card-body">

                <form action="{{ route('permissions.update', $role->id) }}" id="postForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Roles Name :</label>
                        <select class="form-select" id="name" name="name">
                            <option value="">Choose Roles...</option>
                            @foreach ($roles as $item)
                                <option value="{{ $item->name }}" data-id="{{ $item->id }}"
                                    {{ $item->id == $role->id ? 'selected' : '' }}>
                                    {{ $item->name }}
                                </option>
                            @endforeach
                        </select>
                        {{-- <input type="text" name="name" value="{{ $role->name }}" class="form-control" readonly> --}}
                        <span class="error text-danger" id="nameError"></span>
                    </div>
                    {{-- HEADER --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button type="button" id="checkAllBtn" class="btn btn-sm btn-outline-primary">
                            Check All
                        </button>
                    </div>

                    <div class="divider divider-dashed">
                        <div class="divider-text">Permissions Detail</div>
                    </div>

                    {{-- TABLE --}}
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Module</th>
                                    @foreach ($actions as $act)
                                        <th>{{ ucfirst($act) }}</th>
                                    @endforeach
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($groupedPermissions as $groupId => $modules)
                                    @foreach ($modules as $module => $actionsData)
                                        <tr>
                                            <td><strong>{{ ucfirst($module) }}</strong></td>

                                            @foreach ($actions as $act)
                                                <td>
                                                    @php
                                                        $perm = $actionsData[$act] ?? null;
                                                    @endphp

                                                    @if ($perm)
                                                        <div class="form-check form-switch mb-2">
                                                            <input class="form-check-input permission-checkbox"
                                                                type="checkbox" id="flexSwitchCheckChecked"
                                                                name="permissions[]" value="{{ $perm->id }}"
                                                                {{ in_array($perm->id, $rolePermissions) ? 'checked' : '' }}>
                                                        </div>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endforeach

                            </tbody>
                        </table>
                    </div>

                    <span class="text-danger" id="permissionsError"></span>

                    {{-- BUTTON --}}
                    <div class="mt-4">
                        <a href="{{ route('permissions.index') }}" class="btn btn-secondary">Back</a>
                        <button type="submit" id="savedata" class="btn btn-primary">
                            Update
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

@endsection
@push('scripts')
    <script>
        let allChecked = false;

        $('#checkAllBtn').on('click', function() {
            allChecked = !allChecked;
            $('.permission-checkbox').prop('checked', allChecked);

            $(this).text(allChecked ? 'Uncheck All' : 'Check All');
        });

        $('#postForm').on('submit', function(e) {
            e.preventDefault();

            let form = this;

            $.ajax({
                url: form.action,
                method: form.method,
                data: new FormData(form),
                processData: false,
                contentType: false,

                beforeSend: function() {
                    $('#savedata').text('Saving...');
                },

                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: res.title,
                        text: res.message,
                        showClass: {
                            popup: 'animate__animated animate__bounceIn'
                        },
                        customClass: {
                            confirmButton: 'btn btn-primary waves-effect waves-light'
                        },
                        buttonsStyling: false
                    });

                    window.location.href = res.redirect;
                },

                error: function(xhr) {
                    Swal.fire('Error', 'Check your input data', 'error');
                },

                complete: function() {
                    $('#savedata').text('Update');
                }
            });
        });
        document.getElementById('name').addEventListener('change', function() {
            const selected = this.options[this.selectedIndex]; // ambil option terpilih
            const roleId = selected.getAttribute('data-id'); // ambil id-nya
            if (roleId) {
                window.location.href = `/permissions/${roleId}/edit`;
            }
        });
    </script>
@endpush
