<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <title>Reset Password</title>
</head>

<body style="font-family: Arial, sans-serif; background:#f7f7f7; padding:30px;">
    @php
        // $path = public_path('assets/img/favicon/apple-touch-icon.png');
        $path = $logo;
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    @endphp
    <table width="100%" cellpadding="0" cellspacing="0"
        style="max-width:600px; margin:0 auto; background:white; border-radius:10px; padding:40px;">
        <tr>
            <td style="text-align:center;">
                <!-- LOGO -->
                <img src="{{ $base64 }}" alt="Logo" style="width:120px; margin-bottom:20px;">
            </td>
        </tr>

        <tr>
            <td>
                <h2 style="color:#333; text-align:center; margin-bottom:10px;">
                    Reset Password Akun Anda
                </h2>

                <p style="color:#555; font-size:15px; line-height:1.6;">
                    Halo {{ $user->fullname }},
                    <br><br>
                    Kami menerima permintaan untuk mereset password akun Anda.
                    Silakan klik tombol di bawah ini untuk melanjutkan proses reset password.
                </p>

                <div style="text-align:center; margin:30px 0;">
                    <!-- TOMBOL RESET -->
                    <a href="{{ $url }}"
                        style="background:#4F46E5; color:white; padding:12px 25px; text-decoration:none;
                              border-radius:6px; font-size:16px; display:inline-block;">
                        Reset Password
                    </a>
                </div>

                <p style="color:#777; font-size:14px; line-height:1.6;">
                    Link ini hanya berlaku selama <strong>60 menit</strong>.
                    Jika Anda tidak meminta reset password, cukup abaikan email ini.
                </p>

                <hr style="border:none; border-top:1px solid #e5e5e5; margin:30px 0;">

                <p style="color:#aaa; font-size:12px; text-align:center;">
                    &copy; {{ date('Y') }} — Sistem Anda<br>
                    Email ini dikirim otomatis, mohon tidak membalas.
                </p>
            </td>
        </tr>
    </table>

</body>

</html>
