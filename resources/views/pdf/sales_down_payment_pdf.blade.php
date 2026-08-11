<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>
        Sales Down Payment
    </title>

    <style>
        @page {
            size: A5 landscape;
            margin: 120px 27px 50px 27px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .company {
            font-size: 16px;
            font-weight: bold;
        }

        .company-detail {
            font-size: 10px;
            line-height: 1.5;
        }

        .document-title {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .detail-table {
            margin-top: 20px;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #000;
            padding: 7px;
        }

        .detail-table th {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-table {
            margin-top: 15px;
        }

        .total-table td {
            padding: 5px;
        }

        .signature {
            margin-top: 60px;
        }
    </style>
    @include('pdf.partials.css')

</head>

<body>

    {{-- HEADER --}}
    {{-- <table class="header">

        <tr>

            <td width="60%">

                @if ($logoBase64)
                    <img src="{{ $logoBase64 }}" style="max-width: 150px; max-height: 60px;">
                @endif

                <div class="company">
                    {{ $company->company_name ?? '' }}
                </div>

                <div class="company-detail">

                    {{ $company->address ?? '' }}

                    @if ($company->phone)
                        <br>
                        Phone: {{ $company->phone }}
                    @endif

                    @if ($company->email)
                        <br>
                        Email: {{ $company->email }}
                    @endif

                </div>

            </td>

            <td width="40%" class="document-title">

                SALES DOWN PAYMENT

            </td>

        </tr>

    </table> --}}
    @include('pdf.partials.header_logo')
    <table class="info-table" style="margin-top: 20px; width:100%">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div class="section-title">Kepada</div>
                <div class="recipient-box">
                    <strong>{{ $salesDownPayment->customerID->nama_customer }}</strong><br>
                    {!! nl2br(e($salesDownPayment->address)) !!}
                </div>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 20px;">
                <div class="po-box-title">Uang Muka Penjualan</div>
                <table style="width:100%;">
                    <tr>
                        <td width="70">Nomor</td>
                        <td width="10">:</td>
                        <td>{{ $salesDownPayment->sales_downpayment_code }}</td>
                    </tr>
                    <tr>
                        <td width="70">Tanggal</td>
                        <td width="10">:</td>
                        <td>{{ date('d M Y', strtotime($salesDownPayment->sales_downpayment_date)) }}</td>
                    </tr>
                    <tr>
                        <td width="70">PO Nomor</td>
                        <td width="10">:</td>
                        <td>{{ $salesDownPayment->po_number }}</td>
                    </tr>
                    <tr>
                        <td width="70">Pembayaran</td>
                        <td width="10">:</td>
                        <td>{{ $salesDownPayment->paymentTermID?->nama ?? '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- DETAIL --}}
    <table class="items-table">
        <thead>
            <tr>
                <th width="8%">
                    No
                </th>

                <th>
                    Description
                </th>

                <th width="25%">
                    Amount
                </th>

            </tr>

        </thead>

        <tbody>

            <tr>

                <td class="text-center">
                    1
                </td>

                <td>
                    {{ $salesDownPayment->description }}
                    {{-- Down Payment {{ $salesOrder->sales_order_code ?? '-' }} --}}
                </td>

                <td class="text-right">

                    {{ $currency }}

                    {{ number_format($salesDownPayment->down_payment_amount, 2, ',', '.') }}

                </td>

            </tr>

        </tbody>

    </table>
    <table class="w-70 footer-table" style="margin-top: 1px;margin-bottom: 10px; width:100%">
        <tr>
            <td class="keterangan-box">
                @php
                    $currencyId = session('currency_id') ?? \App\Models\Setting\Company::first()->default_currency_id;
                    $currencyCode = \App\Models\Setting\Currency::find($currencyId)?->code ?? 'IDR';

                    // Gunakan nilai asli (jangan di-round agar sen tidak hilang)
                    $grandTotalConvert = convert_currency(
                        $salesDownPayment->down_payment_amount,
                        $model->currency_id ?? 1,
                    );
                @endphp
                <div>Terbilang: {{ terbilang($grandTotalConvert, $currencyCode) }}</div>
            </td>
            <td>
                Sales Order Amount<br> {{ $currency }}
                {{ number_format($salesDownPayment->sales_order_amount, 2, ',', '.') }}
            </td>

        </tr>
    </table>

    {{-- TOTAL --}}
    {{-- <table class="summary-table" style="width:100%;">
        <tr>
            <td width="45%"></td>
            <td width="35%" class="text-right" style="text-align: right;">
                Sales Order Amount<br>
            </td>
            <td width="30%">

            </td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td class="text-right" style="text-align: right;">

            </td>
        </tr>
    </table> --}}
    {{-- SIGNATURE --}}
    <div class="signature-section " style="margin-top:14px">
        <table style="width:100%; table-layout:fixed;">
            <tr>
                <td style="width:75%;"></td>

                <td style="width:25%; text-align:center; vertical-align:top;">
                    <div class="approval-title">Hormat Kami,</div>

                    <div style="height:70px;"></div>

                    <div
                        style="
                width:80%;
                margin:0 auto;
                border-bottom:1px solid #000;
                height:5px;
            ">
                    </div>
                    {{ $company->approval_name }}
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
