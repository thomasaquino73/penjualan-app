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
            padding-left:2px;
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
    </style>
</head>

<body>

    <h2>Product List</h2>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Product Code</th>
                <th>Product Name</th>
                <th>Price</th>
                <th>Product Type</th>
                <th>Inventory Type</th>
            </tr>
        </thead>
        <tbody>

            @foreach ($barangs as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->id_barang }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ number_format($item->price, 0, ',', '.') }}</td>
                    <td>{{ $item->product_type }}</td>
                    <td>{{ $item->typeID->detail }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
