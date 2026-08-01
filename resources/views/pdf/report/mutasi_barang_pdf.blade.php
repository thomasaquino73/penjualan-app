<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
        }

        h2 {
            margin-bottom: 15px;
        }

        .header {
            margin-bottom: 15px;
        }

        .header table {
            width: 100%;
        }

        .header td {
            padding: 2px 4px;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
        }

        table.report th,
        table.report td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
        }

        table.report th {
            background: #efefef;
            text-align: center;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .green {
            color: green;
            font-weight: bold;
        }

        .red {
            color: red;
            font-weight: bold;
        }

        .bold {
            font-weight: bold;
        }
    </style>

</head>

<body>

    <h2>Laporan Mutasi Barang</h2>

    <div class="header">

        <table>
            <tr>
                <td width="120">Nama Barang</td>
                <td>: {{ $detail->nama_barang }}</td>
            </tr>

            <tr>
                <td>Periode</td>
                <td>
                    :
                    {{ Carbon\Carbon::parse($startDate)->format('d-m-Y') }}
                    s/d
                    {{ Carbon\Carbon::parse($endDate)->format('d-m-Y') }}
                </td>
            </tr>

            <tr>
                <td>Saldo Awal</td>
                <td>
                    :
                    {{ number_format($openingBalance, 0, ',', '.') }}
                    {{ $detail->unitID->detail }}
                </td>
            </tr>

        </table>

    </div>

    <table class="report">

        <thead>

            <tr>
                <th width="4%">No</th>
                <th width="10%">Date</th>
                <th width="15%">Document Number</th>
                <th width="14%">Document Type</th>
                <th>Description</th>
                <th width="8%">In</th>
                <th width="8%">Out</th>
                <th width="8%">Units</th>
                <th width="12%">Running Balance</th>
            </tr>

        </thead>

        <tbody>

            @forelse($mutations as $index => $stock)
                <tr>

                    <td class="center">
                        {{ $index + 1 }}
                    </td>

                    <td class="center">
                        {{ \Carbon\Carbon::parse($stock->date_stock)->format('d M Y') }}
                    </td>

                    <td>
                        {{ $stock->document_number ?: '-' }}
                    </td>

                    <td class="center">

                        @switch($stock->document_type)
                            @case('initial_stock')
                                Initial Stock
                            @break

                            @case('receive_item')
                                Receive Item
                            @break

                            @case('delivery_order')
                                Delivery Order
                            @break

                            @case('adjustment')
                                Adjustment
                            @break

                            @case('item_transfer')
                                Item Transfer
                            @break

                            @case('store_sales')
                                Store Sales
                            @break

                            @default
                                {{ ucwords(str_replace('_', ' ', $stock->document_type)) }}
                        @endswitch

                    </td>

                    <td>
                        {{ $stock->keterangan }}
                    </td>

                    <td class="right">

                        @if ($stock->type == 'in')
                            <span class="green">
                                {{ number_format($stock->qty_transaksi, 0, ',', '.') }}
                            </span>
                        @endif

                    </td>

                    <td class="right">

                        @if ($stock->type == 'out')
                            <span class="red">
                                {{ number_format($stock->qty_transaksi, 0, ',', '.') }}
                            </span>
                        @endif

                    </td>

                    <td class="center">

                        {{ $stock->unitID->detail ?? '-' }}

                    </td>

                    <td class="right bold">

                        {{ number_format($stock->saldo_akhir, 0, ',', '.') }}
                        {{ $detail->unitID->detail }}

                    </td>

                </tr>

                @empty

                    <tr>
                        <td colspan="9" class="center">
                            Tidak ada data mutasi.
                        </td>
                    </tr>
                @endforelse

            </tbody>

        </table>

    </body>

    </html>
