<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Item Detail - {{ $barang->nama_barang }}</title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        .header {
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 18pt;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
        }

        .doc-title {
            font-size: 14pt;
            color: #7f8c8d;
            margin-top: 5px;
        }

        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .info-grid td {
            padding: 8px 5px;
            vertical-align: top;
            border-bottom: 1px solid #f2f2f2;
        }

        .label {
            font-weight: bold;
            color: #555;
            width: 30%;
        }

        .value {
            width: 70%;
        }

        /* Status Badge Styling */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 9pt;
            font-weight: bold;
            color: white;
        }

        .bg-success {
            background-color: #27ae60;
        }

        /* Active */
        .bg-warning {
            background-color: #f39c12;
        }

        /* Not Active */
        .bg-danger {
            background-color: #e74c3c;
        }

        /* Deleted */

        .price-section {
            margin-top: 30px;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
        }

        .total-box {
            text-align: right;
            margin-top: 10px;
            font-size: 12pt;
        }

        .total-amount {
            font-size: 16pt;
            font-weight: bold;
            color: #c0392b;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            font-size: 8pt;
            color: #95a5a6;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="company-name">Inventory Management System</div>
        <div class="doc-title">Item Detail Report</div>
    </div>

    <table class="info-grid">
        <tr>
            <td class="label">Item Code</td>
            <td class="value">: {{ $barang->id_barang }}</td>
        </tr>
        <tr>
            <td class="label">Item Name</td>
            <td class="value">: <strong>{{ $barang->nama_barang }}</strong></td>
        </tr>
        <tr>
            <td class="label">Product Type</td>
            <td class="value">: {{ ucfirst(str_replace('_', ' ', $barang->product_type)) }}</td>
        </tr>
        <tr>
            <td class="label">Unit of Measure</td>
            <td class="value">: {{ $barang->unit_id }}</td>
        </tr>
        <tr>
            <td class="label">Registration Date</td>
            <td class="value">: {{ $barang->date ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Status</td>
            <td class="value">:
                @if ($barang->status == 1)
                    <span class="status-badge bg-success">Active</span>
                @elseif($barang->status == 2)
                    <span class="status-badge bg-warning">Inactive</span>
                @else
                    <span class="status-badge bg-danger">Deleted</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Description</td>
            <td class="value">: {{ $barang->keterangan ?? 'No description available' }}</td>
        </tr>
    </table>

    <div class="price-section">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    <div style="font-weight: bold; color: #7f8c8d; margin-bottom: 5px;">Stock Quantity</div>
                    <div style="font-size: 14pt;">{{ number_format($barang->quantity ?? 0) }}</div>
                </td>
                <td style="width: 50%; text-align: right;">
                    <div style="font-weight: bold; color: #7f8c8d; margin-bottom: 5px;">Unit Price</div>
                    <div style="font-size: 14pt;">${{ number_format($barang->price ?? 0, 2) }}</div>
                </td>
            </tr>
        </table>

        <div class="total-box">
            <hr style="border: 0; border-top: 1px solid #ddd;">
            <div>Total Inventory Value (Final Result)</div>
            <div class="total-amount">${{ number_format($barang->hasil_akhir ?? 0, 2) }}</div>
        </div>
    </div>

    <div class="footer">
        Printed on: {{ date('M d, Y H:i:s') }} | This is a system-generated document.
    </div>

</body>

</html>
