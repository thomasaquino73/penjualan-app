<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email Anda</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f6f8fa;
            margin: 0;
            padding: 0;
        }

        .email-wrapper {
            width: 100%;
            padding: 40px 0;
            background-color: #f6f8fa;
        }

        .email-content {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        .email-header {
            text-align: center;
            background-color: #007bff;
            padding: 24px;
        }

        .email-header img {
            max-width: 120px;
            height: auto;
            margin-bottom: 10px;
        }

        .email-header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }

        .email-body {
            padding: 32px 40px;
            color: #333333;
            line-height: 1.6;
        }

        .email-body h2 {
            color: #007bff;
            margin-bottom: 16px;
        }

        .verify-button {
            display: inline-block;
            margin: 24px 0;
            padding: 14px 28px;
            background-color: #007bff;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .verify-button:hover {
            background-color: #0056b3;
        }

        .footer {
            text-align: center;
            font-size: 13px;
            color: #777;
            padding: 24px;
            border-top: 1px solid #e9ecef;
        }

        @media (max-width: 600px) {
            .email-body {
                padding: 24px;
            }

            .verify-button {
                display: block;
                text-align: center;
                width: 100%;
            }
        }
    </style>
</head>
@php
    $path = public_path('image/no-images.jpg');

    if (file_exists($path)) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    } else {
        $base64 = '';
    }
@endphp

<body>
    <div class="email-wrapper">
        <div class="email-content">
            <div class="email-header">
                <img src="{{ $base64 }}" alt="Company Logo">
                <h1>Verifikasi Email Anda</h1>
            </div>

            <div class="email-body">
                <h2>Halo, {{ $user->fullname ?? 'Pengguna' }} 👋</h2>
                <p>Terima kasih telah mendaftar di <strong>Berita Jakarta</strong>!
                    Untuk mengaktifkan akun Anda, silakan klik tombol di bawah ini:</p>

                <p style="text-align:center;">
                    <a href="{{ $url ?? '#' }}" class="verify-button">Verifikasi Email Saya</a>
                </p>

                <p>Jika Anda tidak mendaftar di aplikasi kami, abaikan saja email ini.
                    Tautan ini akan kedaluwarsa dalam waktu 24 jam demi keamanan akun Anda.</p>

                <p>Salam hangat,<br>
                    <strong>Tim IT BJID</strong>
                </p>
            </div>

            <div class="footer">
                &copy; {{ date('Y') }} IT BJID. Seluruh hak cipta dilindungi.
                <br>
                Email ini dikirim secara otomatis, mohon untuk tidak membalas.
            </div>
        </div>
    </div>
</body>

</html>
