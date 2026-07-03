<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Purchase Order - PT. Almex Bintang Timur</title>
    <style>
        /* Pengaturan Kertas Cetak A5 Landscape */
        @page {
            size: A5 landscape;
            margin-top: 120px;
            /* Sesuaikan dengan tinggi header */
            margin-bottom: 50px;
            /* Sesuaikan dengan tinggi footer */
            margin-left: 12px;
            margin-right: 12px;
        }

        /* Definisi Header di setiap halaman */
        header {
            position: fixed;
            top: -110px;
            left: 0;
            right: 0;
            height: 100px;
        }

        /* Definisi Footer di setiap halaman */
        footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            height: 30px;
            font-size: 7pt;
            color: #777;
            text-align: right;
            border-top: 1px solid #eee;
        }

        /* Base Typography */
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            /* Font dikecilkan */
            color: #000000;
            margin: 0;
            padding: 0;
            line-height: 1.2;
        }

        /* Utilitas Dasar */
        .w-100 {
            width: 100%;
        }

        table {
            border-collapse: collapse;
        }

        .divider {
            border-top: 1.2px solid #000000;
            margin-top: 2px;
            margin-bottom: 10px;
        }

        /* Bagian Info (Kepada & PO) */
        .info-table {
            margin-bottom: 10px;
            width: 100%;
        }

        .info-table td {
            vertical-align: top;
            width: 50%;
        }

        .section-title {
            font-size: 9pt;
            font-weight: bold;
            border-bottom: 1.2px solid #000000;
            padding-bottom: 2px;
            margin-bottom: 4px;
        }

        .recipient-box {
            background-color: #f8f9fa;
            padding: 5px 8px;
            min-height: 30px;
            font-size: 8.5pt;
        }

        .po-box-title {
            font-size: 14pt;
            /* Judul DO dikecilkan */
            font-weight: bold;
            text-align: center;
            border-bottom: 1.2px solid #000000;
            padding-bottom: 2px;
            margin-bottom: 5px;
        }

        /* .po-details-table {
            width: 100%;
            font-size: 8.5pt;
            border-collapse: collapse;
        }

        .po-details-table td {
            padding: 0px 2px;
            vertical-align: middle;
        }

        .po-details-table td.label {
            width: 30%;
            white-space: nowrap;
        }

        .po-details-table td.colon {
            width: 5%;
            text-align: center;
        }

        .po-details-table td.value {
            width: 65%;
            background-color: #f8f9fa;
            padding-left: 5px;
        } */

        /* Tabel Utama Barang */
        .items-table {
            width: 100%;
            margin-top: 5px;
            font-size: 8.5pt;
        }

        .items-table th {
            background-color: #1a446c;
            color: #ffffff;
            font-weight: normal;
            padding: 4px 6px;
        }

        .items-table td {
            padding: 3px 6px;
            border-bottom: 1px solid #eee;
        }

        /* Ringkasan & Footer Table */
        .footer-table {
            width: 100%;
            margin-top: 10px;
            font-size: 8.5pt;
        }

        .summary-table {
            width: 100%;
            background-color: #f8f9fa;
        }

        .summary-table td {
            padding: 3px 8px;
        }

        .total-row {
            background-color: #1a446c;
            color: #ffffff;
            font-weight: bold;
        }

        /* Tanda Tangan */
        .signature-section {
            margin-top: 20px;
            width: 100%;
        }

        .dots-line {
            border-bottom: 1px dotted #000;
            width: 80%;
            margin-top: 40px;
        }

        .approval-title {
            font-size: 8.5pt;
            font-weight: bold;
        }

        header {
            position: fixed;
            top: -110px;
            /* Sesuaikan dengan margin-top di @page */
            left: 0;
            right: 0;
            height: 100px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .logo-box {
            width: 250px;
            /* Sesuai dengan lebar gambar logo */
            padding-right: 15px;
            vertical-align: middle;
        }

        .company-title {
            font-size: 16pt;
            /* Dikecilkan agar pas untuk A5 */
            font-weight: bold;
            text-align: right;
            line-height: 1.1;
            color: #1a446c;
            /* Warna navy perusahaan */
            margin-bottom: 3px;
        }

        .company-address {
            font-size: 8pt;
            /* Font kecil untuk alamat */
            text-align: right;
            line-height: 1.2;
            color: #555;
            font-style: italic;
        }
    </style>
</head>

<body>
    <header>
        <div class="header-content">
            @include('pdf.deliveryOrder.partials.header')
            <div class="divider"></div>
        </div>
    </header>

    <footer>
        Printed on: {{ date('Y-m-d H:i:s') }} | Confidential Document
    </footer>

    <main>
        <table class="w-100 info-table">
            <tr>
                <td style="padding-right: 25px;">
                    <div class="section-title">Kepada</div>
                    <div class="recipient-box">
                        <strong>{{ $model->customerID->nama_customer }}</strong><br>
                        {{ $model->address }}
                    </div>
                </td>

                <td style="width: 50%; vertical-align: top; padding-left: 20px;">
                    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000;">
                        <tr>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 5px;">
                                <div style="display: flex;">
                                    <span style="width: 70px;">Tanggal</span>
                                    <span>: {{ date('d M Y', strtotime($model->delivery_order_date)) }}</span>
                                </div>
                            </td>
                            <td style="border-bottom: 1px solid #000; padding: 5px;">
                                <div style="display: flex;">
                                    <span style="width: 70px;">Nomor</span>
                                    <span style="font-weight: bold;">: {{ $model->delivery_order_code }}</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="border-right: 1px solid #000; padding: 5px;">
                                <div style="display: flex;">
                                    <span style="width: 70px;">Ekspedisi</span>
                                    <span>: {{ $model->shippingID?->nama ?? '-' }}</span>
                                </div>
                            </td>
                            <td style="padding: 5px;">
                                <div style="display: flex;">
                                    <span style="width: 70px;">PO No.</span>
                                    <span>: {{ $model->no_document }}</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                {{-- <table style="width: auto; border-collapse: collapse;">
                        <tr>
                            <td style="width: 80px; padding: 2px;">Nomor</td>
                            <td style="width: 10px; padding: 2px;">:</td>
                            <td style="padding: 2px;">{{ $model->delivery_order_code }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 2px;">Tanggal</td>
                            <td style="padding: 2px;">:</td>
                            <td style="padding: 2px;">{{ date('d M Y', strtotime($model->delivery_order_date)) }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 2px;">Ekspedisi</td>
                            <td style="padding: 2px;">:</td>
                            <td style="padding: 2px;">{{ $model->shippingID?->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 2px;">PO No.</td>
                            <td style="padding: 2px;">:</td>
                            <td style="padding: 2px;">{{ $model->no_document }}</td>
                        </tr>
                    </table> --}}
                {{-- </td> --}}
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 14%;">Kode Barang</th>
                    <th style="width: 40%;">Nama Barang</th>
                    <th style="width: 8%; text-align: center;">Kts.</th>
                    <th style="width: 8%; text-align: center;">Satuan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($model->details as $detail)
                    <tr>
                        <td>{{ $detail->produkID->id_barang }}</td>
                        <td>{{ $detail->produkID?->nama_barang ?? 'Product Not Found' }}</td>
                        <td class="text-center">{{ rtrim(rtrim(number_format($detail->qty, 2, ',', '.'), '0'), ',') }}
                        </td>
                        <td class="text-center">{{ $detail->unitID?->detail }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="footer-table" style="width:100%;">
            <tr>
                <td style="width:50%; vertical-align:top;">
                    <div class="section-title">Keterangan</div>
                    <div>{{ $model->description }}</div>
                </td>
                <td style="width:20%;">
                </td>
                <td style="width:30%; vertical-align:top;">
                    <table class="summary-table" style="width:100%;">
                        <tr>
                            <td>Total Kuantitas</td>
                            <td class="text-right">{{ $totalQty }}</td>
                        </tr>
                        <tr class="total-row">
                            <td>Total Barang</td>
                            <td class="text-right">{{ $totalBarang }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="signature-section">
            <table style="width:100%; table-layout:fixed;">
                <tr>
                    <td style="width:25%; text-align:center; vertical-align:top;">
                        <div class="approval-title">Dibuat,</div>
                        <div style="height:40px;"></div>
                        <div style="font-weight:bold; text-decoration:underline;">
                            {{ $model->status == 'processing' ? $model->creator->fullname : '' }}
                        </div>
                    </td>

                    <td style="width:25%; text-align:center; vertical-align:top;">
                        <div class="approval-title">
                            {{ $model->status == 'processing' ? 'Disetujui' : 'Dibuat oleh' }}
                        </div>
                        <div style="height:40px;">
                            <img src="{{ public_path($model->status == 'processing' ? 'image/logo/STEMPEL.png' : 'image/logo/69fd6d6ab719c1778216298.png') }}"
                                style="height:40px;">
                        </div>
                        <div style="font-weight:bold; text-decoration:underline;">
                            {{ $model->status == 'processing' ? 'Yohanes Lukman' : $model->creator->fullname }}
                        </div>
                    </td>

                    <td style="width:25%; text-align:center; vertical-align:top;">
                        <div class="approval-title">Pengirim,</div>
                        <div style="height:40px;"></div>

                        <div
                            style="
        width:80%;
        margin:0 auto;
        border-bottom:1px solid #000;
        height:10px;
    ">
                        </div>
                    </td>

                    <td style="width:25%; text-align:center; vertical-align:top;">
                        <div class="approval-title">Penerima,</div>
                        <div style="height:40px;"></div>

                        <div
                            style="
        width:80%;
        margin:0 auto;
        border-bottom:1px solid #000;
        height:10px;
    ">
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </main>
</body>

</html>
