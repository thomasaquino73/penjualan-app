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
        <form id="postForm" name="postForm" method="POST" action="{{ route('supplier.store') }}">
            @csrf
            <div class="card-body table-responsive p-3">
                <div class="col-xl-12">
                    <div class="nav-align-top mb-4">
                        <ul class="nav nav-pills mb-3" role="tablist">
                            <li class="nav-item">
                                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-pills-top-general" aria-controls="navs-pills-top-general"
                                    aria-selected="true">
                                    General Information
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-pills-top-contact" aria-controls="navs-pills-top-contact"
                                    aria-selected="false">
                                    Contact
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-pills-top-term" aria-controls="navs-pills-top-term"
                                    aria-selected="false">
                                    Term & Bank
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-pills-top-tax" aria-controls="navs-pills-top-tax"
                                    aria-selected="false">
                                    Tax Information
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="navs-pills-top-general" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="id_supplier" class="form-label">Supplier
                                                    ID<small class="text-danger">*</small></label>
                                                <input type="text" id="id_supplier" name="id_supplier"
                                                    class="form-control" placeholder="Enter Supplier ID"
                                                    value="{{ $idNumber }}">
                                                <span class="error text-danger" id="id_supplierError"></span>

                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="nama_supplier" class="form-label">Supplier
                                                    Name<small class="text-danger">*</small></label>
                                                <input type="text" id="nama_supplier" name="nama_supplier"
                                                    class="form-control" placeholder="Enter Supplier Name">
                                                <span class="error text-danger" id="nama_supplierError"></span>

                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="notel_bisnis" class="form-label">Bussines Phone
                                                    Number<small class="text-danger">*</small></label>
                                                <input type="text" id="notel_bisnis" name="notel_bisnis"
                                                    class="form-control" placeholder="Enter Business Phone Number">
                                                <span class="error text-danger" id="notel_bisnisError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="no_hp" class="form-label">Phonecell<small
                                                        class="text-danger">*</small></label>
                                                <input type="text" id="no_hp" name="no_hp" class="form-control"
                                                    placeholder="Enter Phonecell Number">
                                                <span class="error text-danger" id="no_hpError"></span>
                                            </div>

                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="no_whatsapp" class="form-label">Whatsapp<small
                                                        class="text-danger">*</small></label>
                                                <input type="text" id="no_whatsapp" name="no_whatsapp"
                                                    class="form-control" placeholder="Enter Whatsapp Number">
                                                <span class="error text-danger" id="no_whatsappError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="text" id="email" name="email" class="form-control"
                                                    placeholder="Enter Email">
                                                <span class="error text-danger" id="emailError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="faximili" class="form-label">Faximili</label>
                                                <input type="text" id="faximili" name="faximili"
                                                    class="form-control" placeholder="Enter Fax Number">
                                                <span class="error text-danger" id="faximiliError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="website" class="form-label">Website</label>
                                                <input type="text" id="website" name="website" class="form-control"
                                                    placeholder="Enter Website">
                                                <span class="error text-danger" id="websiteError"></span>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12 mb-3">
                                                <label for="alamat_pembayaran" class="form-label">Payment Address</label>
                                                <textarea id="alamat_pembayaran" name="alamat_pembayaran" class="form-control" placeholder="Enter Payment Address"></textarea>
                                                <span class="error text-danger" id="alamat_pembayaranError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="kota" class="form-label">City</label>
                                                <input type="text" id="kota" name="kota" class="form-control"
                                                    placeholder="Enter City">
                                                <span class="error text-danger" id="kotaError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="kodepos" class="form-label">Postal Code</label>
                                                <input type="text" id="kodepos" name="kodepos" class="form-control"
                                                    placeholder="Enter Postal Code">
                                                <span class="error text-danger" id="kodeposError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="provinsi" class="form-label">Province</label>
                                                <input type="text" id="provinsi" name="provinsi"
                                                    class="form-control" placeholder="Enter Province">
                                                <span class="error text-danger" id="provinsiError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="negara" class="form-label">Country</label>
                                                <input type="text" id="negara" name="negara" class="form-control"
                                                    placeholder="Enter Country">
                                                <span class="error text-danger" id="negaraError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label class="form-label">Supplier Type<small>*</small></label>
                                                <select name="tipe_pemasok_id" id="tipe_pemasok_id" class="form-control">
                                                    <option value="" selected hidden>Select Supplier Type</option>
                                                    <option value="1">Perorangan</option>
                                                    <option value="2">Perusahaan</option>
                                                    <option value="3">Pemerintah</option>
                                                </select>
                                                <span class="error text-danger" id="tipe_pemasok_idError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label class="form-label">Status<small>*</small></label>
                                                <select name="status" id="status" class="form-control">
                                                    <option value="" selected hidden>Select Status</option>
                                                    <option value="1">Active</option>
                                                    <option value="2">Not Active</option>
                                                </select>
                                                <span class="error text-danger" id="statusError"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-top-contact" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12 mb-3">
                                                <label for="contact_person" class="form-label">Fullname</label>
                                                <div class="row">
                                                    <div class="col-lg-4">
                                                        <select name="sapaan" id="sapaan" class="form-select">
                                                            <option value="" selected hidden>Select Salutation
                                                            </option>
                                                            <option value="Mr.">Mr.</option>
                                                            <option value="Mrs.">Mrs.</option>
                                                            <option value="Ms.">Ms.</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-8">
                                                        <input type="text" id="contact_person" name="contact_person"
                                                            class="form-control" placeholder="Enter Contact Person">
                                                    </div>
                                                </div>

                                                <span class="error text-danger" id="contact_personError"></span>
                                            </div>
                                            <div class="col-md-12 col-sm-12 mb-3">
                                                <label for="posisi_jabatan" class="form-label">Position</label>
                                                <input type="text" id="posisi_jabatan" name="posisi_jabatan"
                                                    class="form-control" placeholder="Enter Position">
                                                <span class="error text-danger" id="posisi_jabatanError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="email_kontak" class="form-label">Email</label>
                                                <input type="text" id="email_kontak" name="email_kontak"
                                                    class="form-control" placeholder="Enter Email">
                                                <span class="error text-danger" id="email_kontakError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="handphone_kontak" class="form-label">Phone Number</label>
                                                <input type="text" id="handphone_kontak" name="handphone_kontak"
                                                    class="form-control" placeholder="Enter Phone Number">
                                                <span class="error text-danger" id="handphone_kontakError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="notel_bisnis_kontak" class="form-label">Bussines Phone
                                                    Number</label>
                                                <input type="text" id="notel_bisnis_kontak" name="notel_bisnis_kontak"
                                                    class="form-control" placeholder="Enter Business Phone Number">
                                                <span class="error text-danger" id="notel_bisnis_kontakError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="faximili_kontak" class="form-label">Fax Number
                                                    Number</label>
                                                <input type="text" id="faximili_kontak" name="faximili_kontak"
                                                    class="form-control" placeholder="Enter Business Phone Number">
                                                <span class="error text-danger" id="faximili_kontakError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="no_whatsapp_kontak" class="form-label">Whatsapp
                                                    Number</label>
                                                <input type="text" id="no_whatsapp_kontak" name="no_whatsapp_kontak"
                                                    class="form-control" placeholder="Enter Business Phone Number">
                                                <span class="error text-danger" id="no_whatsapp_kontakError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="website_kontak" class="form-label">Website
                                                </label>
                                                <input type="text" id="website_kontak" name="website_kontak"
                                                    class="form-control" placeholder="Enter Business Phone Number">
                                                <span class="error text-danger" id="website_kontakError"></span>
                                            </div>
                                            <div class="col-md-12 col-sm-12 mb-3">
                                                <label for="catatan" class="form-label">Notes</label>
                                                <input type="text" id="catatan" name="catatan" class="form-control"
                                                    placeholder="Enter Business Phone Number">
                                                <span class="error text-danger" id="catatanError"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-top-term" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label class="form-label">Payment Term<small>*</small></label>
                                                <select name="tipe_pemasok_id" id="tipe_pemasok_id"
                                                    class="form-control select2" data-placeholder="Select Payment Term">
                                                    <option></option>
                                                    @foreach ($paymentTerm as $term)
                                                        <option value="{{ $term->id }}">{{ $term->detail }}</option>
                                                    @endforeach
                                                </select>
                                                <span class="error text-danger" id="tipe_pemasok_idError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="discount" class="form-label">Discount</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text">%</span>
                                                    <input type="number" id="discount" name="discount"
                                                        class="form-control" placeholder="0" min="0">
                                                </div>
                                                <span class="error text-danger" id="discountError"></span>
                                            </div>
                                            <div class="col-md-12 col-sm-12 mb-3">
                                                <label for="default_deskripsi" class="form-label">Description</label>
                                                <textarea type="text" id="default_deskripsi" name="default_deskripsi" class="form-control"
                                                    placeholder="Enter Description"></textarea>
                                                <span class="error text-danger" id="default_deskripsiError"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        @include('master_data.supplier.part.data_bank')
                                    </div>

                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-top-tax" role="tabpanel">

                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('supplier.index') }}" type="button" class="btn btn-label-secondary waves-effect">
                        <i class="ti ti-chevron-left me-1"></i>
                        Back
                    </a>
                    <button type="submit" id="savedata" name="savedata" class="btn btn-primary me-sm-3 me-1">
                        <i class="fa fa-save me-1"></i>Save
                    </button>
                </div>
        </form>
    </div>

    </div>

    <div class="modal fade" id="modalPrDetail">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Create new entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formPrDetail">
                    @csrf
                    <input type="hidden" name="id" id="detail_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label" for="nama_bank">Bank Name</label>
                                <select name="nama_bank" id="nama_bank" class="form-select select2-modal "
                                    data-placeholder="Select Bank Name">
                                    <option></option>
                                </select>
                                <span class="error text-danger" id="nama_bankError"></span>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label" for="quantity">Account Name</label>
                                <input type="number" id="quantity" name="quantity" class="form-control"
                                    placeholder="0">
                                <span class="error text-danger" id="quantityError"></span>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label" for="nomor_rekening">Account Number</label>
                                <input type="number" id="nomor_rekening" name="nomor_rekening" class="form-control"
                                    placeholder="0">
                                <span class="error text-danger" id="nomor_rekeningError"></span>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitModal">Create</button>
                    </div>
                </form>
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
                        $('#savedata').html(' <i class="fa fa-save me-1"></i>Save');
                    },
                    success: function(response) {
                        $('#modals').modal('hide');
                        window.location.href = response.redirect;
                        Swal.fire({
                            icon: 'success',
                            title: response.title,
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
                        // reset validation messages (buat kamu implement sendiri)
                        resetValidation();

                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to Create Data',
                            text: 'Please check your data again.',
                            showClass: {
                                popup: 'animate__animated animate__bounceIn'
                            },
                            customClass: {
                                confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                            buttonsStyling: false
                        });

                        let errors = xhr.responseJSON.errors || {};

                        $.each(errors, function(key, value) {
                            displayFieldError(key, value[
                                0]); // fungsi buat nampilin error per field
                        });
                    }
                });


            });

        });
    </script>
@endpush
