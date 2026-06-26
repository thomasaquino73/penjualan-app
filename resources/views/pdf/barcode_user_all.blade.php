<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Daftar Barcode User</title>
    <style>
        body {
            font-family: sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .barcode-box {
            text-align: center;
            padding: 10px;
            border: 1px solid #eee;
            width: 20%;
            /* 100% / 5 kolom = 20% */
        }

        .barcode-box img {
            width: 70px;
            height: 70px;
        }

        .user-name {
            font-size: 10px;
            font-weight: bold;
            margin-top: 5px;
        }

        .user-id {
            font-size: 9px;
            color: #666;
        }
    </style>
</head>

<body>

    <h2 style="text-align: center;">Daftar Barcode User</h2>

    <table>
        <tr>
            @foreach ($users as $index => $user)
                <td class="barcode-box">
                    <img src="data:image/png;base64,{{ $user->qr }}" alt="QR Code">
                    <div class="user-name">{{ $user->name }}</div>
                    <div class="user-id">{{ $user->no_ID }}</div>
                </td>

                {{-- Jika index + 1 habis dibagi 5, maka tutup baris dan buka baris baru --}}
                @if (($index + 1) % 5 == 0 && !$loop->last)
        </tr>
        <tr>
            @endif
            @endforeach
        </tr>
    </table>

</body>

</html>
