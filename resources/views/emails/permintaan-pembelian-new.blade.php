<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Notifikasi Artikel Baru</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7fa;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            width: 100%;
            padding: 30px 0;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: #007bff;
            color: #fff;
            padding: 25px;
            text-align: center;
            font-size: 22px;
            font-weight: bold;
        }

        .content {
            padding: 30px 40px;
            color: #333;
            line-height: 1.6;
        }

        .button {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 25px;
            background: #007bff;
            color: #fff !important;
            text-decoration: none;
            font-weight: bold;
            border-radius: 8px;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #666;
            padding: 20px;
            border-top: 1px solid #eee;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="container">

            <!-- HEADER -->
            <div class="header">
                Notifikasi Purchase Requisition Baru
            </div>

            <!-- BODY -->
            <div class="content">
                <p>Halo <strong>{{ $user->fullname }}</strong>,</p>

                <p><strong>{{ $creator->fullname }}</strong> baru saja membuat purchase requisition baru:</p>

                <p style="font-size: 18px; font-weight: bold; color:#007bff;">
                    “{{ $purchaseRequisition->code }}”
                </p>

                <p>Silakan klik tombol di bawah untuk membuka detail purchase requisitionnya:</p>

                <p style="text-align:center;">
                    <a href="{{ $url }}" class="button">Lihat Pesanan</a>
                </p>

                <p>Jika Anda bukan bagian dari proses editorial, Anda dapat mengabaikan email ini.</p>

                <p>Terima kasih,<br>
                    <strong>Tim {{ $company->nama_perusahaan ?? config('app.name') }}</strong>
                </p>
            </div>

            <!-- FOOTER -->
            <div class="footer">
                Email ini dikirim otomatis oleh sistem .<br>
                © {{ date('Y') }} - {{ $company->nama_perusahaan ?? config('app.name') }}. All rights reserved.
            </div>

        </div>
    </div>
</body>

</html>
