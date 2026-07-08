<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Proforma Invoice - {{ $model->proforma_invoice_code }}</title>
    <style>
        /* Pengaturan Halaman */
        @page {
            margin: 140px 40px 60px 40px;
            /* Atas, Kanan, Bawah, Kiri */
        }

        /* Header Fixed - Akan muncul di setiap halaman */
        header {
            position: fixed;
            top: -120px;
            left: 0;
            right: 0;
            height: 100px;
        }

        /* Footer Fixed */
        footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            height: 30px;
            font-size: 7pt;
            color: #999;
            text-align: right;
            border-top: 1px solid #eee;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            line-height: 1.2;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Styling Header */
        .header-table td {
            vertical-align: top;
        }

        .company-title {
            font-size: 16pt;
            font-weight: bold;
            text-align: right;
        }

        .company-address {
            font-size: 8pt;
            text-align: right;
        }

        /* Styling Umum */
        .divider {
            border-top: 1.5px solid #000;
            margin: 5px 0 15px 0;
        }

        .section-title {
            font-size: 9pt;
            font-weight: bold;
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
        }

        .recipient-box {
            background-color: #f8f9fa;
            padding: 5px;
            font-size: 8.5pt;
        }

        .po-box-title {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            border-bottom: 1.5px solid #000;
            margin-bottom: 5px;
        }

        /* Tabel Barang */
        .items-table {
            margin-top: 10px;
            width: 100%;
        }

        .items-table th {
            background-color: #1a446c;
            color: #fff;
            padding: 5px;
            font-size: 8.5pt;
        }

        .items-table td {
            padding: 4px;
            border-bottom: 1px solid #ddd;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .summary-table {
            width: 100%;
            background-color: #f8f9fa;
            margin-top: 10px;
        }

        .summary-table td {
            padding: 3px 10px;
        }

        .keterangan-content {
            white-space: pre-line;
            /* Ini kuncinya: agar enter/newline tetap terbaca */
            font-size: 8pt;
            margin-top: 5px;
        }
    </style>
    <style>
        /* Tambahkan atau perbarui CSS ini */
        .total-row {
            background-color: #1a446c !important;
            /* Biru */
            color: #ffffff !important;
            /* Tulisan putih */
            font-weight: bold;
        }

        /* Agar teks di dalam total row terlihat jelas */
        .total-row td {
            padding: 8px 10px !important;
        }

        /* Penting: Memaksa header tabel berulang di setiap halaman */
        thead {
            display: table-header-group;
        }

        /* Opsional: Jika tabel terpotong di tengah baris, cegah dengan ini */
        tr {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>

    <header>
        <table class="header-table">
            <tr>
                <td>
                    <img src="{{ public_path('image/logo/logo_print.png') }}" style="width: 330px;" height="120px"
                        alt="Logo Perusahaan">
                </td>
                <td>
                    <div class="company-title">{{ $company->nama_perusahaan }}</div>
                    <div class="company-address">{{ $company->alamat }}</div>
                </td>
            </tr>
        </table>
        <div class="divider"></div>
    </header>

    <footer>
        Printed on: {{ date('Y-m-d H:i:s') }} | Page
        <script type="text/php">if (isset($pdf)) { $font = $fontMetrics->getFont("Arial", "bold"); $pdf->page_text(520, 800, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, 7, array(0,0,0)); }</script>
    </footer>

    <table class="info-table" style="margin-top: 20px; width:100%">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div class="section-title">Kepada</div>
                <div class="recipient-box">
                    <strong>{{ $model->customerID->nama_customer }}</strong><br>
                    {{ $model->address }}
                </div>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 20px;">
                <div class="po-box-title">Proforma Invoice</div>
                <table style="width:100%;">
                    <tr>
                        <td width="70">Nomor</td>
                        <td width="10">:</td>
                        <td>{{ $model->proforma_invoice_code }}</td>
                    </tr>
                    <tr>
                        <td width="70">Tanggal</td>
                        <td width="10">:</td>
                        <td>{{ date('d M Y', strtotime($model->proforma_invoice_date)) }}</td>
                    </tr>
                    <tr>
                        <td width="70">Tanggal Kirim</td>
                        <td width="10">:</td>
                        <td>{{ date('d M Y', strtotime($model->tanggal_pengiriman)) }}</td>
                    </tr>
                    <tr>
                        <td width="70">Pembayaran</td>
                        <td width="10">:</td>
                        <td>{{ $model->paymentTermID?->nama ?? '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama Barang</th>
                <th class="text-center">Kts.</th>
                <th class="text-center">Satuan</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Diskon</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($model->details as $detail)
                <tr>
                    <td>{{ $detail->produkID->id_barang }}</td>
                    <td>{{ $detail->produkID->nama_barang }}</td>
                    <td class="text-center">{{ rtrim(rtrim(number_format($detail->qty, 2, ',', '.'), '0'), ',') }}</td>
                    <td class="text-center">{{ $detail->unitID->detail }}</td>
                    <td class="text-right">{{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($detail->discount, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($detail->amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="w-100 footer-table">
        <tr>
            <td class="keterangan-box" width="60%">
                <div class="keterangan-title">Keterangan</div>
                <div class="keterangan-content">{!! nl2br(e($model->description)) !!}</div>
            </td>
            <td class="summary-box"width="40%">
                <table class="summary-table">
                    <tr>
                        <td>Sub Total</td>
                        <td class="text-right">
                            {{ isset($model) ? format_uang(convert_currency($model->sub_total, $detail->currency_id ?? 1)) : '' }}
                        </td>
                    </tr>
                    <tr>
                        <td>Diskon</td>
                        <td class="text-right">
                            {{ isset($model) ? format_uang(convert_currency($model->disc_nominal, $detail->currency_id ?? 1)) : '' }}
                        </td>
                    </tr>
                    <tr>
                        <td>PPN (11%)</td>
                        <td class="text-right">
                            {{ isset($model) ? format_uang(convert_currency($model->tax_amount, $detail->currency_id ?? 1)) : '' }}
                        </td>
                    </tr>
                    {{-- <tr>
                        <td>Biaya Lain-lain</td>
                        <td class="text-right">0</td>
                    </tr> --}}
                    <tr class="total-row">
                        <td>Total</td>
                        <td class="text-right">
                            {{ isset($model) ? format_uang(convert_currency($model->grand_total, $detail->currency_id ?? 1)) : '' }}
                    </tr>
                </table>
            </td>
        </tr>
    </table>
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
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td style="width: 60%;">
                    <div class="dots-line"></div>
                </td>
                <td class="text-center" style="width: 40%;">
                    @php
                        $title = '';
                        $user = '';

                        if ($model->status == 'rejected') {
                            $title = 'Ditolak Oleh,';
                            $user = $model->rejectedBy?->fullname;
                        } elseif (in_array($model->status, ['approved', 'sent', 'partially_received', 'completed'])) {
                            $title = 'Disetujui Oleh,';
                            $user = $model->approvedBy?->fullname;
                        }
                    @endphp
                    @if ($model->status == 'processing')
                        <div class="approval-title">
                            Disetujui Oleh
                        </div>
                        <div style="height: 65px;">
                            <img src="{{ public_path('image/logo/STEMPEL.png') }}" style="height: 80px;">
                        </div>
                        <div style="font-weight: bold; text-decoration: underline;">
                            Yohanes Lukman
                        </div>
                    @else
                        <div class="approval-title">
                            Dibuat oleh,
                        </div>
                        <div style="height: 65px;">
                            <img src="{{ public_path('image/logo/69fd6d6ab719c1778216298.png') }}"
                                style="height: 80px;">
                        </div>
                        <div style="font-weight: bold; text-decoration: underline;">
                            {{ $model->creator->fullname }}
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>


</body>

</html>
