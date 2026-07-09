@push('scripts')
    <script>
        $("#btnAddShipping").click(function() {
            Swal.fire({
                title: "Add New Shipping",
                input: "text",
                inputLabel: "Shipping Name",
                inputPlaceholder: "Input shipping name...",

                showCancelButton: true,

                // confirmButtonColor: "#3085d6",
                // cancelButtonColor: "#d33",

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
                        url: "{{ route('shipping.store') }}",
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

                            $("#vehicle_id").append(option).trigger("change");

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
                                text: "Failed save shipping",
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
