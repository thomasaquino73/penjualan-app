<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Store Sales - {{ $model->store_sales_code }}</title>
    <style>
        @page {
            size: A5 landscape;
            margin: 5mm 15mm 15mm 15mm;
            /* Atas 5mm, lainnya 15mm */
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            color: #000;
            line-height: 1.3;
        }



        /* Header Section */
        .header-section {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .logo-img {
            max-height: 50px;
            width: auto;
        }

        .company-info {
            text-align: right;
            font-size: 8pt;
        }

        /* Info Section */
        .info-table {
            width: 100%;
            margin-bottom: 10px;
        }

        .info-table td {
            vertical-align: top;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            font-size: 8.5pt;
        }

        .items-table th {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            background-color: #f2f2f2;
        }

        .items-table td {
            border: 1px solid #000;
            padding: 4px 5px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Footer / Summary */
        .summary-section {
            width: 100%;
            margin-top: 10px;
        }

        .summary-table {
            float: right;
            width: 40%;
            font-size: 9pt;
        }

        .summary-table td {
            padding: 2px 5px;
        }

        .total-row {
            font-weight: bold;
            border-top: 1px solid #000;
        }

        .company-title {
            font-size: 14pt;
            font-weight: bold;
            line-height: 1.1;
            text-align: right;
            word-wrap: break-word;
            white-space: normal;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <header>
        <div class="header-content">
            @include('pdf.deliveryOrder.partials.header')
        </div>
    </header>


    <!-- Info Transaksi -->
    <table class="info-table">
        <tr>
            <td>
                <strong>Kepada:</strong> {{ $model->customerID->name ?? $model->customer_name }}<br>
                <strong>Metode:</strong> {{ $model->payment_method }}
            </td>
            <td style="text-align: right;">
                <strong>No:</strong> {{ $model->store_sales_code }}<br>
                <strong>Tanggal:</strong> {{ date('d-m-Y', strtotime($model->store_sales_date)) }}
            </td>
        </tr>
    </table>

    <!-- Tabel Detail Barang -->
    <table class="items-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Produk</th>
                <th>Qty</th>
                <th>Satuan</th>
                <th>Harga</th>
                <th>Diskon</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($model->details as $index => $detail)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $detail->produkID->nama_barang ?? 'N/A' }}</td>
                    <td class="text-center">{{ number_format($detail->qty, 2, ',', '.') }}</td>
                    <td class="text-center">{{ $detail->unitID->detail ?? '-' }}</td>
                    <td class="text-right">{{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($detail->discount, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($detail->amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Ringkasan -->
    <div class="summary-section">
        <table class="summary-table">
            <tr>
                <td>Sub Total</td>
                <td class="text-right">{{ number_format($model->sub_total, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Diskon</td>
                <td class="text-right">{{ number_format($model->disc_nominal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Pajak ({{ $model->tax_percent }}%)</td>
                <td class="text-right">{{ number_format($model->tax_amount, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td>Grand Total</td>
                <td class="text-right">{{ number_format($model->grand_total, 0, ',', '.') }}</td>
            </tr>
        </table>
        <div style="clear: both;"></div>
    </div>
    <table class="w-70 footer-table" style="margin-top: 10px;margin-bottom: 10px; width:60%">
        <tr>
            <td class="keterangan-box">
                @php
                    $currencyId = session('currency_id') ?? \App\Models\Setting\Company::first()->default_currency_id;
                    $currencyCode = \App\Models\Setting\Currency::find($currencyId)?->code ?? 'IDR';

                    // Gunakan nilai asli (jangan di-round agar sen tidak hilang)
                    $grandTotalConvert = convert_currency($model->grand_total, $model->currency_id ?? 1);
                @endphp
                <div>Terbilang: {{ terbilang($grandTotalConvert, $currencyCode) }}</div>
            </td>
        </tr>
    </table>
    <p style="font-size: 8pt; margin-top:20px;">
        <strong>Catatan:</strong> {{ $model->notes ?? '-' }}
    </p>

</body>

</html>
