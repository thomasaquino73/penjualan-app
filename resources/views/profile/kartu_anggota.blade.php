<!DOCTYPE html>
<html>

<head>

    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #8d8d8d;
        }

        .kartu {
            width: 85.6mm;
            height: 53.98mm;
            position: relative;
            overflow: hidden;
        }

        /* background */
        .bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 85.6mm;
            height: 53.98mm;
            object-fit: cover;
            z-index: 0;
        }

        /* logo kiri atas */
        .logo {
            position: absolute;
            top: 6px;
            left: 8px;
            width: 40px;
        }

        /* nama instansi kanan atas */
        .instansi {
            position: absolute;
            top: 8px;
            right: 10px;
            font-size: 12px;
            color: white;
        }

        /* kode barcode */
        .kode {
            position: absolute;
            top: 11mm;
            right: 10mm;
            font-size: 19px;
        }

        /* foto kanan */
        .foto {
            position: absolute;
            top: 18mm;
            right: 6mm;
            width: 14mm;
            height: 16mm;
            border-radius: 3px;
            object-fit: cover;
        }

        /* nama pemilik */
        .nama {
            position: absolute;
            top: 16mm;
            left: 8mm;
            right: 24mm;
            font-size: 15px;
            font-weight: bold;
            color: white;
            line-height: 1.2;
        }

        /* barcode */
        .barcode {
            position: absolute;
            bottom: 16mm;
            left: 8mm;
        }



        /* alamat */
        .alamat {
            position: absolute;
            bottom: 4mm;
            left: 8mm;
            right: 8mm;
            font-size: 8px;
            line-height: 1.2;
        }
    </style>

</head>

<body>

    <div class="kartu">

        <img src="{{ public_path('image/logo/backgroundkartu.png') }}" class="bg">
        <img src="{{ public_path($company->logo) }}" class="logo">

        <div class="instansi">
            {{ $company->nama_instansi }}<br>
            <small style="font-size:8px">Seksi Pelayanan Kematian</small>
        </div>

        <div class="kode">
            {{ $user->no_ID }}
        </div>

        <div class="nama ">
            {{ $user->nama_lengkap }}
        </div>

        <img src="{{ $user->avatar ? public_path($user->avatar) : public_path('image/no-images.jpg') }}" class="foto"
            width="">

        <div class="barcode">
            {!! DNS1D::getBarcodeHTML($user->no_ID, 'C128', 1.4, 35) !!}
        </div>

        <div class="alamat">
            {{ $company->alamat }}
        </div>

    </div>

</body>

</html>
