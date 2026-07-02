@extends('layouts.app')
@section('title', 'Detail Permintaan Pembelian')
@push('style')
    <style>
        .company-title {
            font-size: 26pt;
            font-weight: bold;
            text-align: right;
        }

        .company-address {
            font-size: 12pt;
            text-align: right;
        }
    </style>
@endpush
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
    @php
        switch ($model->status) {
            case 'draft':
                $badge = '<span class="badge bg-secondary"><i class="ti ti-file me-1"></i>Draft</span>';
                break;

            case 'pending':
                $badge = '<span class="badge bg-warning"><i class="ti ti-clock me-1"></i>Pending</span>';
                break;

            case 'processing':
                $badge = '<span class="badge bg-info"><i class="ti ti-loader me-1"></i>Processing</span>';
                break;

            case 'partial':
                $badge = '<span class="badge bg-primary"><i class="ti ti-package me-1"></i>Partial</span>';
                break;

            case 'approved':
                $badge = '<span class="badge bg-success"><i class="ti ti-check me-1"></i>Approved</span>';
                break;

            case 'rejected':
                $badge = '<span class="badge bg-danger"><i class="ti ti-x me-1"></i>Rejected</span>';
                break;

            case 'closed':
                $badge = '<span class="badge bg-dark"><i class="ti ti-lock me-1"></i>Closed</span>';
                break;

            default:
                $badge = '<span class="badge bg-secondary">N/A</span>';
                break;
        }
    @endphp
    <div class="row">
        <div class="col-xl-12">
            <div class="nav-align-left mb-4">
                <ul class="nav nav-pills me-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                            data-bs-target="#navs-pills-left-home" aria-controls="navs-pills-left-home"
                            aria-selected="true">
                            <i class="ti ti-file-text"></i>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                            data-bs-target="#navs-pills-left-profile" aria-controls="navs-pills-left-profile"
                            aria-selected="false" tabindex="-1">
                            <i class="ti ti-info-circle"></i>
                        </button>
                    </li>

                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="navs-pills-left-home" role="tabpanel">
                        <div class="table-responsive p-3">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <img src="{{ asset('image/logo/logo_print.png') }}" style="width: 550px;" height="200px"
                                        alt="Logo Perusahaan">
                                </div>
                                <div class="col-md-6">
                                    <div class="company-title">{{ $company->nama_perusahaan }}</div>
                                    <div class="company-address">{{ $company->alamat }}</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Kepada </h6>
                                    <div class="form-textarea-mock">
                                        <strong>{{ $model->customerID->nama_customer ?? '' }}</strong>
                                    </div>
                                    <span> {{ $model->address }}</span>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <span>Nomor : {{ $model->sales_quotation_code }}</span>
                                        <span>Tanggal : {{ date('d M Y', strtotime($model->sales_quotation_date)) }}</span>
                                        <span>Pembayaran : {{ $model->paymentTermID?->nama ?? '-' }}</span>
                                    </div>

                                </div>
                            </div>
                            <table class="table table-bordered nowrap mt-5" id="table" style="width:100%">
                                <thead class="border-top" style="background-color: #AEDEFC;">
                                    <thead class="border-top" style="background-color: #AEDEFC; ">
                                        <tr>
                                            <th>#</th>
                                            <th>Item</th>
                                            <th>Qty</th>
                                            <th>Unit</th>
                                            <th>Unit Price</th>
                                            <th>Disc</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                </thead>
                                <tbody>
                                    @forelse ($model->details as $index => $item)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>{{ $item->produkID->nama_barang }}</td>
                                            <td class="text-center">{{ $item->qty }}</td>
                                            <td>{{ $item->unitID->nama_unit }}</td>
                                            <td class="text-end">
                                                {{ format_uang(convert_currency($item->unit_price, $model->currency_id ?? 1)) }}
                                            </td>
                                            <td class="text-end">
                                                {{ format_uang(convert_currency($item->discount, $model->currency_id ?? 1)) }}
                                            </td>
                                            <td class="text-end">
                                                {{ format_uang(convert_currency($item->amount, $model->currency_id ?? 1)) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center" style="color: #999; padding: 20px;">No
                                                items found in this
                                                request.</td>
                                        </tr>
                                    @endforelse
                                    <tr>
                                        <td colspan="5"></td>
                                        <td style="text-align: right !important;"><strong> Sub Total :</strong></td>
                                        <td> {{ isset($model) ? format_uang(convert_currency($model->sub_total, $item->currency_id ?? 1)) : '' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5"></td>
                                        <td style="text-align: right !important;"><strong>Discount :</strong></td>
                                        <td> {{ isset($model) ? format_uang(convert_currency($model->disc_nominal, $item->currency_id ?? 1)) : '' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5"></td>
                                        <td style="text-align: right !important;"><strong>Tax (11%) :</strong></td>
                                        <td> {{ isset($model) ? format_uang(convert_currency($model->tax_amount, $detail->currency_id ?? 1)) : '' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="5"></td>
                                        <td style="text-align: right !important;"><strong>Grand Total :</strong></td>
                                        <td> {{ isset($model) ? format_uang(convert_currency($model->grand_total, $item->currency_id ?? 1)) : '' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="w-100 footer-table">
                                <tr>
                                    <td class="keterangan-box">
                                        @php
                                            $currencyId =
                                                session('currency_id') ??
                                                \App\Models\Setting\Company::first()->default_currency_id;
                                            $currencyCode =
                                                \App\Models\Setting\Currency::find($currencyId)?->code ?? 'IDR';

                                            // Gunakan nilai asli (jangan di-round agar sen tidak hilang)
                                            $grandTotalConvert = convert_currency(
                                                $model->grand_total,
                                                $model->currency_id ?? 1,
                                            );
                                        @endphp
                                        <div>Terbilang: {{ terbilang($grandTotalConvert, $currencyCode) }}</div>
                                    </td>
                                </tr>
                            </table>
                            <div class="row mt-5">
                                <div class="col-md-6">
                                    <div class="mb-3 row">
                                        <label class="col-md-4 col-form-label">Description</label>
                                        <div class="col-md-8">
                                            {!! nl2br(e($model->description)) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    @if ($model->status == 'processing')
                                        <div class="approval-title">
                                            Disetujui Oleh
                                        </div>
                                        <div style="height: 65px;">
                                            <img src="{{ asset('image/logo/STEMPEL.png') }}" style="height: 80px;">
                                        </div>
                                        <div style="font-weight: bold; text-decoration: underline;">
                                            Yohanes Lukman
                                        </div>
                                    @else
                                        <div class="approval-title">
                                            Dibuat oleh,
                                        </div>
                                        <div style="height: 65px;">
                                            <img src="{{ asset('image/logo/69fd6d6ab719c1778216298.png') }}"
                                                style="height: 80px;">
                                        </div>
                                        <div style="font-weight: bold; text-decoration: underline;">
                                            {{ $model->creator->fullname }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="navs-pills-left-profile" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <h3><i class="ti ti-bookmarks me-2"></i>Requisition Information</h3>
                                <div class="col-lg-12">
                                    <div class="demo-inline-spacing mt-3">
                                        <ul class="list-group">
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Status
                                                {!! $badge !!}
                                            </li>
                                            <li class="list-group-item disabled">
                                                <span style="float: right; font-size: 0.85em; color: #6c757d;">
                                                    Created by: {{ $model->creator->fullname ?? 'System' }} |
                                                    Created at:
                                                    {{ $model->created_at ? $model->created_at->format('d/m/Y H:i') : '-' }}
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h3><i class="ti ti-clipboard-text me-2"></i>Proceed by</h3>

                                @php
                                    // 1. Kumpulkan semua PO unik dari seluruh detail
                                    $uniquePOs = $model->details->flatMap->salesOrderDetails
                                        ->pluck('salesOrder')
                                        ->unique('id');
                                @endphp

                                @foreach ($uniquePOs as $so)
                                    <div class="card col-md-12 mb-3 p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div style="color: #007bff; font-weight: bold;">
                                                    {{ $so->sales_order_code }}
                                                </div>
                                                <div style="font-size: 0.9rem; color: #666;">
                                                    {{ \Carbon\Carbon::parse($so->sales_order_date)->format('d/m/Y') }}
                                                </div>
                                            </div>

                                            <a href="{{ route('sales-order.print', $so->id) }}"
                                                class="btn btn-sm btn-icon" data-bs-toggle="tooltip" target="_blank"
                                                data-bs-placement="top" data-bs-original-title="Lihat SO">
                                                <i class="ti ti-send"></i> </a>
                                        </div>
                                    </div>
                                @endforeach

                                @if ($uniquePOs->isEmpty())
                                    <div class="text-muted italic">Belum ada SO yang dibuat.</div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
{{-- @push('scripts')
        <script>
            let table = new DataTable('#table');
        </script>
    @endpush --}}
