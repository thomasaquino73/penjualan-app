<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Sales Invoice - {{ $model->sales_invoice_code }}</title>
    @include('pdf.partials.css')
</head>

<body>

    @include('pdf.partials.header_logo')

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
                    {!! nl2br(e($model->address)) !!}
                </div>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 20px;">
                <div class="po-box-title">Sales Invoice</div>
                <table style="width:100%;">
                    <tr>
                        <td width="70">Nomor</td>
                        <td width="10">:</td>
                        <td>{{ $model->sales_invoice_code }}</td>
                    </tr>
                    <tr>
                        <td width="70">Tanggal</td>
                        <td width="10">:</td>
                        <td>{{ date('d M Y', strtotime($model->sales_invoice_date)) }}</td>
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
                            {{ $company->approval_name }}
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
