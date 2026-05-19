<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Purchase Requisition - {{ $detail->code }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9.5pt;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* Layout Utama Atas (Kiri & Kanan) */
        .top-layout {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .top-layout td {
            vertical-align: top;
            padding: 0;
        }

        /* Sisi Kiri - Profil Perusahaan */
        .company-logo {
            width: 75px;
            height: auto;
        }

        .company-details {
            padding-left: 15px;
        }

        .company-name {
            font-size: 12pt;
            font-weight: bold;
            color: #222;
            margin-bottom: 3px;
        }

        .company-info {
            font-size: 8.5pt;
            color: #666;
            line-height: 1.3;
        }

        /* Sisi Kanan - Meta Form Data */
        .form-group-box {
            margin-bottom: 10px;
        }

        .form-label {
            font-size: 8.5pt;
            font-weight: bold;
            color: #555;
            margin-bottom: 3px;
        }

        .form-input-mock {
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 9.5pt;
            color: #495057;
            min-height: 16px;
        }

        .form-textarea-mock {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 9.5pt;
            color: #495057;
            height: 50px;
        }

        /* Divider Tengah */
        .divider-container {
            width: 100%;
            text-align: center;
            margin-top: 10px;
            margin-bottom: 20px;
            position: relative;
        }

        .divider-line {
            border-top: 1px dashed #bbb;
            position: absolute;
            top: 50%;
            width: 100%;
            z-index: 1;
        }

        .divider-text {
            background: #fff;
            padding: 0 15px;
            position: relative;
            z-index: 2;
            font-size: 9pt;
            color: #555;
            font-weight: bold;
        }

        /* Tabel Detail Barang */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 30px;
        }

        .items-table th {
            background-color: #4a4a4a;
            color: #ffffff;
            text-align: left;
            padding: 10px;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
            font-size: 9.5pt;
        }

        .items-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        /* Blok Area Tanda Tangan */
        .signature-container {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .signature-box {
            width: 250px;
            text-align: center;
            font-size: 9.5pt;
        }

        .signature-title {
            font-weight: bold;
            color: #555;
            margin-bottom: 10px;
        }

        .signature-space {
            height: 75px;
            vertical-align: middle;
            text-align: center;
            position: relative;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            width: 80%;
            margin: 0 auto 3px auto;
        }

        .signature-name {
            font-weight: bold;
            color: #222;
        }

        .signature-role {
            font-size: 8.5pt;
            color: #777;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            font-size: 7.5pt;
            color: #999;
            text-align: right;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
    </style>
</head>

<body>

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
                                <div class="form-label">Request Number</div>
                                <div class="form-input-mock">{{ $detail->code }}</div>
                            </div>
                        </td>
                        <td style="width: 50%;">
                            <div class="form-group-box">
                                <div class="form-label">Request Date</div>
                                <div class="form-input-mock">{{ $detail->date ?? date('Y-m-d') }}</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="form-group-box" style="margin-top: 5px;">
                                <div class="form-label">Description</div>
                                <div class="form-textarea-mock">{{ $detail->keterangan ?? '' }}</div>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 55%;">Item</th>
                <th style="width: 20%;" class="text-center">Qty</th>
                <th style="width: 20%;">Unit</th>
                <th style="width: 20%;">Required Date</th>
                <th style="width: 20%;">Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detail->details as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->produkID->nama_barang ?? 'Pralon' }}</td>
                    <td class="text-center">{{ $item->qty ?? 22 }}</td>
                    <td>{{ $item->unitID->nama_unit ?? 'Pack' }}</td>
                    <td>{{ $item->required_date ? Carbon\Carbon::parse($item->required_date)->format('Y-m-d') : 'N/A' }}
                    </td>
                    <td>{{ $item->notes ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center" style="color: #999; padding: 20px;">No items found in this
                        request.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="signature-container" style="width: 100%; margin-top: 30px; border-collapse: collapse;">
        <tr>
            <td colspan="3" style="text-align: right; padding-bottom: 5px;">
                <div class="divider-text">Tangerang,{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
            </td>
        </tr>
        <tr>
            <td style="width: 50%;"></td>

            <td class="signature-box" style="width: 25%; padding: 8px; vertical-align: top;">
                <div
                    style="border: 1px solid #ddd; background-color: #fafafa; padding: 10px; border-radius: 6px; text-align: center;">
                    <div class="signature-title"
                        style="margin-bottom: 5px; font-size: 8.5pt; font-weight: bold; color: #555;">
                        Digitally Created By:
                    </div>

                    <div class="signature-space" style="height: 70px; margin-bottom: 5px; text-align: center;">
                        @if (isset($qrCodeBase64) && $qrCodeBase64 != null)
                            <img src="{{ $qrCodeBase64 }}" style="width: 60px; height: 60px; display: inline-block;">
                        @else
                            <div
                                style="font-size: 7pt; color: #777; border: 1px dashed #ccc; width: 60px; height: 60px; line-height: 60px; margin: 0 auto; background: #fff;">
                                [QR CODE]
                            </div>
                        @endif
                    </div>

                    <div class="signature-line"
                        style="width: 90%; border-bottom: 1px solid #999; margin: 0 auto 4px auto;"></div>

                    <div class="signature-name" style="font-size: 9pt; color: #111; font-weight: bold;">
                        {{ $detail->creator->fullname ?? 'Staff Purchasing' }}
                    </div>

                    <div class="signature-role"
                        style="font-size: 7.5pt; color: #27ae60; font-weight: bold; text-transform: uppercase;">
                        E-SIGNED VERIFIED
                    </div>
                </div>
            </td>

            <td class="signature-box" style="width: 25%; padding: 8px; vertical-align: top;">
                @if (in_array($detail->status, ['processing']))
                    <div
                        style="border: 1px solid #ddd; background-color: #fafafa; padding: 10px; border-radius: 6px; text-align: center;">
                        <div class="signature-title"
                            style="margin-bottom: 5px; font-size: 8.5pt; font-weight: bold; color: #555;">
                            Digitally Approved By:
                        </div>

                        <div class="signature-space" style="height: 70px; margin-bottom: 5px; text-align: center;">
                            @if (isset($qrApprovalBase64) && $qrApprovalBase64 != null)
                                <img src="{{ $qrApprovalBase64 }}"
                                    style="width: 60px; height: 60px; display: inline-block;">
                            @else
                                <div
                                    style="font-size: 7pt; color: #777; border: 1px dashed #ccc; width: 60px; height: 60px; line-height: 60px; margin: 0 auto; background: #fff;">
                                    [QR CODE]
                                </div>
                            @endif
                        </div>

                        <div class="signature-line"
                            style="width: 90%; border-bottom: 1px solid #999; margin: 0 auto 4px auto;"></div>

                        <div class="signature-name" style="font-size: 9pt; color: #111; font-weight: bold;">
                            {{-- DIPERBAIKI: Mengubah $detail->updater menjadi $detail->updater --}}
                            {{ $detail->updater->fullname ?? 'Manager Purchasing' }}
                        </div>

                        <div class="signature-role"
                            style="font-size: 7.5pt; color: #3085d6; font-weight: bold; text-transform: uppercase;">
                            APPROVED VERIFIED
                        </div>
                    </div>
                @else
                @endif
            </td>
        </tr>
    </table>

    <div class="footer">
        Printed on: {{ date('Y-m-d H:i:s') }} | Confidential Document
    </div>

</body>

</html>
