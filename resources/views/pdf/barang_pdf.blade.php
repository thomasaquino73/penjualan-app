<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Detail Barang' }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
        }

        h2 {
            margin: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .box {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            font-size: 11px;
        }

        th {
            background: #f2f2f2;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
        }

        .subtitle {
            font-size: 12px;
            color: #666;
        }

        .badge {
            padding: 2px 6px;
            font-size: 10px;
            color: #fff;
            border-radius: 3px;
        }

        .green {
            background: #28a745;
        }

        .blue {
            background: #007bff;
        }

        .orange {
            background: #fd7e14;
        }

        .gray {
            background: #6c757d;
        }

        .red {
            background: #dc3545;
        }

        img {
            max-height: 120px;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="title">{{ $detail->nama_barang }}</div>
        <div class="subtitle">Kode: {{ $detail->id_barang }}</div>
    </div>
    {{-- ================= STOCK SUMMARY ================= --}}
    <div class="box">
        <h4>Stock Summary</h4>

        <table>
            <tr>
                <th width="30%">Cut Off Date</th>
                <td>
                    {{ $cutOffDate ? \Carbon\Carbon::parse($cutOffDate)->format('d-m-Y') : '-' }}
                </td>
            </tr>

            <tr>
                <th>Opening Balance</th>
                <td>
                    {{ number_format($openingBalance, 0) }}
                    {{ $detail->unitID->detail ?? '' }}
                </td>
            </tr>

            <tr>
                <th>Current Stock</th>
                <td>
                    {{ number_format($currentStock, 0) }}
                    {{ $detail->unitID->detail ?? '' }}
                </td>
            </tr>
        </table>
    </div>
    {{-- ================= HEADER INFO ================= --}}
    <div class="box">
        <table>
            <tr>
                <td width="20%">
                    <img src="{{ public_path($detail->photo_filename ?? 'image/no-images.jpg') }}">
                </td>
                <td>
                    <b>Type:</b> {{ ucfirst($detail->product_type) }} <br>
                    <b>Category:</b> {{ $detail->kategoriID->detail ?? '-' }} <br>
                    <b>Unit:</b> {{ $detail->unitID->detail ?? '-' }} <br>
                    <b>Min Stock:</b> {{ $detail->primary_minimum_stock ?? '-' }} <br>
                    <b>Price:</b>
                    {{ $detail->primary_price
                        ? format_uang(convert_currency($detail->primary_price, $detail->currency_id ?? 1))
                        : '-' }}
                </td>
                <td width="20%" style="text-align: center; vertical-align: middle;">
                    @if (!empty($detail->id_barang))
                        {{-- Milon Barcode langsung merender gambar --}}
                        <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($detail->id_barang, 'C128', 1.5, 40) }}"
                            alt="barcode" />
                        <br>
                        <span style="font-size: 10px;">{{ $detail->id_barang }}</span>
                    @else
                        <span style="color: grey;">-</span>
                    @endif
                </td>
            </tr>
        </table>

        <p>
            <b>Description:</b><br>
            {{ $detail->keterangan ?? '-' }}
        </p>
    </div>

    {{-- ================= UNIT CONVERSION ================= --}}
    <div class="box">
        <h4>Unit Conversion</h4>

        <table>
            <tr>
                <th>No</th>
                <th>From</th>
                <th class="text-center">=</th>
                <th>Result</th>
            </tr>

            @forelse($unitConversion as $i => $conv)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>1 {{ $conv->fromUnitID->detail ?? '-' }}</td>
                    <td class="text-center">=</td>
                    <td>
                        {{ number_format($conv->qty, 0) }}
                        {{ $conv->toUnitID->detail ?? '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No conversion data</td>
                </tr>
            @endforelse
        </table>
    </div>

    {{-- ================= VARIANTS ================= --}}
    <div class="box">
        <h4>Variants</h4>

        <table>
            <tr>
                <th>No</th>
                <th>Variant</th>
                <th>Specifications</th>
            </tr>

            @forelse($detail->variants as $i => $variant)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $variant->variant_name }}</td>
                    <td>
                        @if (!empty($variant->specifications))
                            @foreach ($variant->specifications as $k => $v)
                                {{ $k }}: {{ $v }}<br>
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">No variant</td>
                </tr>
            @endforelse
        </table>
    </div>

    {{-- ================= STOCK HISTORY ================= --}}
    <div class="box">
        <h4>Stock Movement History</h4>

        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="12%">Date</th>
                    <th width="12%">Document</th>
                    <th width="12%">Type</th>
                    <th>Description</th>
                    <th width="10%" class="text-right">In</th>
                    <th width="10%" class="text-right">Out</th>
                    <th width="12%" class="text-right">Balance</th>
                </tr>
            </thead>

            <tbody>

                {{-- OPENING BALANCE --}}
                <tr style="background:#f3f3f3;font-weight:bold;">
                    <td>-</td>
                    <td>
                        {{ $cutOffDate ? \Carbon\Carbon::parse($cutOffDate)->format('d-m-Y') : '-' }}
                    </td>
                    <td>CUT-OFF</td>
                    <td>OPENING</td>
                    <td>Opening Balance</td>
                    <td class="text-right">-</td>
                    <td class="text-right">-</td>
                    <td class="text-right">
                        {{ number_format($openingBalance, 0) }}
                    </td>
                </tr>

                @forelse($mutations as $i => $stock)
                    <tr>
                        <td>{{ $i + 1 }}</td>

                        <td>
                            {{ $stock->date_stock ? \Carbon\Carbon::parse($stock->date_stock)->format('d-m-Y') : '-' }}
                        </td>

                        <td>
                            {{ $stock->document_number ?? '-' }}
                        </td>

                        <td>
                            {{ strtoupper($stock->document_type ?? '-') }}
                        </td>

                        <td>
                            {{ $stock->keterangan ?? '-' }}
                        </td>

                        <td class="text-right">
                            @if ($stock->type == 'in')
                                {{ number_format($stock->qty_transaksi, 0) }}
                            @else
                                -
                            @endif
                        </td>

                        <td class="text-right">
                            @if ($stock->type == 'out')
                                {{ number_format($stock->qty_transaksi, 0) }}
                            @else
                                -
                            @endif
                        </td>

                        <td class="text-right">
                            {{ number_format($stock->saldo_akhir, 0) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">
                            No stock movement after cut off date
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </table>
    </div>

    {{-- ================= WAREHOUSE ================= --}}
    <div class="box">
        <h4>Warehouse Stock</h4>

        <table>
            <thead>
                <tr>
                    <th width="70%">Warehouse</th>
                    <th width="30%" class="text-right">Qty</th>
                </tr>
            </thead>

            <tbody>

                @php
                    $grandTotal = 0;
                @endphp

                @forelse($warehouseHistory as $wh)
                    @php
                        $grandTotal += $wh['total_qty'];
                    @endphp

                    <tr>
                        <td>{{ $wh['warehouse_name'] ?? '-' }}</td>

                        <td class="text-right">
                            {{ number_format($wh['total_qty'] ?? 0, 0) }}
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="2" class="text-center">
                            No warehouse stock data
                        </td>
                    </tr>
                @endforelse

                <tr style="font-weight:bold;background:#f3f3f3;">
                    <td>TOTAL</td>
                    <td class="text-right">
                        {{ number_format($grandTotal, 0) }}
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</body>

</html>
