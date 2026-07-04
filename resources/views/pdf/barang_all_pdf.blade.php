<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Product List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 1px solid #ddd;
        }

        /* default isi kiri */
        td {
            text-align: left;
            border: 1px solid #ddd;
            padding-left: 2px;
        }

        /* header semua tengah */
        th {
            background-color: #f4f4f4;
            text-align: center;
            border: 1px solid #ddd;
            /* tambahkan ini */
        }

        /* kolom No (1) tengah */
        th:nth-child(1),
        td:nth-child(1) {
            text-align: center;
        }

        /* kolom Price (4) tengah */
        th:nth-child(4),
        td:nth-child(4) {
            text-align: center;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        @page {
            margin: 25px 20px 40px 20px;
        }

        footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 20px;
            font-size: 9px;
            color: #555;
        }

        footer .left {
            float: left;
        }

        footer .right {
            float: right;
        }
    </style>
</head>

<body>

    <h1 style="text-align:center">
        Laporan Stock Barang
    </h1>

    <p style="text-align:center">
        Periode :
        {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }}
        s/d
        {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}
    </p>
    <h2>Product List</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Product Code</th>
                <th>Product Name</th>
                <th>Price</th>
                <th>Category</th>
                <th>Stock</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($barangs as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->id_barang }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ format_uang(convert_currency($item->primary_price, $item->currency_id ?? 1)) }}</td>
                    <td>{{ $item->kategoriID?->detail ?? '-' }}</td>
                    <td>
                        {{ number_format($item->report_stock, 0) }}
                        {{ $item->unitID?->detail ?? '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">No data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <footer>
        <div class="left">
            Printed on: {{ now()->format('d-m-Y H:i:s') }}
        </div>

        <div class="right">
            <script type="text/php">
            if (isset($pdf)) {
                $font = $fontMetrics->getFont("Arial", "normal");
                $pdf->page_text(
                    720,
                    575,
                    "Page {PAGE_NUM} of {PAGE_COUNT}",
                    $font,
                    9,
                    array(0,0,0)
                );
            }
        </script>
        </div>
    </footer>
</body>


</html>
