@extends('layouts.app')
@section('title', $title)
@section('konten')
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

                </div>

                <div class="divider divider-dashed">
                    <div class="divider-text">Permissions Detail</div>
                </div>

                {{-- TOMBOL CHECK ALL GLOBAL DI ATAS TABEL --}}
                <div class="d-flex justify-content-start mb-3">
                    <button type="button" id="checkAllGlobalBtn" class="btn btn-sm btn-primary">
                        <i class="bx bx-check-double me-1"></i> Check All (Semua Data)
                    </button>
                </div>

                {{-- TABLE --}}
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 25%;">Module</th>
                                @foreach ($actions as $act)
                                    <th class="text-center">{{ ucfirst($act) }}</th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($groupedPermissions as $groupName => $modules)
                                <tr class="table-light fw-bold text-dark">
                                    <td colspan="{{ count($actions) + 1 }}" class="py-2 text-uppercase bg-light"
                                        style="letter-spacing: 0.5px; font-size: 0.85rem;">
                                        {{ $groupName }}
                                    </td>
                                </tr>

                                @foreach ($modules as $module => $actionsData)
                                    <tr class="module-row">
                                        <td class="ps-3 text-secondary">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <span class="text-muted opacity-50 me-1">└─</span>
                                                    <strong>
                                                        {{ ucfirst(collect($actionsData)->first()->alias ?? str_replace('_', ' ', $module)) }}
                                                    </strong>
                                                </div>

                                                <button type="button"
                                                    class="btn btn-xs btn-outline-secondary py-0 px-1 check-row-btn"
                                                    style="font-size: 0.7rem;">
                                                    Row Check
                                                </button>
                                            </div>
                                        </td>

                                        @foreach ($actions as $act)
                                            <td class="text-center">
                                                @php
                                                    $perm = $actionsData[$act] ?? null;
                                                @endphp

                                                @if ($perm)
                                                    <div class="form-check form-switch d-inline-block mb-0">
                                                        <input class="form-check-input permission-checkbox" type="checkbox"
                                                            name="permissions[]" value="{{ $perm->id }}"
                                                            {{ in_array($perm->id, $rolePermissions) ? 'checked' : '' }}>
                                                    </div>
                                                @else
                                                    <span class="text-muted opacity-50">—</span>
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

@endsection
@push('style')
    <style>
        .table-responsive {
            max-height: 500px;
            /* sesuaikan tinggi area scroll */
            overflow-y: auto;
        }

        .table thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #f8f9fa;
            /* biar gak transparan */
        }
    </style>
@endpush
@push('scripts')
    <script>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const allRows = document.querySelectorAll('.module-row');
            const checkAllGlobalBtn = document.getElementById('checkAllGlobalBtn');

            // 1. FUNGSI UTAMA: Update teks & style tombol per baris secara akurat
            function updateRowButtonText(row) {
                const btn = row.querySelector('.check-row-btn');
                if (!btn) return;

                const rowCheckboxes = row.querySelectorAll('.permission-checkbox');
                if (rowCheckboxes.length === 0) {
                    btn.style.display = 'none';
                    return;
                }

                // Cek apakah SEMUA checkbox aktif di baris ini dalam posisi tercentang
                const isAllChecked = Array.from(rowCheckboxes).every(cb => cb.checked);

                if (isAllChecked) {
                    btn.textContent = 'Clear';
                    btn.classList.remove('btn-outline-secondary');
                    btn.classList.add('btn-secondary');
                } else {
                    btn.textContent = 'Row Check';
                    btn.classList.remove('btn-secondary');
                    btn.classList.add('btn-outline-secondary');
                }
            }

            // 2. FUNGSI UTAMA: Update status tombol "Check All Global" di paling atas
            function updateGlobalButtonText() {
                if (!checkAllGlobalBtn) return;

                const allCheckboxes = document.querySelectorAll('.permission-checkbox');
                if (allCheckboxes.length === 0) return;

                // Cek apakah ada satu saja checkbox di seluruh tabel yang belum dicentang
                const anyUnchecked = Array.from(allCheckboxes).some(cb => !cb.checked);

                if (anyUnchecked) {
                    checkAllGlobalBtn.innerHTML = '<i class="bx bx-check-double me-1"></i> Check All (Semua Data)';
                    checkAllGlobalBtn.classList.remove('btn-danger');
                    checkAllGlobalBtn.classList.add('btn-primary');
                } else {
                    checkAllGlobalBtn.innerHTML = '<i class="bx bx-x-circle me-1"></i> Uncheck All';
                    checkAllGlobalBtn.classList.remove('btn-primary');
                    checkAllGlobalBtn.classList.add('btn-danger');
                }
            }

            // ==========================================
            // RUN ONCE: Jalankan otomatis saat halaman selesai dimuat
            // ==========================================
            allRows.forEach(row => updateRowButtonText(row));
            updateGlobalButtonText();


            // ==========================================
            // EVENT 1: Logika ketika Tombol "Row Check / Clear" Diklik
            // ==========================================
            const rowButtons = document.querySelectorAll('.check-row-btn');
            rowButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const row = this.closest('.module-row');
                    const rowCheckboxes = row.querySelectorAll('.permission-checkbox');

                    // Tentukan status target: jika ada yang mati, kita nyalakan semua. Jika sudah hidup semua, kita matikan.
                    const anyRowUnchecked = Array.from(rowCheckboxes).some(cb => !cb.checked);

                    rowCheckboxes.forEach(cb => {
                        cb.checked = anyRowUnchecked;
                    });

                    // Perbarui visual tombol baris ini & tombol global di atas
                    updateRowButtonText(row);
                    updateGlobalButtonText();
                });
            });


            // ==========================================
            // EVENT 2: Logika Tombol "Check All Global" (Paling Atas)
            // ==========================================
            if (checkAllGlobalBtn) {
                checkAllGlobalBtn.addEventListener('click', function() {
                    const allCheckboxes = document.querySelectorAll('.permission-checkbox');
                    const anyUnchecked = Array.from(allCheckboxes).some(cb => !cb.checked);

                    // Paksa semua checkbox mengikuti status target global
                    allCheckboxes.forEach(cb => {
                        cb.checked = anyUnchecked;
                    });

                    // Jalankan ulang fungsi penyesuaian teks ke seluruh baris
                    allRows.forEach(row => updateRowButtonText(row));

                    // Perbarui visual tombol global ini sendiri
                    updateGlobalButtonText();
                });
            }


            // ==========================================
            // EVENT 3: Jaga-jaga jika Admin mengubah Switch secara manual tunggal
            // ==========================================
            const allCheckboxes = document.querySelectorAll('.permission-checkbox');
            allCheckboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    const row = this.closest('.module-row');
                    if (row) {
                        updateRowButtonText(row);
                    }
                    updateGlobalButtonText();
                });
            });
        });
    </script>
@endpush
