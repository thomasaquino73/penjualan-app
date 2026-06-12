@extends('layouts.app')
@section('title', 'Detail Permintaan Pembelian')
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
                            <table class="top-layout">
                                <tr>
                                    <td style="width: 50%;">
                                        <table style="width: 100%;">
                                            <tr>
                                                <td style="width: 75px; padding-right: 10px;">
                                                    @if (isset($company) && $company->logo)
                                                        <img src="{{ $logoBase64 }}" style="height: 80px;">
                                                    @else
                                                        <div
                                                            style="width: 70px; height: 70px; border: 1px dashed #ccc; background: #fafafa; text-align: center; line-height: 70px; color: #aaa; font-size: 8pt;">
                                                            No Logo
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="company-details">
                                                    <div class="company-name">{{ $company->nama_perusahaan }}</div>
                                                    <div class="company-info">
                                                        {{ $company->alamat }}<br>
                                                        {{ $company->negara }} {{ $company->kodepos ?? '16424' }}<br>
                                                        {{ $company->nomor_telepon }}<br>
                                                        {{ $company->email }}<br>
                                                        <span style="color: #3085d6;">{{ $company->website }}</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>

                                    <td style="width: 5%;"></td>

                                    <td style="width: 45%;">
                                        <table style="width: 100%;">
                                            <tr>
                                                <td style="width: 50%; padding-right: 10px;">
                                                    <div class="form-group-box">
                                                        <div class="form-label"><strong>SQ Number :</strong></div>
                                                        <div class="form-input-mock">{{ $model->sales_quotation_code }}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td style="width: 50%;">
                                                    <div class="form-group-box">
                                                        <div class="form-label"><strong>SQ Date :</strong></div>
                                                        <div class="form-input-mock">
                                                            {{ Carbon\Carbon::parse($model->sales_quotation_date)->format('d-m-Y') }}
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <div class="form-group-box" style="margin-top: 5px;">
                                                        <div class="form-label"><strong>Description :</strong></div>
                                                        <div class="form-textarea-mock">{{ $model->description ?? '' }}
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
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
                                </tbody>
                            </table>
                            <div class="row mt-5">
                                <div class="col-md-6">
                                    <h6><strong>Additional Information</strong></h6>
                                    <div class="mb-3 row">
                                        <label class="col-md-4 col-form-label">Payment Term</label>
                                        <div class="col-md-8">
                                            <div class="input-group input-group-merge">
                                                <select name="payment_term_id" id="payment_term_id" class="form-control"
                                                    disabled>
                                                    <option></option>
                                                    @foreach ($paymentTerm as $pay)
                                                        <option value="{{ $pay->id }}"
                                                            {{ $model->payment_term_id == $pay->id ? 'selected' : '' }}>
                                                            {{ $pay->nama }}
                                                        </option>
                                                    @endforeach
                                                    <option></option>
                                                </select>
                                            </div>
                                            <span class="error text-danger" id="payment_term_idError"></span>

                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label class="col-md-4 col-form-label">Address</label>
                                        <div class="col-md-8">
                                            <div class="input-group input-group-merge">

                                                <textarea name="address" id="address" class="form-control" placeholder="Enter address" disabled>{{ $model->address ?? '' }}</textarea>
                                            </div>
                                            <span class="error text-danger" id="addressError"></span>

                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label class="col-md-4 col-form-label">Description</label>
                                        <div class="col-md-8">
                                            <textarea name="description" id="description" class="form-control" rows="8" placeholder="Enter description"
                                                disabled>{{ $model->description ?? '' }}</textarea>
                                            <span class="error text-danger" id="descriptionError"></span>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label class="col-md-4 col-form-label">Contact</label>
                                        <div class="col-md-8">
                                            <div class="input-group input-group-merge">
                                                <select name="customer_contact_id" id="customer_contact_id"
                                                    class="form-control" disabled>
                                                    <option></option>
                                                </select>
                                            </div>
                                            <span class="error text-danger" id="customer_contact_idError"></span>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6><strong>Tax Information</strong></h6>
                                    <div class="mb-3 row">
                                        <label class="col-md-4 col-form-label">Tax</label>
                                        <div class="col-md-8">
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="form-check form-check-primary">
                                                        <input class="form-check-input" type="checkbox" value="1"
                                                            name="kena_pajak" id="kena_pajak"
                                                            {{ $model->kena_pajak ? 'checked' : '' }} disabled>
                                                        <label class="form-check-label" for="kena_pajak">Including
                                                            Tax</label>
                                                    </div>
                                                </div>

                                                <div class="col-6">
                                                    <div class="form-check form-check-primary">
                                                        <input class="form-check-input" type="checkbox" value="1"
                                                            name="total_termasuk_pajak" id="total_termasuk_pajak"
                                                            {{ $model->total_termasuk_pajak ? 'checked' : '' }} disabled>
                                                        <label class="form-check-label" for="total_termasuk_pajak">Total
                                                            Including Tax</label>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
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

                                @foreach ($uniquePOs as $po)
                                    <div class="card col-md-12 mb-3 p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div style="color: #007bff; font-weight: bold;">
                                                    {{ $po->code }}
                                                </div>
                                                <div style="font-size: 0.9rem; color: #666;">
                                                    {{ \Carbon\Carbon::parse($po->sales_quotation_date)->format('d/m/Y') }}
                                                </div>
                                            </div>

                                            <a href="{{ route('sales-quotation.print', $po->id) }}"
                                                class="btn btn-sm btn-icon" data-bs-toggle="tooltip"
                                                data-bs-placement="top" data-bs-original-title="Lihat PO">
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
