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
                                    Payment & Bank
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
                                                    ID
                                                    <input type="text" id="id_supplier" name="id_supplier"
                                                        class="form-control" placeholder="Enter Supplier ID"
                                                        value="{{ $idNumber }}">
                                                    <span class="error text-danger" id="id_supplierError"></span>

                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="nama_supplier" class="form-label">Supplier Name</label>
                                                <input type="text" id="nama_supplier" name="nama_supplier"
                                                    class="form-control" placeholder="Enter Supplier Name">
                                                <span class="error text-danger" id="nama_supplierError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="notel_bisnis" class="form-label">Bussines Phone
                                                    Number
                                                    <input type="number" id="notel_bisnis" name="notel_bisnis"
                                                        class="form-control" placeholder="Enter Business Phone Number">
                                                    <span class="error text-danger" id="notel_bisnisError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="no_hp" class="form-label">Phonecell</label>
                                                <input type="number" id="no_hp" name="no_hp" class="form-control"
                                                    placeholder="Enter Phonecell Number">
                                                <span class="error text-danger" id="no_hpError"></span>
                                            </div>

                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="no_whatsapp" class="form-label">Whatsapp</label>
                                                <input type="number" id="no_whatsapp" name="no_whatsapp"
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
                                                <label for="faximili" class="form-label">Fax Number</label>
                                                <input type="number" id="faximili" name="faximili"
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
                                                <label for="alamat_pembayaran" class="form-label">Billing Address</label>
                                                <textarea id="alamat_pembayaran" name="alamat_pembayaran" class="form-control" placeholder="Enter Billing Address"></textarea>
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
                                                    <option value="Perorangan">Perorangan</option>
                                                    <option value="Perusahaan">Perusahaan</option>
                                                    <option value="Pemerintah">Pemerintah</option>
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
                                                </label>
                                                <input type="number" id="faximili_kontak" name="faximili_kontak"
                                                    class="form-control" placeholder="Enter Business Phone Number">
                                                <span class="error text-danger" id="faximili_kontakError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="no_whatsapp_kontak" class="form-label">Whatsapp
                                                    Number</label>
                                                <input type="number" id="no_whatsapp_kontak" name="no_whatsapp_kontak"
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
                                                    placeholder="Enter Notes">
                                                <span class="error text-danger" id="catatanError"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-top-term" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="divider my-4">
                                            <div class="divider-text">Payment Detail</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label class="form-label">Payment Term</label>
                                                <select name="payment_term" id="payment_term"
                                                    class="form-control select2" data-placeholder="Select Payment Term">
                                                    <option></option>
                                                    @foreach ($paymentTerm as $term)
                                                        <option value="{{ $term->id }}">{{ $term->detail }}</option>
                                                    @endforeach
                                                </select>
                                                <span class="error text-danger" id="payment_termError"></span>
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
                                        <div class="divider my-4">
                                            <div class="divider-text">Bank Detail</div>
                                        </div>
                                        @include('master_data.supplier.part.data_bank')
                                    </div>

                                </div>
                            </div>
                            <div class="tab-pane fade" id="navs-pills-top-tax" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="basic-default-name">Tax</label>
                                            <div class="col-sm-9">
                                                <div class="form-check form-check-primary col-8">
                                                    <input class="form-check-input" type="checkbox" value="1"
                                                        name="default_pajak" id="default_pajak">
                                                    <label class="form-check-label" for="default_pajak">Default Invoice
                                                        includes Tax</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="basic-default-name">Type ID
                                                Tax</label>
                                            <div class="col-sm-9">
                                                <select name="tipe_id_pajak" id="tipe_id_pajak" class="form-select">
                                                    <option value="" selected hidden>Select Type ID Tax</option>
                                                    <option value="NIK">NIK</option>
                                                    <option value="NPWP">NPWP</option>
                                                    <option value="Paspor">Paspor</option>
                                                    <option value="Lainnya">Lainnya</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="basic-default-name">NPWP
                                                Number</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="nomor_wajib_pajak"
                                                    name="nomor_wajib_pajak" placeholder="Enter NPWP number">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="basic-default-name">Taxpayer
                                                Name</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="nama_wajib_pajak"
                                                    name="nama_wajib_pajak" placeholder="Enter Taxpayer Name">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="basic-default-name">ID TKU</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="id_tku" name="id_tku"
                                                    placeholder="Enter ID TKU">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="basic-default-name">Tax
                                                address</label>
                                            <div class="col-sm-9">
                                                <div class="form-check form-check-primary col-8">
                                                    <input class="form-check-input" type="checkbox" value="1"
                                                        name="check_address" id="check_address">
                                                    <label class="form-check-label" for="check_address">Tax address is
                                                        the same as payment address</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label"
                                                for="basic-default-name">Address</label>
                                            <div class="col-sm-9">
                                                <textarea class="form-control" id="alamat_pajak" name="alamat_pajak" placeholder="Enter Tax Address"></textarea>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="basic-default-name">City</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="kota_pajak"
                                                    name="kota_pajak" placeholder="Enter City">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="basic-default-name">Postal
                                                Code</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="kodepos_pajak"
                                                    name="kodepos_pajak" placeholder="Enter Postal Code">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label"
                                                for="basic-default-name">Province</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="provinsi_pajak"
                                                    name="provinsi_pajak" placeholder="Enter Province">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label"
                                                for="basic-default-name">Country</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="negara_pajak"
                                                    name="negara_pajak" placeholder="Enter Country">
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                                    @foreach ($databank as $item)
                                        <option value="{{ $item->id }}">{{ $item->detail }} -
                                            {{ $item->description }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="error text-danger" id="nama_bankError"></span>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label" for="nama_rekening">Account Name</label>
                                <input type="text" id="nama_rekening" name="nama_rekening" class="form-control"
                                    placeholder="Enter Account Name">
                                <span class="error text-danger" id="nama_rekeningError"></span>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label" for="nomor_rekening">Account Number</label>
                                <input type="number" id="nomor_rekening" name="nomor_rekening" class="form-control"
                                    placeholder="Enter Account Number" min="0">
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

            function toggleAddress() {
                const isChecked = $('#check_address').is(':checked');
                const alamat = $('#alamat_pembayaran').val().trim();

                // ❌ VALIDASI: kalau checkbox dicentang tapi alamat kosong
                if (isChecked && alamat === '') {

                    // balikin ke unchecked
                    $('#check_address').prop('checked', false);

                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops...',
                        text: 'Billing address is still empty!',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-success'
                        },
                        buttonsStyling: false
                    });

                    return;
                }

                // ✅ KALAU CHECKED
                if (isChecked) {

                    $('#alamat_pajak, #kota_pajak, #kodepos_pajak, #provinsi_pajak, #negara_pajak')
                        .prop('readonly', true);

                    $('#alamat_pajak').val($('#alamat_pembayaran').val());
                    $('#kota_pajak').val($('#kota').val());
                    $('#kodepos_pajak').val($('#kodepos').val());
                    $('#provinsi_pajak').val($('#provinsi').val());
                    $('#negara_pajak').val($('#negara').val());

                }
                // ❌ KALAU UNCHECKED
                else {

                    $('#alamat_pajak, #kota_pajak, #kodepos_pajak, #provinsi_pajak, #negara_pajak')
                        .prop('readonly', false)
                        .val('');
                }
            }

            // 🚀 run pertama kali (load)
            toggleAddress();

            // 🎯 saat checkbox berubah
            $('#check_address').on('change', function() {
                toggleAddress();
            });

        });
        $(document).ready(function() {


            $('#postForm').on('submit', function(e) {
                e.preventDefault();
                var form = this;
                let formData = new FormData(form);

                formData.append('items_detail', JSON.stringify(prDetailsData));

                let rekeningData = [];
                $('.rekening-item').each(function() {
                    rekeningData.push({
                        nama_bank: $(this).find('.nama_bank').val(),
                        nomor_rekening: $(this).find('.nomor_rekening').val(),
                        nama_rekening: $(this).find('.nama_rekening').val()
                    });
                });

                formData.append('rekening_data', JSON.stringify(rekeningData));

                $.ajax({
                    url: $(form).attr('action'),
                    method: $(form).attr('method'),
                    data: formData, // ✅ pakai ini
                    processData: false,
                    contentType: false,
                    dataType: 'json', // ✅ FIX typo

                    beforeSend: function() {
                        $('#savedata').html(
                            '<i class="fa fa-spin fa-spinner me-1"></i> Sending...');
                    },

                    complete: function() {
                        $('#savedata').html('<i class="fa fa-save me-1"></i> Save');
                    },

                    success: function(response) {
                        $('#modals').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: response.title,
                            text: response.message,
                            customClass: {
                                confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                            buttonsStyling: false
                        }).then(() => {
                            window.location.href = response.redirect;
                        });
                    },

                    error: function(xhr) {
                        resetValidation();

                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to Create Data',
                            text: xhr.responseJSON?.message ||
                                'Please check your data again.',
                            customClass: {
                                confirmButton: 'btn btn-primary waves-effect waves-light'
                            },
                            buttonsStyling: false
                        });

                        let errors = xhr.responseJSON?.errors || {};

                        $.each(errors, function(key, value) {
                            displayFieldError(key, value[0]);
                        });
                    }
                });
            });


            let prDetailsData = [];
            let table = new DataTable('#table', {
                processing: true,
                serverSide: false,
                responsive: true,
                select: true,
                searching: false,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All']
                ],
                data: prDetailsData, // Mengarah ke array di atas
                columns: [

                    {
                        data: 'nomor_rekening'
                    },
                    {
                        data: 'nama_rekening'
                    },
                    {
                        data: 'nama_bank'
                    },

                ],
                layout: {
                    topStart: {
                        buttons: [{
                                text: '<i class="ti ti-plus me-1"></i> New',
                                className: 'btn btn-primary btn-sm me-2',
                                action: function(e, dt, node, config) {
                                    var supplierId = $('#nama_supplier').val();

                                    if (!supplierId || supplierId === '') {
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Warning!',
                                            text: 'Please enter Supplier Name first before adding new data.',
                                            confirmButtonColor: '#3085d6',
                                            confirmButtonText: 'OK',
                                            customClass: {
                                                confirmButton: 'btn btn-danger'
                                            },
                                            buttonsStyling: false
                                        });
                                        return false;
                                    }

                                    $('#formPrDetail')[0].reset();
                                    $('#detail_id').val('');
                                    $('#modalTitle').text('Create new entry');
                                    $('#btnSubmitModal').text('Create');
                                    $('#modalPrDetail').modal('show');
                                }
                            },
                            {
                                text: '<i class="ti ti-edit me-1"></i> Edit',
                                className: 'btn btn-warning btn-sm me-2',
                                extend: 'selectedSingle',
                                action: function(e, dt, node, config) {
                                    let data = dt.row({
                                        selected: true
                                    }).data();
                                    let rowIndex = dt.row({
                                        selected: true
                                    }).index();

                                    // 1. Set penanda bahwa ini adalah mode EDIT
                                    window.isEditingMode = true;

                                    $('#detail_id').val(rowIndex);
                                    $('#quantity').val(data.quantity);
                                    $('#unit_id').data('pending-val', data.unit_id);

                                    // 2. Set value produk dan trigger change
                                    $('#product_id').val(data.product_id).trigger('change');

                                    // 3. Set harga unit price asli dari tabel data
                                    $('#unit_price').val(data.unit_price);
                                    $('#discount').val(data.discount || 0); // Jika ada diskon
                                    $('#modalTitle').text('Edit entry');
                                    $('#btnSubmitModal').text('Update');
                                    $('#modalPrDetail').modal('show');
                                }
                            },
                            {
                                text: '<i class="ti ti-trash me-1"></i> Delete',
                                className: 'btn btn-danger btn-sm me-2',
                                extend: 'selected',
                                action: function(e, dt, node, config) {
                                    let rowIndex = dt.row({
                                        selected: true
                                    }).index();
                                    let data = dt.row({
                                        selected: true
                                    }).data();
                                    let name = data.data_produk ? data.data_produk : '';

                                    Swal.fire({
                                        title: 'Are you sure?',
                                        text: "Want to delete data: " + name,
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonText: 'Yes, delete it!',
                                        cancelButtonText: 'Cancel',
                                        customClass: {
                                            confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
                                            cancelButton: 'btn btn-label-secondary waves-effect waves-light'
                                        },
                                        buttonsStyling: false
                                    }).then(function(result) {
                                        if (result.isConfirmed) {
                                            prDetailsData.splice(rowIndex, 1);
                                            dt.clear().rows.add(prDetailsData).draw();
                                            calculateGrandTotal();
                                            calculateTotalOrder()
                                            toastr.success('Deleted Data Successfully',
                                                '', {
                                                    timeOut: 1500,
                                                    progressBar: true
                                                });
                                        }
                                    });
                                }
                            },
                            {
                                text: '<i class="ti ti-refresh me-1"></i> Clear All',
                                className: 'btn btn-secondary btn-sm',
                                action: function(e, dt, node, config) {
                                    prDetailsData = [];
                                    dt.clear().draw();
                                    calculateGrandTotal();
                                    calculateTotalOrder()
                                    $('#percent').val(0);

                                }
                            }
                        ]
                    }
                }
            });

            $('.select2-modal').each(function() {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: $this.attr('data-placeholder'),
                    width: '100%',
                    dropdownParent: $('#modalPrDetail')
                });
            });
            $('#showModalpr').on('click', function(e) {
                e.preventDefault();

                let tbody = $('#requisitionTableBody');

                // Reset checkbox 'Check All' menjadi tidak tercentang saat modal dibuka
                $('#checkAll').prop('checked', false);

                tbody.html(
                    '<tr><td colspan="3" class="text-center"><i class="fa fa-spin fa-spinner me-1"></i> Loading data...</td></tr>'
                );
                $('#modalRequisitionDetail').modal('show');

                $.ajax({
                    url: "{{ route('purchase-order.requisitions.processing') }}",
                    type: "GET",
                    dataType: "json",
                    success: function(response) {
                        tbody.empty();

                        if (response && response.length > 0) {
                            $.each(response, function(key, item) {
                                let dateFormatted = new Date(item.created_at)
                                    .toLocaleDateString('id-ID');

                                // Tambahkan checkbox dengan class 'checkItem' dan value berupa ID data
                                tbody.append(`
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input checkItem" type="checkbox" value="${item.id}">
                                    </div>
                                </td>
                                <td><strong>${item.code}</strong></td>
                                <td>${dateFormatted}</td>
                            </tr>
                        `);
                            });
                        } else {
                            tbody.html(
                                '<tr><td colspan="3" class="text-center text-muted">No processing data found.</td></tr>'
                            );
                        }
                    },
                    error: function(xhr) {
                        tbody.html(
                            '<tr><td colspan="3" class="text-center text-danger">Failed to fetch data.</td></tr>'
                        );
                    }
                });
            });

            $('#formPrDetail').on('submit', function(e) {
                e.preventDefault();

                let BankID = $('#nama_bank').val();
                let namaBank = $('#nama_bank option:selected').text();
                let nomorRekening = $('#nomor_rekening').val();
                let namaRekening = $('#nama_rekening').val();
                let detailId = $('#detail_id').val();
                if (!namaBank || !nomorRekening) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please fill all required fields! (Bank Name and Account Number)',
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });
                    return false;
                }

                // Validasi Duplikasi
                let isDuplicate = false;
                if (prDetailsData && prDetailsData.length > 0) {
                    for (let i = 0; i < prDetailsData.length; i++) {
                        if (prDetailsData[i].nama_bank == namaBank && prDetailsData[i].nomor_rekening ==
                            nomorRekening) {
                            if (detailId === '') {
                                isDuplicate = true;
                                break;
                            } else if (detailId !== '' && i != detailId) {
                                isDuplicate = true;
                                break;
                            }
                        }
                    }
                }

                if (isDuplicate) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Bank Account Already Exists!',
                        html: `The bank account <b>"${namaBank}"</b> with number <b>"${nomorRekening}"</b> is already registered.<br>Please edit the item if you want to change it.`,
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });
                    return false;
                }
                let itemData = {
                    'bank_id': BankID, // ✅ untuk DB
                    'nama_bank': namaBank, // ✅ untuk tampilan
                    'nomor_rekening': nomorRekening,
                    'nama_rekening': namaRekening
                };

                if (detailId === '') {
                    prDetailsData.push(itemData);
                } else {
                    prDetailsData[detailId] = itemData;
                }

                table.clear().rows.add(prDetailsData).draw();
                $('#modalPrDetail').modal('hide');
            });

        });
    </script>
@endpush
