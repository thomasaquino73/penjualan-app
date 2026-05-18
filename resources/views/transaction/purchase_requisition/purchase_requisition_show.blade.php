@extends('layouts.app')
@section('konten')
    <div class="card">
        <div
            class="card-header d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center">
            <div class="col-12 col-lg-5">
                <div class="d-flex flex-column flex-md-row gap-2 justify-content-start justify-content-lg-end">
                    <!-- Tempat tombol aksi jika ada -->
                </div>
            </div>
        </div>

        <!-- Card Body / Konten Utama -->
        <div class="card-body">
            <div class="row g-4">

                <!-- SISI KIRI: Detail Perusahaan -->
                <div class="col-md-6">
                    <div class="d-flex align-items-start gap-3">
                        <!-- Logo Perusahaan -->
                        <div class="flex-shrink-0">
                            @if (isset($company) && $company->logo)
                                <img src="{{ asset($company->logo) }}" alt="Logo {{ $company->nama_perusahaan }}"
                                    class="img-fluid rounded" style="max-width: 100px; height: auto; object-fit: contain;">
                            @else
                                <img src="{{ asset('image/no-images.jpg') }}" alt="Logo {{ $company->nama_perusahaan }}"
                                    class="img-fluid rounded" style="max-width: 100px; height: auto; object-fit: contain;">
                            @endif
                        </div>

                        <!-- Data Perusahaan -->
                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-1">{{ $company->nama_perusahaan ?? 'Nama Perusahaan' }}</h5>

                            <p class="text-muted small mb-2">
                                {{ $company->alamat ?? 'Alamat belum diatur' }}<br>
                                {{ $company->negara ?? '' }} {{ $company->kodepos ?? '' }}
                            </p>

                            <div class="row g-1 small text-secondary">
                                @if ($company->nomor_telepon)
                                    <div class="col-12">
                                        <i class="bi bi-telephone-fill me-1"></i> {{ $company->nomor_telepon }}
                                    </div>
                                @endif
                                @if ($company->email)
                                    <div class="col-12">
                                        <i class="bi bi-envelope-fill me-1"></i> {{ $company->email }}
                                    </div>
                                @endif
                                @if ($company->website)
                                    <div class="col-12">
                                        <i class="bi bi-globe me-1"></i> <a href="{{ $company->website }}" target="_blank"
                                            class="text-decoration-none text-secondary">{{ $company->website }}</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SISI KANAN: Request Number & Date (Kode Anda) -->
                <div class="col-md-6">
                    <div class="row justify-content-end">
                        <!-- Dibungkus col-lg-10 atau full col-12 agar presisi ke kanan -->
                        <div class="col-12 col-xl-11">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-semibold">Request Number</label>
                                    <input type="text" name="code" id="code" class="form-control bg-light"
                                        value="{{ isset($model) ? $model->code : $idNumber }}"
                                        {{ isset($model) ? 'disabled' : '' }}>
                                </div>

                                <div class="col-6 mb-3">
                                    <label class="form-label fw-semibold">Request Date</label>
                                    <input type="text" name="date" id="date" class="form-control bg-light"
                                        value="{{ isset($model) ? $model->date : date('Y-m-d') }}"
                                        {{ isset($model) ? 'disabled' : '' }}>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" id="description" class="form-control" {{ isset($model) ? 'disabled' : '' }}>{{ isset($model) ? $model->description : '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="card-body table-responsive p-3">
            <div class="row mt-3">
                <table class="table display responsive nowrap" id="table">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Unit</th>
                            <th>Required Date</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($modelDetail as $detail)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $detail->produkID->nama_barang ?? 'N/A' }}</td>
                                <td>{{ $detail->qty }}</td>
                                <td>{{ $detail->unitID->detail ?? 'N/A' }}</td>
                                <td>{{ $detail->required_date ? Carbon\Carbon::parse($detail->required_date)->format('Y-m-d') : 'N/A' }}
                                </td>
                                <td>{{ $detail->notes ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer d-flex justify-content-end gap-2 mt-4">
                @if (auth()->id() !== $model->created_by)
                    @can('permintaan_pembelian-approval', $model)
                        <button type="button" class="btn btn-primary btn-approval-pr" data-status="processing"
                            data-id="{{ $model->id }}">
                            Approve
                        </button>

                        <button type="button" class="btn btn-danger btn-approval-pr" data-status="rejected"
                            data-id="{{ $model->id }}">
                            Reject
                        </button>
                    @endcan
                @endif
                <a href="{{ route('permintaan-pembelian.index') }}" class="btn btn-secondary"><i
                        class="ti ti-chevron-left me-1"></i>back</a>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).on('click', '.btn-approval-pr', function() {
            let id = $(this).data('id');
            let statusTarget = $(this).data('status'); // Expected: 'processing' or 'rejected'

            // Konfigurasi teks berdasarkan statusTarget
            let textKeterangan = statusTarget === 'processing' ? 'approve' : 'reject';
            let confirmBtnColor = statusTarget === 'processing' ? '#28a745' : '#dc3545';
            let confirmBtnText = statusTarget === 'processing' ? 'Yes, Approve!' : 'Yes, Reject!';
            let confirmBtnClass = statusTarget === 'processing' ? 'btn btn-success me-3 waves-effect waves-light' :
                'btn btn-danger me-3 waves-effect waves-light';

            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to ${textKeterangan} this Purchase Requisition document.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: confirmBtnColor,
                confirmButtonText: confirmBtnText,
                customClass: {
                    confirmButton: confirmBtnClass,
                    cancelButton: 'btn btn-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/permintaan-pembelian/change-status/' +
                            id, // Match with your status update route
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: id,
                            status: statusTarget
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message ||
                                    'The status has been updated successfully.',
                                icon: 'success',
                                showCancelButton: false,
                                confirmButtonColor: '#28a745',
                                confirmButtonText: 'OK',
                                customClass: {
                                    confirmButton: 'btn btn-success'
                                },
                                buttonsStyling: false
                            });

                            if (result.isConfirmed || result.isDismissed) {
                                window.location.href =
                                    "{{ route('permintaan-pembelian.index') }}";
                            }
                        },
                        error: function(err) {
                            let errorMessage = 'Something went wrong.';
                            if (err.responseJSON && err.responseJSON.error) {
                                errorMessage = err.responseJSON.error;
                            } else if (err.responseJSON && err.responseJSON.message) {
                                errorMessage = err.responseJSON.message;
                            }

                            Swal.fire({
                                title: 'Failed!',
                                text: errorMessage,
                                icon: 'error',
                                showCancelButton: false,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                },
                                buttonsStyling: false
                            });
                        }
                    });
                }
            });
        });
    </script>
@endpush
