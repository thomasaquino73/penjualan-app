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
            <div class="row">
                <div class="col-lg-6 col-md-12">
                    <div class="divider my-4">
                        <div class="divider-text">General Information</div>
                    </div>
                    <div class="row">

                        <div class="col-sm-6 mb-3">
                            <small class="text-muted d-block">
                                Customer Code
                            </small>
                            <span class="fw-semibold">
                                {{ $customer->id_customer ?? '-' }}
                            </span>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <small class="text-muted d-block">
                                Customer Name
                            </small>
                            <span class="fw-semibold">
                                {{ $customer->nama_customer ?? '-' }}
                            </span>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <small class="text-muted d-block">
                                Category
                            </small>
                            <span class="fw-semibold">
                                {{ $customer->kategoriCustomer->detail ?? '-' }}
                            </span>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <small class="text-muted d-block">
                                Email
                            </small>
                            <span class="fw-semibold">
                                {{ $customer->email ?? '-' }}
                            </span>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <small class="text-muted d-block">
                                Phone Number #1
                            </small>
                            <span class="fw-semibold">
                                {{ $customer->phone_1 ?? '-' }}
                            </span>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <small class="text-muted d-block">
                                Phone Number #2
                            </small>
                            <span class="fw-semibold">
                                {{ $customer->phone_2 ?? '-' }}
                            </span>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <small class="text-muted d-block">
                                Fax Number
                            </small>
                            <span class="fw-semibold">
                                {{ $customer->faximili ?? '-' }}
                            </span>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <small class="text-muted d-block">
                                Website
                            </small>
                            <span class="fw-semibold">
                                {{ $customer->website ?? '-' }}
                            </span>
                        </div>
                        <div class="col-sm-12 mb-3">
                            <small class="text-muted d-block">
                                Billing Address
                            </small>
                            <span class="fw-semibold">
                                {{ $customer->alamat_tagihan ?? '-' }}
                            </span>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <small class="text-muted d-block">
                                City
                            </small>
                            <span class="fw-semibold">
                                {{ $customer->kota_tagihan ?? '-' }}
                            </span>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <small class="text-muted d-block">
                                Province
                            </small>
                            <span class="fw-semibold">
                                {{ $customer->provinsi_tagihan ?? '-' }}
                            </span>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <small class="text-muted d-block">
                                Country
                            </small>
                            <span class="fw-semibold">
                                {{ $customer->negara_tagihan ?? '-' }}
                            </span>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <small class="text-muted d-block">
                                Postal Code
                            </small>
                            <span class="fw-semibold">
                                {{ $customer->kodepos_tagihan ?? '-' }}
                            </span>
                        </div>
                    </div>

                </div>
                <div class="col-lg-6 col-md-12">
                    <div class="divider my-4">
                        <div class="divider-text">Contact Information</div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <small class="text-muted d-block">
                                Fullname
                            </small>
                            <span class="fw-semibold">
                                {{ $kontak->sapaan ?? '' }} {{ $kontak->contact_person ?? '-' }}
                            </span>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <small class="text-muted d-block">
                                Position
                            </small>
                            <span class="fw-semibold">
                                {{ $kontak->posisi_jabatan ?? '-' }}
                            </span>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <small class="text-muted d-block">
                                Email
                            </small>
                            <span class="fw-semibold">
                                {{ $kontak->email_kontak ?? '-' }}
                            </span>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <small class="text-muted d-block">
                                Phone Number #1
                            </small>
                            <span class="fw-semibold">
                                {{ $kontak->phone1_kontak ?? '-' }}
                            </span>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <small class="text-muted d-block">
                                Phone Number #2
                            </small>
                            <span class="fw-semibold">
                                {{ $kontak->phone2_kontak ?? '-' }}
                            </span>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <small class="text-muted d-block">
                                Fax Number
                            </small>
                            <span class="fw-semibold">
                                {{ $kontak->faximili_kontak ?? '-' }}
                            </span>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <small class="text-muted d-block">
                                Website
                            </small>
                            <span class="fw-semibold">
                                {{ $kontak->website_kontak ?? '-' }}
                            </span>
                        </div>
                        <div class="col-sm-12 mb-3">
                            <small class="text-muted d-block">
                                Notes
                            </small>
                            <span class="fw-semibold">
                                {{ $kontak->catatan ?? '-' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12 col-md-12">
                    <div class="divider my-4">
                        <div class="divider-text">Delivery Information</div>
                    </div>
                    <table class="table display responsive nowrap" id="table">
                        <thead class="border-top" style="background-color: #AEDEFC; ">
                            <tr>
                                <th>Name</th>
                                <th>Phone Number</th>
                                <th>Street</th>
                                <th>City</th>
                                <th>Province</th>
                                <th>Country</th>
                                <th>Zipcode</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($delivery as $delivery)
                                <tr>
                                    <td>{{ $delivery->nama_penerima ?? '-' }}</td>
                                    <td>{{ $delivery->handphone_penerima ?? '-' }}</td>
                                    <td>{{ $delivery->alamat_pengiriman ?? '-' }}</td>
                                    <td>{{ $delivery->kota_pengiriman ?? '-' }}</td>
                                    <td>{{ $delivery->provinsi_pengiriman ?? '-' }}</td>
                                    <td>{{ $delivery->negara_pengiriman ?? '-' }}</td>
                                    <td>{{ $delivery->kodepos_pengiriman ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No delivery information available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="col-lg-12 col-md-12">
                    <div class="divider my-4">
                        <div class="divider-text">Tax Information</div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <small class="text-muted d-block">
                                        Type ID Tax
                                    </small>
                                    <span class="fw-semibold">
                                        {{ $pajak->tipe_id_pajak ?? '-' }}
                                    </span>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <small class="text-muted d-block">
                                        Taxpayer Number
                                    </small>
                                    <span class="fw-semibold">
                                        {{ $pajak->nomor_wajib_pajak ?? '-' }}
                                    </span>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <small class="text-muted d-block">
                                        Taxpayer Name
                                    </small>
                                    <span class="fw-semibold">
                                        {{ $pajak->nama_wajib_pajak ?? '-' }}
                                    </span>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <small class="text-muted d-block">
                                        ID TKU
                                    </small>
                                    <span class="fw-semibold">
                                        {{ $pajak->id_tku ?? '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <div class="row">
                                <div class="col-sm-12 mb-3">
                                    <small class="text-muted d-block">
                                        Address
                                    </small>
                                    <span class="fw-semibold">
                                        {{ $pajak->alamat_pajak ?? '-' }}
                                    </span>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <small class="text-muted d-block">
                                        City
                                    </small>
                                    <span class="fw-semibold">
                                        {{ $pajak->kota_pajak ?? '-' }}
                                    </span>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <small class="text-muted d-block">
                                        Province
                                    </small>
                                    <span class="fw-semibold">
                                        {{ $pajak->provinsi_pajak ?? '-' }}
                                    </span>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <small class="text-muted d-block">
                                        Country
                                    </small>
                                    <span class="fw-semibold">
                                        {{ $pajak->negara_pajak ?? '-' }}
                                    </span>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <small class="text-muted d-block">
                                        Postal Code
                                    </small>
                                    <span class="fw-semibold">
                                        {{ $pajak->kodepos_pajak ?? '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
            <div class="card-footer">

            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        let table = new DataTable('#table', {
            responsive: true
        });
    </script>
@endpush
