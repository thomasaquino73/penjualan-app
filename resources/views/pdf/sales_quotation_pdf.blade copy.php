<!DOCTYPE html>
<html lang="id">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.1/css/bootstrap.min.css">

<head>
    <meta charset="UTF-8">
    <title>Purchase Order - PT. Almex Bintang Timur</title>
    <style>
        /* Ubah ukuran font global menjadi sedikit lebih kecil */
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            /* Diperkecil dari 10pt */
            line-height: 1.2;
        }

        /* Agar header muncul di setiap halaman */
        .header-fixed {
            position: fixed;
            top: 0;
            width: 100%;
        }

        /* Tambahkan padding pada body agar konten tidak tertutup header/footer */
        body {
            margin-top: 100px;
            /* Sesuaikan dengan tinggi header Anda */
            margin-bottom: 50px;
        }

        /* Footer sudah memiliki position: fixed dari kode asli Anda */
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            font-size: 7pt;
            color: #999;
            text-align: right;
            border-top: 1px solid #eee;
        }

        /* Opsional: Jika ingin tabel tidak terpotong buruk */
        table {
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        .bg-blue {
        background-color: #1a446c !important;
        color: #ffffff;
    }

    .items-table th {
        background-color: #1a446c; /* Warna Biru */
        color: #ffffff;
        /* ... sisa properti lainnya */
    }

    .summary-table tr.total-row {
        background-color: #1a446c; /* Warna Biru */
        color: #ffffff;
        font-weight: bold;
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
                            <img src="{{ public_path('image/logo/logo_print.png') }}" style="width: 340px;">

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
                    <strong>{{ $model->customerID->nama_customer }}</strong><br>
                    {{ $model->address }}

                </div>
            </td>
            <td style="padding-left: 25px;">
                {{-- <div class="po-box-title">Sales Quotation</div> --}}
                <div class="po-box-title">Penawaran Harga</div>
                <table class="po-details-table">
                    <tr>
                        <td class="label">Nomor</td>
                        <td class="colon">:</td>
                        <td class="value">{{ $model->sales_quotation_code }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tanggal</td>
                        <td class="colon">:</td>
                        <td class="value">
                            {{ date('d M Y', strtotime($model->sales_quotation_date)) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Pembayaran</td>
                        <td class="colon">:</td>
                        <td class="value">
                            {{ $model->paymentTermID ? $model->paymentTermID->nama : '-' }}
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
                <th style="width: 8%; text-align: center;">Satuan</th>
                <th style="width: 12%; text-align: right;">@Harga</th>
                <th style="width: 12%; text-align: right;">Diskon</th>
                <th style="width: 14%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($model->details as $detail)
                <tr>
                    <td>{{ $detail->produkID->id_barang }}</td>
                    <td>{{ $detail->produkID ? $detail->produkID->nama_barang : 'Product Not Found' }}</td>
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
            <td class="keterangan-box">
                <div class="keterangan-title">Keterangan</div>
                <div class="keterangan-content">{!! $model->description !!}</div>
            </td>
            <td class="summary-box">
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
    <tr class="total-row">
        <td style="padding: 5px 10px;">Total</td>
        <td class="text-right" style="padding: 5px 10px;">
            {{ isset($model) ? format_uang(convert_currency($model->grand_total, $detail->currency_id ?? 1)) : '' }}
        </td>
    </tr>
</table>
            </td>
        </tr>
    </table>
    <table class="w-70 footer-table" style="margin-top: 10px;margin-bottom: 20px; width:120px">
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
    <div class="footer">
        Printed on: {{ date('Y-m-d H:i:s') }} | Confidential Document
    </div>
</body>

</html>
