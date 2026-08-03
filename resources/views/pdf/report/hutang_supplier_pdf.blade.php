<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">

    <style>
        @page {
            size: A4 portrait;
            margin: 125px 30px 40px 30px;
        }

        body {
            font-family: DejaVu Sans;
            font-size: 10px;
            color: #000;
        }

        header {
            position: fixed;
            top: -105px;
            left: 0;
            right: 0;
            height: 90px;
            text-align: center;
        }

        footer {
            position: fixed;
            bottom: -25px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
        }

        .company {
            font-size: 18px;
            font-weight: bold;
        }

        .address {
            font-size: 10px;
        }

        .title {
            margin-top: 8px;
            font-size: 15px;
            font-weight: bold;
        }

        .periode {
            margin-top: 5px;
            font-size: 10px;
        }

        hr {
            border: 0;
            border-top: 2px solid #000;
            margin-top: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
        }

        th {
            background: #EAEAEA;
            font-weight: bold;
            text-align: center;
        }

        td {
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .supplier-title {
            margin-top: 15px;
            margin-bottom: 5px;
            font-size: 11px;
            font-weight: bold;
            background: #F2F2F2;
            padding: 5px;
            border: 1px solid #000;
        }

        .saldo {
            font-weight: bold;
            background: #F5F5F5;
        }

        .no-data {
            text-align: center;
            padding: 20px;
        }
    </style>

</head>

<body>

    <h2>{{ $company->company_name }}</h2>
    <p>{{ $company->address }}</p>

    <h3 align="center">
        LAPORAN KARTU HUTANG SUPPLIER
    </h3>

    <p>
        Periode :
        {{ date('d/m/Y', strtotime($start_date)) }}
        -
        {{ date('d/m/Y', strtotime($end_date)) }}
    </p>

    @forelse ($groups as $supplierId => $rows)
        <h4>
            Supplier :
            {{ optional($rows->first()->supplier)->nama_supplier }}
        </h4>

        <table>

            <thead>

                <tr>

                    <th width="30">No</th>

                    <th width="90">Tanggal</th>

                    <th>No Dokumen</th>

                    <th>Keterangan</th>

                    <th width="90">Debit</th>

                    <th width="90">Credit</th>

                    <th width="100">Saldo</th>

                </tr>

            </thead>

            <tbody>

                @php
                    $saldo = 0;
                @endphp

                @foreach ($rows as $row)
                    @php

                        /*
Hutang

Invoice = Credit (+)

Payment = Debit (-)
*/

                        $saldo += $row->credit;
                        $saldo -= $row->debit;

                    @endphp

                    <tr>

                        <td class="text-center">
                            {{ $loop->iteration }}
                        </td>

                        <td class="text-center">
                            {{ date('d/m/Y', strtotime($row->transaction_date)) }}
                        </td>

                        <td>
                            {{ $row->document_no }}
                        </td>

                        <td>
                            {{ $row->description }}
                        </td>

                        <td class="text-right">
                            {{ number_format($row->debit, 2, ',', '.') }}
                        </td>

                        <td class="text-right">
                            {{ number_format($row->credit, 2, ',', '.') }}
                        </td>

                        <td class="text-right">
                            {{ number_format($saldo, 2, ',', '.') }}
                        </td>

                    </tr>
                @endforeach

                <tr>

                    <th colspan="6" class="text-right">
                        Saldo Akhir
                    </th>

                    <th class="text-right">
                        {{ number_format($saldo, 2, ',', '.') }}
                    </th>

                </tr>

            </tbody>

        </table>
    @empty

        <table>

            <thead>

                <tr>

                    <th width="30">No</th>

                    <th width="90">Tanggal</th>

                    <th>No Dokumen</th>

                    <th>Keterangan</th>

                    <th width="90">Debit</th>

                    <th width="90">Credit</th>

                    <th width="100">Saldo</th>

                </tr>

            </thead>

            <tbody>

                <tr>

                    <td colspan="7" class="text-center">
                        Tidak ada data untuk ditampilkan.
                    </td>

                </tr>
            </tbody>
        </table>
    @endforelse

</body>

</html>
