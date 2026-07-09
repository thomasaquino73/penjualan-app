@push('scripts')
    <script>
        $("#btnAddTerm").click(function() {
            Swal.fire({
                title: "Add New Payment Term",
                input: "text",
                theme: "bootstrap-5",
                inputLabel: "Payment Term Name",
                inputPlaceholder: "Input Payment Term name...",
                showCancelButton: true,
                confirmButtonText: "Save",
                cancelButtonText: "Cancel",
                customClass: {
                    confirmButton: "btn btn-primary me-2",
                    cancelButton: "btn btn-danger",
                },
                buttonsStyling: false,
                inputValidator: (value) => {
                    if (!value) {
                        return "Shipping wajib diisi";
                    }
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('syarat-pembayaran.store') }}",
                        type: "POST",

                        data: {
                            nama: result.value,
                            _token: "{{ csrf_token() }}",
                        },
                        success: function(response) {
                            let option = new Option(
                                response.nama,
                                response.id,
                                true,
                                true,
                            );
                            $("#payment_term").append(option).trigger("change");

                            Swal.fire({
                                icon: "success",
                                title: "Success",
                                text: response.message,
                                customClass: {
                                    confirmButton: "btn btn-primary me-2",
                                },
                                buttonsStyling: false,
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "Failed save payment term",
                                customClass: {
                                    confirmButton: "btn btn-info",
                                },
                                buttonsStyling: false,
                            });
                        },
                    });
                }
            });
        });
    </script>
@endpush
