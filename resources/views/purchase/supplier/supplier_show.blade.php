@extends('layouts.app')
@section('title', $title)
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
        <div class="card-body table-responsive p-3">
            {{-- <div class="row"> --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="divider my-4">
                        <div class="divider-text"><span class="badge bg-success">General Information</span></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6 col-sm-12 mb-3">
                                    <label for="id_supplier" class="form-label">Supplier
                                        ID</label>
                                    <input type="text" id="id_supplier" name="id_supplier" class="form-control"
                                        placeholder="" value="{{ $supplier->id_supplier }}" disabled>
                                </div>
                                <div class="col-md-6 col-sm-12 mb-3">
                                    <label for="nama_supplier" class="form-label">Supplier
                                        Name</label>
                                    <input type="text" id="nama_supplier" name="nama_supplier" class="form-control"
                                        placeholder="" value="{{ $supplier->nama_supplier }}" disabled>
                                </div>
                                <div class="col-md-6 col-sm-12 mb-3">
                                    <label for="nama_supplier" class="form-label">Category</label>
                                    <select name="kategori_supplier_id" id="kategori_supplier_id" class="form-control"
                                        data-placeholder="Select Supplier Category" disabled>
                                        <option></option>
                                        @foreach ($kategoriPemasok as $kat)
                                            <option value="{{ $kat->id }}"
                                                {{ $supplier->kategori_supplier_id == $kat->id ? 'selected' : '' }}>
                                                {{ $kat->detail }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 col-sm-12 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="text" id="email" name="email" class="form-control" placeholder=""
                                        value="{{ $supplier->email }}" disabled>
                                </div>
                                <div class="col-md-6 col-sm-12 mb-3">
                                    <label for="phone_1" class="form-label">Phone #1
                                        Number</label>
                                    <input type="number" id="phone_1" name="phone_1" class="form-control" placeholder=""
                                        value="{{ $supplier->phone_1 }}" disabled>
                                </div>
                                <div class="col-md-6 col-sm-12 mb-3">
                                    <label for="phone_2" class="form-label">Phone #2</label>
                                    <input type="number" id="phone_2" name="phone_2" class="form-control" placeholder=""
                                        value="{{ $supplier->phone_2 }}" disabled>
                                </div>
                                <div class="col-md-6 col-sm-12 mb-3">
                                    <label for="faximili" class="form-label">Fax Number</label>
                                    <input type="number" id="faximili" name="faximili" class="form-control" placeholder=""
                                        value="{{ $supplier->faximili }}" disabled>
                                </div>
                                <div class="col-md-6 col-sm-12 mb-3">
                                    <label for="website" class="form-label">Website</label>
                                    <input type="text" id="website" name="website" class="form-control" placeholder=""
                                        value="{{ $supplier->website }}" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12 col-sm-12 mb-3">
                                    <label for="alamat_pembayaran" class="form-label">Billing Address</label>
                                    <textarea id="alamat_pembayaran" name="alamat_pembayaran" class="form-control" placeholder=" " disabled>{{ $supplier->alamat_pembayaran }}</textarea>
                                </div>
                                <div class="col-md-6 col-sm-12 mb-3">
                                    <label for="kota" class="form-label">City</label>
                                    <input type="text" id="kota" name="kota" class="form-control" placeholder=" "
                                        value="{{ $supplier->kota }}" disabled>
                                </div>
                                <div class="col-md-6 col-sm-12 mb-3">
                                    <label for="kodepos" class="form-label">Postal Code</label>
                                    <input type="text" id="kodepos" name="kodepos" class="form-control"
                                        placeholder="" value="{{ $supplier->kodepos }}" disabled>
                                </div>
                                <div class="col-md-6 col-sm-12 mb-3">
                                    <label for="provinsi" class="form-label">Province</label>
                                    <input type="text" id="provinsi" name="provinsi" class="form-control"
                                        placeholder="" value="{{ $supplier->provinsi }}" disabled>
                                </div>
                                <div class="col-md-6 col-sm-12 mb-3">
                                    <label for="negara" class="form-label">Country</label>
                                    <input type="text" id="negara" name="negara" class="form-control"
                                        placeholder="" value="{{ $supplier->negara }}" disabled>
                                </div>
                                <div class="col-md-6 col-sm-12 mb-3">
                                    <label class="form-label">Supplier Type</label>
                                    <select name="tipe_pemasok_id" id="tipe_pemasok_id" class="form-control"
                                        data-placeholder="Select Supplier Type" disabled>
                                        <option>
                                        </option>
                                        <option value="Perorangan"
                                            {{ $supplier->tipe_pemasok_id === 'Perorangan' ? 'selected' : '' }}>
                                            Perorangan</option>
                                        <option value="Perusahaan"
                                            {{ $supplier->tipe_pemasok_id === 'Perusahaan' ? 'selected' : '' }}>
                                            Perusahaan</option>
                                        <option value="Pemerintah"
                                            {{ $supplier->tipe_pemasok_id === 'Pemerintah' ? 'selected' : '' }}>
                                            Pemerintah</option>
                                    </select>
                                </div>
                                <div class="col-md-6 col-sm-12 mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" id="status" class="form-control"
                                        data-placeholder="Select Status" disabled>
                                        <option></option>
                                        <option value="1" {{ $supplier->status == '1' ? 'selected' : '' }}>
                                            Active</option>
                                        <option value="2" {{ $supplier->status == '2' ? 'selected' : '' }}>
                                            Not Active</option>
                                    </select>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="col-md-12">
                    <div class="divider my-4">
                        <div class="divider-text"><span class="badge bg-success">Contact</span></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-sm-12 mb-3">
                            <label for="contact_person" class="form-label">Fullname</label>
                            <div class="row">
                                <div class="col-lg-12">
                                    <input type="text" id="contact_person" name="contact_person" class="form-control"
                                        placeholder=""
                                        value="{{ isset($kontak) ? $kontak->sapaan : '' }} {{ isset($kontak) ? $kontak->contact_person : '' }}"
                                        disabled>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-12 mb-3">
                            <label for="posisi_jabatan" class="form-label">Position</label>
                            <input type="text" id="posisi_jabatan" name="posisi_jabatan" class="form-control"
                                placeholder="" value="{{ isset($kontak) ? $kontak->posisi_jabatan : '' }}" disabled>
                        </div>
                        <div class="col-md-3 col-sm-12 mb-3">
                            <label for="email_kontak" class="form-label">Email</label>
                            <input type="text" id="email_kontak" name="email_kontak" class="form-control"
                                placeholder="" value="{{ isset($kontak) ? $kontak->email_kontak : '' }}" disabled>
                        </div>
                        <div class="col-md-3 col-sm-12 mb-3">
                            <label for="phone1_kontak" class="form-label">Phone #1</label>
                            <input type="text" id="phone1_kontak" name="phone1_kontak" class="form-control"
                                placeholder="" value="{{ isset($kontak) ? $kontak->phone1_kontak : '' }}"disabled>
                        </div>
                        <div class="col-md-3 col-sm-12 mb-3">
                            <label for="phone1_kontak" class="form-label">Phone #2
                                Number</label>
                            <input type="text" id="phone1_kontak" name="phone1_kontak" class="form-control"
                                placeholder="" value="{{ isset($kontak) ? $kontak->phone1_kontak : '' }}" disabled>
                        </div>
                        <div class="col-md-3 col-sm-12 mb-3">
                            <label for="faximili_kontak" class="form-label">Fax Number
                            </label>
                            <input type="number" id="faximili_kontak" name="faximili_kontak" class="form-control"
                                placeholder="" value="{{ isset($kontak) ? $kontak->faximili_kontak : '' }}" disabled>
                        </div>
                        <div class="col-md-3 col-sm-12 mb-3">
                            <label for="website_kontak" class="form-label">Website
                            </label>
                            <input type="text" id="website_kontak" name="website_kontak" class="form-control"
                                placeholder="" value="{{ isset($kontak) ? $kontak->website_kontak : '' }}" disabled>
                        </div>
                        <div class="col-md-12 col-sm-12 mb-3">
                            <label for="catatan" class="form-label">Notes</label>
                            <input type="text" id="catatan" name="catatan" class="form-control" placeholder=""
                                value="{{ isset($kontak) ? $kontak->catatan : '' }}" disabled>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="row">
                    <div class="col-md-12">
                        <div class="divider my-4">
                            <div class="divider-text"> <span class="badge bg-success">Payment & Bank</span></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-sm-12">
                                <div class="divider my-4">
                                    <div class="divider-text">Payment Detail</div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-sm-12 mb-3">
                                        <label class="form-label">Payment Term</label>
                                        <select name="payment_term" id="payment_term" class="form-control "
                                            data-placeholder="Select Payment Term" disabled>
                                            <option></option>
                                            @foreach ($paymentTerm as $term)
                                                <option value="{{ $term->id }}"
                                                    {{ $pembelian?->payment_term == $term->id ? 'selected' : '' }}>
                                                    {{ $term->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 col-sm-12 mb-3">
                                        <label for="discount" class="form-label">Discount</label>
                                        <input type="number" id="discount" name="discount" class="form-control"
                                            placeholder="" value="{{ isset($pembelian) ? $pembelian->discount : '' }}"
                                            disabled>
                                    </div>
                                    <div class="col-md-12 col-sm-12 mb-3">
                                        <label for="default_deskripsi" class="form-label">Description</label>
                                        <textarea type="text" id="default_deskripsi" name="default_deskripsi" class="form-control"
                                            placeholder="Enter Description" disabled>{{ isset($pembelian) ? $pembelian->default_deskripsi : '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="divider my-4">
                                    <div class="divider-text">
                                        Bank Detail
                                    </div>
                                </div>
                                <table class="table display responsive nowrap" id="table">
                                    <thead class="border-top" style="background-color: #AEDEFC; ">
                                        <tr>
                                            <th>Account Number</th>
                                            <th>Account Name</th>
                                            <th>Bank</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rekening as $rek)
                                            <tr>
                                                <td>{{ $rek->nomor_rekening }}</td>
                                                <td>{{ $rek->nama_rekening }}</td>
                                                <td>{{ $rek->nama_bank }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3">No Data Found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="divider my-4">
                        <div class="divider-text"><span class="badge bg-success">Tax Information</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label" for="basic-default-name">Tax</label>
                                <div class="col-sm-9">
                                    <div class="form-check form-check-primary col-8">
                                        <input class="form-check-input" type="checkbox" value="1"
                                            name="default_pajak" id="default_pajak"
                                            {{ $pajak?->default_pajak == 1 ? 'checked' : '' }} disabled>
                                        <label class="form-check-label" for="default_pajak">Default Invoice
                                            includes Tax</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label" for="basic-default-name">Type ID
                                    Tax</label>
                                <div class="col-sm-9">
                                    <select name="tipe_id_pajak" id="tipe_id_pajak" class="form-control" disabled>
                                        <option value=""></option>
                                        <option value="NIK" {{ $pajak?->tipe_id_pajak == 'NIK' ? 'selected' : '' }}>NIK
                                        </option>
                                        <option value="NPWP" {{ $pajak?->tipe_id_pajak == 'NPWP' ? 'selected' : '' }}>
                                            NPWP
                                        </option>
                                        <option value="Paspor" {{ $pajak?->tipe_id_pajak == 'Paspor' ? 'selected' : '' }}>
                                            Paspor
                                        </option>
                                        <option value="Lainnya"
                                            {{ $pajak?->tipe_id_pajak == 'Lainnya' ? 'selected' : '' }}>
                                            Lainnya
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label" for="basic-default-name">NPWP
                                    Number</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="nomor_wajib_pajak"
                                        name="nomor_wajib_pajak" placeholder=""
                                        value="{{ isset($pajak) ? $pajak->nomor_wajib_pajak : '' }}" disabled>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label" for="basic-default-name">Taxpayer
                                    Name</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="nama_wajib_pajak"
                                        name="nama_wajib_pajak" placeholder=""
                                        value="{{ isset($pajak) ? $pajak->nama_wajib_pajak : '' }}" disabled>

                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label" for="basic-default-name">ID TKU</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="id_tku" name="id_tku"
                                        placeholder="" value="{{ isset($pajak) ? $pajak->id_tku : '' }}" disabled>
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
                                            {{ $pajak?->check_address == 1 ? 'checked' : '' }} disabled>
                                        <label class="form-check-label" for="check_address">Tax address is
                                            the same as payment address</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label" for="basic-default-name">Address</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" id="alamat_pajak" name="alamat_pajak" placeholder="Enter Tax Address" disabled>{{ isset($pajak) ? $pajak->alamat_pajak : '' }}</textarea>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label" for="basic-default-name">City</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="kota_pajak" name="kota_pajak"
                                        placeholder="" value="{{ isset($pajak) ? $pajak->kota_pajak : '' }}" disabled>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label" for="basic-default-name">Postal
                                    Code</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="kodepos_pajak" name="kodepos_pajak"
                                        placeholder="" value="{{ isset($pajak) ? $pajak->kodepos_pajak : '' }}" disabled>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label" for="basic-default-name">Province</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="provinsi_pajak" name="provinsi_pajak"
                                        placeholder="" value="{{ isset($pajak) ? $pajak->provinsi_pajak : '' }}"
                                        disabled>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label" for="basic-default-name">Country</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="negara_pajak" name="negara_pajak"
                                        placeholder="" value="{{ isset($pajak) ? $pajak->negara_pajak : '' }}" disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">

                </div>
                {{-- </div> --}}
            </div>
        @endsection
