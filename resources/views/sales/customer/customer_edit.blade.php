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
        <form id="postForm" name="postForm" method="POST" action="{{ route('customer.update', $customer->id) }}">
            @csrf
            @method('PUT')
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
                                    Contact Information
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-pills-top-term" aria-controls="navs-pills-top-term"
                                    aria-selected="false">
                                    Delivery Information
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
                                                <label for="id_customer" class="form-label">Customer
                                                    ID</label>

                                                <input type="text" id="id_customer" name="id_customer"
                                                    class="form-control" placeholder="Enter Customer ID"
                                                    value="{{ $customer->id_customer }}" readonly>
                                                <span class="error text-danger" id="id_customerError"></span>

                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="nama_customer" class="form-label">Customer
                                                    Name</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-user"></i></span>
                                                    <input type="text" id="nama_customer" name="nama_customer"
                                                        class="form-control" placeholder="Enter Customer Name"
                                                        value="{{ $customer->nama_customer }}">
                                                </div>
                                                <span class="error text-danger" id="nama_customerError"></span>

                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="kategori_customer_id" class="form-label">Category</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-category"></i></span>
                                                    <select name="kategori_customer_id" id="kategori_customer_id"
                                                        class="form-select select2"
                                                        data-placeholder="Select Supplier Category">
                                                        <option></option>
                                                        @foreach ($kategoriCustomer as $kat)
                                                            <option value="{{ $kat->id }}"
                                                                {{ $customer->kategori_customer_id == $kat->id ? 'selected' : '' }}>
                                                                {{ $kat->detail }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <span class="error text-danger" id="kategori_customer_idError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-mail"></i></span>
                                                    <input type="email" id="email" name="email" class="form-control"
                                                        placeholder="Enter Email" value=" {{ $customer->email }}">
                                                </div>
                                                <span class="error text-danger" id="emailError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="notel_bisnis" class="form-label">Phone #1
                                                    Number</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-phone"></i></span>
                                                    <input type="number" id="phone_1" name="phone_1"
                                                        class="form-control" placeholder="Enter Phone Number"
                                                        value="{{ $customer->phone_1 }}">
                                                </div>

                                                <span class="error text-danger" id="phone_1Error"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="no_hp" class="form-label">Phone #2</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-phone"></i></span>
                                                    <input type="number" id="phone_2" name="phone_2"
                                                        class="form-control" placeholder="Enter Phone Number"
                                                        value="{{ $customer->phone_2 }}">
                                                </div>
                                                <span class="error text-danger" id="phone_2Error"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="faximili" class="form-label">Fax Number</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-phone"></i></span>
                                                    <input type="number" id="faximili" name="faximili"
                                                        class="form-control" placeholder="Enter Fax Number"
                                                        value="{{ $customer->faximili }}">
                                                    <span class="error text-danger" id="faximiliError"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="website" class="form-label">Website</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-globe"></i></span>
                                                    <input type="text" id="website" name="website"
                                                        class="form-control" placeholder="Enter Website"
                                                        value="{{ $customer->website }}">
                                                </div>
                                                <span class="error text-danger" id="websiteError"></span>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="row">
                                            <div class="col-md-12 col-sm-12 mb-3">
                                                <label for="alamat_tagihan" class="form-label">Billing Address</label>
                                                <textarea id="alamat_tagihan" name="alamat_tagihan" class="form-control" placeholder="Enter Billing Address">{{ $customer->alamat_tagihan }}</textarea>
                                                <span class="error text-danger" id="alamat_tagihanError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="kota_tagihan" class="form-label">City</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-map"></i></span>
                                                    <input type="text" id="kota_tagihan" name="kota_tagihan"
                                                        class="form-control" placeholder="Enter City"
                                                        value="{{ $customer->kota_tagihan }}">
                                                </div>

                                                <span class="error text-danger" id="kota_tagihanError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="kodepos_tagihan" class="form-label">Postal Code</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-map"></i></span>
                                                    <input type="text" id="kodepos_tagihan" name="kodepos_tagihan"
                                                        class="form-control" placeholder="Enter Postal Code"
                                                        value="{{ $customer->kodepos_tagihan }}">
                                                </div>

                                                <span class="error text-danger" id="kodepos_tagihanError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="provinsi_tagihan" class="form-label">Province</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-map"></i></span>
                                                    <input type="text" id="provinsi_tagihan" name="provinsi_tagihan"
                                                        class="form-control" placeholder="Enter Province"
                                                        value="{{ $customer->provinsi_tagihan }}">
                                                </div>

                                                <span class="error text-danger" id="provinsi_tagihanError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="negara_tagihan" class="form-label">Country</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-map"></i></span>
                                                    <input type="text" id="negara_tagihan" name="negara_tagihan"
                                                        class="form-control" placeholder="Enter Country"
                                                        value="{{ $customer->negara_tagihan }}">
                                                </div>

                                                <span class="error text-danger" id="negara_tagihanError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label class="form-label">Status<small>*</small></label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i
                                                            class="ti ti-creative-commons-nd"></i></span>
                                                    <select name="status" id="status" class="form-control">
                                                        <option value="" selected hidden>Select Status</option>
                                                        <option
                                                            value="1"{{ $customer->status == 1 ? 'selected' : '' }}>
                                                            Active
                                                        </option>
                                                        <option
                                                            value="2"{{ $customer->status == 2 ? 'selected' : '' }}>
                                                            Not
                                                            Active</option>
                                                    </select>
                                                </div>

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
                                                        <div class="input-group input-group-merge">
                                                            <span class="input-group-text"><i
                                                                    class="ti ti-layout-dashboard"></i></span>
                                                            <select name="sapaan" id="sapaan" class="form-select">
                                                                <option value="" selected hidden>Select Salutation
                                                                </option>
                                                                <option value="Mr."
                                                                    {{ optional($kontak)->sapaan === 'Mr.' ? 'selected' : '' }}>
                                                                    Mr.
                                                                </option>
                                                                <option value="Mrs."
                                                                    {{ optional($kontak)->sapaan === 'Mrs.' ? 'selected' : '' }}>
                                                                    Mrs.
                                                                </option>
                                                                <option value="Ms."
                                                                    {{ optional($kontak)->sapaan === 'Ms.' ? 'selected' : '' }}>
                                                                    Ms.
                                                                </option>
                                                            </select>
                                                        </div>

                                                    </div>
                                                    <div class="col-lg-8">
                                                        <div class="input-group input-group-merge">
                                                            <span class="input-group-text"><i
                                                                    class="ti ti-user"></i></span>
                                                            <input type="text" id="contact_person"
                                                                name="contact_person" class="form-control"
                                                                placeholder="Enter Contact Person"
                                                                value="{{ optional($kontak)->contact_person }}">
                                                        </div>

                                                    </div>
                                                </div>
                                                <span class="error text-danger" id="contact_personError"></span>
                                            </div>
                                            <div class="col-md-12 col-sm-12 mb-3">
                                                <label for="posisi_jabatan" class="form-label">Position</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-sitemap"></i></span>
                                                    <input type="text" id="posisi_jabatan" name="posisi_jabatan"
                                                        class="form-control" placeholder="Enter Position"
                                                        value="{{ optional($kontak)->posisi_jabatan }}">
                                                </div>

                                                <span class="error text-danger" id="posisi_jabatanError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="email_kontak" class="form-label">Email</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-mail"></i></span>
                                                    <input type="text" id="email_kontak" name="email_kontak"
                                                        class="form-control" placeholder="Enter Email"
                                                        value="{{ optional($kontak)->email_kontak }}">
                                                </div>
                                                <span class="error text-danger" id="email_kontakError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="phone1_kontak" class="form-label">Phone #1</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-phone"></i></span>
                                                    <input type="text" id="phone1_kontak" name="phone1_kontak"
                                                        class="form-control" placeholder="Enter Phone Number"
                                                        value="{{ optional($kontak)->phone1_kontak }}">
                                                </div>
                                                <span class="error text-danger" id="phone1_kontakError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="phone2_kontak" class="form-label">Phone #2
                                                    Number</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-phone"></i></span>
                                                    <input type="text" id="phone2_kontak" name="phone2_kontak"
                                                        class="form-control" placeholder="Enter Phone Number"
                                                        value="{{ optional($kontak)->phone2_kontak }}">
                                                </div>
                                                <span class="error text-danger" id="phone2_kontakError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="faximili_kontak" class="form-label">Fax Number
                                                </label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-phone"></i></span>
                                                    <input type="number" id="faximili_kontak" name="faximili_kontak"
                                                        class="form-control" placeholder="Enter Fax Number">
                                                </div>
                                                <span class="error text-danger" id="faximili_kontakError"
                                                    value="{{ optional($kontak)->faximili_kontak }}"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="website_kontak" class="form-label">Website
                                                </label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-globe"></i></span>
                                                    <input type="text" id="website_kontak" name="website_kontak"
                                                        class="form-control" placeholder="Enter Website URL"
                                                        value="{{ optional($kontak)->website_kontak }}">
                                                </div>
                                                <span class="error text-danger" id="website_kontakError"></span>
                                            </div>
                                            <div class="col-md-12 col-sm-12 mb-3">
                                                <label for="catatan" class="form-label">Notes</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-notes"></i></span>
                                                    <input type="text" id="catatan" name="catatan"
                                                        class="form-control" placeholder="Enter Notes"
                                                        value="{{ optional($kontak)->catatan }}">
                                                </div>
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
                                            <div class="row mb-3">
                                                <div class="col-sm-9">
                                                    <div class="form-check form-check-primary col-8">
                                                        <input class="form-check-input" type="checkbox" value="1"
                                                            name="default_pengiriman" id="default_pengiriman"
                                                            {{ optional($pengiriman)->default_pengiriman == 1 ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="default_pengiriman">Same as
                                                            billing address</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 col-sm-12 mb-3">
                                                <label for="kota_pengiriman" class="form-label">Delivery Address</label>

                                                <textarea id="alamat_pengiriman" name="alamat_pengiriman" class="form-control" placeholder="Enter Main Address">{{ optional($pengiriman)->alamat_pengiriman }}</textarea>
                                                <span class="error text-danger" id="alamat_pengirimanError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="kota_pengiriman" class="form-label">City</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-map"></i></span>
                                                    <input type="text" id="kota_pengiriman" name="kota_pengiriman"
                                                        class="form-control" placeholder="Enter City"
                                                        value="{{ optional($pengiriman)->kota_pengiriman }}">
                                                </div>

                                                <span class="error text-danger" id="kota_pengirimanError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="kodepos_pengiriman" class="form-label">Postal
                                                    Code</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-map"></i></span>
                                                    <input type="text" id="kodepos_pengiriman"
                                                        name="kodepos_pengiriman" class="form-control"
                                                        placeholder="Enter Postal Code"
                                                        value="{{ optional($pengiriman)->kodepos_pengiriman }}">
                                                </div>

                                                <span class="error text-danger" id="kodepos_pengirimanError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="provinsi_pengiriman" class="form-label">Province</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-map"></i></span>
                                                    <input type="text" id="provinsi_pengiriman"
                                                        name="provinsi_pengiriman" class="form-control"
                                                        placeholder="Enter Province"
                                                        value="{{ optional($pengiriman)->provinsi_pengiriman }}">
                                                </div>

                                                <span class="error text-danger" id="provinsi_pengirimanError"></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="negara_pengiriman" class="form-label">Country</label>
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-map"></i></span>
                                                    <input type="text" id="negara_pengiriman" name="negara_pengiriman"
                                                        class="form-control" placeholder="Enter Country"
                                                        value="{{ optional($pengiriman)->negara_pengiriman }}">
                                                </div>

                                                <span class="error text-danger" id="negara_pengirimanError"></span>
                                            </div>

                                        </div>
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
                                                        name="default_pajak" id="default_pajak"
                                                        {{ optional($pajak)->default_pajak == 1 ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="default_pajak">Default Invoice
                                                        includes Tax</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="basic-default-name">Type ID
                                                Tax</label>
                                            <div class="col-sm-9">
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-category"></i></span>
                                                    <select name="tipe_id_pajak" id="tipe_id_pajak" class="form-select">
                                                        <option value="" selected hidden>Select Type ID Tax</option>
                                                        <option value="NIK"
                                                            {{ optional($pajak)->tipe_id_pajak == 'NIK' ? 'selected' : '' }}>
                                                            NIK
                                                        </option>
                                                        <option value="NPWP"
                                                            {{ optional($pajak)->tipe_id_pajak == 'NPWP' ? 'selected' : '' }}>
                                                            NPWP
                                                        </option>
                                                        <option value="Paspor"
                                                            {{ optional($pajak)->tipe_id_pajak == 'Paspor' ? 'selected' : '' }}>
                                                            Paspor
                                                        </option>
                                                        <option value="Lainnya"
                                                            {{ optional($pajak)->tipe_id_pajak == 'Lainnya' ? 'selected' : '' }}>
                                                            Lainnya
                                                        </option>
                                                    </select>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="basic-default-name">NPWP
                                                Number</label>
                                            <div class="col-sm-9">
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-barcode"></i></span>
                                                    <input type="text" class="form-control" id="nomor_wajib_pajak"
                                                        name="nomor_wajib_pajak" placeholder="Enter NPWP number"
                                                        value="{{ optional($pajak)->nomor_wajib_pajak }}">
                                                </div>

                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="basic-default-name">Taxpayer
                                                Name</label>
                                            <div class="col-sm-9">
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-user"></i></span>
                                                    <input type="text" class="form-control" id="nama_wajib_pajak"
                                                        name="nama_wajib_pajak" placeholder="Enter Taxpayer Name"
                                                        value="{{ optional($pajak)->nama_wajib_pajak }}">
                                                </div>

                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="basic-default-name">ID TKU</label>
                                            <div class="col-sm-9">
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-id-badge-2"></i></span>
                                                    <input type="text" class="form-control" id="id_tku"
                                                        name="id_tku" placeholder="Enter ID TKU"
                                                        value="{{ optional($pajak)->id_tku }}">
                                                </div>

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
                                                        name="check_address" id="check_address"
                                                        {{ optional($pajak)->check_address == 1 ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="check_address">Tax address is
                                                        the same as billing address</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label"
                                                for="basic-default-name">Address</label>
                                            <div class="col-sm-9">
                                                <textarea class="form-control" id="alamat_pajak" name="alamat_pajak" placeholder="Enter Tax Address">{{ optional($pajak)->alamat_pajak }}</textarea>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="basic-default-name">City</label>
                                            <div class="col-sm-9">
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-map"></i></span>
                                                    <input type="text" class="form-control" id="kota_pajak"
                                                        name="kota_pajak" placeholder="Enter City"
                                                        value="{{ optional($pajak)->kota_pajak }}">
                                                </div>

                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label" for="basic-default-name">Postal
                                                Code</label>
                                            <div class="col-sm-9">
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-map"></i></span>
                                                    <input type="text" class="form-control" id="kodepos_pajak"
                                                        name="kodepos_pajak" placeholder="Enter Postal Code"
                                                        value="{{ optional($pajak)->kodepos_pajak }}">
                                                </div>

                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label"
                                                for="basic-default-name">Province</label>
                                            <div class="col-sm-9">
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-map"></i></span>
                                                    <input type="text" class="form-control" id="provinsi_pajak"
                                                        name="provinsi_pajak" placeholder="Enter Province"
                                                        value="{{ optional($pajak)->provinsi_pajak }}">
                                                </div>

                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-sm-3 col-form-label"
                                                for="basic-default-name">Country</label>
                                            <div class="col-sm-9">
                                                <div class="input-group input-group-merge">
                                                    <span class="input-group-text"><i class="ti ti-map"></i></span>
                                                    <input type="text" class="form-control" id="negara_pajak"
                                                        name="negara_pajak" placeholder="Enter Country"
                                                        value="{{ optional($pajak)->negara_pajak }}">
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('customer.index') }}" type="button" class="btn btn-label-secondary waves-effect">
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
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            function toggleDelivery() {
                const isDelivery = $('#default_pengiriman').is(':checked');
                const alamat = $('#alamat_tagihan').val().trim();

                // ❌ VALIDASI: kalau checkbox dicentang tapi alamat kosong
                if (isDelivery && alamat === '') {

                    // balikin ke unchecked
                    $('#default_pengiriman').prop('checked', false);

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
                if (isDelivery) {


                    $('#alamat_pengiriman, #kota_pengiriman, #kodepos_pengiriman, #provinsi_pengiriman, #negara_pengiriman')
                        .prop('readonly', true);

                    $('#alamat_pengiriman').val($('#alamat_tagihan').val());
                    $('#kota_pengiriman').val($('#kota_tagihan').val());
                    $('#kodepos_pengiriman').val($('#kodepos_tagihan').val());
                    $('#provinsi_pengiriman').val($('#provinsi_tagihan').val());
                    $('#negara_pengiriman').val($('#negara_tagihan').val());

                }
                // ❌ KALAU UNCHECKED
                else {

                    $('#alamat_pengiriman, #kota_pengiriman, #kodepos_pengiriman, #provinsi_pengiriman, #negara_pengiriman')
                        .prop('readonly', false)
                        .val('');
                }
            }

            function toggleAddress() {
                const isChecked = $('#check_address').is(':checked');
                const alamat = $('#alamat_tagihan').val().trim();

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

                    $('#alamat_pajak').val($('#alamat_tagihan').val());
                    $('#kota_pajak').val($('#kota_tagihan').val());
                    $('#kodepos_pajak').val($('#kodepos_tagihan').val());
                    $('#provinsi_pajak').val($('#provinsi_tagihan').val());
                    $('#negara_pajak').val($('#negara_tagihan').val());

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
            toggleDelivery();

            // 🎯 saat checkbox berubah
            $('#check_address').on('change', function() {
                toggleAddress();
            });

            $('#default_pengiriman').on('change', function() {
                toggleDelivery();
            });

        });
        $(document).ready(function() {


            $('#postForm').on('submit', function(e) {
                e.preventDefault();
                var form = this;
                let formData = new FormData(form);
                $.ajax({
                    url: $(form).attr('action'),
                    method: $(form).attr('method'),
                    data: formData, // ✅ pakai ini
                    processData: false,
                    contentType: false,
                    dataType: 'json', // ✅ FIX typo
                    global: false,
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
        });
    </script>
@endpush
