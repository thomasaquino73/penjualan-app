<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Nota {{ $model->store_sales_code }}</title>

    <style>
        @page {
            size: 80mm auto;
            margin: 5mm;
        }

        body {
            font-family: "Courier New", monospace;
            font-size: 10px;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 1px 0;
            vertical-align: top;
        }

        .item-name {
            font-weight: bold;
        }

        .small {
            font-size: 9px;
        }

        .total td {
            padding-top: 3px;
        }

        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 9px;
        }

        .header {
            text-align: center;
            margin-bottom: 5px;
        }

        .company-name {
            font-size: 15px;
            font-weight: bold;
        }

        .info td:first-child {
            width: 20%;
        }

        .watermark {
            position: fixed;
            top: 38%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            opacity: 0.12;
            z-index: -1000;
        }

        .watermark img {
            width: 220px;
            height: auto;
        }
    </style>
</head>

<body>
    {{-- WATERMARK --}}
    @if ($model->status == 'draft')
        <div class="watermark">
            <img src="{{ public_path('image/stamps/watermarkunpaid.png') }}">

        </div>
    @elseif ($model->status == 'paid')
        <div class="watermark">
            <img src="{{ public_path('image/stamps/watermarkblack.png') }}">
        </div>
    @endif
    {{-- HEADER --}}
    <header>
        <div class="header-content">
            @include('pdf.deliveryOrder.partials.header')
        </div>
    </header>


    <hr>

    {{-- INFO TRANSAKSI --}}
    <table class="info">
        <tr>
            <td>No</td>
            <td>: {{ $model->store_sales_code }}</td>
            <td>Tanggal</td>
            <td>: {{ date('d/m/Y H:i', strtotime($model->created_at)) }}</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>: {{ $model->creator->fullname ?? '-' }}</td>
            <td>Customer</td>
            <td>: {{ $model->customer_name ?? 'UMUM' }}</td>
        </tr>
        <tr>
            <td>Bayar</td>
            <td>: {{ strtoupper($model->payment_method) }}</td>
            <td>Ekspedisi</td>
            <td>: {{ strtoupper($model->shipping_method) }}</td>
        </tr>
    </table>

    <hr>

    {{-- ITEM --}}
    @foreach ($model->details as $detail)
        <div class="item-name">
            {{ $detail->produkID->nama_barang }}
        </div>

        <table>
            <tr>
                <td>
                    {{ number_format($detail->qty, 2, ',', '.') }}
                    x
                    {{ number_format($detail->unit_price, 0, ',', '.') }}

                    @if ($detail->discount > 0)
                        (-{{ number_format($detail->discount, 0, ',', '.') }})
                    @endif
                </td>

                <td class="right">
                    {{ number_format($detail->amount, 0, ',', '.') }}
                </td>
            </tr>
        </table>
    @endforeach

    <hr>

    {{-- TOTAL --}}
    <table>

        <tr>
            <td>Subtotal</td>
            <td class="right">
                {{ number_format($model->sub_total, 0, ',', '.') }}
            </td>
        </tr>

        <tr>
            <td>Diskon</td>
            <td class="right">
                {{ number_format($model->disc_nominal, 0, ',', '.') }}
            </td>
        </tr>

        @if ($model->tax_amount > 0)
            <tr>
                <td>PPN {{ $model->tax_percent }}%</td>
                <td class="right">
                    {{ number_format($model->tax_amount, 0, ',', '.') }}
                </td>
            </tr>
        @endif

        <tr class="bold">
            <td>TOTAL</td>
            <td class="right">
                {{ number_format($model->grand_total, 0, ',', '.') }}
            </td>
        </tr>
        @if ($model->payment_method == 'Cash')
            <tr>
                <td>Tunai</td>
                <td class="right">
                    {{ number_format($model->amount_receive, 0, ',', '.') }}
                </td>
            </tr>

            <tr>
                <td>Kembali</td>
                <td class="right">
                    {{ number_format($model->change, 0, ',', '.') }}
                </td>
            </tr>
        @elseif($model->payment_method == 'Transfer')
            <tr>
                <td>Transfer ke Bank</td>
                <td class="right">
                    {{ $model->bankID->bank_name }} ({{ $model->bankID->account_number }})
                </td>
            </tr>
        @else
        @endif


    </table>

    <hr>

    {{-- TERBILANG --}}
    @php
        $currencyId = session('currency_id') ?? \App\Models\Setting\Company::first()->default_currency_id;
        $currencyCode = \App\Models\Setting\Currency::find($currencyId)?->code ?? 'IDR';
        $grandTotalConvert = convert_currency($model->grand_total, $model->currency_id ?? 1);
    @endphp

    <div class="small">
        <strong>Terbilang :</strong><br>
        {{ terbilang($grandTotalConvert, $currencyCode) }}
    </div>

    @if ($model->notes)
        <hr>
        <div class="small">
            Catatan : {{ $model->notes }}
        </div>
    @endif

    <hr>

    {{-- FOOTER --}}
    <div class="footer">

        *** TERIMA KASIH *** <br>

        Telah Berbelanja<br>

        Barang yang sudah dibeli<br>
        tidak dapat dikembalikan<br><br>

        Simpan struk ini sebagai<br>
        bukti pembelian.

    </div>

</body>

</html>
