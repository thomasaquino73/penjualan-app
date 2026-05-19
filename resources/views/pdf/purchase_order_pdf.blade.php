<!DOCTYPE html>
<html lang="id">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.1/css/bootstrap.min.css">

<head>
    <meta charset="UTF-8">
    <title>Purchase Order - PT. Almex Bintang Timur</title>
    <style>
        /* Pengaturan Kertas Cetak A4 */
        @page {
            size: A4;
            margin: 15mm 12mm;
            background-color: #ffffff;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
            color: #000000;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }

        .w-100 {
            width: 100%;
        }

        table {
            border-collapse: collapse;
        }

        /* Bagian KOP / Header */
        .header-table td {
            vertical-align: top;
        }

        .logo-box {
            width: 65px;
            padding-right: 10px;
        }

        .logo-box svg {
            width: 60px;
            height: auto;
        }

        .company-contact {
            font-size: 7.5pt;
            color: #444444;
            line-height: 1.2;
        }

        .company-title {
            font-size: 20pt;
            font-weight: bold;
            text-align: right;
            letter-spacing: -0.5px;
            margin: 0;
        }

        .company-address {
            font-size: 9.5pt;
            text-align: right;
            line-height: 1.2;
            color: #111111;
        }

        .divider {
            border-top: 1.5px solid #000000;
            margin-top: 5px;
            margin-bottom: 15px;
        }

        /* Detail Kepada & Nomor PO */
        .info-table {
            margin-bottom: 15px;
        }

        .info-table td {
            vertical-align: top;
            width: 50%;
        }

        .section-title {
            font-size: 11pt;
            font-weight: bold;
            border-bottom: 1.5px solid #000000;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }

        .recipient-box {
            background-color: #e9ecef;
            padding: 8px 10px;
            min-height: 85px;
            font-size: 10pt;
            line-height: 1.25;
        }

        .po-box-title {
            font-size: 18pt;
            font-weight: bold;
            text-align: center;
            border-bottom: 1.5px solid #000000;
            padding-bottom: 2px;
            margin-bottom: 5px;
        }

        .po-details-table {
            width: 100%;
            font-size: 10pt;
        }

        .po-details-table td {
            padding: 2px 0;
        }

        .po-details-table td.label {
            width: 32%;
        }

        .po-details-table td.colon {
            width: 5%;
            text-align: center;
        }

        .po-details-table td.value {
            width: 63%;
            background-color: #e9ecef;
            padding-left: 6px;
        }

        /* Tabel Utama Barang */
        .items-table {
            width: 100%;
            margin-top: 10px;
            margin-bottom: 15px;
        }

        .items-table th {
            background-color: #1a446c;
            /* Warna Biru Navy Almex */
            color: #ffffff;
            font-weight: normal;
            font-size: 10.5pt;
            padding: 6px 8px;
            text-align: left;
        }

        .items-table td {
            padding: 5px 8px;
            font-size: 10pt;
            vertical-align: middle;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* Ringkasan Total (Summary) */
        .footer-table {
            width: 100%;
            margin-top: 10px;
        }

        .footer-table td {
            vertical-align: top;
        }

        .keterangan-box {
            width: 50%;
            padding-right: 40px;
        }

        .keterangan-title {
            font-weight: bold;
            border-bottom: 1.5px solid #000000;
            padding-bottom: 3px;
            margin-bottom: 6px;
        }

        .summary-box {
            width: 50%;
        }

        .summary-table {
            width: 100%;
            background-color: #e9ecef;
        }

        .summary-table td {
            padding: 4px 10px;
            font-size: 10pt;
        }

        .summary-table tr.total-row {
            background-color: #1a446c;
            color: #ffffff;
            font-weight: bold;
        }

        .summary-table tr.total-row td {
            border-top: 1.5px solid #000000;
            padding: 5px 10px;
        }

        /* Garis Tanda Tangan */
        .signature-section {
            margin-top: 40px;
            width: 100%;
        }

        .signature-table {
            width: 100%;
        }

        .signature-table td {
            vertical-align: bottom;
        }

        .dots-line {
            border-bottom: 1px dotted #000000;
            width: 85%;
            margin-top: 70px;
        }

        .approval-title {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 5px;
        }

        /* Pencegahan Yatim Piatu (Orphaned row) saat cetak */
        .items-table tr {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>

    <table class="w-100 header-table">
        <tr>
            <td style="width: 55%;">
                <table>
                    <tr>
                        <td class="logo-box">
                            @if (isset($company) && $company->logo)
                                <img src="{{ $logoBase64 }}" style="height: 80px;">
                            @else
                                <div
                                    style="width: 70px; height: 70px; border: 1px dashed #ccc; background: #fafafa; text-align: center; line-height: 70px; color: #aaa; font-size: 8pt;">
                                    No Logo
                                </div>
                            @endif
                        </td>
                        <td class="company-contact">
                            {{ $company->nomor_telepon }}<br>
                            {{ $company->alamat }}<br>
                            {{ $company->email }}<br>
                            {{ $company->website }}<br>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 45%;">
                <div class="company-title">{{ $company->nama_perusahaan }}</div>
                <div class="company-address">
                    {{ $company->alamat }}
                </div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <table class="w-100 info-table">
        <tr>
            <td style="padding-right: 25px;">
                <div class="section-title">Kepada</div>
                <div class="recipient-box">
                    <strong>{{ $model->supplier->nama }}</strong><br>
                    {{ $model->supplier->alamat }}

                </div>
            </td>
            <td style="padding-left: 25px;">
                <div class="po-box-title">Purchase Order</div>
                <table class="po-details-table">
                    <tr>
                        <td class="label">Nomor</td>
                        <td class="colon">:</td>
                        <td class="value">{{ $model->code }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tanggal</td>
                        <td class="colon">:</td>
                        <td class="value">
                            {{ date('d M Y', strtotime($model->date)) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tanggal Kirim</td>
                        <td class="colon">:</td>
                        <td class="value">
                            {{ isset($model->expected_date) ? date('d M Y', strtotime($model->expected_date)) : '' }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 14%;">Kode Barang</th>
                <th style="width: 40%;">Nama Barang</th>
                <th style="width: 8%; text-align: center;">Kts.</th>
                <th style="width: 12%; text-align: right;">@Harga</th>
                <th style="width: 12%; text-align: right;">Diskon</th>
                <th style="width: 14%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($model->details as $detail)
                <tr>
                    <td>{{ $detail->product_id }}</td>
                    <td>{{ $detail->produkID ? $detail->produkID->nama_barang : 'Product Not Found' }}</td>
                    <td class="text-center">{{ $detail->qty }}</td>
                    <td class="text-right">{{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($detail->discount, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($detail->amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="w-100 footer-table">
        <tr>
            <td class="keterangan-box">
                <div class="keterangan-title">Keterangan</div>
                <div class="keterangan-content">{{ $model->description }}</div>
            </td>
            <td class="summary-box">
                <table class="summary-table">
                    <tr>
                        <td>Sub Total</td>
                        <td class="text-right">
                            {{ isset($model) ? number_format($model->sub_total, 2, ',', '.') : '' }}</td>
                    </tr>
                    <tr>
                        <td>Diskon</td>
                        <td class="text-right">0</td>
                    </tr>
                    <tr>
                        <td>PPN (11%)</td>
                        <td class="text-right">{{ isset($model) ? number_format($model->ppn, 0, ',', '.') : '28.133' }}
                        </td>
                    </tr>
                    <tr>
                        <td>Biaya Lain-lain</td>
                        <td class="text-right">0</td>
                    </tr>
                    <tr class="total-row">
                        <td>Total</td>
                        <td class="text-right">
                            {{ isset($model) ? number_format($model->total, 2, ',', '.') : '283.885,35' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td style="width: 60%;">
                    <div class="dots-line"></div>
                </td>
                <td class="text-center" style="width: 40%;">
                    <div class="approval-title">Disetujui Oleh,</div>
                    <div style="height: 65px;"></div>
                    <div style="font-weight: bold; text-decoration: underline;">PT. ALMEX BINTANG TIMUR</div>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
