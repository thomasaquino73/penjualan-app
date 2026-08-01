@extends('layouts.app')
@section('konten')
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

    <div class="card">
        <div
            class="card-header d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center">

            <h5 class="card-title mb-2 mb-lg-0">{{ $title }}</h5>

            <div class="col-12 col-lg-5">
                <div
                    class="d-flex flex-column flex-md-row gap-2
                    justify-content-start justify-content-lg-end">


                </div>
            </div>

        </div>
        <div class="card-datatable table-responsive p-3">
            {{--  --}}
            <div class="demo-inline-spacing mt-3">
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Stok Barang
                        <span class="badge ">
                            <button class="btn btn btn-icon btn-label-primary waves-effect me-1" id="stockCard"><i
                                    class="ti ti-folder"></i></button>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Kartu Mutasi Barang
                        <span class="badge ">
                            <button class="btn btn btn-icon btn-label-primary waves-effect me-1" id="mutationCard"><i
                                    class="ti ti-folder"></i></button>
                        </span>
                    </li>

                </ul>
            </div>
            {{--  --}}
        </div>
    </div>
    @include('report.inventory-report.modals.modals_stok')
    @include('report.inventory-report.modals.modals_mutation')
@endsection
@push('scripts')
    <script>
        $(function() {

            const startDate = flatpickr("#start_date", {
                enableTime: false,
                dateFormat: "Y-m-d", // dikirim ke server
                altInput: true,
                altFormat: "d-m-Y", // ditampilkan ke user
                defaultDate: "{{ now()->format('Y-m-d') }}",
                onChange: function(selectedDates) {
                    endDate.set('minDate', selectedDates[0]);
                }
            });

            const endDate = flatpickr("#end_date", {
                enableTime: false,
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d-m-Y",
                defaultDate: "{{ now()->format('Y-m-d') }}",
                onChange: function(selectedDates) {
                    startDate.set('maxDate', selectedDates[0]);
                }
            });

        });
        $(document).ready(function() {
            $(".select2-modal").each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: $this.attr("data-placeholder"),
                    width: "100%",
                    dropdownParent: $("#modalmutation"),
                });
            });

            $("#formPrintStock").on("submit", function(e) {

                if ($("#barang_id_stock").val() == "") {
                    e.preventDefault();

                    Swal.fire({
                        icon: "warning",
                        title: "Peringatan",
                        text: "Silahkan pilih barang terlebih dahulu.",
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });

                    return false;
                }

                $(this).attr("target", "_blank");

                $("#modalstok").modal("hide");
            });

            $("#formPrintMutation").on("submit", function(e) {

                if ($("#barang_id").val() == "") {
                    e.preventDefault();

                    Swal.fire({
                        icon: "warning",
                        title: "Peringatan",
                        text: "Silahkan pilih barang terlebih dahulu.",
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });

                    return false;
                }

                $(this).attr("target", "_blank");

                $("#modalmutation").modal("hide");
            });
        });

        $('#stockCard').click(function() {
            $('#modalstok').modal('show');
        });

        $('#mutationCard').click(function() {
            $('#modalmutation').modal('show');
        });
    </script>
@endpush
